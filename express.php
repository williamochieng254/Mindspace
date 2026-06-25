<?php
require_once __DIR__ . '/_helpers.php';
require_auth();

$pdo = db();
$userId = (int) auth_user()['id'];
$notice = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $content = trim($_POST['content'] ?? '');
    $prompt = trim($_POST['prompt'] ?? '');
    if ($content !== '') {
        $stmt = $pdo->prepare('INSERT INTO expressions (user_id, content, prompt) VALUES (?, ?, ?)');
        $stmt->execute([$userId, $content, $prompt ?: null]);
        $notice = 'Saved. Well done for showing up.';
    }
}

$stmt = $pdo->prepare('SELECT content, prompt, created_at FROM expressions WHERE user_id = ? ORDER BY created_at DESC LIMIT 20');
$stmt->execute([$userId]);
$entries = $stmt->fetchAll();
$prompts = [
    'How are you feeling right now, and why?',
    'What is one thing you are grateful for today?',
    'What has been weighing on your mind lately?',
    'Describe a moment recently when you felt at peace.',
];

page_head('Talk', '<style>
    .talk-hero { background: linear-gradient(160deg, #D4EFDF 0%, #EDE9FE 100%); padding: 24px 20px 32px; text-align: center; }
    .talk-hero h1 { font-family: "Caveat", cursive; font-size: 1.85rem; font-weight: 700; color: var(--text); margin-bottom: 4px; }
    .talk-hero p { font-size: 0.88rem; color: var(--text-secondary); }
    .talk-body { background: var(--white); border-radius: 28px 28px 0 0; margin-top: -16px; padding: 24px 20px 40px; box-shadow: 0 -4px 24px rgba(0,0,0,0.06); min-height: 65vh; }
    .prompt-chip { background: var(--purple-light); border: none; border-radius: var(--radius-sm); padding: 12px 14px; font-size: 0.875rem; font-weight: 500; color: var(--purple-dark); cursor: pointer; text-align: left; display: block; width: 100%; margin-bottom: 8px; transition: all var(--transition); font-family: var(--font); }
    .reflection-item { border: 1.5px solid var(--mid-grey); border-radius: var(--radius-sm); padding: 14px; margin-bottom: 10px; }
    .reflection-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 6px; }
    .reflection-date { font-size: 0.72rem; color: var(--text-muted); }
    .reflection-prompt { font-size: 0.72rem; color: var(--purple); font-style: italic; margin-bottom: 4px; }
    .reflection-text { font-size: 0.875rem; line-height: 1.6; color: var(--text); }
  </style>');
?>
  <div class="top-nav"><span class="page-title">Talk it out</span></div>
  <div class="page-content">
    <div class="talk-hero"><h1>Your safe space &#127807;</h1><p>Write freely. No one else sees this.</p></div>
    <div class="talk-body">
      <?php if ($notice): ?><div class="alert alert-success mb-3"><?= e($notice) ?></div><?php endif; ?>

      <form method="POST">
        <div class="label-caps mb-2">Choose a prompt or write freely</div>
        <?php foreach ($prompts as $p): ?>
        <button class="prompt-chip" type="button" onclick="document.getElementById('prompt').value=this.textContent.trim();document.getElementById('content').focus();"><?= e($p) ?></button>
        <?php endforeach; ?>
        <input type="hidden" id="prompt" name="prompt">
        <textarea id="content" name="content" class="input" rows="5" placeholder="Start writing..."></textarea>
        <div class="flex-between mt-2"><span class="text-xs text-muted">Private reflection</span><button class="btn btn-primary">Save</button></div>
      </form>

      <div class="mt-3">
        <div class="flex-between mb-3"><span style="font-weight:700">Your reflections</span></div>
        <?php if (!$entries): ?>
        <p class="text-sm text-muted">No reflections yet.</p>
        <?php else: foreach ($entries as $entry): ?>
        <div class="reflection-item">
          <div class="reflection-header"><span class="reflection-date"><?= e(date('D, M j', strtotime($entry['created_at']))) ?></span></div>
          <?php if ($entry['prompt']): ?><div class="reflection-prompt">"<?= e($entry['prompt']) ?>"</div><?php endif; ?>
          <div class="reflection-text"><?= nl2br(e($entry['content'])) ?></div>
        </div>
        <?php endforeach; endif; ?>
      </div>
    </div>
  </div>
<?php bottom_nav('talk'); page_foot(); ?>
