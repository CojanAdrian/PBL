<?php

namespace Tests;

use App\Models\Lang;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Lang::insert([
            ['name' => 'English', 'code' => 'en', 'active' => true],
            ['name' => 'Română', 'code' => 'ro', 'active' => true],
        ]);
    }
}
