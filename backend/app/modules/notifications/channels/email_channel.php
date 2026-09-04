<?php

/**
 * MASAR - Email Notification Channel
 *
 * Responsible for delivering notifications
 * through email.
 *
 * Dispatcher
 *     ↓
 * EmailChannel
 *     ↓
 * Mailer / Application Mail Service
 */


/*
|--------------------------------------------------------------------------
| Email Notification Channel
|--------------------------------------------------------------------------
*/

class EmailChannel
{
    /*
    |--------------------------------------------------------------------------
    | Send
    |--------------------------------------------------------------------------
    */

    public function send(
        array $notification,
        array $options = []
    ): bool|array {

        $payload =
            $this->normalize(
                $notification,
                $options
            );


        if (
            $payload === false
        ) {

            return false;
        }


        /*
         * Prefer an application mailer when
         * one is already available.
         */

        $result =
            $this->sendThroughMailer(
                $payload
            );


        if (
            $result !== null
        ) {

            return $result;
        }


        /*
         * Function-based mail implementation.
         */

        $result =
            $this->sendThroughFunction(
                $payload
            );


        if (
            $result !== null
        ) {

            return $result;
        }


        /*
         * Native PHP mail fallback.
         */

        return $this->sendThroughNativeMail(
            $payload
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Handle Alias
    |--------------------------------------------------------------------------
    */

    public function handle(
        array $notification,
        array $options = []
    ): bool|array {

        return $this->send(
            $notification,
            $options
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Deliver Alias
    |--------------------------------------------------------------------------
    */

    public function deliver(
        array $notification,
        array $options = []
    ): bool|array {

        return $this->send(
            $notification,
            $options
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Normalize
    |--------------------------------------------------------------------------
    */

    protected function normalize(
        array $notification,
        array $options = []
    ): array|false {

        $email =
            trim(
                (string) (
                    $notification['email']
                    ?? $notification['user_email']
                    ?? $options['email']
                    ?? ''
                )
            );


        /*
         * Email may also be supplied as recipient.
         */

        if (
            $email === '' &&
            isset(
                $notification['recipient']
            )
        ) {

            $email =
                trim(
                    (string)
                    $notification['recipient']
                );
        }


        if (
            $email === '' ||
            !filter_var(
                $email,
                FILTER_VALIDATE_EMAIL
            )
        ) {

            return false;
        }


        $name =
            trim(
                (string) (
                    $notification['user_name']
                    ?? $notification['recipient_name']
                    ?? $options['name']
                    ?? ''
                )
            );


        $subject =
            trim(
                (string) (
                    $notification['email_subject']
                    ?? $notification['subject']
                    ?? $notification['title']
                    ?? 'Notification'
                )
            );


        if ($subject === '') {
            $subject = 'Notification';
        }


        $body =
            trim(
                (string) (
                    $notification['email_body']
                    ?? $notification['body']
                    ?? ''
                )
            );


        if ($body === '') {

            $body =
                $subject;
        }


        $html =
            $notification['html']
            ?? $options['html']
            ?? false;


        return [
            'email' =>
                $email,

            'name' =>
                $name,

            'subject' =>
                $subject,

            'body' =>
                $body,

            'html' =>
                (bool) $html,

            'from' =>
                $notification['from']
                ?? $options['from']
                ?? null,

            'reply_to' =>
                $notification['reply_to']
                ?? $options['reply_to']
                ?? null,

            'notification' =>
                $notification
        ];
    }


    /*
    |--------------------------------------------------------------------------
    | Send Through Application Mailer
    |--------------------------------------------------------------------------
    */

    protected function sendThroughMailer(
        array $payload
    ): bool|array|null {

        /*
         * Mailer class.
         */

        if (
            class_exists(
                'Mailer'
            )
        ) {

            try {

                $mailer =
                    new Mailer();


                if (
                    method_exists(
                        $mailer,
                        'send'
                    )
                ) {

                    return
                        $mailer->send(
                            $payload['email'],
                            $payload['subject'],
                            $payload['body'],
                            [
                                'name' =>
                                    $payload['name'],

                                'html' =>
                                    $payload['html'],

                                'from' =>
                                    $payload['from'],

                                'reply_to' =>
                                    $payload['reply_to']
                            ]
                        );
                }

            } catch (Throwable $e) {

                return false;
            }
        }


        /*
         * EmailService class.
         */

        if (
            class_exists(
                'EmailService'
            )
        ) {

            try {

                $service =
                    new EmailService();


                if (
                    method_exists(
                        $service,
                        'send'
                    )
                ) {

                    return
                        $service->send(
                            $payload['email'],
                            $payload['subject'],
                            $payload['body'],
                            [
                                'name' =>
                                    $payload['name'],

                                'html' =>
                                    $payload['html'],

                                'from' =>
                                    $payload['from'],

                                'reply_to' =>
                                    $payload['reply_to']
                            ]
                        );
                }

            } catch (Throwable $e) {

                return false;
            }
        }


        return null;
    }


    /*
    |--------------------------------------------------------------------------
    | Send Through Function
    |--------------------------------------------------------------------------
    */

    protected function sendThroughFunction(
        array $payload
    ): bool|array|null {

        if (
            function_exists(
                'send_email'
            )
        ) {

            try {

                return
                    send_email(
                        $payload['email'],
                        $payload['subject'],
                        $payload['body'],
                        [
                            'name' =>
                                $payload['name'],

                            'html' =>
                                $payload['html'],

                            'from' =>
                                $payload['from'],

                            'reply_to' =>
                                $payload['reply_to']
                        ]
                    );

            } catch (Throwable $e) {

                return false;
            }
        }


        if (
            function_exists(
                'mail_send'
            )
        ) {

            try {

                return
                    mail_send(
                        $payload['email'],
                        $payload['subject'],
                        $payload['body'],
                        [
                            'name' =>
                                $payload['name'],

                            'html' =>
                                $payload['html'],

                            'from' =>
                                $payload['from'],

                            'reply_to' =>
                                $payload['reply_to']
                        ]
                    );

            } catch (Throwable $e) {

                return false;
            }
        }


        return null;
    }


    /*
    |--------------------------------------------------------------------------
    | Native PHP Mail
    |--------------------------------------------------------------------------
    */

    protected function sendThroughNativeMail(
        array $payload
    ): bool {

        $headers = [];


        /*
         * MIME header.
         */

        if (
            $payload['html']
        ) {

            $headers[] =
                'MIME-Version: 1.0';

            $headers[] =
                'Content-Type: text/html; charset=UTF-8';

        } else {

            $headers[] =
                'Content-Type: text/plain; charset=UTF-8';
        }


        /*
         * From.
         */

        if (
            !empty(
                $payload['from']
            )
        ) {

            $from =
                trim(
                    (string)
                    $payload['from']
                );


            if (
                filter_var(
                    $from,
                    FILTER_VALIDATE_EMAIL
                )
            ) {

                $headers[] =
                    'From: ' .
                    $from;
            }
        }


        /*
         * Reply-To.
         */

        if (
            !empty(
                $payload['reply_to']
            )
        ) {

            $reply_to =
                trim(
                    (string)
                    $payload['reply_to']
                );


            if (
                filter_var(
                    $reply_to,
                    FILTER_VALIDATE_EMAIL
                )
            ) {

                $headers[] =
                    'Reply-To: ' .
                    $reply_to;
            }
        }


        $header_string =
            implode(
                "\r\n",
                $headers
            );


        try {

            return (bool)
                mail(
                    $payload['email'],
                    $payload['subject'],
                    $payload['body'],
                    $header_string
                );

        } catch (Throwable $e) {

            return false;
        }
    }


    /*
    |--------------------------------------------------------------------------
    | Is Available
    |--------------------------------------------------------------------------
    */

    public function isAvailable(): bool
    {
        return
            class_exists('Mailer') ||
            class_exists('EmailService') ||
            function_exists('send_email') ||
            function_exists('mail_send') ||
            function_exists('mail');
    }


    /*
    |--------------------------------------------------------------------------
    | Channel Name
    |--------------------------------------------------------------------------
    */

    public function name(): string
    {
        return 'email';
    }
}


/*
|--------------------------------------------------------------------------
| Function-Based Compatibility API
|--------------------------------------------------------------------------
*/

function email_channel_send(
    array $notification,
    array $options = []
): bool|array {

    return
        (new EmailChannel())
            ->send(
                $notification,
                $options
            );
}


function email_channel_handle(
    array $notification,
    array $options = []
): bool|array {

    return
        (new EmailChannel())
            ->handle(
                $notification,
                $options
            );
}


function email_channel_deliver(
    array $notification,
    array $options = []
): bool|array {

    return
        (new EmailChannel())
            ->deliver(
                $notification,
                $options
            );
}
