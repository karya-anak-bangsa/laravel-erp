<?php

namespace Database\Factories\Sistem;

use App\Enums\Sistem\AksiAktivitas;
use App\Models\Pengguna;
use App\Models\Sistem\LogAktivitas;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<LogAktivitas>
 */
class LogAktivitasFactory extends Factory
{
    protected $model = LogAktivitas::class;

    /**
     * Bawaan: pengguna masuk ke panel admin (tanpa data). Log perubahan data diisi test sendiri atau ditulis
     * sungguhan lewat form admin.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'id_pengguna' => Pengguna::factory(),
            'aksi' => AksiAktivitas::Masuk,
            'alamat_ip' => fake()->ipv4(),
        ];
    }
}
