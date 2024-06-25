<?php

namespace XCoorp\PassportControl\Tests;

use PHPUnit\Framework\TestCase as BaseTestCase;
use XCoorp\PassportControl\PassportControlServiceProvider;

require_once __DIR__.'/helper/config.php';

abstract class TestCase extends BaseTestCase
{
    public static array $config = [];

    protected function setUp(): void
    {
        parent::setUp();
        static::$config = ([
            'passcontrol.introspection_url' => 'http://fake.introspection.url/oauth/introspect',
            'passcontrol.public_key_path' => __DIR__.'/../storage/oauth-public.key',
        ]);
    }

    public function getPackageProviders($app): array
    {
        return [
            PassportControlServiceProvider::class,
        ];
    }
}
