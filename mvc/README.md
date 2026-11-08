# هيكل MVC بسيط (Router + Controller أساسي + طبقة PDO)

هذا المجلد يحتوي على إعادة هيكلة المشروع القديم (Procedural + Includes)
إلى نمط **MVC** بسيط، مع الحفاظ على قاعدة البيانات والجلسات الحالية.

## البنية

```
mvc/
├── bootstrap.php          ← نقطة الإقلاع: تحميل الإعدادات + PDO + الجلسات + المسارات
├── config.php             ← إعدادات الاتصال بالقاعدة
├── core/
│   ├── Router.php         ← الراوتر: يحوّل URL إلى Controller@action
│   ├── Controller.php     ← المتحكم الأساسي (render / json / redirect / requireLogin)
│   ├── Database.php       ← طبقة PDO (Singleton + Prepared Statements فقط)
│   └── View.php           ← محرك القوالب (ob_start + extract)
├── controllers/
│   ├── HomeController.php
│   ├── AuthController.php
│   └── UserController.php
└── views/
    ├── layout.php
    ├── home/index.php
    ├── auth/login.php
    └── users/list.php
```

## كيف يعمل التدفق (Request Flow)

1. `index.php` في جذر المشروع يستدعي `mvc/bootstrap.php`.
2. `Router::dispatch()` يقرأ `REQUEST_URI` ويطابقه مع المسارات المسجّلة.
3. الراوتر يستدعي دالة أو `Controller@action` مع وسائط المسار (params).
4. الـ Controller يرث من `Controller` الأساسي ويحصل على `$this->db` (طبقة PDO)
   و`$this->view` (محرك القوالب)، ثم يعرض View أو يحوّل أو يرجع JSON.

## أمثلة URLs بعد التحويل (.htaccess)

| الرابط الجديد        | الوجهة                      |
|----------------------|-----------------------------|
| `/`                  | `HomeController@index`      |
| `/home`              | `HomeController@index`      |
| `/forum/{id}`        | `HomeController@forum`      |
| `/topic/{id}/{page}` | `HomeController@topic`      |
| `/login`             | `AuthController@showLogin`  |
| `/logout`            | `AuthController@logout`     |
| `/users`             | `UserController@index`      |

## ملاحظات الأمان التي طُبّقت

- كل الاستعلامات عبر **Prepared Statements** (`:param`) — لا دمج SQL بالنص.
- كلمات المرور: `password_hash()` / `password_verify()` (كما في الكود الأصلي).
- `PDO::ERRMODE_EXCEPTION` + `FETCH_ASSOC` كوضع افتراضي للاتصال.
- المتغيرات العامة القديمة (`define("op", ...)`) استُبدلت بوسائط صريحة في الراوتر.
- الحماية من الدخول غير المسجّل عبر `requireLogin()` في المتحكم الأساسي.

## الدمج التدريجي مع الكود القديم

الملفات القديمة (`home.php`, `users.php`, `msg.php`, `post.php`...) ما زالت تعمل كما هي؛
يمكن تحويلها صفحة بصفحة عبر إضافة مسار جديد في `mvc/bootstrap.php`:

```php
Router::add('/path', 'ControllerName@method');
```
