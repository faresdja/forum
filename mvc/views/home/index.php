<?php /* views/home/index.php — الرئيسية: الفئات والمنتديات */ ?>
<h2><?= htmlspecialchars($pageTitle) ?></h2>

<?php if (empty($cats) && empty($forums)): ?>
  <div class="alert alert-info">لا توجد منتديات بعد.</div>
<?php endif; ?>

<?php foreach ($cats as $cat): ?>
  <div class="panel panel-default">
    <div class="panel-heading"><?= htmlspecialchars($cat['f_name']) ?></div>
    <table class="table">
      <tbody>
      <?php foreach ($forums as $f): if ((int)$f['f_cat'] !== (int)$cat['f_id']) continue; ?>
        <tr>
          <td>
            <a href="/forum/<?= (int)$f['f_id'] ?>"><?= htmlspecialchars($f['f_name']) ?></a>
            <br><small><?= htmlspecialchars($f['f_desc'] ?? '') ?></small>
          </td>
          <td><?= (int)($f['f_t'] ?? 0) ?> موضوع</td>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
  </div>
<?php endforeach; ?>
