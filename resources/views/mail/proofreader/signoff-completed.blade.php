<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Persetujuan Bukti Baca (Sign-off) Selesai</title>
    <style>
        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; line-height: 1.6; color: #1f2937; margin: 0; padding: 24px; background-color: #f9fafb; }
        .card { max-width: 600px; margin: 0 auto; background: #ffffff; border-radius: 12px; border: 1px solid #e5e7eb; padding: 28px; box-shadow: 0 1px 3px rgba(0,0,0,0.05); }
        .header { border-bottom: 2px solid #059669; padding-bottom: 16px; margin-bottom: 20px; }
        .header h1 { color: #059669; font-size: 20px; margin: 0 0 6px 0; }
        .badge { display: inline-block; padding: 4px 10px; font-size: 11px; font-weight: 700; border-radius: 6px; background-color: #ecfdf5; color: #047857; border: 1px solid #a7f3d0; }
        .meta-table { width: 100%; border-collapse: collapse; margin: 18px 0; font-size: 13px; }
        .meta-table td { padding: 8px 12px; border-bottom: 1px solid #f3f4f6; }
        .meta-table td.label { font-weight: 600; color: #4b5563; width: 35%; }
        .btn { display: inline-block; padding: 10px 20px; font-size: 13px; font-weight: 600; color: #ffffff !important; background-color: #059669; text-decoration: none; border-radius: 8px; margin-top: 14px; }
        .footer { margin-top: 24px; font-size: 11px; color: #9ca3af; text-align: center; border-top: 1px solid #f3f4f6; padding-top: 14px; }
    </style>
</head>
<body>
    <div class="card">
        <div class="header">
            <h1>Persetujuan Bukti Baca (Sign-off) Selesai</h1>
            <span class="badge">Naskah {{ $submission->submission_number }}</span>
        </div>

        <p>Yth. Dewan Redaksi &amp; Layout Editor,</p>
        <p>Pemeriksaan bukti baca (proofreading) telah selesai secara menyeluruh. Galai publikasi telah disetujui (sign-off) oleh Proofreader dan siap diteruskan untuk persetujuan akhir Pemimpin Redaksi (Editor in Chief).</p>

        <table class="meta-table">
            <tr>
                <td class="label">Nomor Naskah:</td>
                <td><strong>{{ $submission->submission_number }}</strong></td>
            </tr>
            <tr>
                <td class="label">Judul Naskah:</td>
                <td>{{ $submission->title }}</td>
            </tr>
            <tr>
                <td class="label">Versi Galai:</td>
                <td>{{ $galley->label }} (v{{ $galley->version }})</td>
            </tr>
            <tr>
                <td class="label">Proofreader:</td>
                <td>{{ $proofreader->name }}</td>
            </tr>
            <tr>
                <td class="label">Status Naskah:</td>
                <td><strong style="color: #059669;">Disetujui Proofreader (Sign-off Lengkap)</strong></td>
            </tr>
        </table>

        <p style="text-align: center;">
            <a href="{{ $workspaceUrl }}" class="btn">Lanjutkan ke Persetujuan Akhir</a>
        </p>

        <div class="footer">
            Sistem Informasi Riset dan Inovasi Daerah (SIRIDA) Kota Makassar &bull; Email otomatis, mohon tidak membalas langsung.
        </div>
    </div>
</body>
</html>
