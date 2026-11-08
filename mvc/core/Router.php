<?php
/**
 * Router.php — الراوتر (Front Controller Dispatch)
 *
 * بديل عن منطق التوجيه القديم:
 *   home.php?op=forum&id=1  /  users.php?op=friends&type=add&u=$1  /  .htaccess مشوّش
 * الآن كل مسار يربط URL بـ Controller@action بشكل مركزي وواضح.
 */
class Router
{
    /** @var array<string, array<string, string|array>> المسارات حسب الطريقة HTTP */
    private static $routes = [];

    /** @var string|null نقطة النهاية عند عدم تطابق أي مسار */
    private static $fallback = null;

    /**
     * تسجيل مسار GET.
     * الصيغة: '/forum/{id}' => 'HomeController@forum' أو دالة مُغلَقة (Closure).
     */
    public static function get(string $pattern, $handler): void
    {
        self::add('GET', $pattern, $handler);
    }

    /** تسجيل مسار POST. */
    public static function post(string $pattern, $handler): void
    {
        self::add('POST', $pattern, $handler);
    }

    /** تسجيل مسار لأي طريقة HTTP. */
    public static function add(string $method, string $pattern, $handler): void
    {
        self::$routes[strtoupper($method)][self::normalize($pattern)] = $handler;
    }

    /** مسار افتراضي (404). */
    public static function fallback($handler): void
    {
        self::$fallback = $handler;
    }

    /**
     * تنفيذ التوجيه: قراءة REQUEST_URI ومطابقتها مع المسارات.
     */
    public static function dispatch(): void
    {
        $method = strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET');
        $uri    = self::normalize(parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH));

        foreach (self::$routes[$method] ?? [] as $pattern => $handler) {
            $regex = preg_quote($pattern, '#');
            // تحويل {name} إلى مجموعة تسمية (?<name>[^/]+)
            $regex = preg_replace('#\\\\\{(\w+)\\\\\}#', '(?<$1>[^/]+)', $regex);

            if (preg_match('#^' . $regex . '$#u', $uri, $matches)) {
                $params = array_filter($matches, 'is_string', ARRAY_FILTER_USE_KEY);
                self::invoke($handler, $params);
                return;
            }
        }

        // لا يوجد تطابق => 404
        http_response_code(404);
        if (self::$fallback !== null) {
            self::invoke(self::$fallback, []);
        } else {
            echo '<h1>404 — الصفحة غير موجودة</h1>';
        }
    }

    /** استدعاء الـ Handler: Closure أو "Controller@action". */
    private static function invoke($handler, array $params): void
    {
        if ($handler instanceof Closure) {
            call_user_func_array($handler, array_values($params));
            return;
        }

        if (!is_string($handler) || !str_contains($handler, '@')) {
            throw new RuntimeException('صيغة المعالج غير صحيحة، استخدم "ControllerName@method".');
        }

        [$controllerName, $action] = explode('@', $handler, 2);

        // تحميل ملف المتحكم تلقائياً من controllers/
        $file = __DIR__ . '/../controllers/' . $controllerName . '.php';
        if (!is_file($file)) {
            throw new RuntimeException("ملف المتحكم غير موجود: $controllerName");
        }
        require_once $file;

        if (!class_exists($controllerName) || !method_exists($controllerName, $action)) {
            throw new RuntimeException("المتحكم أو الدالة غير موجودين: $handler");
        }

        $controller = new $controllerName();
        call_user_func_array([$controller, $action], array_values($params));
    }

    /** توحيد شكل المسار: إزالة الشرطة الزائدة وجعل الجذر '/'. */
    private static function normalize(string $uri): string
    {
        $uri = '/' . trim(rawurldecode($uri), '/');
        return $uri === '/' ? '/' : rtrim($uri, '/');
    }
}
