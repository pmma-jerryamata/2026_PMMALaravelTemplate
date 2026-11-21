<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Cache;

class TimeController extends Controller
{
    public function __invoke()
    {
        // Cache an offset, not a timestamp, so every response remains current.
        $sample = Cache::remember('pagasa-time-offset', 60, function () {
            $socket = @stream_socket_client('udp://ntp.pagasa.dost.gov.ph:123', $errno, $error, 2);
            if (! $socket) {
                return ['offset' => 0, 'synced' => false];
            }
            try {
                stream_set_timeout($socket, 2);
                $sent = microtime(true);
                // NTP v4 client request. A random transmit value validates the reply origin.
                $nonce = random_bytes(8);
                $packet = "\x23".str_repeat("\0", 39).$nonce;
                if (@fwrite($socket, $packet) !== strlen($packet)) {
                    return ['offset' => 0, 'synced' => false];
                }
                $reply = @fread($socket, 512);
                $received = microtime(true);
                if (! is_string($reply) || strlen($reply) < 48 || substr($reply, 24, 8) !== $nonce) {
                    return ['offset' => 0, 'synced' => false];
                }
                $mode = ord($reply[0]) & 7;
                $leap = ord($reply[0]) >> 6;
                $stratum = ord($reply[1]);
                if ($mode !== 4 || $leap === 3 || $stratum < 1 || $stratum > 15) {
                    return ['offset' => 0, 'synced' => false];
                }
                $receive = unpack('Nseconds/Nfraction', substr($reply, 32, 8));
                $transmit = unpack('Nseconds/Nfraction', substr($reply, 40, 8));
                $t2 = $receive['seconds'] - 2208988800 + $receive['fraction'] / 4294967296;
                $t3 = $transmit['seconds'] - 2208988800 + $transmit['fraction'] / 4294967296;
                return ['offset' => (($t2 - $sent) + ($t3 - $received)) / 2, 'synced' => true];
            } finally {
                fclose($socket);
            }
        });

        return response()->json([
            'timestamp' => (microtime(true) + $sample['offset']) * 1000,
            'synced' => $sample['synced'],
            'source' => $sample['synced'] ? 'PAGASA' : 'Server clock (PAGASA unavailable)',
        ])->header('Cache-Control', 'no-store');
    }
}
