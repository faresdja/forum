<?php
/**
 * Database.php — طبقة PDO بسيطة (Model Layer)
 *
 * بديل حديث عن الصنف القديم inc____/class.database.php:
 *  - اتصال PDO واحد فقط لكل طلب (static) رغم تعدد النسخ (كان الكود القديم ينشئ new Database() في كل مكان).
 *  - Prepared Statements حصراً عبر select()/selectOne()/insert()/update()/delete().
 *  - أوضاع PDO ثابتة: ERRMODE_EXCEPTION + FETCH_ASSOC.
 */
class Database
{
    /** @var PDO|null الاتصال الفريد */
    private static $pdo = null;

    /** @var string بادئة الجداول f__ */
    private $prefix;

    public function __construct(?array $cfg = null)
    {
        if ($cfg === null) {
            $cfg = AppConfig::instance()->get('db');
        }

        $dsn = sprintf(
            'mysql:host=%s;dbname=%s;charset=%s',
            $cfg['host'],
            $cfg['name'],
            $cfg['charset']
        );

        try {
            self::$pdo = new PDO($dsn, $cfg['user'], $cfg['pass'], [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION, // رمي استثناء بدل الطباعة
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,       // مصفوفات ترابطية افتراضياً
                PDO::ATTR_EMULATE_PREPARES   => false,                  // Prepared Statements حقيقية
            ]);
        } catch (PDOException $e) {
            // في وضع التطوير اعرض السبب، وفي الإنتاج سجّله فقط
            if (!empty($GLOBALS['APP_DEBUG'])) {
                die('لا يوجد إتصال بالقاعدة والسبب: ' . htmlspecialchars($e->getMessage()));
            }
            error_log('DB connection failed: ' . $e->getMessage());
            die('حدث خطأ في الاتصال بقاعدة البيانات.');
        }

        $this->prefix = $cfg['prefix'] ?? '';
    }

    /**
     * الحصول على اتصال PDO الفريد.
     * يُنشأ عند أول استخدام من إعدادات AppConfig.
     */
    public static function getPdo(): PDO
    {
        if (self::$pdo === null) {
            new self(); // يقرأ AppConfig::instance()->get('db')
        }
        return self::$pdo;
    }

    /* ================== واجهة استعلامات آمنة ================== */

    /** تنفيذ استعلام معلماته جاهزة وإرجاع Statement */
    public function run(string $sql, array $params = []): PDOStatement
    {
        $stmt = self::getPdo()->prepare($sql);
        $stmt->execute($params);
        return $stmt;
    }

    /** جلب عدة صفوف */
    public function select(string $sql, array $params = []): array
    {
        return $this->run($sql, $params)->fetchAll();
    }

    /** جلب صف واحد أو null */
    public function selectOne(string $sql, array $params = []): ?array
    {
        $row = $this->run($sql, $params)->fetch();
        return $row === false ? null : $row;
    }

    /** جلب عمود واحد (مثل COUNT) */
    public function scalar(string $sql, array $params = [])
    {
        return $this->run($sql, $params)->fetchColumn();
    }

    /** إدراج راجعاً آخر ID */
    public function insert(string $table, array $data): int
    {
        $cols = implode(', ', array_map(fn($c) => "`$c`", array_keys($data)));
        $phs  = implode(', ', array_map(fn($c) => ":$c", array_keys($data)));
        $this->run("INSERT INTO `$table` ($cols) VALUES ($phs)", $data);
        return (int) self::getPdo()->lastInsertId();
    }

    /** تحديث مع WHERE كمصفوفة */
    public function update(string $table, array $data, array $where): int
    {
        $set = implode(', ', array_map(fn($c) => "`$c` = :set_$c", array_keys($data)));
        $whr = implode(' AND ', array_map(fn($c) => "`$c` = :w_$c", array_keys($where)));

        $params = [];
        foreach ($data  as $k => $v) { $params["set_$k"] = $v; }
        foreach ($where as $k => $v) { $params["w_$k"]   = $v; }

        return $this->run("UPDATE `$table` SET $set WHERE $whr", $params)->rowCount();
    }

    /** حذف */
    public function delete(string $table, array $where): int
    {
        $whr = implode(' AND ', array_map(fn($c) => "`$c` = :$c", array_keys($where)));
        return $this->run("DELETE FROM `$table` WHERE $whr", $where)->rowCount();
    }

    /** اسم جدول مع البادئة: table('users') => f__users */
    public function table(string $name): string
    {
        return $this->prefix . $name;
    }
}
