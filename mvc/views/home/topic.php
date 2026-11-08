<?php /* views/home/topic.php — الموضوع والردود */ ?>
<div class="panel panel-primary">
  <div class="panel-heading"><?= htmlspecialchars($pageTitle) ?></div>
  <div class="panel-body">
    <p><?= nl2br(htmlspecialchars($topic['t_msg'] ?? '')) ?></p>
    <footer>بواسطة: <?= htmlspecialchars($topic['user_name'] ?? '') ?></footer>
  </div>
</div>

<?php foreach ($replies as $r): ?>
  <div class="panel panel-default">
    <div class="panel-body">
      <p><?= nl2br(htmlspecialchars($r['r_msg'] ?? '')) ?></p>
      <footer>
        <?= htmlspecialchars($r['user_name'] ?? '') ?>
        <small><?= htmlspecialchars($r['r_date'] ?? '') ?></small>
      </footer>
    </div>
  </div>
<?php endforeach; ?>

<?php if (!empty($me)): ?>
<form method="post" action="/topic/<?= (int)$topic['t_id'] ?>/reply">
  <textarea name="reply" class="form-control" rows="4" placeholder="اكتب ردك..."></textarea>
  <button type="submit" class="btn btn-primary">إرسال الرد</button>
</form>
<?php else: ?>
  <div class="alert alert-warning"><a href="/login">سجّل الدخول</a> للرد على هذا الموضوع.</div>
<?php endif; ?>
