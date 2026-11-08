<?php /* views/auth/login.php — نموذج الدخول، بديل system/login.php */ ?>
<h2>الدخول</h2>

<?php if (!empty($error)): ?>
  <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
<?php endif; ?>

<form method="post" action="/login" class="form-home">
  <div class="form-group">
    <label for="username">اسم المستخدم أو البريد</label>
    <input type="text" id="username" name="username" class="form-control" required>
  </div>
  <div class="form-group">
    <label for="password">كلمة المرور</label>
    <input type="password" id="password" name="password" class="form-control" required>
  </div>
  <button type="submit" class="btn btn-primary">دخول</button>
  <a href="/register">ليس لديك حساب؟ سجّل الآن</a>
</form>
