<?php

require_once __DIR__ . '/../../shared/functions/security.php';

function middleware_security_headers(): void
{
    security_apply_http_headers();
}
