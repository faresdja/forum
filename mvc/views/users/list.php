<?php /* views/users/list.php — قائمة الأعضاء، بديل users.php القديمة */ ?>
<h2><?= htmlspecialchars($pageTitle) ?></h2>

<ul class="list-group">
  <?php foreach ($users as $u): ?>
    <li class="list-group-item">
      <a href="/user/<?= (int)$u['user_id'] ?>">
        <?= htmlspecialchars($u['user_name']) ?>
      </a>
      — المشاركات: <?= (int)($u['user_to'] ?? 0) + (int)($u['user_re'] ?? 0) ?>
    </li>
  <?php endforeach; ?>
  <?php if (empty($users)): ?>
    <li class="list-group-item">لا يوجد أعضاء.</li>
  <?php endif; ?>
</ul>
