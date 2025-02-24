<?php

use XCoorp\PassportControl\Tests\TestCase;

if (! function_exists('config')) {
    function config(string $key): mixed
    {
        return TestCase::$config[$key] ?? null;
    }
}
