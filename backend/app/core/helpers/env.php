<?php

function env(string $key, $default = null): mixed
{
    $value = getenv($key);

    if ($value === false) {
        if (array_key_exists($key, $_ENV)) {
            return $_ENV[$key];
        }

        if (array_key_exists($key, $_SERVER)) {
            return $_SERVER[$key];
        }

        return $default;
    }

    return $value;
}
