<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SystemSetting extends Model
{
    use HasFactory, HasUlids;

    protected $table = 'system_settings';

    protected $fillable = [
        'key',
        'value',
        'type',
        'group',
    ];

    /**
     * Nilai default konfigurasi platform SIRIDA.
     *
     * @return array<string, mixed>
     */
    public static function defaults(): array
    {
        return [
            'site_name' => 'SIRIDA — Portal Ilmiah BRIDA Kota Makassar',
            'site_description' => 'Sistem Informasi Riset dan Inovasi Daerah Kota Makassar — Repositori dan penerbitan berkala ilmiah terpadu Badan Riset dan Inovasi Daerah.',
            'maintenance_mode' => false,
            'maintenance_message' => 'Sistem sedang dalam proses pemeliharaan rutin server. Silakan coba beberapa saat lagi.',
            'max_upload_size_mb' => 32,
            'allowed_file_extensions' => 'pdf, doc, docx, odt, jpg, jpeg, png, csv, html',
            'session_lifetime_minutes' => 120,
        ];
    }

    /**
     * Mendapatkan nilai pengaturan berdasarkan kunci.
     */
    public static function getValue(string $key, mixed $default = null): mixed
    {
        $setting = static::where('key', $key)->first();

        if ($setting === null) {
            return $default ?? (static::defaults()[$key] ?? null);
        }

        return match ($setting->type) {
            'boolean' => filter_var($setting->value, FILTER_VALIDATE_BOOLEAN),
            'integer' => (int) $setting->value,
            'json' => json_decode((string) $setting->value, true),
            default => $setting->value,
        };
    }

    /**
     * Menyimpan atau memperbarui nilai pengaturan platform.
     */
    public static function setValue(string $key, mixed $value, string $type = 'string', string $group = 'general'): self
    {
        $storedValue = match ($type) {
            'boolean' => $value ? '1' : '0',
            'integer' => (string) ((int) $value),
            'json' => is_string($value) ? $value : json_encode($value),
            default => (string) $value,
        };

        return static::updateOrCreate(
            ['key' => $key],
            [
                'value' => $storedValue,
                'type' => $type,
                'group' => $group,
            ]
        );
    }

    /**
     * Mengambil seluruh data pengaturan dalam format array terstruktur untuk ViewModel/UI.
     *
     * @return array{
     *     site_name: string,
     *     site_description: string,
     *     maintenance_mode: bool,
     *     maintenance_message: string,
     *     max_upload_size_mb: int,
     *     allowed_file_extensions: string,
     *     session_lifetime_minutes: int
     * }
     */
    public static function getAllSettings(): array
    {
        $defaults = static::defaults();

        return [
            'site_name' => (string) static::getValue('site_name', $defaults['site_name']),
            'site_description' => (string) static::getValue('site_description', $defaults['site_description']),
            'maintenance_mode' => (bool) static::getValue('maintenance_mode', $defaults['maintenance_mode']),
            'maintenance_message' => (string) static::getValue('maintenance_message', $defaults['maintenance_message']),
            'max_upload_size_mb' => (int) static::getValue('max_upload_size_mb', $defaults['max_upload_size_mb']),
            'allowed_file_extensions' => (string) static::getValue('allowed_file_extensions', $defaults['allowed_file_extensions']),
            'session_lifetime_minutes' => (int) static::getValue('session_lifetime_minutes', $defaults['session_lifetime_minutes']),
        ];
    }
}
