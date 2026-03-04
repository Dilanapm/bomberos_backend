<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // Limpiar vistas Blade compiladas para evitar errores de rutas cacheadas
        // (ej. route('logout') no definida en ciertos órdenes de prueba)
        $this->artisan('view:clear');
    }
}
