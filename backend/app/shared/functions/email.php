<?php

/**
 * MASAR - Email Functions
 *
 * Shared helpers for email validation, normalization,
 * headers, and safe message preparation.
 */

require_once __DIR__ . '/email_templates.php';

/*
|--------------------------------------------------------------------------
| Email Validation
|--------------------------------------------------------------------------
*/

function is_valid_email(
    mixed $email
): bool {
    if (!is_string($email)) {
        return false;
    }

    $email = trim($email);

    if ($email === '') {
        return false;
    }

    return filter_var(
        $email,
        FILTER_VALIDATE_EMAIL
    ) !== false;
}

/*
|--------------------------------------------------------------------------
| Email Normalization
|--------------------------------------------------------------------------
*/

function normalize_email(
    mixed $email
): ?string {
    if (!is_string($email)) {
        return null;
    }

    $email = trim($email);

    if (!is_valid_email($email)) {
        return null;
    }

    return strtolower($email);
}

/*
|--------------------------------------------------------------------------
| Email Domain
|--------------------------------------------------------------------------
*/

function email_domain(
    mixed $email
): ?string {
    $email = normalize_email($email);

    if ($email === null) {
        return null;
    }

    $parts = explode(
        '@',
        $email,
        2
    );

    return $parts[1] ?? null;
}

/*
|--------------------------------------------------------------------------
| Email Username
|--------------------------------------------------------------------------
*/

function email_username(
    mixed $email
): ?string {
    $email = normalize_email($email);

    if ($email === null) {
        return null;
    }

    $parts = explode(
        '@',
        $email,
        2
    );

    return $parts[0] ?? null;
}

/*
|--------------------------------------------------------------------------
| Domain Validation
|--------------------------------------------------------------------------
*/

function is_valid_email_domain(
    mixed $email
): bool {
    $domain = email_domain($email);

    if ($domain === null) {
        return false;
    }

    return filter_var(
        $domain,
        FILTER_VALIDATE_DOMAIN,
        FILTER_FLAG_HOSTNAME
    ) !== false;
}

/*
|--------------------------------------------------------------------------
| Multiple Emails
|--------------------------------------------------------------------------
*/

function normalize_emails(
    mixed $emails
): array {
    if (!is_array($emails)) {
        return [];
    }

    $normalized = [];

    foreach ($emails as $email) {
        $email = normalize_email($email);

        if ($email !== null) {
            $normalized[] = $email;
        }
    }

    return array_values(
        array_unique($normalized)
    );
}

/*
|--------------------------------------------------------------------------
| Email List Parsing
|--------------------------------------------------------------------------
*/

function parse_email_list(
    mixed $emails
): array {
    if (is_array($emails)) {
        return normalize_emails($emails);
    }

    if (!is_string($emails)) {
        return [];
    }

    $emails = trim($emails);

    if ($emails === '') {
        return [];
    }

    $items = preg_split(
        '/[,;]+/',
        $emails
    );

    return normalize_emails($items ?: []);
}

/*
|--------------------------------------------------------------------------
| Header Encoding
|--------------------------------------------------------------------------
*/

function encode_email_header(
    mixed $value
): string {
    if (!is_string($value)) {
        return '';
    }

    $value = trim($value);

    if ($value === '') {
        return '';
    }

    if (preg_match('/[\x80-\xFF]/', $value)) {
        return '=?UTF-8?B?' .
            base64_encode($value) .
            '?=';
    }

    return $value;
}

/*
|--------------------------------------------------------------------------
| Email Subject
|--------------------------------------------------------------------------
*/

function normalize_email_subject(
    mixed $subject
): string {
    if (!is_string($subject)) {
        return '';
    }

    $subject = trim($subject);

    /*
     * Prevent header injection.
     */
    $subject = str_replace(
        ["\r", "\n"],
        '',
        $subject
    );

    return encode_email_header($subject);
}

