<?php

namespace App\Services;

use RuntimeException;
use Symfony\Component\Process\Process;

class ClamAvScanner
{
    public function scan(string $path): string
    {
        if (config('filesecurity.driver') === 'clamav') {
            $process = new Process([config('filesecurity.clamav_binary'), '--no-summary', '--', $path]);
            $process->setTimeout(120)->disableOutput();

            return match ($process->run()) {
                0 => 'clean', 1 => 'infected',
                default => throw new RuntimeException('Pemindai gagal; berkas tetap dikarantina.'),
            };
        }
        if (config('filesecurity.driver') !== 'clamd') {
            throw new RuntimeException('Pemindai belum dikonfigurasi; berkas tetap dikarantina.');
        }
        $socket = @stream_socket_client('tcp://'.config('filesecurity.clamd_host').':'.config('filesecurity.clamd_port'), $errorCode, $errorMessage, 5);
        if (! is_resource($socket)) {
            throw new RuntimeException('Pemindai tidak tersedia; berkas tetap dikarantina.');
        }
        stream_set_timeout($socket, 120);
        $file = fopen($path, 'rb');
        if (! $file) {
            fclose($socket);
            throw new RuntimeException('Objek pemindaian tidak tersedia.');
        }
        try {
            $this->writeAll($socket, "zINSTREAM\0");
            while (! feof($file)) {
                $chunk = fread($file, 1024 * 1024);
                if ($chunk === false) {
                    throw new RuntimeException('Objek pemindaian tidak dapat dibaca.');
                }
                if ($chunk !== '') {
                    $this->writeAll($socket, pack('N', strlen($chunk)).$chunk);
                }
            }
            $this->writeAll($socket, pack('N', 0));
            $response = stream_get_line($socket, 4096, "\0");
            if ($response === 'stream: OK') {
                return 'clean';
            }
            if (is_string($response) && preg_match('/^stream: .+ FOUND$/', $response)) {
                return 'infected';
            }
            throw new RuntimeException('Hasil pemindaian tidak sah; berkas tetap dikarantina.');
        } finally {
            fclose($file);
            fclose($socket);
        }
    }

    private function writeAll($stream, string $bytes): void
    {
        while ($bytes !== '') {
            $written = fwrite($stream, $bytes);
            if (! $written) {
                throw new RuntimeException('Koneksi pemindai terputus.');
            }
            $bytes = substr($bytes, $written);
        }
    }
}
