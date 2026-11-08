<?php
/**
 * index.php — الواجهة الأمامية (Front Controller) بنمط MVC
 *
 * كان الملف القديم يفرّع يدوياً على $_GET['op'] مع include مباشر؛
 * الآن كل شيء يمر عبر mvc/bootstrap.php ← Router ← Controller ← View/PDO.
 */

// إن وُجد ملف تثبيت لم يكتمل، حوّله كما في الكود القديم
if (file_exists("install/new.php")) {
    header("Location: install/new.php");
    exit;
}

require_once __DIR__ . '/mvc/bootstrap.php';
