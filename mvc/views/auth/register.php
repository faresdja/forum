<?php /* views/auth/register.php — نموذج التسجيل، بديل system/sign-up.php */ ?>
<h2>التسجيل</h2>

<?php if (!empty($error)): ?>
  <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
<?php endif; ?>

<form method="post" action="/register" class="form-home">
  <div class="form-group">
    <label for="username">اسم المستخدم</label>
    <input type="text" id="username" name="username" class="form-control" required>
  </div>
  <div class="form-group">
    <label for="email">البريد الإلكتروني</label>
    <input type="email" id="email" name="email" class="form-control" required>
  </div>
  <div class="form-group">
    <label for="password">كلمة المرور (6 أحرف فأكثر)</label>
    <input type="password" id="password" name="password" class="form-control" minlength="6" required>
  </div>
  <div class="form-group">
    <label for="age">السن</label>
    <input type="number" id="age" name="age" class="form-control" min="10" max="99">
  </div>
  <div class="form-group">
    <label>الجنس</label>
    <select name="gender" class="form-control">
      <option value="1">ذكر</option>
      <option value="2">أنثى</option>
    </select>
  </div>
  <button type="submit" class="btn btn-success">إنشاء الحساب</button>
</form>
