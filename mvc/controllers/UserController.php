<?php
/**
 * UserController — قائمة الأعضاء وملف العضو (بديل users.php القديمة)
 */
class UserController extends Controller
{
    /** GET /users — قائمة الأعضاء */
    public function index(): void
    {
        $users = $this->db->select(
            'SELECT user_id, user_name, user_avatar, user_gender, user_to, user_re
             FROM ' . $this->db->table('users') . '
             ORDER BY user_id DESC'
        );

        $this->render('users/list', [
            'pageTitle' => 'قائمة الاعضاء',
            'users'     => $users,
            'me'        => $this->user(),
        ]);
    }

    /** GET /user/{id} — ملف عضو */
    public function show(string $id): void
    {
        $u = $this->db->selectOne(
            'SELECT * FROM ' . $this->db->table('users') . ' WHERE user_id = :id',
            [':id' => (int) $id]
        );

        if ($u === null) {
            // نفس رسالة الخطأ القديمة: رقم العضوية خاطئ
            $this->render('home/error', [
                'pageTitle' => 'خطأ',
                'error'     => 'رقم العضوية خاطئ',
            ], 404);
            return;
        }

        $topics = $this->db->select(
            'SELECT t_id, t_name, t_date FROM ' . $this->db->table('topics') . '
             WHERE user_id = :uid ORDER BY t_id DESC LIMIT 20',
            [':uid' => (int) $id]
        );

        $this->render('users/profile', [
            'pageTitle' => 'ملف: ' . $u['user_name'],
            'u'         => $u,
            'topics'    => $topics,
            'me'        => $this->user(),
        ]);
    }

    /** POST /api/users — مثال REST API باستخدام طبقة PDO + JSON */
    public function store(): void
    {
        $this->requireGroup(4); // إداري فقط يستطيع إنشاء أعضاء عبر API

        $data = json_decode(file_get_contents('php://input'), true) ?? [];

        $uname = trim($data['username'] ?? '');
        $umail = trim($data['email'] ?? '');
        $upass = $data['password'] ?? '';

        if ($uname === '' || !filter_var($umail, FILTER_VALIDATE_EMAIL) || strlen($upass) < 6) {
            $this->json(['ok' => false, 'error' => 'بيانات غير صالحة'], 422);
        }

        $id = $this->db->insert($this->db->table('users'), [
            'user_name'  => $uname,
            'user_email' => $umail,
            'user_pass'  => password_hash($upass, PASSWORD_DEFAULT),
            'user_age'   => (int) ($data['age'] ?? 0),
            'user_gender'=> (int) ($data['gender'] ?? 0),
        ]);

        $this->json(['ok' => true, 'user_id' => $id], 201);
    }
}
