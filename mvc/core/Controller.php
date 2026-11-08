<?php
/**
 * Controller.php — المتحكم الأساسي (Base Controller)
 *
 * كل المتحكمات ترث منه، ويوفر:
 *  - $this->db     : طبقة PDO (Database)
 *  - $this->view   : محرك القوالب (View)
 *  - render/json/redirect : استجابات جاهزة
 *  - user() / requireLogin() / currentUserId() : جلسة المستخدم
 *    (يستخدم نفس مفتاح الجلسة القديم 'user_session' حتى يعمل الدخول مع الكود القديم).
 */
abstract class Controller
{
    /** @var Database */
    protected $db;

    /** @var View */
    protected $view;

    public function __construct()
    {
        // طبقة PDO: اتصال واحد لكل الطلب حتى مع تعدد النسخ
        $this->db = new Database();

        // محرك العرض
        $this->view = new View();
    }

    /* ==================== الاستجابات ==================== */

    /** عرض قالب مع تخطيط عام وإرساله. */
    protected function render(string $template, array $data = [], int $status = 200): void
    {
        http_response_code($status);
        echo $this->view->render($template, $data);
    }

    /** عرض قالب بدون تخطيط (partials / AJAX). */
    protected function partial(string $template, array $data = []): void
    {
        echo $this->view->partial($template, $data);
    }

    /** إرجاع JSON. */
    protected function json($data, int $status = 200): void
    {
        http_response_code($status);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
        exit;
    }

    /** تحويل (Redirect) ثم إيقاف التنفيذ. */
    protected function redirect(string $to, int $status = 302): void
    {
        http_response_code($status);
        header('Location: ' . $to);
        exit;
    }

    /* ==================== الجلسة والمستخدم ==================== */

    /** هل المستخدم مسجّل الدخول؟ (نفس منطق class.session.php القديمة) */
    protected function isLoggedin(): bool
    {
        return isset($_SESSION['user_session']) && $_SESSION['user_session'] !== '';
    }

    /** معرف المستخدم الحالي أو null. */
    protected function currentUserId(): ?int
    {
        return $this->isLoggedin() ? (int) $_SESSION['user_session'] : null;
    }

    /** صف المستخدم الكامل من جدول f__users أو null. */
    protected function user(): ?array
    {
        if (!$this->isLoggedin()) {
            return null;
        }
        static $cache = null;
        if ($cache === null) {
            $cache = $this->db->selectOne(
                'SELECT * FROM ' . $this->db->table('users') . ' WHERE user_id = :uid',
                [':uid' => $this->currentUserId()]
            );
        }
        return $cache;
    }

    /** حماية الصفحات: يحوّل لصفحة الدخول إن لم يكن مسجلاً. */
    protected function requireLogin(): void
    {
        if (!$this->isLoggedin()) {
            $this->redirect('/login');
        }
    }

    /** حماية صفحات الإدارة: مجموعة المستخدم > 0 فقط. */
    protected function requireGroup(int $minGroup = 1): void
    {
        $this->requireLogin();
        $u = $this->user();
        if ($u === null || (int) $u['user_group'] < $minGroup) {
            http_response_code(403);
            die('<h1>403 — لا تملك صلاحية الوصول</h1>');
        }
    }
}
