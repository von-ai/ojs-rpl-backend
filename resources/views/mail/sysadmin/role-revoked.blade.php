<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Pemberitahuan Pencabutan Hak Akses System Administrator</title>
    <style>
        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; line-height: 1.6; color: #1f2937; margin: 0; padding: 24px; background-color: #f9fafb; }
        .card { max-width: 600px; margin: 0 auto; background: #ffffff; border-radius: 12px; border: 1px solid #e5e7eb; padding: 28px; box-shadow: 0 1px 3px rgba(0,0,0,0.05); }
        .header { border-bottom: 2px solid #b91c1c; padding-bottom: 16px; margin-bottom: 20px; }
        .header h1 { color: #b91c1c; font-size: 20px; margin: 0 0 6px 0; }
        .badge { display: inline-block; padding: 4px 10px; font-size: 11px; font-weight: 700; border-radius: 6px; background-color: #fee2e2; color: #991b1b; border: 1px solid #fecaca; }
        .meta-table { width: 100%; border-collapse: collapse; margin: 18px 0; font-size: 13px; }
        .meta-table td { padding: 8px 12px; border-bottom: 1px solid #f3f4f6; }
        .meta-table td.label { font-weight: 600; color: #4b5563; width: 35%; }
        .footer { margin-top: 24px; font-size: 11px; color: #9ca3af; text-align: center; border-top: 1px solid #f3f4f6; padding-top: 14px; }
    </style>
</head>
<body>
    <div class="card">
        <div class="header">
            <h1>Pencabutan Hak Akses System Administrator</h1>
            <span class="badge">Perubahan Hak Akses</span>
        </div>

        <p>Yth. {{ $user->name }},</p>
        <p>Kami memberitahukan bahwa hak akses Anda sebagai <strong>System Administrator</strong> pada platform SIRIDA BRIDA Kota Makassar telah dicabut{{ $revokedBy ? ' oleh ' . $revokedBy->name : '' }}.</p>

        <table class="meta-table">
            <tr>
                <td class="label">Nama Pengguna:</td>
                <td><strong>{{ $user->name }}</strong></td>
            </tr>
            <tr>
                <td class="label">Alamat Email:</td>
                <td>{{ $user->email }}</td>
            </tr>
            <tr>
                <td class="label">Status:</td>
                <td><strong style="color: #b91c1c;">Hak Akses System Administrator Dinonaktifkan</strong></td>
            </tr>
        </table>

        <p>Peran reguler Anda lainnya pada jurnal (jika ada) tetap berjalan seperti biasa. Apabila Anda merasa terjadi kekeliruan, silakan hubungi tim administrator BRIDA Kota Makassar.</p>

        <div class="footer">
            Sistem Informasi Riset dan Inovasi Daerah (SIRIDA) Kota Makassar &bull; Email otomatis sistem.
        </div>
    </div>
</body>
</html>
