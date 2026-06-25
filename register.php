<?php
require_once __DIR__ . '/_helpers.php';
if (auth_user()) redirect_to('dashboard.php');

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['fullname'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm = $_POST['confirm'] ?? '';

    if (strlen($name) < 2) $error = 'Enter your name.';
    elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) $error = 'Enter a valid email.';
    elseif (strlen($password) < 6) $error = 'Password must be at least 6 characters.';
    elseif ($password !== $confirm) $error = 'Passwords do not match.';
    else {
        $stmt = db()->prepare('SELECT id FROM users WHERE email = ?');
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            $error = 'An account with this email already exists.';
        } else {
            $hash = password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);
            $stmt = db()->prepare('INSERT INTO users (name, email, password) VALUES (?, ?, ?)');
            $stmt->execute([$name, $email, $hash]);
            $id = (int) db()->lastInsertId();
            $_SESSION['user'] = ['id' => $id, 'name' => $name, 'email' => $email, 'role' => 'user'];
            redirect_to('dashboard.php');
        }
    }
}

page_head('Create Account', '<style>
    .auth-hero { background: linear-gradient(160deg, #EDE9FE 0%, #D4EFDF 100%); padding: 48px 28px 36px; text-align: center; }
    .auth-hero .logo-text { font-family: "Caveat", cursive; font-size: 2.2rem; font-weight: 700; color: var(--purple); margin-bottom: 20px; display: block; }
    .auth-hero h1 { font-size: 1.65rem; font-weight: 700; color: var(--text); margin-bottom: 4px; }
    .auth-hero p { color: var(--text-secondary); font-size: 0.92rem; }
    .auth-form-wrap { background: var(--white); flex: 1; border-radius: 28px 28px 0 0; margin-top: -16px; padding: 28px 24px 40px; box-shadow: 0 -4px 24px rgba(0,0,0,0.06); }
    .field-label { font-size: 0.7rem; font-weight: 700; letter-spacing: 0.08em; text-transform: uppercase; color: var(--text-muted); margin-bottom: 6px; display: block; }
  </style>');
?>
  <div style="min-height:100vh;display:flex;flex-direction:column">
    <div class="auth-hero">
      <span class="logo-text">MindSpace</span>
      <h1>Start your journey.</h1>
      <p>Free, private, and made for you.</p>
    </div>

    <div class="auth-form-wrap">
      <?php if ($error): ?><div class="alert alert-error mb-3"><?= e($error) ?></div><?php endif; ?>

      <form method="POST" novalidate>
        <div class="form-group">
          <label class="field-label" for="fullname">YOUR NAME</label>
          <input type="text" id="fullname" name="fullname" class="input" placeholder="First name or nickname" autocomplete="name" value="<?= e($_POST['fullname'] ?? '') ?>" required />
        </div>
        <div class="form-group">
          <label class="field-label" for="email">EMAIL</label>
          <input type="email" id="email" name="email" class="input" placeholder="you@email.com" autocomplete="email" value="<?= e($_POST['email'] ?? '') ?>" required />
        </div>
        <div class="form-group">
          <label class="field-label" for="password">PASSWORD</label>
          <input type="password" id="password" name="password" class="input" placeholder="At least 6 characters" autocomplete="new-password" required />
        </div>
        <div class="form-group">
          <label class="field-label" for="confirm">CONFIRM PASSWORD</label>
          <input type="password" id="confirm" name="confirm" class="input" placeholder="Repeat password" autocomplete="new-password" required />
        </div>

        <button type="submit" class="btn btn-primary btn-full btn-lg mt-2">Create Account</button>
      </form>

      <p class="text-center text-sm mt-3" style="color:var(--text-secondary)">Already have an account? <a href="login.php" style="color:var(--purple);font-weight:600">Log in</a></p>
      <p class="text-center text-xs mt-3" style="color:var(--text-muted);line-height:1.6">Not a substitute for professional mental health care.</p>
    </div>
  </div>
<?php page_foot(); ?>
