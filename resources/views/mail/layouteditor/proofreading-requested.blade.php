<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Permohonan Proofreading Galai</title>
    <style>
        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; line-height: 1.6; color: #1f2937; margin: 0; padding: 24px; background-color: #f9fafb; }
        .card { max-width: 600px; margin: 0 auto; background: #ffffff; border-radius: 12px; border: 1px solid #e5e7eb; padding: 28px; box-shadow: 0 1px 3px rgba(0,0,0,0.05); }
        .header { border-bottom: 2px solid #800000; padding-bottom: 16px; margin-bottom: 20px; }
        .header h1 { color: #800000; font-size: 20px; margin: 0 0 6px 0; }
        .badge { display: inline-block; padding: 4px 10px; font-size: 11px; font-weight: 700; border-radius: 6px; background-color: #eff6ff; color: #1d4ed8; border: 1px solid #bfdbfe; }
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
            <h1>Permohonan Pemeriksaan Galai (Proofreading)</h1>
            <span class="badge">Naskah {{ $submission->submission_number }}</span>
        </div>

        <p>Yth. Mitra Bestari / Proofreader,</p>
        <p>Berkas draf tata letak galai publikasi baru telah selesai disusun dan diserahterimakan untuk pemeriksaan kebahasaan, ketepatan ejaan, tipografi, serta kesesuaian format tabel dan gambar.</p>

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
                <td class="label">Batas Waktu Tinjauan:</td>
                <td><strong style="color: #b91c1c;">{{ $dueAt }}</strong></td>
            </tr>
            @if($assignment->message)
            <tr>
                <td class="label">Catatan Redaksi:</td>
                <td><em>"{{ $assignment->message }}"</em></td>
            </tr>
            @endif
        </table>

        <p>Silakan akses portal jurnal untuk mengunduh berkas galai dan memasukkan catatan koreksi bila diperlukan:</p>
        <p style="text-align: center;">
            <a href="{{ $workspaceUrl }}" class="btn">Buka Lembar Kerja Proofreading</a>
        </p>

        <div class="footer">
            Sistem Informasi Riset dan Inovasi Daerah (SIRIDA) Kota Makassar &bull; Email otomatis, mohon tidak membalas langsung.
        </div>
    </div>
</body>
</html>