/*
|--------------------------------------------------------------------------
| Email Name
|--------------------------------------------------------------------------
*/

function normalize_email_name(
    mixed $name
): string {
    if (!is_string($name)) {
        return '';
    }

    $name = trim($name);

    /*
     * Prevent header injection.
     */
    return str_replace(
        ["\r", "\n"],
        '',
        $name
    );
}

/*
|--------------------------------------------------------------------------
| Sender Formatting
|--------------------------------------------------------------------------
*/

function format_email_sender(
    mixed $email,
    mixed $name = null
): ?string {
    $email = normalize_email($email);

    if ($email === null) {
        return null;
    }

    $name = normalize_email_name($name);

    if ($name === '') {
        return $email;
    }

    return
        encode_email_header($name) .
        ' <' .
        $email .
        '>';
}

/*
|--------------------------------------------------------------------------
| Recipient Formatting
|--------------------------------------------------------------------------
*/

function format_email_recipient(
    mixed $email,
    mixed $name = null
): ?string {
    return format_email_sender(
        $email,
        $name
    );
}

/*
|--------------------------------------------------------------------------
| Header Sanitization
|--------------------------------------------------------------------------
*/

function sanitize_email_header(
    mixed $header
): string {
    if (!is_string($header)) {
        return '';
    }

    return str_replace(
        ["\r", "\n"],
        '',
        trim($header)
    );
}

/*
|--------------------------------------------------------------------------
| Reply-To
|--------------------------------------------------------------------------
*/

function format_reply_to(
    mixed $email,
    mixed $name = null
): ?string {
    return format_email_sender(
        $email,
        $name
    );
}

/*
|--------------------------------------------------------------------------
| Message Body
|--------------------------------------------------------------------------
*/

function normalize_email_body(
    mixed $body
): string {
    if (!is_string($body)) {
        return '';
    }

    return trim($body);
}

/*
|--------------------------------------------------------------------------
| Plain Text Body
|--------------------------------------------------------------------------
*/

function normalize_plain_email_body(
    mixed $body
): string {
    $body = normalize_email_body($body);

    if ($body === '') {
        return '';
    }

    return strip_tags($body);
}

/*
|--------------------------------------------------------------------------
| HTML To Plain Text
|--------------------------------------------------------------------------
*/

function html_to_plain_text(
    mixed $html
): string {
    if (!is_string($html)) {
        return '';
    }

    $html = str_replace(
        ["\r", "\n"],
        ' ',
        $html
    );

    /*
     * Add line breaks around block-level and
     * line-break elements.
     */
    $html = preg_replace('/<br\s*\/?\s*>/i', "\n", $html) ?? $html;
    $html = preg_replace('/<\/(p|div|li|tr|h[1-6]|table|ul|ol|blockquote)>/i', "\n", $html) ?? $html;

    $text = strip_tags($html);
    $text = html_entity_decode(
        $text,
        ENT_QUOTES | ENT_HTML5,
        'UTF-8'
    );

    $text = preg_replace('/[ \t]+/', ' ', $text) ?? $text;
    $text = preg_replace('/ ?\n ?/', "\n", $text) ?? $text;
    $text = preg_replace('/\n{3,}/', "\n\n", $text) ?? $text;

    return trim($text);
}

/*
|--------------------------------------------------------------------------
| HTML Body
|--------------------------------------------------------------------------
*/

function normalize_html_email_body(
    mixed $body
): string {
    if (!is_string($body)) {
        return '';
    }

    return trim($body);
}

/*
|--------------------------------------------------------------------------
| Email Headers
|--------------------------------------------------------------------------
*/

