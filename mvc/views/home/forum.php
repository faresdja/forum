<?php /* views/home/forum.php — مواضيع منتدى مع ترقيم صفحات */ ?>
<h2><?= htmlspecialchars($pageTitle) ?></h2>

<table class="table table-striped">
  <thead>
    <tr><th>الموضوع</th><th>الكاتب</th><th></th></tr>
  </thead>
  <tbody>
  <?php if (empty($topics)): ?>
    <tr><td colspan="3">لا توجد مواضيع في هذا المنتدى بعد.</td></tr>
  <?php endif; ?>
  <?php foreach ($topics as $t): ?>
    <tr>
      <td><a href="/topic/<?= (int)$t['t_id'] ?>"><?= htmlspecialchars($t['t_name']) ?></a></td>
      <td><?= htmlspecialchars($t['user_name'] ?? 'زائر') ?></td>
      <td><small><?= htmlspecialchars($t['t_date'] ?? '') ?></small></td>
    </tr>
  <?php endforeach; ?>
  </tbody>
</table>

<nav>
  <?php if ($page > 1): ?><a href="/forum/<?= (int)$forum['f_id'] ?>?page=<?= $page - 1 ?>">السابق</a><?php endif; ?>
  <span>صفحة <?= (int)$page ?></span>
  <a href="/forum/<?= (int)$forum['f_id'] ?>?page=<?= $page + 1 ?>">التالي</a>
</nav>
