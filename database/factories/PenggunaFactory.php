<?php

namespace Database\Factories;

use App\Enums\Sistem\PeranPengguna;
use App\Models\Pengguna;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;

/**
 * @extends Factory<Pengguna>
 */
class PenggunaFactory extends Factory
{
    protected $model = Pengguna::class;

    protected static ?string $password;

    /**
     * Bawaan Super Administrator agar test modul mana pun bisa membuka halamannya; pembatasan peran
     * diuji lewat state administrator().
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'nama' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'password' => static::$password ??= Hash::make('password'),
            'peran' => PeranPengguna::SuperAdministrator,
        ];
    }

    public function administrator(): static
    {
        return $this->state(['peran' => PeranPengguna::Administrator]);
    }
}
