<?php
/**
 * config.php — إعدادات التطبيق (MVC)
 * نفس إعدادات الكود القديم (inc____/class.database.php) مع دعم متغيرات البيئة.
 */

return [
    // إعدادات قاعدة البيانات (PDO / MySQL)
    'db' => [
        'host'    => getenv('DB_HOST') ?: 'localhost',
        'name'    => getenv('DB_NAME') ?: '2223',
        'user'    => getenv('DB_USER') ?: 'root',
        'pass'    => getenv('DB_PASS') !== false ? getenv('DB_PASS') : 'root',
        'charset' => 'utf8',
        // بادئة الجداول القديمة "f__" (انظر $prx في class.database.php)
        'prefix'  => 'f__',
    ],

    // إعدادات عامة
    'app' => [
        'name'     => 'منتديات فووريم',
        'timezone' => 'Africa/Algiers',
        'debug'    => true, // اجعلها false في الإنتاج
        'base_url' => '',   // مثل: 'http://localhost/forum' عند التثبيت في مجلد فرعي
    ],
];
