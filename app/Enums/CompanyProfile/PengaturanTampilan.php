<?php

namespace App\Enums\CompanyProfile;

use App\Enums\Concerns\MenyediakanOpsi;

/**
 * Pilihan di form template (pilihan pemilik 2026-10-14). Default mengunci isian warna sampai lebar konten dan
 * mengisinya dengan nilai bawaan config/tema.php. Tidak disimpan sebagai kolom: template dianggap Default bila
 * semua nilai tampilannya sama dengan nilai bawaan jenisnya (TemaService::pengaturan()).
 */
enum PengaturanTampilan: string
{
    use MenyediakanOpsi;

    case Bawaan = 'bawaan';
    case Kustom = 'kustom';

    public function label(): string
    {
        return match ($this) {
            self::Bawaan => 'Default',
            self::Kustom => 'Custom',
        };
    }
}
