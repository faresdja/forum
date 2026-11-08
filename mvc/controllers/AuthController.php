<?php
/**
 * AuthController — تسجيل الدخول / الخروج / التسجيل
 * بديل عن index.php + system/login.php + system/sign-up.php القديمة.
 * يستخدم نفس جدول f__users ونفس مفتاح الجلسة user_session للتوافق مع الكود القديم.
 */
class AuthController extends Controller
{
    /** GET /login — نموذج الدخول */
    public function showLogin(): void
    {
        if ($this->isLoggedin()) {
            $this->redirect('/home');
        }
        $this->render('auth/login', ['pageTitle' => 'الدخول', 'error' => null]);
    }

    /** POST /login — التحقق من البيانات (Prepared Statements + password_verify) */
    public function login(): void
    {
        $name = trim($_POST['username'] ?? '');
        $pass = $_POST['password'] ?? '';

        if ($name === '' || $pass === '') {
            $this->render('auth/login', [
                'pageTitle' => 'الدخول',
                'error'     => 'ادخل اسم المستخدم وكلمة المرور',
            ], 422);
            return;
        }

        // مثل class.user.php القديمة: الدخول بالاسم أو البريد
        $row = $this->db->selectOne(
            'SELECT user_id, user_name, user_pass FROM ' . $this->db->table('users') . '
             WHERE user_name = :u OR user_email = :u LIMIT 1',
            [':u' => $name]
        );

        if ($row === null || !password_verify($pass, $row['user_pass'])) {
            $this->render('auth/login', [
                'pageTitle' => 'الدخول',
                'error'     => 'بيانات الدخول غير صحيحة',
            ], 422);
            return;
        }

        // إعادة توليد معرّف الجلسة لمنع Session Fixation
        session_regenerate_id(true);
        $_SESSION['user_session'] = $row['user_id'];

        $this->redirect('/home');
    }

    /** GET /register — نموذج التسجيل */
    public function showRegister(): void
    {
        $this->render('auth/register', ['pageTitle' => 'التسجيل', 'error' => null]);
    }

    /** POST /register — إنشاء حساب جديد بـ password_hash */
    public function register(): void
    {
        $uname   = trim($_POST['username'] ?? '');
        $umail   = trim($_POST['email'] ?? '');
        $upass   = $_POST['password'] ?? '';
        $uage    = (int) ($_POST['age'] ?? 0);
        $ugender = in_array($_POST['gender'] ?? '', ['1', '2'], true) ? (int) $_POST['gender'] : 0;

        if ($uname === '' || $umail === '' || strlen($upass) < 6 || !filter_var($umail, FILTER_VALIDATE_EMAIL)) {
            $this->render('auth/register', [
                'pageTitle' => 'التسجيل',
                'error'     => 'تحقق من البيانات (البريد غير صالح أو كلمة المرور أقصر من 6 أحرف)',
            ], 422);
            return;
        }

        // منع تكرار الاسم/البريد (مثل register في class.user.php)
        $exists = $this->db->scalar(
            'SELECT COUNT(*) FROM ' . $this->db->table('users') . ' WHERE user_name = :u OR user_email = :e',
            [':u' => $uname, ':e' => $umail]
        );

        if ((int) $exists > 0) {
            $this->render('auth/register', [
                'pageTitle' => 'التسجيل',
                'error'     => 'اسم المستخدم أو البريد مستخدم مسبقاً',
            ], 422);
            return;
        }

        $this->db->insert($this->db->table('users'), [
            'user_name'   => $uname,
            'user_email'  => $umail,
            'user_pass'   => password_hash($upass, PASSWORD_DEFAULT),
            'user_age'    => $uage,
            'user_gender' => $ugender,
        ]);

        $this->redirect('/login');
    }

    /** GET|POST /logout — إنهاء الجلسة (بديل logout.php القديم) */
    public function logout(): void
    {
        $_SESSION = [];
        if (ini_get('session.use_cookies')) {
            $p = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $p['path'], $p['domain'], $p['secure'], $p['httponly']);
        }
        session_destroy();
        $this->redirect('/login');
    }
}
