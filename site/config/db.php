<?php
// Настройки подключения к базе данных
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'gosha_rubchinskiy');

function getDb(): mysqli {
    static $db = null;
    if ($db === null) {
        $db = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
        if ($db->connect_error) {
            http_response_code(500);
            die(json_encode(['error' => 'Ошибка подключения к БД: ' . $db->connect_error]));
        }
        $db->set_charset('utf8mb4');
    }
    return $db;
}

// Генератор SVG-заглушки вместо фото товара
function placeholderSvg(string $label, int $colorIndex = 0): string {
    $colors = ['#1a1a2e','#16213e','#0f3460','#533483','#2b2d42','#3d405b','#1b1b2f','#162447'];
    $bg = $colors[$colorIndex % count($colors)];
    $lines = array_slice(explode(' ', $label), 0, 3);
    $y = 48;
    $textParts = '';
    foreach ($lines as $line) {
        $textParts .= '<text x="50%" y="' . $y . '%" dominant-baseline="middle" text-anchor="middle"
            font-family="Arial,sans-serif" font-size="11" fill="#aaaaaa">'
            . htmlspecialchars($line) . '</text>';
        $y += 14;
    }
    $svg = '<svg xmlns="http://www.w3.org/2000/svg" width="400" height="500" viewBox="0 0 400 500">
        <rect width="400" height="500" fill="' . $bg . '"/>
        <rect x="160" y="190" width="80" height="80" rx="4" fill="none" stroke="#444" stroke-width="1.5"/>
        <line x1="160" y1="190" x2="240" y2="270" stroke="#444" stroke-width="1"/>
        <line x1="240" y1="190" x2="160" y2="270" stroke="#444" stroke-width="1"/>
        ' . $textParts . '
    </svg>';
    return 'data:image/svg+xml;base64,' . base64_encode($svg);
}

// Заглушка для слайдера (широкая)
function placeholderSliderSvg(string $label, int $idx = 0): string {
    $gradients = [
        ['#0d0d0d', '#1a1a1a'],
        ['#111118', '#1e1e2e'],
        ['#0a0a0f', '#151520'],
    ];
    [$c1, $c2] = $gradients[$idx % count($gradients)];
    $svg = '<svg xmlns="http://www.w3.org/2000/svg" width="800" height="600" viewBox="0 0 800 600">
        <defs>
            <linearGradient id="g" x1="0" y1="0" x2="1" y2="1">
                <stop offset="0%" stop-color="' . $c1 . '"/>
                <stop offset="100%" stop-color="' . $c2 . '"/>
            </linearGradient>
        </defs>
        <rect width="800" height="600" fill="url(#g)"/>
        <rect x="300" y="160" width="200" height="250" rx="6" fill="none" stroke="#333" stroke-width="2"/>
        <line x1="300" y1="160" x2="500" y2="410" stroke="#333" stroke-width="1"/>
        <line x1="500" y1="160" x2="300" y2="410" stroke="#333" stroke-width="1"/>
        <text x="400" y="460" dominant-baseline="middle" text-anchor="middle"
            font-family="Arial,sans-serif" font-size="14" fill="#666">' . htmlspecialchars($label) . '</text>
    </svg>';
    return 'data:image/svg+xml;base64,' . base64_encode($svg);
}
