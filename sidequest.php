<?php
require_once __DIR__ . '/_helpers.php';
require_auth();

$pdo = db();
$user = current_user_row();
$userId = (int) $user['id'];
$notice = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $questId = (int) ($_POST['quest_id'] ?? 0);
    if ($questId) {
        $stmt = $pdo->prepare('SELECT id, xp FROM quests WHERE id = ? AND active = 1');
        $stmt->execute([$questId]);
        $quest = $stmt->fetch();

        if ($quest) {
            $stmt = $pdo->prepare('SELECT id FROM user_quests WHERE user_id = ? AND quest_id = ?');
            $stmt->execute([$userId, $questId]);
            if (!$stmt->fetch()) {
                $pdo->prepare('INSERT INTO user_quests (user_id, quest_id) VALUES (?, ?)')->execute([$userId, $questId]);
                $pdo->prepare('UPDATE users SET total_xp = total_xp + ?, last_active = CURDATE() WHERE id = ?')->execute([(int) $quest['xp'], $userId]);
                $notice = '+' . (int) $quest['xp'] . ' pts! Quest complete.';
                $user = current_user_row();
            }
        }
    }
}

$activeFilter = $_GET['cat'] ?? 'All';
$allowed = ['All', 'Mindfulness', 'Physical', 'Social', 'Reflection', 'Self-care', 'Growth'];
$params = [$userId];
$where = 'q.active = 1';
if (in_array($activeFilter, $allowed, true) && $activeFilter !== 'All') {
    $where .= ' AND q.category = ?';
    $params[] = $activeFilter;
}

$stmt = $pdo->prepare("
    SELECT q.*, uq.id AS done_id
    FROM quests q
    LEFT JOIN user_quests uq ON uq.quest_id = q.id AND uq.user_id = ?
    WHERE $where
    ORDER BY done_id IS NOT NULL ASC, q.xp DESC
");
$stmt->execute($params);
$quests = $stmt->fetchAll();

$xp = (int) $user['total_xp'];
$level = level_info($xp);

page_head('Side Quests', '<style>
    .xp-header { margin: 0 20px 16px; background: var(--white); border-radius: var(--radius); padding: 14px 16px; box-shadow: var(--shadow-sm); }
    .xp-header-row { display: flex; align-items: center; justify-content: space-between; margin-bottom: 8px; }
    .level-badge { background: var(--purple-light); color: var(--purple-dark); font-size: 0.75rem; font-weight: 700; padding: 4px 10px; border-radius: var(--radius-full); }
    .pts-total { font-size: 0.82rem; font-weight: 700; color: var(--text); }
    .mood-match-banner { margin: 0 20px 16px; background: linear-gradient(135deg, #EDE9FE, #D4EFDF); border-radius: var(--radius); padding: 12px 16px; font-size: 0.82rem; font-weight: 500; color: var(--purple-dark); }
    .quest-list { padding: 0 20px; }
    .quest-actions { display: flex; gap: 8px; flex-shrink: 0; }
    .q-cat-dot { width: 7px; height: 7px; border-radius: 50%; display: inline-block; margin-right: 3px; background: var(--purple); }
  </style>');
?>
  <div class="top-nav">
    <span class="page-title">Side Quests</span>
    <span class="streak-badge">&#128293; <?= (int) $user['streak'] ?> days</span>
  </div>

  <div class="page-content">
    <?php if ($notice): ?><div class="alert alert-success" style="margin:16px 20px"><?= e($notice) ?></div><?php endif; ?>
    <div class="xp-header">
      <div class="xp-header-row"><span class="level-badge">Level <?= $level['level'] ?></span><span class="pts-total"><?= $xp ?> pts</span></div>
      <div class="xp-bar-track"><div class="xp-bar-fill" style="width:<?= $level['pct'] ?>%"></div></div>
      <div class="text-xs text-muted mt-1"><?= $level['max'] ? 'Maximum level reached!' : $level['remaining'] . ' pts to Level ' . ($level['level'] + 1) ?></div>
    </div>

    <div class="mood-match-banner">&#10024; Matched to support your wellness today</div>

    <div class="section" style="padding-bottom:8px">
      <div class="filter-tabs">
        <?php foreach ($allowed as $cat): ?>
        <a class="filter-tab <?= $activeFilter === $cat ? 'active' : '' ?>" href="?cat=<?= urlencode($cat) ?>"><?= $cat === 'All' ? 'For you' : e($cat) ?></a>
        <?php endforeach; ?>
      </div>
    </div>

    <div class="quest-list">
      <?php if (!$quests): ?><p class="text-sm text-muted">No quests available yet.</p><?php endif; ?>
      <?php foreach ($quests as $q): $done = (bool) $q['done_id']; ?>
      <div class="quest-card<?= $done ? ' completed' : '' ?>" style="position:relative;<?= $done ? 'opacity:0.65' : '' ?>">
        <div class="quest-icon" style="background:#EDE9FE">&#9876;</div>
        <div class="quest-info">
          <div class="quest-title"><?= e($q['title']) ?></div>
          <div class="quest-meta"><span class="quest-pts">+<?= (int) $q['xp'] ?> pts</span><span>-</span><span><?= e($q['difficulty']) ?></span><span>-</span><span class="q-cat-dot"></span><span><?= e($q['category']) ?></span></div>
          <div class="text-xs text-muted mt-1" style="line-height:1.4"><?= e($q['description']) ?></div>
        </div>
        <div class="quest-actions">
          <?php if ($done): ?>
          <span style="font-size:1.4rem">&#9989;</span>
          <?php else: ?>
          <form method="POST"><input type="hidden" name="quest_id" value="<?= (int) $q['id'] ?>"><button class="btn btn-primary btn-sm">Accept</button></form>
          <a class="btn btn-ghost btn-sm" href="side-quests.php">Skip</a>
          <?php endif; ?>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
<?php bottom_nav('quests'); page_foot(); ?>
