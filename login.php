<?php
require_once __DIR__ . '/_helpers.php';
if (auth_user()) redirect_to('dashboard.php');

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Please enter a valid email.';
    } elseif (strlen($password) < 6) {
        $error = 'Password must be at least 6 characters.';
    } else {
        $stmt = db()->prepare('SELECT id, name, email, password, role FROM users WHERE email = ?');
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user'] = [
                'id' => (int) $user['id'],
                'name' => $user['name'],
                'email' => $user['email'],
                'role' => $user['role'],
            ];
            db()->prepare('UPDATE users SET last_active = CURDATE() WHERE id = ?')->execute([$user['id']]);
            redirect_to(($user['role'] === 'admin') ? 'admin/index.php' : 'dashboard.php');
        }
        $error = 'Invalid email or password. Please try again.';
    }
}

page_head('Log In', '<style>
    .auth-page { min-height: 100vh; display: flex; flex-direction: column; padding: 0; }
    .auth-hero { background: linear-gradient(160deg, #EDE9FE 0%, #D4EFDF 100%); padding: 56px 28px 40px; text-align: center; }
    .auth-hero .logo-text { font-family: "Caveat", cursive; font-size: 2.2rem; font-weight: 700; color: var(--purple); margin-bottom: 24px; display: block; }
    .auth-hero h1 { font-size: 1.75rem; font-weight: 700; color: var(--text); margin-bottom: 4px; }
    .auth-hero p { color: var(--text-secondary); font-size: 0.95rem; }
    .auth-form-wrap { background: var(--white); flex: 1; border-radius: 28px 28px 0 0; margin-top: -16px; padding: 32px 24px 40px; box-shadow: 0 -4px 24px rgba(0,0,0,0.06); }
    .field-label { font-size: 0.7rem; font-weight: 700; letter-spacing: 0.08em; text-transform: uppercase; color: var(--text-muted); margin-bottom: 6px; display: block; }
    .forgot-link { font-size: 0.82rem; color: var(--purple); text-decoration: none; font-weight: 500; }
    .divider { text-align: center; color: var(--text-muted); font-size: 0.82rem; margin: 20px 0; position: relative; }
    .divider::before { content: ""; position: absolute; top: 50%; left: 0; right: 0; height: 1px; background: var(--mid-grey); }
    .divider span { background: var(--white); padding: 0 12px; position: relative; }
  </style>');
?>
  <div class="auth-page">
    <div class="auth-hero">
      <span class="logo-text">MindSpace</span>
      <h1>Welcome back.</h1>
      <p>How are you feeling today?</p>
    </div>

    <div class="auth-form-wrap">
      <?php if ($error): ?><div class="alert alert-error mb-3"><?= e($error) ?></div><?php endif; ?>

      <form method="POST" novalidate>
        <div class="form-group">
          <label class="field-label" for="email">EMAIL</label>
          <input type="email" id="email" name="email" class="input" placeholder="you@email.com" autocomplete="email" value="<?= e($_POST['email'] ?? '') ?>" required />
        </div>

        <div class="form-group">
          <div class="flex-between mb-1">
            <label class="field-label" for="password">PASSWORD</label>
            <a href="#" class="forgot-link">Forgot password?</a>
          </div>
          <input type="password" id="password" name="password" class="input" placeholder="********" autocomplete="current-password" required />
        </div>

        <button type="submit" class="btn btn-primary btn-full btn-lg mt-3">Log In</button>
      </form>

      <div class="divider mt-3"><span>or</span></div>
      <p class="text-center text-sm" style="color:var(--text-secondary)">New here? <a href="register.php" style="color:var(--purple);font-weight:600">Create an account</a></p>
      <p class="text-center text-xs mt-3" style="color:var(--text-muted);line-height:1.6">This platform supports your well-being but does not replace professional mental health care.</p>
    </div>
  </div>
<?php page_foot(); ?> 