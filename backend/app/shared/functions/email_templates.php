<?php

/**
 * MASAR - Email Templates
 *
 * Shared builders for professional transactional
 * email messages (HTML + plain-text versions).
 *
 * These helpers produce clean, minimal, valid
 * email-safe HTML with a single primary action
 * and a matching plain-text alternative.
 */

/*
|--------------------------------------------------------------------------
| Escape HTML
|--------------------------------------------------------------------------
*/

function email_template_escape(
    mixed $value
): string {
    return htmlspecialchars(
        (string) ($value ?? ''),
        ENT_QUOTES,
        'UTF-8'
    );
}

/*
|--------------------------------------------------------------------------
| Safe Action URL
|--------------------------------------------------------------------------
*/

function email_template_url(
    mixed $value
): string {
    $url = trim((string) ($value ?? ''));

    if ($url === '' || filter_var($url, FILTER_VALIDATE_URL) === false) {
        return '';
    }

    return $url;
}

/*
|--------------------------------------------------------------------------
| Application URL
|--------------------------------------------------------------------------
*/

function email_template_app_url(): string {
    return rtrim(
        (string) (getenv('APP_URL') ?: 'http://localhost'),
        '/'
    );
}

/*
|--------------------------------------------------------------------------
| Primary Action Button
|--------------------------------------------------------------------------
*/

function email_template_button(
    mixed $label,
    mixed $url
): string {
    $url = email_template_url($url);

    if ($url === '') {
        return '';
    }

    return
        '<table role="presentation" cellpadding="0" cellspacing="0" border="0" style="margin:24px 0;">' .
        '<tr>' .
        '<td align="center" style="border-radius:6px;">' .
        '<a href="' . email_template_escape($url) . '" target="_blank" style="display:inline-block;background-color:#1d4ed8;color:#ffffff;font-size:16px;font-weight:600;text-decoration:none;padding:12px 28px;border-radius:6px;">' .
        email_template_escape($label) .
        '</a>' .
        '</td>' .
        '</tr>' .
        '</table>';
}

/*
|--------------------------------------------------------------------------
| Email Document Layout
|--------------------------------------------------------------------------
*/

function email_template_layout(
    mixed $preheader,
    mixed $content_html
): string {
    $year = date('Y');

    return
        '<!DOCTYPE html>' .
        '<html lang="en" xmlns="http://www.w3.org/1999/xhtml">' .
        '<head>' .
        '<meta charset="utf-8">' .
        '<meta name="viewport" content="width=device-width, initial-scale=1.0">' .
        '<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">' .
        '<title>MASAR</title>' .
        '</head>' .
        '<body style="margin:0;padding:0;background-color:#f4f5f7;font-family:Arial,Helvetica,sans-serif;-webkit-text-size-adjust:100%;-ms-text-size-adjust:100%;">' .
        (trim((string) $preheader) !== ''
            ? '<div style="display:none;max-height:0;overflow:hidden;mso-hide:all;">' .
                email_template_escape($preheader) .
                '</div>'
            : '') .
        '<table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="background-color:#f4f5f7;">' .
        '<tr>' .
        '<td align="center" style="padding:24px 16px;">' .
        '<table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="max-width:600px;background-color:#ffffff;border-radius:8px;overflow:hidden;">' .
        '<tr>' .
        '<td align="center" style="background-color:#1d4ed8;padding:28px 24px;">' .
        '<span style="color:#ffffff;font-size:28px;font-weight:700;letter-spacing:1px;">MASAR</span>' .
        '</td>' .
        '</tr>' .
        '<tr>' .
        '<td style="padding:32px 32px 24px 32px;color:#1f2937;font-size:16px;line-height:1.6;">' .
        (string) $content_html .
        '</td>' .
        '</tr>' .
        '<tr>' .
        '<td style="padding:20px 32px;background-color:#f9fafb;border-top:1px solid #e5e7eb;color:#6b7280;font-size:13px;line-height:1.5;">' .
        '<p style="margin:0 0 8px 0;">You are receiving this email because an account was created on MASAR. If you did not create this account, you can safely ignore this email.</p>' .
        '<p style="margin:0;">&copy; ' . $year . ' MASAR. All rights reserved.</p>' .
        '</td>' .
        '</tr>' .
        '</table>' .
        '</td>' .
        '</tr>' .
        '</table>' .
        '</body>' .
        '</html>';
}

/*
|--------------------------------------------------------------------------
| Welcome Message
|--------------------------------------------------------------------------
*/

function email_build_welcome_message(
    mixed $full_name
): array {
    $first_name = trim((string) ($full_name ?? '')) !== ''
        ? trim((string) $full_name)
        : 'MASAR Student';

    $greeting = trim(explode(' ', $first_name, 2)[0] ?? $first_name);

    $app_url = email_template_app_url();
    $action_url = email_template_url($app_url);

    $subject = 'Welcome to MASAR 🎉';

    $content =
        '<p style="margin:0 0 16px 0;">Hi ' . email_template_escape($greeting) . ',</p>' .
        '<p style="margin:0 0 16px 0;">Welcome to MASAR! 🎉</p>' .
        '<p style="margin:0 0 16px 0;">Your student account has been successfully created.</p>' .
        '<p style="margin:0 0 16px 0;">MASAR helps students discover training and internship opportunities, connect with companies, and build a documented professional profile.</p>' .
        '<p style="margin:0 0 16px 0;">We recommend completing your profile so you can get the most out of MASAR.</p>';

    if ($action_url !== '') {
        $content .=
            email_template_button('Go to MASAR', $action_url) .
            '<p style="margin:0 0 16px 0;color:#6b7280;font-size:14px;">If the button above does not work, copy and paste this link into your browser:</p>' .
            '<p style="margin:0 0 24px 0;color:#6b7280;font-size:14px;word-break:break-all;">' .
            '<a href="' . email_template_escape($action_url) . '" style="color:#1d4ed8;">' . email_template_escape($action_url) . '</a>' .
            '</p>';
    }

    $content .=
        '<p style="margin:0 0 4px 0;">Best regards,</p>' .
        '<p style="margin:0;">MASAR Team</p>';

    $html = email_template_layout($subject, $content);

    $text =
        'Hi ' . $greeting . ',' . "\n\n" .
        'Welcome to MASAR! 🎉' . "\n\n" .
        'Your student account has been successfully created.' . "\n\n" .
        'MASAR helps students discover training and internship opportunities, connect with companies, and build a documented professional profile.' . "\n\n" .
        'We recommend completing your profile so you can get the most out of MASAR.' . "\n\n" .
        ($action_url !== '' ? 'Get started: ' . $action_url . "\n\n" : '') .
        'If you did not create this account, you can safely ignore this email.' . "\n\n" .
        'Best regards,' . "\n" .
        'MASAR Team';

    return [
        'subject' => $subject,
        'html' => $html,
        'text' => $text,
        'action_url' => $action_url,
    ];
}
