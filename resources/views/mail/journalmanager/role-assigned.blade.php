<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Pemberitahuan Penetapan Peran Jurnal</title>
    <style>
        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; line-height: 1.6; color: #1f2937; margin: 0; padding: 24px; background-color: #f9fafb; }
        .card { max-width: 600px; margin: 0 auto; background: #ffffff; border-radius: 12px; border: 1px solid #e5e7eb; padding: 28px; box-shadow: 0 1px 3px rgba(0,0,0,0.05); }
        .header { border-bottom: 2px solid #800000; padding-bottom: 16px; margin-bottom: 20px; }
        .header h1 { color: #800000; font-size: 20px; margin: 0 0 6px 0; }
        .badge { display: inline-block; padding: 4px 10px; font-size: 11px; font-weight: 700; border-radius: 6px; background-color: #f3e8ff; color: #6b21a8; border: 1px solid #e9d5ff; }
        .meta-table { width: 100%; border-collapse: collapse; margin: 18px 0; font-size: 13px; }
        .meta-table td { padding: 8px 12px; border-bottom: 1px solid #f3f4f6; }
        .meta-table td.label { font-weight: 600; color: #4b5563; width: 35%; }
        .btn { display: inline-block; padding: 10px 20px; font-size: 13px; font-weight: 600; color: #ffffff !important; background-color: #800000; text-decoration: none; border-radius: 8px; margin-top: 14px; }
        .footer { margin-top: 24px; font-size: 11px; color: #9ca3af; text-align: center; border-top: 1px solid #f3f4f6; padding-top: 14px; }
    </style>
</head>
<body>
    <div class="card">
        <div class="header">
            <h1>Penetapan Peran Jurnal Ilmiah</h1>
            <span class="badge">{{ $roleLabel }}</span>
        </div>

        <p>Yth. {{ $user->name }},</p>
        <p>Journal Manager telah menetapkan peran resmi baru kepada akun Anda dalam pengelolaan portal jurnal ilmiah SIRIDA BRIDA Kota Makassar.</p>

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
                <td class="label">Peran yang Ditetapkan:</td>
                <td><strong style="color: #800000;">{{ $roleLabel }}</strong></td>
            </tr>
            @if(!empty($userRole->expertise_keywords))
            <tr>
                <td class="label">Bidang Kepakaran:</td>
                <td>{{ implode(', ', (array)$userRole->expertise_keywords) }}</td>
            </tr>
            @endif
        </table>

        <p>Silakan masuk ke sistem untuk mulai menjalankan tugas sesuai wewenang peran Anda:</p>
        <p style="text-align: center;">
            <a href="{{ $loginUrl }}" class="btn">Masuk ke Portal SIRIDA</a>
        </p>

        <div class="footer">
            Sistem Informasi Riset dan Inovasi Daerah (SIRIDA) Kota Makassar &bull; Email otomatis sistem.
        </div>
    </div>
</body>
</html>
