<?php
require_once __DIR__ . '/_helpers.php';
require_auth();

$pdo = db();
$user = current_user_row();
$userId = (int) $user['id'];
$notice = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $mood = (int) ($_POST['mood'] ?? 0);
    $notes = trim($_POST['notes'] ?? '');
    if ($mood >= 1 && $mood <= 5) {
        $stmt = $pdo->prepare('INSERT INTO mood_entries (user_id, mood, notes) VALUES (?, ?, ?)');
        $stmt->execute([$userId, $mood, $notes ?: null]);
        $pdo->prepare('UPDATE users SET streak = streak + IF(last_active IS NULL OR last_active < CURDATE(), 1, 0), last_active = CURDATE() WHERE id = ?')->execute([$userId]);
        $notice = 'Entry saved! Well done for checking in.';
        $user = current_user_row();
    }
}

$stmt = $pdo->prepare('SELECT mood, notes, logged_at FROM mood_entries WHERE user_id = ? ORDER BY logged_at DESC LIMIT 7');
$stmt->execute([$userId]);
$history = $stmt->fetchAll();

page_head('Log Mood', '<style>
    .mood-hero { background: linear-gradient(160deg, #EDE9FE 0%, #D4EFDF 100%); padding: 24px 20px 32px; text-align: center; }
    .mood-hero h1 { font-family: "Caveat", cursive; font-size: 1.8rem; font-weight: 700; color: var(--text); margin-bottom: 4px; }
    .mood-hero p { font-size: 0.9rem; color: var(--text-secondary); }
    .form-section { background: var(--white); border-radius: 28px 28px 0 0; margin-top: -16px; padding: 28px 20px 40px; box-shadow: 0 -4px 24px rgba(0,0,0,0.06); min-height: 65vh; }
    .mood-row-5 { display: flex; gap: 6px; justify-content: space-between; margin-bottom: 28px; }
    .mood-btn-5 { flex: 1; display: flex; flex-direction: column; align-items: center; gap: 5px; padding: 12px 4px; border-radius: 14px; border: 2px solid var(--mid-grey); background: var(--white); cursor: pointer; transition: all var(--transition); font-family: var(--font); }
    .mood-btn-5 .emoji { font-size: 1.7rem; line-height: 1; }
    .mood-btn-5 .lbl { font-size: 0.62rem; font-weight: 600; color: var(--text-muted); }
    .mood-btn-5:hover { border-color: var(--purple-light); background: #faf9ff; }
    .tags-label { font-size: 0.7rem; font-weight: 700; letter-spacing: 0.08em; text-transform: uppercase; color: var(--text-muted); display: block; margin-bottom: 10px; }
    .history-entry { background: var(--soft-grey); border-radius: var(--radius-sm); padding: 12px 14px; margin-bottom: 8px; display: flex; align-items: center; gap: 12px; }
    .history-emoji { font-size: 1.4rem; flex-shrink: 0; }
    .history-info { flex: 1; }
    .history-label { font-weight: 600; font-size: 0.875rem; }
    .history-date { font-size: 0.72rem; color: var(--text-muted); }
    .history-tags { font-size: 0.72rem; color: var(--text-secondary); margin-top: 2px; }
  </style>');
?>
  <div class="top-nav">
    <span class="page-title">Log mood</span>
    <a class="icon-btn" href="dashboard.php">&#10005;</a>
  </div>

  <div class="page-content no-bottom-nav" style="padding-bottom:100px">
    <div class="mood-hero"><h1>How are you, right now?</h1><p>No judgement - just check in.</p></div>

    <div class="form-section">
      <?php if ($notice): ?><div class="alert alert-success mb-3"><?= e($notice) ?></div><?php endif; ?>
      <form method="POST">
        <div class="mood-row-5">
          <?php foreach ([1,2,3,4,5] as $m): [$label, $emoji] = mood_label($m); ?>
          <button class="mood-btn-5" type="submit" name="mood" value="<?= $m ?>"><span class="emoji"><?= $emoji ?></span><span class="lbl"><?= e($label) ?></span></button>
          <?php endforeach; ?>
        </div>

        <div class="form-group">
          <span class="tags-label">NOTE (OPTIONAL)</span>
          <textarea name="notes" class="input" rows="3" placeholder="Write what's on your mind..."></textarea>
        </div>
        <button class="btn btn-primary btn-full" type="submit">Save entry</button>
      </form>

      <div class="mt-3">
        <div class="flex-between mb-2"><span style="font-weight:700;font-size:0.95rem">Recent entries</span><span class="streak-badge">&#128293; <?= (int) $user['streak'] ?> days</span></div>
        <?php if (!$history): ?>
        <p class="text-sm text-muted">No entries yet. Log your first mood above!</p>
        <?php else: foreach ($history as $h): [$label, $emoji] = mood_label((int) $h['mood']); ?>
        <div class="history-entry">
          <div class="history-emoji"><?= $emoji ?></div>
          <div class="history-info">
            <div class="history-label"><?= e($label) ?></div>
            <?php if ($h['notes']): ?><div class="history-tags text-muted" style="font-style:italic">"<?= e($h['notes']) ?>"</div><?php endif; ?>
          </div>
          <div class="history-date"><?= e(date('D, M j', strtotime($h['logged_at']))) ?></div>
        </div>
        <?php endforeach; endif; ?>
      </div>
    </div>
  </div>
<?php bottom_nav('mood'); page_foot(); ?>
