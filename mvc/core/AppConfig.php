<?php
/**
 * AppConfig.php — مخزن الإعدادات المركزي (Simple Registry)
 * بديل عن ثوابت define() المنتشرة في global.php القديم.
 */
class AppConfig
{
    /** @var array|null */
    private static $config = null;

    /** @var AppConfig|null */
    private static $self = null;

    public static function load(array $config): void
    {
        self::$config = $config;
        self::$self   = new self();
    }

    public static function instance(): AppConfig
    {
        if (self::$self === null) {
            throw new RuntimeException('لم تُحمَّل الإعدادات بعد، نادِ AppConfig::load() أولاً.');
        }
        return self::$self;
    }

    /**
     * قراءة إعداد بنقطة مسار: get('db.host') أو get('db').
     */
    public function get(string $key, $default = null)
    {
        $segments = explode('.', $key);
        $value    = self::$config;
        foreach ($segments as $seg) {
            if (!is_array($value) || !array_key_exists($seg, $value)) {
                return $default;
            }
            $value = $value[$seg];
        }
        return $value;
    }
}