function build_email_headers(
    mixed $from = null,
    mixed $from_name = null,
    mixed $reply_to = null,
    mixed $reply_to_name = null,
    mixed $cc = null,
    mixed $bcc = null
): array {
    $headers = [
        'MIME-Version: 1.0',
        'Content-Type: text/html; charset=UTF-8'
    ];

    if ($from !== null) {
        $sender = format_email_sender(
            $from,
            $from_name
        );

        if ($sender !== null) {
            $headers[] = 'From: ' . $sender;
        }
    }

    if ($reply_to !== null) {
        $reply = format_reply_to(
            $reply_to,
            $reply_to_name
        );

        if ($reply !== null) {
            $headers[] = 'Reply-To: ' . $reply;
        }
    }

    $cc_list = parse_email_list($cc);

    if ($cc_list !== []) {
        $headers[] =
            'Cc: ' .
            implode(', ', $cc_list);
    }

    $bcc_list = parse_email_list($bcc);

    if ($bcc_list !== []) {
        $headers[] =
            'Bcc: ' .
            implode(', ', $bcc_list);
    }

    $headers[] = 'X-Mailer: MASAR';

    return $headers;
}

/*
|--------------------------------------------------------------------------
| Header String
|--------------------------------------------------------------------------
*/

function email_headers_to_string(
    mixed $headers
): string {
    if (!is_array($headers)) {
        return '';
    }

    $headers = array_filter(
        array_map(
            'sanitize_email_header',
            $headers
        ),
        static fn ($header) =>
            $header !== ''
    );

    return implode(
        "\r\n",
        $headers
    );
}

/*
|--------------------------------------------------------------------------
| Mail Recipient Validation
|--------------------------------------------------------------------------
*/

function validate_email_recipient(
    mixed $email
): ?string {
    return normalize_email($email);
}

/*
|--------------------------------------------------------------------------
| Email Configuration
|--------------------------------------------------------------------------
*/

function get_default_email_headers(
    mixed $from = null,
    mixed $from_name = null
): array {
    return build_email_headers(
        $from,
        $from_name
    );
}

/*
|--------------------------------------------------------------------------
| Email Payload
|--------------------------------------------------------------------------
*/

function build_email_payload(
    mixed $to,
    mixed $subject,
    mixed $body,
    mixed $from = null,
    mixed $from_name = null,
    mixed $reply_to = null,
    mixed $reply_to_name = null
): ?array {
    $recipient = normalize_email($to);

    if ($recipient === null) {
        return null;
    }

    $subject =
        normalize_email_subject($subject);

    $body =
        normalize_html_email_body($body);

    if ($subject === '' || $body === '') {
        return null;
    }

    return [
        'to' => $recipient,
        'subject' => $subject,
        'body' => $body,
        'headers' => build_email_headers(
            $from,
            $from_name,
            $reply_to,
            $reply_to_name
        )
    ];
}

/*
|--------------------------------------------------------------------------
| Safe Mail Parameters
|--------------------------------------------------------------------------
*/

function sanitize_mail_parameters(
    mixed $parameters
): string {
    if (!is_string($parameters)) {
        return '';
    }

    /*
     * Prevent command/header injection through
     * PHP mail() additional parameters.
     */
    return str_replace(
        ["\r", "\n"],
        '',
        $parameters
    );
}

