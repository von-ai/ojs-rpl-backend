<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Surat Pengingat Penelaahan Naskah Ilmiah</title>
    <style>
        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; line-height: 1.6; color: #1f2937; margin: 0; padding: 24px; background-color: #f9fafb; }
        .card { max-width: 600px; margin: 0 auto; background: #ffffff; border-radius: 12px; border: 1px solid #e5e7eb; padding: 28px; box-shadow: 0 1px 3px rgba(0,0,0,0.05); }
        .header { border-bottom: 2px solid #E40017; padding-bottom: 16px; margin-bottom: 20px; }
        .header h1 { color: #E40017; font-size: 20px; margin: 0 0 6px 0; }
        .badge { display: inline-block; padding: 4px 10px; font-size: 11px; font-weight: 700; border-radius: 6px; background-color: #fef3c7; color: #92400e; border: 1px solid #fde68a; }
        .meta-table { width: 100%; border-collapse: collapse; margin: 18px 0; font-size: 13px; }
        .meta-table td { padding: 8px 12px; border-bottom: 1px solid #f3f4f6; }
        .meta-table td.label { font-weight: 600; color: #4b5563; width: 35%; }
        .btn { display: inline-block; padding: 10px 22px; font-size: 13px; font-weight: 600; color: #ffffff !important; background-color: #E40017; text-decoration: none; border-radius: 8px; margin-top: 14px; }
        .footer { margin-top: 24px; font-size: 11px; color: #9ca3af; text-align: center; border-top: 1px solid #f3f4f6; padding-top: 14px; }
        .notice-box { background-color: #fffbeb; border: 1px solid #fde68a; border-radius: 8px; padding: 12px 14px; font-size: 12px; color: #92400e; margin: 16px 0; }
    </style>
</head>
<body>
    <div class="card">
        <div class="header">
            <h1>Surat Pengingat Penelaahan Naskah</h1>
            <span class="badge">{{ $statusLabel }} &bull; {{ $submission->submission_number }}</span>
        </div>

        <p>Yth. <strong>{{ $reviewer->name }}</strong>,</p>

        @if($isInvitedOnly)
            <p>
                Kami menyampaikan pengingat perihal undangan penelaahan sejawat (*peer-review*) untuk naskah ilmiah berikut pada sistem jurnal <strong>{{ $submission->journal?->name ?? 'SIRIDA BRIDA Kota Makassar' }}</strong>.
            </p>
            <div class="notice-box">
                Mohon kesediaan Bapak/Ibu untuk memberikan konfirmasi kesediaan (<strong>Menerima</strong> atau <strong>Menolak</strong>) penugasan sebelum batas waktu konfirmasi berakhir.
            </div>
        @else
            <p>
                Kami menyampaikan pengingat perihal evaluasi telaah sejawat (*peer-review*) yang sedang Anda laksanakan untuk naskah ilmiah berikut pada sistem jurnal <strong>{{ $submission->journal?->name ?? 'SIRIDA BRIDA Kota Makassar' }}</strong>.
            </p>
            <div class="notice-box">
                Mohon kesediaan Bapak/Ibu untuk menyelesaikan dan mengirimkan lembar evaluasi telaah substantif naskah sebelum batas waktu penelaahan.
            </div>
        @endif

        <table class="meta-table">
            <tr>
                <td class="label">Nomor Naskah:</td>
                <td><strong>{{ $submission->submission_number }}</strong></td>
            </tr>
            <tr>
                <td class="label">Judul Naskah:</td>
                <td><em>{{ $submission->title }}</em></td>
            </tr>
            <tr>
                <td class="label">Kategori / Section:</td>
                <td>{{ $submission->section?->name ?? 'Artikel Riset' }}</td>
            </tr>
            <tr>
                <td class="label">Editor Pengirim:</td>
                <td>{{ $editor->name }} (Editor Bagian)</td>
            </tr>
            <tr>
                <td class="label">Tenggat Waktu:</td>
                <td><strong style="color: #b91c1c;">{{ $dueAt }}</strong></td>
            </tr>
            @if(!empty($customMessage))
            <tr>
                <td class="label">Pesan Redaksi:</td>
                <td><em>"{{ $customMessage }}"</em></td>
            </tr>
            @endif
        </table>

        <p>Silakan klik tautan di bawah ini untuk mengakses ruang telaah naskah secara langsung:</p>
        <p style="text-align: center;">
            <a href="{{ $workspaceUrl }}" class="btn">Buka Ruang Telaah Mitra Bestari</a>
        </p>

        <p style="font-size: 12px; color: #6b7280; margin-top: 20px;">
            Atas dedikasi, waktu, dan kontribusi saintifik yang Bapak/Ibu berikan bagi peningkatan mutu berkala ilmiah kami, Dewan Redaksi mengucapkan terima kasih.
        </p>

        <div class="footer">
            <p>Surel resmi ini dikirimkan secara otomatis oleh Sistem Manajemen Publikasi Jurnal Ilmiah SIRIDA &bull; BRIDA Kota Makassar.</p>
        </div>
    </div>
</body>
</html>
