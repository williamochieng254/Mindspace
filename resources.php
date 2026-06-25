<?php
require_once __DIR__ . '/_helpers.php';
require_auth();

$stmt = db()->query('SELECT title, description, content, category, emoji, read_time FROM resources WHERE active = 1 ORDER BY category, created_at DESC');
$resources = $stmt->fetchAll();

page_head('Resources', '<style>
    .resources-hero { background: linear-gradient(160deg, #EDE9FE 0%, #FEF3E8 100%); padding: 24px 20px 32px; text-align: center; }
    .resources-hero h1 { font-family: "Caveat", cursive; font-size: 1.85rem; font-weight: 700; color: var(--text); margin-bottom: 4px; }
    .resources-hero p { font-size: 0.88rem; color: var(--text-secondary); }
    .resources-body { background: var(--white); border-radius: 28px 28px 0 0; margin-top: -16px; padding: 24px 20px 40px; box-shadow: 0 -4px 24px rgba(0,0,0,0.06); min-height: 65vh; }
    .resource-card { background: var(--soft-grey); border-radius: var(--radius); padding: 16px; margin-bottom: 12px; display: flex; align-items: flex-start; gap: 14px; border: 1.5px solid var(--mid-grey); transition: border-color var(--transition); }
    .resource-card:hover { border-color: var(--purple-light); }
    .resource-icon { width: 44px; height: 44px; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 1.3rem; flex-shrink: 0; }
    .resource-info { flex: 1; }
    .resource-title { font-weight: 700; font-size: 0.9rem; color: var(--text); margin-bottom: 3px; }
    .resource-desc { font-size: 0.78rem; color: var(--text-secondary); line-height: 1.5; }
    .resource-tag { display: inline-block; font-size: 0.65rem; font-weight: 700; letter-spacing: 0.06em; text-transform: uppercase; padding: 2px 8px; border-radius: var(--radius-full); margin-top: 6px; background: var(--purple-light); color: var(--purple-dark); }
    .crisis-banner { background: #FFF1F0; border: 1.5px solid #FECACA; border-radius: var(--radius); padding: 16px; margin-bottom: 20px; }
    .crisis-banner h3 { font-size: 0.88rem; font-weight: 700; color: #991B1B; margin-bottom: 6px; }
    .crisis-banner p { font-size: 0.78rem; color: #7f1d1d; line-height: 1.6; }
    .crisis-banner strong { color: #991B1B; }
  </style>');
?>
  <div class="top-nav"><span class="page-title">Resources</span></div>

  <div class="page-content">
    <div class="resources-hero"><h1>You're not alone &#129309;</h1><p>Helpful tools, hotlines, and guides.</p></div>

    <div class="resources-body">
      <div class="crisis-banner">
        <h3>&#9888; In crisis right now?</h3>
        <p>Please reach out immediately. Befrienders Kenya: <strong>+254 722 178 177</strong><br>Available 24/7 - free, confidential, non-judgmental.</p>
      </div>

      <?php if (!$resources): ?>
      <p class="text-sm text-muted">No resources available yet.</p>
      <?php else: foreach ($resources as $r): ?>
      <div class="resource-card">
        <div class="resource-icon" style="background:#EDE9FE"><?= e($r['emoji']) ?></div>
        <div class="resource-info">
          <div class="resource-title"><?= e($r['title']) ?></div>
          <div class="resource-desc"><?= e($r['description']) ?></div>
          <?php if ($r['content']): ?><div class="resource-desc mt-1"><?= nl2br(e($r['content'])) ?></div><?php endif; ?>
          <span class="resource-tag"><?= e($r['category']) ?> - <?= e($r['read_time']) ?></span>
        </div>
      </div>
      <?php endforeach; endif; ?>
    </div>
  </div>
<?php bottom_nav('resources'); page_foot(); ?>
