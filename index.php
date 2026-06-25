<?php
require_once __DIR__ . '/_helpers.php';
if (auth_user()) redirect_to('dashboard.php');

page_head('Mental Wellness for Youth', '<style>
    .landing-hero { background: linear-gradient(160deg, #EDE9FE 0%, #D4EFDF 60%, #FEF3E8 100%); padding: 60px 24px 48px; text-align: center; }
    .landing-logo { font-family: "Caveat", cursive; font-size: 2.8rem; font-weight: 700; color: var(--purple); display: block; margin-bottom: 20px; }
    .landing-hero h1 { font-size: 1.6rem; font-weight: 700; color: var(--text); line-height: 1.3; margin-bottom: 12px; }
    .landing-hero p { color: var(--text-secondary); font-size: 0.95rem; line-height: 1.65; max-width: 320px; margin: 0 auto 28px; }
    .cta-group { display: flex; flex-direction: column; gap: 10px; align-items: stretch; }
    .landing-body { padding: 32px 24px 40px; }
    .feature-card { background: var(--white); border-radius: var(--radius); padding: 18px; margin-bottom: 12px; box-shadow: var(--shadow-sm); display: flex; align-items: flex-start; gap: 14px; }
    .feature-icon { font-size: 1.8rem; flex-shrink: 0; }
    .feature-card h3 { font-size: 0.95rem; font-weight: 700; margin-bottom: 3px; }
    .feature-card p { font-size: 0.82rem; color: var(--text-secondary); line-height: 1.5; }
    .crisis-note { background: var(--amber-light); border-radius: var(--radius-sm); padding: 14px 16px; margin-top: 24px; font-size: 0.8rem; color: #7a4a00; line-height: 1.6; }
    .crisis-note strong { display: block; margin-bottom: 3px; }
  </style>');
?>
  <div class="page-content no-bottom-nav">
    <div class="landing-hero">
      <span class="landing-logo">MindSpace</span>
      <h1>Your mental wellness journey starts here &#127793;</h1>
      <p>Track your mood, complete daily self-care quests, and find space to express yourself - free and built for young people in Kenya.</p>
      <div class="cta-group">
        <a href="register.php" class="btn btn-primary btn-lg">Get Started - It's Free</a>
        <a href="login.php" class="btn btn-ghost btn-lg">I already have an account</a>
      </div>
    </div>

    <div class="landing-body">
      <?php
      $features = [
          ['&#128202;', 'Mood Tracking', 'Log how you feel daily and see your emotional patterns over time. Self-awareness is the first step.'],
          ['&#9876;', 'Side Quests', 'Gamified self-care challenges that earn you points and build streaks.'],
          ['&#9997;', 'Expression Space', 'A safe, private place to write freely or follow guided prompts.'],
          ['&#9855;', 'Inclusive by Design', 'Built to be accessible and neurodivergent-friendly - clear layouts, flexible interaction, no overwhelm.'],
      ];
      foreach ($features as [$icon, $title, $text]): ?>
      <div class="feature-card">
        <div class="feature-icon"><?= $icon ?></div>
        <div><h3><?= e($title) ?></h3><p><?= e($text) ?></p></div>
      </div>
      <?php endforeach; ?>

      <div class="crisis-note">
        <strong>&#9888; Important</strong>
        MindSpace is a self-help tool, not a clinical service. If you are in crisis, contact a mental health professional or Befrienders Kenya: <strong>+254 722 178 177</strong>.
      </div>
    </div>
  </div>
<?php page_foot(); ?>
