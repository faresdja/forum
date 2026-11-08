<?php /* views/home/error.php — صفحة خطأ (بديل رسائل e_class القديمة) */ ?>
<div class="alert alert-danger">
  <h3>خطأ</h3>
  <p><?= htmlspecialchars($error ?? 'حدث خطأ غير متوقع') ?></p>
  <a href="/home" class="btn btn-default">العودة للرئيسية</a>
</div>
