<?php
/**
 * HomeController — واجهة المنتديات (رئيسية / منتدى / موضوع)
 * بديل عن home.php القديم الذي كان يفرّع على الثابت op بـ if/elseif.
 */
class HomeController extends Controller
{
    /** GET / أو /home — الرئيسية: الفئات والمنتديات */
    public function index(): void
    {
        // نفس استعلامات system/home.php القديمة لكن عبر طبقة PDO الجديدة.
        // ملاحظة: في الكود القديم الفئة هي سجل في f__forums بـ f_type = 0
        $cats = $this->db->select(
            'SELECT * FROM ' . $this->db->table('forums') . ' WHERE f_type = 0 ORDER BY f_id ASC'
        );

        $forums = $this->db->select(
            'SELECT * FROM ' . $this->db->table('forums') . ' WHERE f_type != 0 ORDER BY f_id ASC'
        );

        $this->render('home/index', [
            'pageTitle' => 'المنتديات',
            'cats'      => $cats,
            'forums'    => $forums,
            'me'        => $this->user(),
        ]);
    }

    /** GET /forums — قائمة كل المنتديات */
    public function forums(): void
    {
        $forums = $this->db->select(
            'SELECT * FROM ' . $this->db->table('forums') . ' WHERE f_type != 0 ORDER BY f_cat ASC, f_id ASC'
        );

        $this->render('home/index', [
            'pageTitle' => 'كل المنتديات',
            'cats'      => [],
            'forums'    => $forums,
            'me'        => $this->user(),
        ]);
    }

    /** GET /forum/{id} — مواضيع منتدى معين */
    public function forum(string $id): void
    {
        // تحقق من صحة رقم المنتدى (كان في post.php القديم: $numf == 0 => خطأ)
        $forum = $this->db->selectOne(
            'SELECT * FROM ' . $this->db->table('forums') . ' WHERE f_id = :id',
            [':id' => (int) $id]
        );

        if ($forum === null) {
            $this->render('home/error', [
                'pageTitle' => 'خطأ',
                'error'     => 'رقم المنتدى خاطئ',
            ], 404);
            return;
        }

        $page    = max(1, (int) ($_GET['page'] ?? 1));
        $perPage = 20;
        $offset  = ($page - 1) * $perPage;

        $topics = $this->db->select(
            'SELECT t.*, u.user_name
             FROM ' . $this->db->table('topics') . ' t
             LEFT JOIN ' . $this->db->table('users') . ' u ON u.user_id = t.user_id
             WHERE t.f_id = :fid
             ORDER BY t.t_id DESC
             LIMIT ' . $perPage . ' OFFSET ' . $offset,   // ثوابت لا مستعمل — آمنة
            [':fid' => (int) $id]
        );

        $this->render('home/forum', [
            'pageTitle' => $forum['f_name'],
            'forum'     => $forum,
            'topics'    => $topics,
            'page'      => $page,
            'me'        => $this->user(),
        ]);
    }

    /** GET /topic/{id} أو /topic/{id}/{page} — عرض موضوع واحد */
    public function topic(string $id, ?string $page = null): void
    {
        $topic = $this->db->selectOne(
            'SELECT t.*, u.user_name, u.user_avatar
             FROM ' . $this->db->table('topics') . ' t
             LEFT JOIN ' . $this->db->table('users') . ' u ON u.user_id = t.user_id
             WHERE t.t_id = :id',
            [':id' => (int) $id]
        );

        if ($topic === null) {
            $this->render('home/error', [
                'pageTitle' => 'خطأ',
                'error'     => 'الموضوع غير موجود',
            ], 404);
            return;
        }

        $replies = $this->db->select(
            'SELECT r.*, u.user_name, u.user_avatar, u.user_sig
             FROM ' . $this->db->table('replys') . ' r
             LEFT JOIN ' . $this->db->table('users') . ' u ON u.user_id = r.user_id
             WHERE r.t_id = :tid
             ORDER BY r.r_id ASC',
            [':tid' => (int) $id]
        );

        $this->render('home/topic', [
            'pageTitle' => $topic['t_name'],
            'topic'     => $topic,
            'replies'   => $replies,
            'me'        => $this->user(),
        ]);
    }

    /** POST /topic/{id}/reply — إضافة رد (بديل post.php القديمة في جزء الردود) */
    public function reply(string $id): void
    {
        $this->requireLogin(); // لا يردّ إلا عضو مسجّل (كانت $usergroup == 0 => redirect)

        $message = trim($_POST['reply'] ?? '');
        if ($message === '') {
            $this->redirect('/topic/' . (int) $id);
        }

        // نفس أعمدة INSERT القديمة: (t_id, user_id, r_msg, r_date)
        $this->db->insert($this->db->table('replys'), [
            't_id'   => (int) $id,
            'user_id'=> (int) $this->currentUserId(),
            'r_msg'  => $message,
            'r_date' => date('Y-m-d H:i:s'),
        ]);

        $this->redirect('/topic/' . (int) $id);
    }
}
