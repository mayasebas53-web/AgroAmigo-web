<?php
function crop_placeholder_emoji(string $cropType, int $cropId): string
{
    $emojiByKeyword = [
        'tomate' => '🍅',
        'maiz' => '🌽',
        'papa' => '🥔',
        'zanahoria' => '🥕',
        'lechuga' => '🥬',
        'fresa' => '🍓',
        'frijol' => '🫘',
        'arroz' => '🌾',
        'manzana' => '🍎',
        'cafe' => '☕',
    ];

    $normalizedType = strtolower(strtr($cropType, [
        'á' => 'a', 'é' => 'e', 'í' => 'i', 'ó' => 'o', 'ú' => 'u', 'ü' => 'u',
    ]));
    foreach ($emojiByKeyword as $keyword => $emoji) {
        if (str_contains($normalizedType, $keyword)) {
            return $emoji;
        }
    }

    $fallbackEmojis = ['🌱', '🌿', '🪴', '🍃', '🌾'];
    return $fallbackEmojis[$cropId % count($fallbackEmojis)];
}
