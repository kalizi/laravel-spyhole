<?php

namespace Kalizi\LaravelSpyhole\Tests;

use Kalizi\LaravelSpyhole\LaravelSpyholeServiceProvider;

class TestCase extends \Orchestra\Testbench\TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->artisan('migrate');
    }


    protected function getPackageProviders($app): array
    {
        return [LaravelSpyholeServiceProvider::class];
    }
}
