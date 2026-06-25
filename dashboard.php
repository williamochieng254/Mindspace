<?php
require_once __DIR__ . '/_helpers.php';
require_auth();

$pdo = db();
$user = current_user_row();
$userId = (int) $user['id'];
$hour = (int) date('G');
$greeting = $hour < 12 ? 'Good morning,' : ($hour < 18 ? 'Good afternoon,' : 'Good evening,');

$stmt = $pdo->prepare('SELECT COUNT(*) FROM user_quests WHERE user_id = ?');
$stmt->execute([$userId]);
$doneCount = (int) $stmt->fetchColumn();

$stmt = $pdo->prepare('
    SELECT q.*
    FROM quests q
    WHERE q.active = 1
    AND q.id NOT IN (SELECT quest_id FROM user_quests WHERE user_id = ?)
    ORDER BY q.xp DESC
    LIMIT 2
');
$stmt->execute([$userId]);
$todayQuests = $stmt->fetchAll();

$xp = (int) $user['total_xp'];
$level = level_info($xp);

page_head('Home', '<style>
    .greeting-section { padding: 18px 20px 12px; }
    .greeting-label { font-size: 0.68rem; font-weight: 700; letter-spacing: 0.1em; text-transform: uppercase; color: var(--text-muted); display: block; margin-bottom: 2px; }
    .greeting-name { font-family: "Caveat", cursive; font-size: 2rem; font-weight: 700; color: var(--text); line-height: 1.1; }
    .mood-check-card { background: linear-gradient(135deg, #EDE9FE 0%, #D4EFDF 100%); border-radius: var(--radius-lg); padding: 20px; margin: 0 20px 16px; }
    .mood-check-title { font-size: 0.88rem; font-weight: 600; color: var(--text); margin-bottom: 14px; }
    .mini-mood-row { display: flex; gap: 8px; justify-content: space-between; }
    .mini-mood-btn { flex: 1; display: flex; flex-direction: column; align-items: center; gap: 3px; padding: 10px 4px; border-radius: 12px; border: 2px solid transparent; background: rgba(255,255,255,0.6); cursor: pointer; transition: all var(--transition); font-family: var(--font); }
    .mini-mood-btn .emoji { font-size: 1.4rem; line-height: 1; }
    .mini-mood-btn .lbl { font-size: 0.62rem; font-weight: 600; color: var(--text-secondary); }
    .section-header { display: flex; align-items: center; justify-content: space-between; margin-bottom: 12px; }
    .section-header h2 { font-size: 1rem; font-weight: 700; }
    .section-header a { font-size: 0.8rem; color: var(--purple); font-weight: 500; text-decoration: none; }
    .today-quest { background: var(--white); border-radius: var(--radius); padding: 14px 16px; margin-bottom: 10px; display: flex; align-items: center; gap: 14px; box-shadow: var(--shadow-sm); border: 1.5px solid var(--mid-grey); }
    .tq-icon { width: 46px; height: 46px; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 1.3rem; flex-shrink: 0; }
    .tq-info { flex: 1; }
    .tq-title { font-weight: 600; font-size: 0.9rem; margin-bottom: 2px; }
    .tq-meta { font-size: 0.72rem; color: var(--text-muted); }
    .bottom-cards { display: flex; gap: 10px; padding: 0 20px 16px; }
    .bottom-card { flex: 1; background: var(--white); border-radius: var(--radius); padding: 16px 14px; box-shadow: var(--shadow-sm); text-align: center; }
    .bottom-card .big-num { font-size: 1.8rem; font-weight: 800; color: var(--purple); line-height: 1; }
    .bottom-card .lbl { font-size: 0.68rem; color: var(--text-muted); margin-top: 3px; }
    .bottom-card .emoji-big { font-size: 1.6rem; margin-bottom: 4px; }
  </style>');
?>
  <div class="top-nav">
    <span class="nav-logo">MindSpace</span>
    <a class="icon-btn" href="logout.php" title="Sign out">&#8617;</a>
  </div>

  <div class="page-content">
    <div class="greeting-section">
      <span class="greeting-label"><?= e($greeting) ?></span>
      <div class="greeting-name"><?= e($user['name']) ?></div>
    </div>

    <form class="mood-check-card" method="POST" action="mood-tracker.php">
      <div class="mood-check-title">How are you feeling?</div>
      <div class="mini-mood-row">
        <?php foreach ([1,2,3,4,5] as $m): [$label, $emoji] = mood_label($m); ?>
        <button class="mini-mood-btn" type="submit" name="mood" value="<?= $m ?>">
          <span class="emoji"><?= $emoji ?></span><span class="lbl"><?= e($label) ?></span>
        </button>
        <?php endforeach; ?>
      </div>
    </form>

    <div class="section">
      <div class="section-header"><h2>Today's quests</h2><a href="side-quests.php">See all &#8594;</a></div>
      <?php if (!$todayQuests): ?>
      <div class="text-sm text-muted text-center" style="padding:20px 0">All quests done today!</div>
      <?php else: foreach ($todayQuests as $q): ?>
      <div class="today-quest">
        <div class="tq-icon" style="background:#EDE9FE">&#9876;</div>
        <div class="tq-info">
          <div class="tq-title"><?= e($q['title']) ?></div>
          <div class="tq-meta">+<?= (int) $q['xp'] ?> pts - <?= e($q['difficulty']) ?></div>
        </div>
        <a href="side-quests.php" class="btn btn-soft btn-sm">Start</a>
      </div>
      <?php endforeach; endif; ?>
    </div>

    <div class="bottom-cards">
      <div class="bottom-card"><div class="emoji-big">&#128293;</div><div class="big-num"><?= (int) $user['streak'] ?></div><div class="lbl">day streak</div></div>
      <div class="bottom-card"><div class="emoji-big">&#11088;</div><div class="big-num"><?= $xp ?></div><div class="lbl">pts earned</div></div>
      <div class="bottom-card"><div class="emoji-big">&#9989;</div><div class="big-num"><?= $doneCount ?></div><div class="lbl">quests done</div></div>
    </div>

    <div class="section" style="padding-top:0">
      <div class="flex-between mb-1">
        <span class="text-xs text-muted">Level <?= $level['level'] ?> - <?= $xp ?> pts</span>
        <span class="text-xs text-muted"><?= $level['max'] ? 'Max level!' : $level['remaining'] . ' to next level' ?></span>
      </div>
      <div class="xp-bar-track"><div class="xp-bar-fill" style="width:<?= $level['pct'] ?>%"></div></div>
    </div>
  </div>
<?php bottom_nav('home'); page_foot(); ?>
