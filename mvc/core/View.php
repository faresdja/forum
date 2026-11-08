<?php
/**
 * View.php — محرك قوالب بسيط (View Layer)
 *
 * بديل عن class.tpl.php القديمة التي كانت تعتمد على ثوابت عامة (define) لكل قالب.
 * يعمل بـ output buffering + extract، مع تخطيط عام اختياري (layout).
 */
class View
{
    /** @var string مجلد القوالب */
    private $basePath;

    /** @var string|null التخطيط الافتراضي */
    private $layout = 'layout';

    public function __construct(?string $basePath = null)
    {
        $this->basePath = rtrim($basePath ?? (__DIR__ . '/../views'), '/');
    }

    /** تعيين تخطيط عام افتراضي (مرر null لتعطيله). */
    public function setLayout(?string $layout): void
    {
        $this->layout = $layout;
    }

    /**
     * عرض قالب وإرجاع الناتج كنص.
     *
     * @param string $template مسار نسبي داخل views/ مثل 'home/index'
     * @param array  $data     متغيرات تُستخرج داخل القالب
     */
    public function render(string $template, array $data = []): string
    {
        $content = $this->renderFile($template, $data);

        if ($this->layout !== null) {
            // $content متاح داخل التخطيط
            return $this->renderFile($this->layout, array_merge($data, ['content' => $content]));
        }

        return $content;
    }

    /** عرض قالب بدون تخطيط (للأجزاء partials أو AJAX). */
    public function partial(string $template, array $data = []): string
    {
        return $this->renderFile($template, $data);
    }

    /** داخلي: include آمن داخل نطاق معزول. */
    private function renderFile(string $template, array $data): string
    {
        $path = $this->basePath . '/' . str_replace(['..', "\0"], '', $template) . '.php';

        if (!is_file($path)) {
            throw new RuntimeException("القالب غير موجود: $template");
        }

        extract($data, EXTR_SKIP); // لا يسحق المتغيرات الداخلية مثل $path
        ob_start();
        include $path;
        return ob_get_clean();
    }
}
