<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/session.php';
require_admin();

$pdo = db();
$msg = '';

// Handle form actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'add') {
        $title  = trim($_POST['title'] ?? '');
        $desc   = trim($_POST['description'] ?? '');
        $xp     = (int) ($_POST['xp'] ?? 20);
        $cat    = trim($_POST['category'] ?? '');
        $diff   = $_POST['difficulty'] ?? 'Easy';
        if ($title && $desc && $cat) {
            $pdo->prepare('INSERT INTO quests (title, description, xp, category, difficulty) VALUES (?,?,?,?,?)')
                ->execute([$title, $desc, $xp, $cat, $diff]);
            $msg = 'Quest added successfully.';
        }
    } elseif ($action === 'toggle') {
        $id = (int) ($_POST['id'] ?? 0);
        $pdo->prepare('UPDATE quests SET active = NOT active WHERE id = ?')->execute([$id]);
        $msg = 'Quest status updated.';
    } elseif ($action === 'delete') {
        $id = (int) ($_POST['id'] ?? 0);
        $pdo->prepare('DELETE FROM quests WHERE id = ?')->execute([$id]);
        $msg = 'Quest deleted.';
    }
}

$quests = $pdo->query('SELECT * FROM quests ORDER BY active DESC, category, id')->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Manage Quests — MindSpace Admin</title>
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
      <li><a href="index.php">Admin</a></li>
      <li><a href="quests.php" class="active">Quests</a></li>
      <li><a href="resources.php">Resources</a></li>
      <li><a href="users.php">Users</a></li>
    </ul>
  </div>
</nav>

<div class="dashboard-layout">
  <aside class="sidebar">
    <ul class="sidebar-menu">
      <li><a href="index.php"><span class="icon">📊</span> Overview</a></li>
      <li><a href="quests.php" class="active"><span class="icon">⚔️</span> Quests</a></li>
      <li><a href="resources.php"><span class="icon">📚</span> Resources</a></li>
      <li><a href="users.php"><span class="icon">👥</span> Users</a></li>
    </ul>
  </aside>

  <main>
    <div class="section-header"><h2>Manage Quests ⚔️</h2><p>Add, toggle, or remove side quests.</p></div>

    <?php if ($msg): ?><div class="alert alert-success mb-2"><?= htmlspecialchars($msg) ?></div><?php endif; ?>

    <!-- Add form -->
    <div class="card mb-3">
      <div class="card-title mb-2">Add New Quest</div>
      <form method="POST">
        <input type="hidden" name="action" value="add" />
        <div class="grid-2">
          <div class="form-group">
            <label>Title</label>
            <input type="text" name="title" class="form-control" required placeholder="Quest title" />
          </div>
          <div class="grid-2" style="gap:0.75rem">
            <div class="form-group">
              <label>Category</label>
              <select name="category" class="form-control">
                <option>Mindfulness</option><option>Reflection</option><option>Physical</option>
                <option>Self-care</option><option>Social</option><option>Growth</option>
              </select>
            </div>
            <div class="form-group">
              <label>Difficulty</label>
              <select name="difficulty" class="form-control">
                <option>Easy</option><option>Medium</option><option>Hard</option>
              </select>
            </div>
          </div>
        </div>
        <div class="form-group">
          <label>Description</label>
          <textarea name="description" class="form-control" rows="2" required placeholder="What does the user do?"></textarea>
        </div>
        <div class="form-group" style="max-width:120px">
          <label>XP Reward</label>
          <input type="number" name="xp" class="form-control" value="25" min="5" max="200" />
        </div>
        <button type="submit" class="btn btn-primary">Add Quest</button>
      </form>
    </div>

    <!-- Quest table -->
    <div class="card">
      <div class="card-title mb-2">All Quests (<?= count($quests) ?>)</div>
      <div style="overflow-x:auto">
        <table>
          <thead><tr><th>Title</th><th>Category</th><th>Difficulty</th><th>XP</th><th>Status</th><th>Actions</th></tr></thead>
          <tbody>
          <?php foreach ($quests as $q): ?>
          <tr>
            <td><?= htmlspecialchars($q['title']) ?></td>
            <td><?= htmlspecialchars($q['category']) ?></td>
            <td><?= $q['difficulty'] ?></td>
            <td>+<?= $q['xp'] ?></td>
            <td><span class="tag <?= $q['active'] ? 'tag-green' : 'tag-orange' ?>"><?= $q['active'] ? 'Active' : 'Hidden' ?></span></td>
            <td style="display:flex;gap:0.5rem">
              <form method="POST" style="display:inline">
                <input type="hidden" name="action" value="toggle" />
                <input type="hidden" name="id" value="<?= $q['id'] ?>" />
                <button class="btn btn-outline btn-sm"><?= $q['active'] ? 'Hide' : 'Show' ?></button>
              </form>
              <form method="POST" onsubmit="return confirm('Delete this quest?')" style="display:inline">
                <input type="hidden" name="action" value="delete" />
                <input type="hidden" name="id" value="<?= $q['id'] ?>" />
                <button class="btn btn-sm" style="background:#fed7d7;color:#9b2c2c;border:none">Delete</button>
              </form>
            </td>
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
