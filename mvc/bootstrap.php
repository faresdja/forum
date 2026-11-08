<?php
/**
 * bootstrap.php — نقطة الإقلاع (تُستدعى من index.php في جذر المشروع)
 *
 * يستبدل global.php القديم:
 *  - تحميل الإعدادات عبر AppConfig بدل سلسلة require_once + define العامة.
 *  - بدء الجلسة بنفس اسم الجلسة القديم (MYAPPSESS) للتوافق.
 *  - تسجيل المسارات (Routes) وتوجيه الطلب عبر Router.
 */

define('MVC_ROOT', __DIR__);

/* 1) الإعدادات */
require_once MVC_ROOT . '/core/AppConfig.php';
AppConfig::load(require MVC_ROOT . '/config.php');

$appCfg   = AppConfig::instance()->get('app');
$GLOBALS['APP_DEBUG'] = $appCfg['debug'];

date_default_timezone_set($appCfg['timezone']); // Africa/Algiers كما في global.php

if ($appCfg['debug']) {
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);
} else {
    ini_set('display_errors', 0);
    error_reporting(0);
}

/* 2) النوى (Core) */
require_once MVC_ROOT . '/core/Database.php';
require_once MVC_ROOT . '/core/View.php';
require_once MVC_ROOT . '/core/Controller.php';
require_once MVC_ROOT . '/core/Router.php';

/* 3) الجلسات — توافقية مع الكود القديم (class.session.php) */
if (session_status() === PHP_SESSION_NONE) {
    session_name('MYAPPSESS');               // نفس اسم الجلسة القديمة
    session_start();
}

/* 4) المسارات (Routes) — بديل عن home.php?op=... و .htaccess */
Router::get('/',              'HomeController@index');
Router::get('/home',          'HomeController@index');
Router::get('/forums',        'HomeController@forums');
Router::get('/forum/{id}',    'HomeController@forum');
Router::get('/topic/{id}',    'HomeController@topic');
Router::get('/topic/{id}/{page}', 'HomeController@topic');
Router::post('/topic/{id}/reply', 'HomeController@reply');

Router::get('/login',         'AuthController@showLogin');
Router::post('/login',        'AuthController@login');
Router::get('/register',      'AuthController@showRegister');
Router::post('/register',     'AuthController@register');
Router::get('/logout',        'AuthController@logout');
Router::post('/logout',       'AuthController@logout');

Router::get('/users',         'UserController@index');
Router::get('/user/{id}',     'UserController@show');
Router::post('/api/users',    'UserController@store'); // مثال JSON API

// أي مسار قديم (.html/.php) غير معروف => 404 مخصص
Router::fallback(function () {
    http_response_code(404);
    echo '<h1>404 — الصفحة غير موجودة</h1><a href="/">العودة للرئيسية</a>';
});

/* 5) التوجيه */
Router::dispatch();
