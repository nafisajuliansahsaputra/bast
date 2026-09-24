<?php

declare(strict_types=1);

const SETUP_TOKEN_HASH = '__SETUP_TOKEN_HASH__';

$core = __DIR__.'/app-core';
$envPath = $core.'/.env';
$databasePath = $core.'/database/database.sqlite';
$lockPath = $core.'/storage/app/infinityfree-installed.lock';

if (is_file($lockPath)) {
    http_response_code(410);
    exit('Installer sudah dinonaktifkan.');
}

function h(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}

function envQuote(string $value): string
{
    return '"'.str_replace(
        ["\\", '"', "\r", "\n"],
        ["\\\\", '\\"', '', ''],
        $value,
    ).'"';
}

$error = null;
$success = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $token = (string) ($_POST['setup_token'] ?? '');

        if (! hash_equals(SETUP_TOKEN_HASH, hash('sha256', $token))) {
            throw new RuntimeException('Setup token salah.');
        }

        $adminEmail = trim((string) ($_POST['admin_email'] ?? ''));
        $adminPassword = (string) ($_POST['admin_password'] ?? '');

        if ($adminEmail === '' || strlen($adminPassword) < 8) {
            throw new RuntimeException(
                'Email admin dan password minimal 8 karakter wajib diisi.',
            );
        }

        if (filter_var($adminEmail, FILTER_VALIDATE_EMAIL) === false) {
            throw new RuntimeException('Format email Super Admin tidak valid.');
        }

        if (! is_file($databasePath) || filesize($databasePath) === 0) {
            throw new RuntimeException(
                'Database demo SQLite tidak ditemukan. Build ulang package InfinityFree sebelum upload.',
            );
        }

        $scheme = (! empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
            ? 'https'
            : 'http';

        $host = (string) ($_SERVER['HTTP_HOST'] ?? '');
        $appUrl = $host !== ''
            ? $scheme.'://'.$host
            : 'http://localhost';

        $appKey = 'base64:'.base64_encode(random_bytes(32));

        $env = implode("\n", [
            'APP_NAME=BAST',
            'APP_ENV=production',
            'APP_KEY='.$appKey,
            'APP_DEBUG=false',
            'APP_URL='.$appUrl,
            'APP_LOCALE=en',
            'APP_FALLBACK_LOCALE=en',
            'APP_FAKER_LOCALE=en_US',
            '',
            'LOG_CHANNEL=single',
            'LOG_LEVEL=error',
            '',
            'DB_CONNECTION=sqlite',
            'DB_DATABASE='.envQuote($databasePath),
            'DB_FOREIGN_KEYS=true',
            '',
            'SESSION_DRIVER=file',
            'SESSION_LIFETIME=120',
            'SESSION_ENCRYPT=false',
            'SESSION_SECURE_COOKIE=true',
            'CACHE_STORE=file',
            'QUEUE_CONNECTION=sync',
            'FILESYSTEM_DISK=local',
            '',
            'MAIL_MAILER=log',
            'MAIL_FROM_ADDRESS=noreply@bast.local',
            'MAIL_FROM_NAME=BAST',
            '',
            'BAST_DOCUMENT_CODE=BAST',
            'BAST_INSTITUTION_CODE=DISKOMINFO',
            'BAST_SUPER_ADMIN_EMAIL='.envQuote($adminEmail),
            'BAST_DEMO_MODE=true',
            'BAST_DEMO_EMAIL=rina.maharani@bast.local',
            'BAST_DEMO_READ_ONLY=true',
            'BAST_DEMO_RESET=false',
            '',
            'VITE_APP_NAME=BAST',
            '',
        ]);

        if (file_put_contents($envPath, $env, LOCK_EX) === false) {
            throw new RuntimeException('Gagal menulis file .env.');
        }

        @chmod($databasePath, 0664);
        @chmod(dirname($databasePath), 0775);

        require $core.'/vendor/autoload.php';

        /** @var \Illuminate\Foundation\Application $app */
        $app = require $core.'/bootstrap/app.php';
        $app->usePublicPath(__DIR__);
        $app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

        \Illuminate\Support\Facades\DB::connection()->getPdo();

        foreach (['roles', 'users', 'basts', 'migrations'] as $requiredTable) {
            if (! \Illuminate\Support\Facades\Schema::hasTable($requiredTable)) {
                throw new RuntimeException(
                    'Database demo BAST tidak lengkap. Build ulang package InfinityFree.',
                );
            }
        }

        $superAdminRole = \App\Models\Role::query()
            ->where('slug', 'super-admin')
            ->first();

        if (! $superAdminRole instanceof \App\Models\Role) {
            throw new RuntimeException(
                'Role Super Admin tidak ditemukan pada database demo.',
            );
        }

        $superAdmin = \App\Models\User::query()
            ->where('role_id', $superAdminRole->id)
            ->first();

        if (! $superAdmin instanceof \App\Models\User) {
            throw new RuntimeException(
                'Akun Super Admin tidak ditemukan pada database demo.',
            );
        }

        $emailAlreadyUsed = \App\Models\User::query()
            ->where('email', $adminEmail)
            ->whereKeyNot($superAdmin->getKey())
            ->exists();

        if ($emailAlreadyUsed) {
            throw new RuntimeException(
                'Email Super Admin tersebut sudah dipakai akun lain.',
            );
        }

        $superAdmin->forceFill([
            'email' => $adminEmail,
            'email_verified_at' => now(),
            'password' => \Illuminate\Support\Facades\Hash::make(
                $adminPassword,
            ),
        ])->save();

        if (! is_dir(dirname($lockPath))) {
            mkdir(dirname($lockPath), 0755, true);
        }

        if (
            file_put_contents(
                $lockPath,
                'Installed at '.date(DATE_ATOM)."\n",
                LOCK_EX,
            ) === false
        ) {
            throw new RuntimeException(
                'BAST sudah terhubung, tetapi gagal membuat installer lock.',
            );
        }

        $success = 'BAST berhasil dikonfigurasi. Hapus install.php dari htdocs, lalu buka halaman utama.';
    } catch (Throwable $exception) {
        $error = $exception->getMessage();
    }
}

