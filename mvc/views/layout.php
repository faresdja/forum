<?php /* views/layout.php — التخطيط العام، بديل system/header.php + footer.php */ ?>
<!DOCTYPE html>
<html dir="rtl" lang="ar">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= htmlspecialchars($pageTitle ?? 'منتديات') ?></title>
<link rel="stylesheet" href="system/css/cairo.css">
</head>
<body class="body-<?= htmlspecialchars($body ?? '') ?>">

<nav class="navbar navbar-default">
  <div class="container-fluid">
    <ul class="nav navbar-nav">
      <li><a href="/home">الرئيسية</a></li>
      <li><a href="/forums">المنتديات</a></li>
      <li><a href="/users">الأعضاء</a></li>
      <?php if (!empty($me)): ?>
        <li><a href="/logout">خروج</a></li>
      <?php else: ?>
        <li><a href="/login">الدخول</a></li>
        <li><a href="/register">تسجيل</a></li>
      <?php endif; ?>
    </ul>
  </div>
</nav>

<div class="container">
  <?= $content ?? '' ?>
</div>

<footer class="footer">
  <p>منتديات فووريم — هيكل MVC (Router + Controller + PDO)</p>
</footer>
</body>
</html>
