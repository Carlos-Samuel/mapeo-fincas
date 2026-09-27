<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Carga la finca demo. El usuario administrador se crea aparte con:
     *   php artisan make:filament-user
     */
    public function run(): void
    {
        $this->call([DemoSeeder::class, DemoRutasSeeder::class]);
    }
}