?><!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>BAST InfinityFree Setup</title>
    <style>
        body{font-family:system-ui,-apple-system,sans-serif;background:#f5f7f9;color:#17212b;margin:0;padding:40px 16px}
        main{max-width:720px;margin:auto;background:#fff;border:1px solid #dbe3e8;border-radius:18px;padding:28px}
        h1{margin-top:0} label{display:block;font-weight:600;margin:14px 0 6px}
        input{width:100%;box-sizing:border-box;padding:11px 12px;border:1px solid #bcc9d2;border-radius:9px}
        button{margin-top:22px;background:#1d5d8f;color:#fff;border:0;border-radius:9px;padding:12px 18px;font-weight:700;cursor:pointer}
        .msg{padding:12px 14px;border-radius:9px;margin-bottom:16px}
        .err{background:#fff1f1;color:#8a1f1f}.ok{background:#eefaf3;color:#17613b}
        p{line-height:1.55;color:#52616d}
    </style>
</head>
<body>
<main>
    <h1>BAST Setup</h1>
    <p>
        Package sudah membawa database demo SQLite. Tidak perlu membuat database
        MySQL atau menjalankan migration di InfinityFree. Masukkan setup token
        dan tentukan akun Super Admin.
    </p>

    <?php if ($error !== null): ?>
        <div class="msg err"><?= h($error) ?></div>
    <?php endif; ?>

    <?php if ($success !== null): ?>
        <div class="msg ok"><?= h($success) ?></div>
    <?php else: ?>
        <form method="post" autocomplete="off">
            <label>Setup Token</label>
            <input name="setup_token" required>

            <label>Super Admin Email</label>
            <input name="admin_email" type="email" required>

            <label>Super Admin Password</label>
            <input name="admin_password" type="password" minlength="8" required>

            <button type="submit">Aktifkan BAST</button>
        </form>
    <?php endif; ?>
</main>
</body>
</html>
