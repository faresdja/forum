<?php /* views/users/profile.php — ملف عضو */ ?>
<h2><?= htmlspecialchars($pageTitle) ?></h2>

<div class="panel panel-default">
  <div class="panel-body">
    <img src="<?= htmlspecialchars($u['user_avatar'] ?? 'img/nophoto.gif') ?>" width="90" height="90" class="img-circle" alt="">
    <p>الاسم: <?= htmlspecialchars($u['user_name']) ?></p>
    <p>تاريخ الانضمام: <?= htmlspecialchars($u['joining_date'] ?? '') ?></p>
    <p>المواضيع: <?= (int)($u['user_to'] ?? 0) ?> — الردود: <?= (int)($u['user_re'] ?? 0) ?></p>
  </div>
</div>

<h4>آخر مواضيعه</h4>
<ul>
  <?php foreach ($topics as $t): ?>
    <li><a href="/topic/<?= (int)$t['t_id'] ?>"><?= htmlspecialchars($t['t_name']) ?></a></li>
  <?php endforeach; ?>
  <?php if (empty($topics)): ?><li>لا مواضيع بعد.</li><?php endif; ?>
</ul>
