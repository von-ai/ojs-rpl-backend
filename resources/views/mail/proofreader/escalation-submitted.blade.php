<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Eskalasi Ketidaksesuaian Copyedit oleh Proofreader</title>
    <style>
        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; line-height: 1.6; color: #1f2937; margin: 0; padding: 24px; background-color: #f9fafb; }
        .card { max-width: 600px; margin: 0 auto; background: #ffffff; border-radius: 12px; border: 1px solid #e5e7eb; padding: 28px; box-shadow: 0 1px 3px rgba(0,0,0,0.05); }
        .header { border-bottom: 2px solid #b45309; padding-bottom: 16px; margin-bottom: 20px; }
        .header h1 { color: #b45309; font-size: 20px; margin: 0 0 6px 0; }
        .badge { display: inline-block; padding: 4px 10px; font-size: 11px; font-weight: 700; border-radius: 6px; background-color: #fffbeb; color: #b45309; border: 1px solid #fde68a; }
        .meta-table { width: 100%; border-collapse: collapse; margin: 18px 0; font-size: 13px; }
        .meta-table td { padding: 8px 12px; border-bottom: 1px solid #f3f4f6; }
        .meta-table td.label { font-weight: 600; color: #4b5563; width: 35%; }
        .btn { display: inline-block; padding: 10px 20px; font-size: 13px; font-weight: 600; color: #ffffff !important; background-color: #b45309; text-decoration: none; border-radius: 8px; margin-top: 14px; }
        .footer { margin-top: 24px; font-size: 11px; color: #9ca3af; text-align: center; border-top: 1px solid #f3f4f6; padding-top: 14px; }
    </style>
</head>
<body>
    <div class="card">
        <div class="header">
            <h1>Tiket Eskalasi Proofreader</h1>
            <span class="badge">Tiket #ESC-{{ substr($escalation->id, -8) }}</span>
        </div>

        <p>Yth. Section Editor,</p>
        <p>Proofreader telah mengajukan tiket eskalasi terkait ketidaksesuaian substansial antara draf galai dengan naskah acuan copyediting. Mohon intervensi dan arahan penyelesaian redaksional.</p>

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
                <td class="label">Diajukan Oleh:</td>
                <td>{{ $proofreader->name }} (Proofreader)</td>
            </tr>
            <tr>
                <td class="label">Alasan Eskalasi:</td>
                <td><em style="color: #b45309;">"{{ $escalation->reason }}"</em></td>
            </tr>
            <tr>
                <td class="label">Status Tiket:</td>
                <td><strong style="color: #b45309;">Terbuka (Menunggu Review Editor)</strong></td>
            </tr>
        </table>

        <p style="text-align: center;">
            <a href="{{ $workspaceUrl }}" class="btn">Tinjau Eskalasi di Lembar Kerja</a>
        </p>

        <div class="footer">
            Sistem Informasi Riset dan Inovasi Daerah (SIRIDA) Kota Makassar &bull; Email otomatis, mohon tidak membalas langsung.
        </div>
    </div>
</body>
</html>
