<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/session.php';
require_admin();

$pdo = db();

// Stats
$userCount  = $pdo->query('SELECT COUNT(*) FROM users WHERE role = "user"')->fetchColumn();
$moodCount  = $pdo->query('SELECT COUNT(*) FROM mood_entries')->fetchColumn();
$questsDone = $pdo->query('SELECT COUNT(*) FROM user_quests')->fetchColumn();
$exprCount  = $pdo->query('SELECT COUNT(*) FROM expressions')->fetchColumn();

// Recent users
$recentUsers = $pdo->query(
    'SELECT id, name, email, streak, total_xp, last_active, created_at
     FROM users WHERE role = "user" ORDER BY created_at DESC LIMIT 10'
)->fetchAll();

// Mood breakdown today
$moodToday = $pdo->query(
    'SELECT mood, COUNT(*) AS cnt FROM mood_entries WHERE DATE(logged_at) = CURDATE() GROUP BY mood ORDER BY mood'
)->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Admin Panel — MindWell</title>
  <link rel="stylesheet" href="../css/style.css" />
  <link rel="stylesheet" href="../css/dashboard.css" />
  <style>
    table { width:100%; border-collapse:collapse; font-size:0.875rem; }
    th, td { padding:0.7rem 0.85rem; text-align:left; border-bottom:1px solid var(--border); }
    th { background:var(--bg); font-weight:600; font-size:0.8rem; text-transform:uppercase; letter-spacing:0.04em; color:var(--text-muted); }
    tr:hover td { background:#fafafa; }
    .badge { display:inline-block; padding:0.15rem 0.55rem; border-radius:99px; font-size:0.72rem; font-weight:700; }
    .badge-admin { background:#fed7d7; color:#9b2c2c; }
    .badge-user  { background:#e9d8fd; color:#553c9a; }
  </style>
</head>
<body>
<nav>
  <div class="nav-inner">
    <a href="../index.php" class="nav-logo">MindSpace</a>
    <ul class="nav-links">
      <li><a href="index.php" class="active">Admin</a></li>
      <li><a href="quests.php">Quests</a></li>
      <li><a href="resources.php">Resources</a></li>
      <li><a href="users.php">Users</a></li>
      <li><a href="../logout.php">Sign Out</a></li>
    </ul>
  </div>
</nav>

<div class="dashboard-layout">
  <aside class="sidebar">
    <ul class="sidebar-menu">
      <li><a href="index.php" class="active"><span class="icon">📊</span> Overview</a></li>
      <li><a href="quests.php"><span class="icon">⚔️</span> Quests</a></li>
      <li><a href="resources.php"><span class="icon">📚</span> Resources</a></li>
      <li><a href="users.php"><span class="icon">👥</span> Users</a></li>
    </ul>
  </aside>

  <main>
    <div class="section-header">
      <h2>Admin Overview 📊</h2>
      <p>System statistics and user activity.</p>
    </div>

    <div class="grid-4 mb-3">
      <div class="stat-badge"><div class="stat-value"><?= $userCount ?></div><div class="stat-label">Registered users</div></div>
      <div class="stat-badge"><div class="stat-value"><?= $moodCount ?></div><div class="stat-label">Mood entries</div></div>
      <div class="stat-badge"><div class="stat-value"><?= $questsDone ?></div><div class="stat-label">Quests completed</div></div>
      <div class="stat-badge"><div class="stat-value"><?= $exprCount ?></div><div class="stat-label">Reflections</div></div>
    </div>

    <!-- Today's mood breakdown -->
    <div class="card mb-3">
      <div class="card-title mb-2">Today's Mood Breakdown</div>
      <?php if ($moodToday): ?>
        <?php $labels = ['','😔 Very Low','😟 Low','😐 Neutral','😊 Good','😄 Great']; ?>
        <?php foreach ($moodToday as $m): ?>
          <div class="flex-between mb-1">
            <span style="font-size:0.9rem"><?= $labels[$m['mood']] ?></span>
            <span class="tag tag-purple"><?= $m['cnt'] ?> entries</span>
          </div>
        <?php endforeach; ?>
      <?php else: ?>
        <p class="text-muted text-sm">No mood entries logged today.</p>
      <?php endif; ?>
    </div>

    <!-- Recent users -->
    <div class="card">
      <div class="flex-between mb-2">
        <div class="card-title">Recent Users</div>
        <a href="users.php" class="btn btn-outline btn-sm">View All</a>
      </div>
      <div style="overflow-x:auto">
        <table>
          <thead>
            <tr><th>Name</th><th>Email</th><th>Streak</th><th>XP</th><th>Last Active</th><th>Joined</th></tr>
          </thead>
          <tbody>
            <?php foreach ($recentUsers as $u): ?>
            <tr>
              <td><?= htmlspecialchars($u['name']) ?></td>
              <td><?= htmlspecialchars($u['email']) ?></td>
              <td>🔥 <?= $u['streak'] ?></td>
              <td>⭐ <?= $u['total_xp'] ?></td>
              <td><?= $u['last_active'] ?? '—' ?></td>
              <td><?= date('d M Y', strtotime($u['created_at'])) ?></td>
            </tr>
            <?php endforeach; ?>
            <?php if (!$recentUsers): ?>
            <tr><td colspan="6" style="text-align:center;color:var(--text-muted)">No users yet.</td></tr>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>
  </main>
</div>

<footer><p>🌱 MindSpace Admin Panel</p></footer>
</body>
</html>
