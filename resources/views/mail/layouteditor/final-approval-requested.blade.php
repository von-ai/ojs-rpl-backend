<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Permohonan Persetujuan Akhir Galai EIC</title>
    <style>
        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; line-height: 1.6; color: #1f2937; margin: 0; padding: 24px; background-color: #f9fafb; }
        .card { max-width: 600px; margin: 0 auto; background: #ffffff; border-radius: 12px; border: 1px solid #e5e7eb; padding: 28px; box-shadow: 0 1px 3px rgba(0,0,0,0.05); }
        .header { border-bottom: 2px solid #800000; padding-bottom: 16px; margin-bottom: 20px; }
        .header h1 { color: #800000; font-size: 20px; margin: 0 0 6px 0; }
        .badge { display: inline-block; padding: 4px 10px; font-size: 11px; font-weight: 700; border-radius: 6px; background-color: #ecfdf5; color: #047857; border: 1px solid #a7f3d0; }
        .meta-table { width: 100%; border-collapse: collapse; margin: 18px 0; font-size: 13px; }
        .meta-table td { padding: 8px 12px; border-bottom: 1px solid #f3f4f6; }
        .meta-table td.label { font-weight: 600; color: #4b5563; width: 35%; }
        .checksum-box { font-family: monospace; font-size: 11px; background-color: #f3f4f6; padding: 8px 12px; border-radius: 6px; word-break: break-all; color: #374151; margin-top: 4px; }
        .btn { display: inline-block; padding: 10px 20px; font-size: 13px; font-weight: 600; color: #ffffff !important; background-color: #800000; text-decoration: none; border-radius: 8px; margin-top: 14px; }
        .footer { margin-top: 24px; font-size: 11px; color: #9ca3af; text-align: center; border-top: 1px solid #f3f4f6; padding-top: 14px; }
    </style>
</head>
<body>
    <div class="card">
        <div class="header">
            <h1>Permohonan Persetujuan Akhir Galai Publikasi</h1>
            <span class="badge">Siap Terbit &bull; {{ $submission->submission_number }}</span>
        </div>

        <p>Yth. Pemimpin Redaksi (*Editor in Chief*),</p>
        <p>Seluruh putaran tata letak, pemeriksaan mutu proofreading, dan penyelesaian catatan koreksi untuk naskah berikut telah rampung. Galai final diajukan untuk otorisasi dan persetujuan akhir publikasi nomor edisi jurnal.</p>

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
                <td class="label">Versi Galai Final:</td>
                <td>{{ $galley->label }} (Versi {{ $galley->version }})</td>
            </tr>
            <tr>
                <td class="label">Pemohon (Layout Editor):</td>
                <td>{{ $actor->full_name ?? $actor->name }}</td>
            </tr>
            <tr>
                <td class="label">Catatan Pengajuan:</td>
                <td><em>"{{ $approval->note ?? 'Disetujui untuk penjadwalan edisi terbitan.' }}"</em></td>
            </tr>
            <tr>
                <td class="label">SHA-256 Checksum:</td>
                <td><div class="checksum-box">{{ $galley->checksum_sha256 }}</div></td>
            </tr>
        </table>

        <p>Silakan akses portal jurnal untuk memvalidasi berkas galai dan menjadwalkan edisi terbitan:</p>
        <p style="text-align: center;">
            <a href="{{ $workspaceUrl }}" class="btn">Tinjau Galai Final & Otorisasi</a>
        </p>

        <div class="footer">
            Sistem Informasi Riset dan Inovasi Daerah (SIRIDA) Kota Makassar &bull; Email otomatis, mohon tidak membalas langsung.
        </div>
    </div>
</body>
</html>
