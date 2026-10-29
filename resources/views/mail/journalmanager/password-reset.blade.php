<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Permohonan Reset Kata Sandi</title>
    <style>
        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; line-height: 1.6; color: #1f2937; margin: 0; padding: 24px; background-color: #f9fafb; }
        .card { max-width: 600px; margin: 0 auto; background: #ffffff; border-radius: 12px; border: 1px solid #e5e7eb; padding: 28px; box-shadow: 0 1px 3px rgba(0,0,0,0.05); }
        .header { border-bottom: 2px solid #800000; padding-bottom: 16px; margin-bottom: 20px; }
        .header h1 { color: #800000; font-size: 20px; margin: 0 0 6px 0; }
        .badge { display: inline-block; padding: 4px 10px; font-size: 11px; font-weight: 700; border-radius: 6px; background-color: #fef3c7; color: #92400e; border: 1px solid #fde68a; }
        .btn { display: inline-block; padding: 10px 20px; font-size: 13px; font-weight: 600; color: #ffffff !important; background-color: #d97706; text-decoration: none; border-radius: 8px; margin-top: 14px; }
        .alert-box { background-color: #eff6ff; border: 1px solid #bfdbfe; border-radius: 8px; padding: 12px; font-size: 12px; color: #1e40af; margin: 16px 0; }
        .footer { margin-top: 24px; font-size: 11px; color: #9ca3af; text-align: center; border-top: 1px solid #f3f4f6; padding-top: 14px; }
    </style>
</head>
<body>
    <div class="card">
        <div class="header">
            <h1>Tautan Pemulihan Kata Sandi Akun</h1>
            <span class="badge">Keamanan Akun</span>
        </div>

        <p>Yth. {{ $user->name }},</p>
        <p>Kami menerima permohonan untuk mengatur ulang kata sandi akun portal SIRIDA Anda yang diajukan melalui Journal Manager atau permintaan mandiri.</p>

        <p>Silakan klik tombol di bawah ini untuk mengatur kata sandi baru:</p>
        <p style="text-align: center;">
            <a href="{{ $resetUrl }}" class="btn">Atur Ulang Kata Sandi Saya</a>
        </p>

        <div class="alert-box">
            Tautan reset ini hanya berlaku selama 60 menit. Jika Anda tidak pernah mengajukan permintaan pemulihan kata sandi ini, Anda dapat mengabaikan email ini dengan aman.
        </div>

        <div class="footer">
            Sistem Informasi Riset dan Inovasi Daerah (SIRIDA) Kota Makassar &bull; Email keamanan sistem.
        </div>
    </div>
</body>
</html>
