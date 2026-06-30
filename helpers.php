<?php
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/config/session.php';

function e($value): string {
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

function redirect_to(string $path): void {
    header('Location: ' . $path);
    exit;
}

function current_user_row(): ?array {
    $user = auth_user();
    if (!$user) return null;

    $stmt = db()->prepare('SELECT id, name, email, role, streak, total_xp FROM users WHERE id = ?');
    $stmt->execute([$user['id']]);
    return $stmt->fetch() ?: null;
}

function page_head(string $title, string $extraStyle = ''): void {
    echo '<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0" />
  <title>' . e($title) . ' - MindSpace</title>
  <link rel="stylesheet" href="css/style.css" />
  ' . $extraStyle . '
</head>
<body>
<div class="app-shell">
  <div class="status-bar">
    <span class="time status-time">9:41</span>
    <div class="status-icons">&bull;&bull;&bull; &#9646;&#9646;&#9646;</div>
  </div>';
}

function page_foot(bool $appJs = false): void {
    if ($appJs) echo "\n<script src=\"js/app.js\"></script>";
    echo "\n</div>\n</body>\n</html>";
}

function bottom_nav(string $active): void {
    $items = [
        'dashboard.php' => ['Home', '&#127968;', 'home'],
        'sidequest.php' => ['Quests', '&#9876;', 'quests'],
        'moodtrack.php' => ['Mood', '&#128202;', 'mood'],
        'express.php' => ['Talk', '&#9997;', 'talk'],
        'resources.php' => ['Resources', '&#128218;', 'resources'],
    ];

    echo '<nav class="bottom-nav">';
    foreach ($items as $href => [$label, $icon, $key]) {
        $class = $active === $key ? ' class="active"' : '';
        echo '<a href="' . $href . '"' . $class . '><span class="nav-icon">' . $icon . '</span>' . e($label) . '</a>';
    }
    echo '</nav>';
}

function level_info(int $xp): array {
    $levels = [0, 80, 180, 340, 560, 900];
    $level = 1;
    foreach ($levels as $i => $min) {
        if ($xp >= $min) $level = $i + 1;
    }
    $level = min($level, count($levels));
    $next = $levels[$level] ?? $levels[count($levels) - 1];
    $prev = $levels[$level - 1] ?? 0;
    $pct = $next > $prev ? min(100, (int) round((($xp - $prev) / ($next - $prev)) * 100)) : 100;

    return [
        'level' => $level,
        'next' => $next,
        'pct' => $pct,
        'remaining' => max(0, $next - $xp),
        'max' => $level >= count($levels),
    ];
}

function mood_label(int $mood): array {
    return [
        1 => ['Awful', '&#128542;'],
        2 => ['Meh', '&#128533;'],
        3 => ['Okay', '&#128528;'],
        4 => ['Good', '&#128578;'],
        5 => ['Great', '&#128516;'],
    ][$mood] ?? ['Unknown', '&#128528;'];
}
