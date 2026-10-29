<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Penugasan Tata Letak Galai Publikasi</title>
    <style>
        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; line-height: 1.6; color: #1f2937; margin: 0; padding: 24px; background-color: #f9fafb; }
        .card { max-width: 600px; margin: 0 auto; background: #ffffff; border-radius: 12px; border: 1px solid #e5e7eb; padding: 28px; box-shadow: 0 1px 3px rgba(0,0,0,0.05); }
        .header { border-bottom: 2px solid #800000; padding-bottom: 16px; margin-bottom: 20px; }
        .header h1 { color: #800000; font-size: 20px; margin: 0 0 6px 0; }
        .badge { display: inline-block; padding: 4px 10px; font-size: 11px; font-weight: 700; border-radius: 6px; background-color: #fef3c7; color: #92400e; border: 1px solid #fde68a; }
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
            <h1>Penugasan Tata Letak Galai Publikasi</h1>
            <span class="badge">Penugasan Baru &bull; {{ $submission->submission_number }}</span>
        </div>

        <p>Yth. Penata Letak (*Layout Editor*),</p>
        <p>Anda telah ditugaskan untuk menyusun draf berkas galai publikasi (PDF) dari naskah hasil penyuntingan akhir (*copyediting final*).</p>

        <table class="meta-table">
            <tr>
                <td class="label">Nomor Naskah:</td>
                <td><strong>{{ $submission->submission_number }}</strong></td>
            </tr>
            <tr>
                <td class="label">Judul Naskah:</td>
                <td>{{ $submission->title }}</td>
            </tr>
            @if($assignedBy)
            <tr>
                <td class="label">Ditugaskan Oleh:</td>
                <td>{{ $assignedBy->full_name ?? $assignedBy->name }} (Section Editor)</td>
            </tr>
            @endif
            <tr>
                <td class="label">Batas Waktu:</td>
                <td><strong style="color: #b91c1c;">{{ $dueAt }}</strong></td>
            </tr>
            @if($assignment->reason)
            <tr>
                <td class="label">Instruksi Khusus:</td>
                <td><em>"{{ $assignment->reason }}"</em></td>
            </tr>
            @endif
        </table>

        <p>Silakan akses ruang kerja penugasan untuk mengunduh naskah Word acuan copyediting dan mengunggah draf galai PDF:</p>
        <p style="text-align: center;">
            <a href="{{ $workspaceUrl }}" class="btn">Buka Ruang Kerja Tata Letak</a>
        </p>

        <div class="footer">
            Sistem Informasi Riset dan Inovasi Daerah (SIRIDA) Kota Makassar &bull; Email otomatis, mohon tidak membalas langsung.
        </div>
    </div>
</body>
</html>
