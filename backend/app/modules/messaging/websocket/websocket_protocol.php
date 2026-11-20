<?php

/**
 * MASAR - Minimal RFC 6455 WebSocket protocol helpers
 *
 * Implemented with PHP core stream functions only. No ext-sockets, pcntl or
 * third-party library is required, which keeps the server runnable under the
 * stock Laragon Windows PHP CLI.
 */

define('WS_GUID', '258EAFA5-E914-47DA-95CA-C5AB0DC85B11');

define('WS_OP_CONTINUATION', 0x0);
define('WS_OP_TEXT', 0x1);
define('WS_OP_BINARY', 0x2);
define('WS_OP_CLOSE', 0x8);
define('WS_OP_PING', 0x9);
define('WS_OP_PONG', 0xA);


/*
|--------------------------------------------------------------------------
| HTTP Upgrade
|--------------------------------------------------------------------------
*/

function ws_read_http_request($socket, int $timeout_seconds = 5): ?array
{
    stream_set_blocking($socket, true);
    stream_set_timeout($socket, $timeout_seconds);

    $raw = '';

    while (strpos($raw, "\r\n\r\n") === false) {

        $chunk =
            fread(
                $socket,
                4096
            );


        if ($chunk === false || $chunk === '') {
            break;
        }

        $raw .= $chunk;


        if (strlen($raw) > 16384) {
            break;
        }
    }

    stream_set_blocking($socket, false);

    if (strpos($raw, "\r\n\r\n") === false) {
        return null;
    }

    [$head] =
        explode(
            "\r\n\r\n",
            $raw,
            2
        );

    $lines =
        explode(
            "\r\n",
            $head
        );

    $request_line =
        array_shift($lines);

    $headers = [];

    foreach ($lines as $line) {

        $parts =
            explode(
                ':',
                $line,
                2
            );


        if (count($parts) !== 2) {
            continue;
        }

        $headers[
            strtolower(trim($parts[0]))
        ] =
            trim($parts[1]);
    }

    return [
        'request_line' => (string) $request_line,
        'headers'      => $headers,
    ];
}


function ws_handshake_response(string $key): string
{
    $accept =
        base64_encode(
            sha1(
                $key . WS_GUID,
                true
            )
        );

    return
        "HTTP/1.1 101 Switching Protocols\r\n"
        . "Upgrade: websocket\r\n"
        . "Connection: Upgrade\r\n"
        . "Sec-WebSocket-Accept: " . $accept . "\r\n\r\n";
}


function ws_http_response(
    int $status,
    string $reason,
    array $payload = [],
    array $extra_headers = []
): string {

    $body =
        json_encode(
            $payload,
            JSON_UNESCAPED_UNICODE |
            JSON_UNESCAPED_SLASHES
        ) ?: '{}';

    $response =
        "HTTP/1.1 {$status} {$reason}\r\n"
        . 'Content-Type: application/json; charset=utf-8' . "\r\n"
        . 'Content-Length: ' . strlen($body) . "\r\n"
        . 'Connection: close' . "\r\n";


    foreach ($extra_headers as $header) {
        $response .= $header . "\r\n";
    }

    return $response . "\r\n" . $body;
}


/*
|--------------------------------------------------------------------------
| Frame Encoding
|--------------------------------------------------------------------------
*/

function ws_frame_encode(
    string $payload,
    int $opcode = WS_OP_TEXT,
    bool $fin = true
): string {

    $first =
        ($fin ? 0x80 : 0x00) |
        ($opcode & 0x0F);

    $length =
        strlen($payload);


    if ($length < 126) {

        $header =
            chr($first) .
            chr($length);

    } elseif ($length <= 65535) {

        $header =
            chr($first) .
            chr(126) .
            pack('n', $length);

    } else {

        $header =
            chr($first) .
            chr(127) .
            pack('NN', 0, $length);
    }


    return $header . $payload;
}


function ws_frame_text(string $json): string
{
    return ws_frame_encode($json, WS_OP_TEXT);
}


function ws_frame_ping(string $payload = ''): string
{
    return ws_frame_encode($payload, WS_OP_PING);
}


function ws_frame_pong(string $payload = ''): string
{
    return ws_frame_encode($payload, WS_OP_PONG);
}


function ws_frame_close(
    int $code = 1000,
    string $reason = ''
): string {

    return
        ws_frame_encode(
            pack('n', $code) . $reason,
            WS_OP_CLOSE
        );
}


/*
|--------------------------------------------------------------------------
| Frame Decoding
|--------------------------------------------------------------------------
|
| Client frames are always masked. Incomplete frames stay in $buffer for the
| next read. A protocol violation throws and the caller closes the socket.
|
*/

function ws_decode_frames(
    string &$buffer,
    int $max_payload = 16384
): array {

    $frames = [];


    while (strlen($buffer) >= 2) {

        $first =
            ord($buffer[0]);

        $second =
            ord($buffer[1]);


        if (($first & 0x70) !== 0) {
            throw new RuntimeException('Invalid WebSocket RSV bits.');
        }


        $fin =
            ($first & 0x80) !== 0;

        $opcode =
            $first & 0x0F;

        $masked =
            ($second & 0x80) !== 0;

        $length =
            $second & 0x7F;

        $offset = 2;


        if ($length === 126) {

            if (strlen($buffer) < 4) {
                break;
            }

            $length =
                unpack('n', substr($buffer, 2, 2))[1];

            $offset = 4;

        } elseif ($length === 127) {

            if (strlen($buffer) < 10) {
                break;
            }

            $high =
                unpack('N', substr($buffer, 2, 4))[1];

            $low =
                unpack('N', substr($buffer, 6, 4))[1];


            if ($high !== 0) {
                throw new RuntimeException('WebSocket frame too large.');
            }

            $length = $low;
            $offset = 10;
        }


        if ($length > $max_payload) {
            throw new RuntimeException('WebSocket payload too large.');
        }


        if ($masked) {

            if (strlen($buffer) < $offset + 4 + $length) {
                break;
            }

            $mask =
                substr($buffer, $offset, 4);

            $data =
                substr($buffer, $offset + 4, $length);

            $payload = '';


            for ($i = 0; $i < $length; $i++) {
                $payload .= $data[$i] ^ $mask[$i % 4];
            }

            $offset += 4 + $length;

        } else {

            if (strlen($buffer) < $offset + $length) {
                break;
            }

            $payload =
                substr($buffer, $offset, $length);

            $offset += $length;
        }


        $buffer =
            substr($buffer, $offset);

        $frames[] = [
            'fin'     => $fin,
            'opcode'  => $opcode,
            'payload' => $payload,
        ];
    }


    return $frames;
}