if (!function_exists('send_email')) {
    function send_email(
        string $to,
        string $subject,
        string $body,
        array $options = []
    ): bool {
        $recipient = normalize_email($to);

        if ($recipient === null) {
            if (function_exists('logger_security')) {
                logger_security('email_send_failed', [
                    'reason' => 'Invalid recipient email.',
                ]);
            }

            return false;
        }

        $autoload = __DIR__ . '/../../../vendor/autoload.php';

        if (file_exists($autoload)) {
            require_once $autoload;
        }

        if (!class_exists('\PHPMailer\PHPMailer\PHPMailer')) {
            if (function_exists('logger_security')) {
                logger_security('email_send_failed', [
                    'reason' => 'PHPMailer is not available.',
                    'recipient_domain' => email_domain($recipient),
                ]);
            }

            return false;
        }

        $mail_config = require __DIR__ . '/../../config/mail.php';

        $from_email = $options['from'] ?? $mail_config['from']['address'] ?? 'no-reply@masar.local';
        $from_name = $options['name'] ?? $mail_config['from']['name'] ?? 'MASAR';
        $reply_to = $options['reply_to'] ?? $mail_config['reply_to']['address'] ?? null;
        $reply_to_name = $options['reply_to_name'] ?? $mail_config['reply_to']['name'] ?? $from_name;
        $is_html = !empty($options['html']);

        $text_body = $body;

        if (isset($options['text']) && is_string($options['text']) && trim($options['text']) !== '') {
            $text_body = trim($options['text']);
        } elseif ($is_html) {
            $text_body = html_to_plain_text($body);
        }

        try {
            $mail = new \PHPMailer\PHPMailer\PHPMailer(true);
            $driver = strtolower($mail_config['driver'] ?? 'smtp');

            if ($driver === 'smtp') {
                $mail->isSMTP();
                $mail->Host = $mail_config['host'] ?? 'smtp.gmail.com';
                $mail->Port = (int) ($mail_config['port'] ?? 587);
                $mail->SMTPAuth = true;
                $mail->Username = $mail_config['username'] ?? '';
                $mail->Password = $mail_config['password'] ?? '';
                $mail->SMTPSecure = $mail_config['encryption'] ?? 'tls';
                $mail->SMTPAutoTLS = true;
            } elseif ($driver === 'sendmail') {
                $mail->isSendmail();
            } else {
                $mail->isMail();
            }

            /*
             |--------------------------------------------------------------------------
             | Message Quality Settings
             |--------------------------------------------------------------------------
             |
             | A proper UTF-8 charset and base64 encoding keep the message
             | readable for clients that do not handle 8-bit bodies, and make
             | sure non-ASCII content (e.g. Arabic names) is labelled correctly.
             |
             | The Message-ID host is derived from the sending domain so the
             | generated identifier does not fall back to "localhost".
             |
             */

            $mail->CharSet = 'UTF-8';
            $mail->Encoding = 'base64';
            $mail->XMailer = 'MASAR';

            if (is_valid_email_domain($from_email)) {
                $from_domain = email_domain($from_email);
                if (is_string($from_domain) && $from_domain !== '') {
                    $mail->Hostname = $from_domain;
                }
            }

            $mail->setFrom($from_email, $from_name);
            $mail->Sender = $from_email;
            $mail->addAddress($recipient);

            if (is_string($reply_to) && filter_var($reply_to, FILTER_VALIDATE_EMAIL)) {
                $mail->addReplyTo($reply_to, $reply_to_name);
            }

            $mail->Subject = sanitize_email_header($subject);

            if ($is_html) {
                $mail->isHTML(true);
                $mail->Body = normalize_email_body($body);
                $mail->AltBody = $text_body;
            } else {
                $mail->isHTML(false);
                $mail->Body = $text_body;
                $mail->AltBody = '';
            }

            $sent = $mail->send();

            if (function_exists('logger_security')) {
                logger_security(
                    $sent ? 'email_smtp_accepted' : 'email_smtp_rejected',
                    [
                        'recipient_domain' => email_domain($recipient),
                        'message_id' => $mail->getLastMessageID(),
                        'error_info' => $mail->ErrorInfo,
                    ]
                );
            }

            return $sent;
        } catch (Throwable $e) {
            if (function_exists('logger_security')) {
                logger_security('email_send_failed', [
                    'reason' => get_class($e),
                    'message' => $e->getMessage(),
                    'error_info' => isset($mail) ? $mail->ErrorInfo : '',
                    'recipient_domain' => email_domain($recipient),
                    'mail_host' => $mail_config['host'] ?? null,
                    'mail_port' => $mail_config['port'] ?? null,
                ]);
            }

            return false;
        }
    }
}

if (!function_exists('mail_send')) {
    function mail_send(
        string $to,
        string $subject,
        string $body,
        array $options = []
    ): bool {
        return send_email($to, $subject, $body, $options);
    }
}
