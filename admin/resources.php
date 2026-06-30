<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/session.php';
require_admin();

$pdo = db();
$msg = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    if ($action === 'add') {
        $title = trim($_POST['title'] ?? '');
        $desc  = trim($_POST['description'] ?? '');
        $cat   = trim($_POST['category'] ?? '');
        $emoji = trim($_POST['emoji'] ?? '📄');
        $time  = trim($_POST['read_time'] ?? '3 min read');
        if ($title && $desc && $cat) {
            $pdo->prepare('INSERT INTO resources (title, description, category, emoji, read_time) VALUES (?,?,?,?,?)')
                ->execute([$title, $desc, $cat, $emoji, $time]);
            $msg = 'Resource added.';
        }
    } elseif ($action === 'toggle') {
        $id = (int) ($_POST['id'] ?? 0);
        $pdo->prepare('UPDATE resources SET active = NOT active WHERE id = ?')->execute([$id]);
        $msg = 'Resource status updated.';
    } elseif ($action === 'delete') {
        $id = (int) ($_POST['id'] ?? 0);
        $pdo->prepare('DELETE FROM resources WHERE id = ?')->execute([$id]);
        $msg = 'Resource deleted.';
    }
}

$resources = $pdo->query('SELECT * FROM resources ORDER BY active DESC, category, id')->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" /><meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Manage Resources — MindSpace Admin</title>
  <link rel="stylesheet" href="../css/style.css" />
  <link rel="stylesheet" href="../css/dashboard.css" />
  <style>
    table { width:100%; border-collapse:collapse; font-size:0.875rem; }
    th, td { padding:0.7rem 0.85rem; text-align:left; border-bottom:1px solid var(--border); }
    th { background:var(--bg); font-weight:600; font-size:0.8rem; text-transform:uppercase; letter-spacing:0.04em; color:var(--text-muted); }
  </style>
</head>
<body>
<nav>
  <div class="nav-inner">
    <a href="../index.php" class="nav-logo">MindSpace</a>
    <ul class="nav-links">
      <li><a href="index.php">Admin</a></li><li><a href="quests.php">Quests</a></li>
      <li><a href="resources.php" class="active">Resources</a></li><li><a href="users.php">Users</a></li>
    </ul>
  </div>
</nav>

<div class="dashboard-layout">
  <aside class="sidebar">
    <ul class="sidebar-menu">
      <li><a href="index.php"><span class="icon">📊</span> Overview</a></li>
      <li><a href="quests.php"><span class="icon">⚔️</span> Quests</a></li>
      <li><a href="resources.php" class="active"><span class="icon">📚</span> Resources</a></li>
      <li><a href="users.php"><span class="icon">👥</span> Users</a></li>
    </ul>
  </aside>

  <main>
    <div class="section-header"><h2>Manage Resources 📚</h2><p>Add, toggle, or remove mental health articles.</p></div>

    <?php if ($msg): ?><div class="alert alert-success mb-2"><?= htmlspecialchars($msg) ?></div><?php endif; ?>

    <div class="card mb-3">
      <div class="card-title mb-2">Add New Resource</div>
      <form method="POST">
        <input type="hidden" name="action" value="add" />
        <div class="grid-2">
          <div class="form-group">
            <label>Title</label>
            <input type="text" name="title" class="form-control" required placeholder="Article title" />
          </div>
          <div class="grid-2" style="gap:0.75rem">
            <div class="form-group">
              <label>Category</label>
              <select name="category" class="form-control">
                <option>Anxiety</option><option>Depression</option><option>Self-care</option>
                <option>Mindfulness</option><option>Relationships</option><option>Academic</option>
              </select>
            </div>
            <div class="form-group">
              <label>Emoji</label>
              <input type="text" name="emoji" class="form-control" value="📄" maxlength="4" />
            </div>
          </div>
        </div>
        <div class="form-group">
          <label>Description</label>
          <textarea name="description" class="form-control" rows="2" required placeholder="Short summary…"></textarea>
        </div>
        <div class="form-group" style="max-width:150px">
          <label>Read Time</label>
          <input type="text" name="read_time" class="form-control" value="4 min read" />
        </div>
        <button type="submit" class="btn btn-primary">Add Resource</button>
      </form>
    </div>

    <div class="card">
      <div class="card-title mb-2">All Resources (<?= count($resources) ?>)</div>
      <div style="overflow-x:auto">
        <table>
          <thead><tr><th>Emoji</th><th>Title</th><th>Category</th><th>Read Time</th><th>Status</th><th>Actions</th></tr></thead>
          <tbody>
          <?php foreach ($resources as $r): ?>
          <tr>
            <td style="font-size:1.3rem"><?= htmlspecialchars($r['emoji']) ?></td>
            <td><?= htmlspecialchars($r['title']) ?></td>
            <td><?= htmlspecialchars($r['category']) ?></td>
            <td><?= htmlspecialchars($r['read_time']) ?></td>
            <td><span class="tag <?= $r['active'] ? 'tag-green' : 'tag-orange' ?>"><?= $r['active'] ? 'Active' : 'Hidden' ?></span></td>
            <td style="display:flex;gap:0.5rem">
              <form method="POST" style="display:inline">
                <input type="hidden" name="action" value="toggle" /><input type="hidden" name="id" value="<?= $r['id'] ?>" />
                <button class="btn btn-outline btn-sm"><?= $r['active'] ? 'Hide' : 'Show' ?></button>
              </form>
              <form method="POST" onsubmit="return confirm('Delete?')" style="display:inline">
                <input type="hidden" name="action" value="delete" /><input type="hidden" name="id" value="<?= $r['id'] ?>" />
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
