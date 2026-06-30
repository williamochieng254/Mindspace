<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/session.php';
require_admin();

$pdo = db();

$users = $pdo->query(
    'SELECT u.id, u.name, u.email, u.role, u.streak, u.total_xp, u.last_active, u.created_at,
            COUNT(DISTINCT m.id) AS mood_count,
            COUNT(DISTINCT uq.id) AS quest_count,
            COUNT(DISTINCT e.id) AS expr_count
     FROM users u
     LEFT JOIN mood_entries m  ON m.user_id  = u.id
     LEFT JOIN user_quests  uq ON uq.user_id = u.id
     LEFT JOIN expressions  e  ON e.user_id  = u.id
     GROUP BY u.id
     ORDER BY u.created_at DESC'
)->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" /><meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Users — MindSpace Admin</title>
  <link rel="stylesheet" href="../css/style.css" />
  <link rel="stylesheet" href="../css/dashboard.css" />
  <style>
    table { width:100%; border-collapse:collapse; font-size:0.875rem; }
    th, td { padding:0.7rem 0.85rem; text-align:left; border-bottom:1px solid var(--border); }
    th { background:var(--bg); font-weight:600; font-size:0.8rem; text-transform:uppercase; letter-spacing:0.04em; color:var(--text-muted); }
    tr:hover td { background:#fafafa; }
  </style>
</head>
<body>
<nav>
  <div class="nav-inner">
    <a href="../index.php" class="nav-logo">MindSpace</a>
    <ul class="nav-links">
      <li><a href="index.php">Admin</a></li><li><a href="quests.php">Quests</a></li>
      <li><a href="resources.php">Resources</a></li><li><a href="users.php" class="active">Users</a></li>
    </ul>
  </div>
</nav>

<div class="dashboard-layout">
  <aside class="sidebar">
    <ul class="sidebar-menu">
      <li><a href="index.php"><span class="icon">📊</span> Overview</a></li>
      <li><a href="quests.php"><span class="icon">⚔️</span> Quests</a></li>
      <li><a href="resources.php"><span class="icon">📚</span> Resources</a></li>
      <li><a href="users.php" class="active"><span class="icon">👥</span> Users</a></li>
    </ul>
  </aside>

  <main>
    <div class="section-header"><h2>All Users 👥</h2><p>User activity and engagement overview.</p></div>

    <div class="card">
      <div class="flex-between mb-2">
        <div class="card-title">Registered Users (<?= count($users) ?>)</div>
      </div>
      <div style="overflow-x:auto">
        <table>
          <thead>
            <tr>
              <th>Name</th><th>Email</th><th>Role</th>
              <th>Streak</th><th>XP</th><th>Moods</th><th>Quests</th><th>Reflections</th>
              <th>Last Active</th><th>Joined</th>
            </tr>
          </thead>
          <tbody>
          <?php foreach ($users as $u): ?>
          <tr>
            <td><?= htmlspecialchars($u['name']) ?></td>
            <td><?= htmlspecialchars($u['email']) ?></td>
            <td>
              <span class="tag <?= $u['role'] === 'admin' ? 'tag-orange' : 'tag-purple' ?>">
                <?= $u['role'] ?>
              </span>
            </td>
            <td>🔥 <?= $u['streak'] ?></td>
            <td>⭐ <?= $u['total_xp'] ?></td>
            <td><?= $u['mood_count'] ?></td>
            <td><?= $u['quest_count'] ?></td>
            <td><?= $u['expr_count'] ?></td>
            <td><?= $u['last_active'] ?? '—' ?></td>
            <td><?= date('d M Y', strtotime($u['created_at'])) ?></td>
          </tr>
          <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>
  </main>
</div>
<footer><p>🌱 MindSpace Admin Panel</p></footer>
</body>
</html>
