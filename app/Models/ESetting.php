<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Crypt;

class ESetting extends Model
{
    protected $table = 'e_settings';

    protected $fillable = [
        'setting_group',
        'setting_key',
        'setting_value',
    ];

    /**
     * Get a setting value by group and key
     */
    public static function get(string $group, string $key, $default = null)
    {
        $setting = self::where('setting_group', $group)
            ->where('setting_key', $key)
            ->first();

        if (!$setting) {
            return $default;
        }

        $value = $setting->setting_value;

        // Try to decode JSON
        if ($value && (str_starts_with($value, '[') || str_starts_with($value, '{'))) {
            $decoded = json_decode($value, true);
            if (json_last_error() === JSON_ERROR_NONE) {
                return $decoded;
            }
        }

        return $value;
    }

    /**
     * Set a setting value
     */
    public static function set(string $group, string $key, $value): void
    {
        if (is_array($value)) {
            $value = json_encode($value);
        }

        self::updateOrCreate(
            ['setting_group' => $group, 'setting_key' => $key],
            ['setting_value' => (string) $value]
        );
    }

    /**
     * Get all settings by group
     */
    public static function getGroup(string $group): array
    {
        $settings = self::where('setting_group', $group)->get();
        $result = [];

        foreach ($settings as $setting) {
            $value = $setting->setting_value;

            // Try to decode JSON
            if ($value && (str_starts_with($value, '[') || str_starts_with($value, '{'))) {
                $decoded = json_decode($value, true);
                if (json_last_error() === JSON_ERROR_NONE) {
                    $value = $decoded;
                }
            }

            $result[$setting->setting_key] = $value;
        }

        return $result;
    }

    /**
     * Set multiple settings for a group
     */
    public static function setGroup(string $group, array $data): void
    {
        foreach ($data as $key => $value) {
            self::set($group, $key, $value);
        }
    }

    /**
     * Get website info settings
     */
    public static function getWebsiteInfo(): array
    {
        return self::getGroup('website');
    }

    /**
     * Get logo URL
     */
    public static function getLogo(): ?string
    {
        $logo = self::get('website', 'logo');
        return $logo ?: null;
    }

    /**
     * Set logo path
     */
    public static function setLogo(?string $path): void
    {
        self::set('website', 'logo', $path);
    }

    /**
     * Get raw logo path (without asset)
     */
    public static function getLogoPath(): ?string
    {
        return self::get('website', 'logo');
    }

    /**
     * Get favicon URL
     */
    public static function getFavicon(): ?string
    {
        $favicon = self::get('website', 'favicon');
        return $favicon ?: null;
    }

    /**
     * Set favicon path
     */
    public static function setFavicon(?string $path): void
    {
        self::set('website', 'favicon', $path);
    }

    /**
     * Get email SMTP settings
     */
    public static function getEmailSmtp(): array
    {
        $settings = self::getGroup('email_smtp');

        // Decrypt password if exists
        if (!empty($settings['password'])) {
            try {
                $settings['password'] = Crypt::decryptString($settings['password']);
            } catch (\Exception $e) {
                // Password might not be encrypted yet
            }
        }

        return $settings;
    }

    /**
     * Set email SMTP settings (with password encryption)
     */
    public static function setEmailSmtp(array $data): void
    {
        // Encrypt password if provided
        if (!empty($data['password'])) {
            $data['password'] = Crypt::encryptString($data['password']);
        }

        self::setGroup('email_smtp', $data);
    }

    /**
     * Apply email SMTP settings to Laravel config
     */
    public static function applyMailConfig(): void
    {
        $smtp = self::getEmailSmtp();

        if (!empty($smtp['is_active']) && $smtp['is_active'] === '1') {
            config([
                'mail.default' => 'smtp',
                'mail.mailers.smtp.host' => $smtp['host'] ?? 'smtp.gmail.com',
                'mail.mailers.smtp.port' => (int) ($smtp['port'] ?? 587),
                'mail.mailers.smtp.username' => $smtp['username'] ?? '',
                'mail.mailers.smtp.password' => $smtp['password'] ?? '',
                'mail.mailers.smtp.encryption' => $smtp['encryption'] ?? 'tls',
                'mail.from.address' => $smtp['from_email'] ?? $smtp['username'] ?? '',
                'mail.from.name' => $smtp['from_name'] ?? config('app.name'),
            ]);
        }
    }

    /**
     * Get analytics services configuration
     */
    public static function getAnalyticsServices(): array
    {
        $services = self::get('analytics', 'services', []);
        return is_array($services) ? $services : [];
    }

    /**
     * Set analytics services configuration
     */
    public static function setAnalyticsServices(array $services): void
    {
        self::set('analytics', 'services', $services);
    }

    /**
     * Get active analytics scripts for embedding in frontend
     * Returns array with 'head' and 'body' scripts
     */
    public static function getActiveAnalyticsScripts(): array
    {
        $services = self::getAnalyticsServices();
        $scripts = ['head' => '', 'body' => ''];

        foreach ($services as $service) {
            if (empty($service['is_active']) || $service['is_active'] !== '1') {
                continue;
            }

            $type = $service['type'] ?? '';
            $code = $service['code'] ?? '';
            $position = $service['position'] ?? 'head';

            switch ($type) {
                case 'google_analytics':
                    if (!empty($code)) {
                        $scripts['head'] .= "<!-- Google Analytics -->
<script async src=\"https://www.googletagmanager.com/gtag/js?id={$code}\"></script>
<script>
  window.dataLayer = window.dataLayer || [];
  function gtag(){dataLayer.push(arguments);}
  gtag('js', new Date());
  gtag('config', '{$code}');
</script>\n";
                    }
                    break;

                case 'google_tag_manager':
                    if (!empty($code)) {
                        $scripts['head'] .= "<!-- Google Tag Manager -->
<script>(function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':
new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],
j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src=
'https://www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);
})(window,document,'script','dataLayer','{$code}');</script>\n";
                        $scripts['body'] .= "<!-- Google Tag Manager (noscript) -->
<noscript><iframe src=\"https://www.googletagmanager.com/ns.html?id={$code}\"
height=\"0\" width=\"0\" style=\"display:none;visibility:hidden\"></iframe></noscript>\n";
                    }
                    break;

                case 'facebook_pixel':
                    if (!empty($code)) {
                        $scripts['head'] .= "<!-- Facebook Pixel -->
<script>
!function(f,b,e,v,n,t,s)
{if(f.fbq)return;n=f.fbq=function(){n.callMethod?
n.callMethod.apply(n,arguments):n.queue.push(arguments)};
if(!f._fbq)f._fbq=n;n.push=n;n.loaded=!0;n.version='2.0';
n.queue=[];t=b.createElement(e);t.async=!0;
t.src=v;s=b.getElementsByTagName(e)[0];
s.parentNode.insertBefore(t,s)}(window, document,'script',
'https://connect.facebook.net/en_US/fbevents.js');
fbq('init', '{$code}');
fbq('track', 'PageView');
</script>
<noscript><img height=\"1\" width=\"1\" style=\"display:none\"
src=\"https://www.facebook.com/tr?id={$code}&ev=PageView&noscript=1\"/></noscript>\n";
                    }
                    break;

                case 'tiktok_pixel':
                    if (!empty($code)) {
                        $scripts['head'] .= "<!-- TikTok Pixel -->
<script>
!function (w, d, t) {
  w.TiktokAnalyticsObject=t;var ttq=w[t]=w[t]||[];ttq.methods=[\"page\",\"track\",\"identify\",\"instances\",\"debug\",\"on\",\"off\",\"once\",\"ready\",\"alias\",\"group\",\"enableCookie\",\"disableCookie\"],ttq.setAndDefer=function(t,e){t[e]=function(){t.push([e].concat(Array.prototype.slice.call(arguments,0)))}};for(var i=0;i<ttq.methods.length;i++)ttq.setAndDefer(ttq,ttq.methods[i]);ttq.instance=function(t){for(var e=ttq._i[t]||[],n=0;n<ttq.methods.length;n++)ttq.setAndDefer(e,ttq.methods[n]);return e},ttq.load=function(e,n){var i=\"https://analytics.tiktok.com/i18n/pixel/events.js\";ttq._i=ttq._i||{},ttq._i[e]=[],ttq._i[e]._u=i,ttq._t=ttq._t||{},ttq._t[e]=+new Date,ttq._o=ttq._o||{},ttq._o[e]=n||{};var o=document.createElement(\"script\");o.type=\"text/javascript\",o.async=!0,o.src=i+\"?sdkid=\"+e+\"&lib=\"+t;var a=document.getElementsByTagName(\"script\")[0];a.parentNode.insertBefore(o,a)};
  ttq.load('{$code}');
  ttq.page();
}(window, document, 'ttq');
</script>\n";
                    }
                    break;

                case 'hotjar':
                    if (!empty($code)) {
                        $scripts['head'] .= "<!-- Hotjar Tracking -->
<script>
(function(h,o,t,j,a,r){
    h.hj=h.hj||function(){(h.hj.q=h.hj.q||[]).push(arguments)};
    h._hjSettings={hjid:{$code},hjsv:6};
    a=o.getElementsByTagName('head')[0];
    r=o.createElement('script');r.async=1;
    r.src=t+h._hjSettings.hjid+j+h._hjSettings.hjsv;
    a.appendChild(r);
})(window,document,'https://static.hotjar.com/c/hotjar-','.js?sv=');
</script>\n";
                    }
                    break;

                case 'clarity':
                    if (!empty($code)) {
                        $scripts['head'] .= "<!-- Microsoft Clarity -->
<script type=\"text/javascript\">
(function(c,l,a,r,i,t,y){
    c[a]=c[a]||function(){(c[a].q=c[a].q||[]).push(arguments)};
    t=l.createElement(r);t.async=1;t.src=\"https://www.clarity.ms/tag/\"+i;
    y=l.getElementsByTagName(r)[0];y.parentNode.insertBefore(t,y);
})(window, document, \"clarity\", \"script\", \"{$code}\");
</script>\n";
                    }
                    break;

                case 'custom':
                    if (!empty($code)) {
                        $scripts[$position] .= "<!-- Custom Script -->\n{$code}\n";
                    }
                    break;
            }
        }

        return $scripts;
    }

    /**
     * Get available analytics service types
     */
    public static function getAnalyticsTypes(): array
    {
        return [
            ['value' => 'google_analytics', 'label' => 'Google Analytics (GA4)', 'placeholder' => 'G-XXXXXXXXXX', 'icon' => 'fab fa-google'],
            ['value' => 'google_tag_manager', 'label' => 'Google Tag Manager', 'placeholder' => 'GTM-XXXXXXX', 'icon' => 'fas fa-tags'],
            ['value' => 'facebook_pixel', 'label' => 'Facebook Pixel', 'placeholder' => '1234567890123456', 'icon' => 'fab fa-facebook'],
            ['value' => 'tiktok_pixel', 'label' => 'TikTok Pixel', 'placeholder' => 'XXXXXXXXXX', 'icon' => 'fab fa-tiktok'],
            ['value' => 'hotjar', 'label' => 'Hotjar', 'placeholder' => '1234567', 'icon' => 'fas fa-fire'],
            ['value' => 'clarity', 'label' => 'Microsoft Clarity', 'placeholder' => 'xxxxxxxxxx', 'icon' => 'fab fa-microsoft'],
            ['value' => 'custom', 'label' => 'Custom Script', 'placeholder' => '<script>...</script>', 'icon' => 'fas fa-code'],
        ];
    }

    /**
     * Get default theme colors (8 colors)
     */
    public static function getDefaultThemeColors(): array
    {
        return [
            'color_primary' => '#1a2a4d',
            'color_primary_dark' => '#0f1a30',
            'color_secondary' => '#2eb72e',
            'color_secondary_dark' => '#259625',
            'color_menu_hover' => '#2eb72e',
            'color_icon' => '#2eb72e',
            'color_text' => '#333333',
            'color_text_secondary' => '#666666',
            'color_text_tertiary' => '#cccccc',
            'color_background' => '#ffffff',
        ];
    }

    /**
     * Get theme colors metadata (label + description)
     */
    public static function getThemeColorsMetadata(): array
    {
        return [
            'color_primary' => [
                'label' => 'Màu chính',
                'description' => 'Navbar, footer, tiêu đề section, breadcrumb'
            ],
            'color_primary_dark' => [
                'label' => 'Màu chính tối',
                'description' => 'Topbar, hover states của màu chính'
            ],
            'color_secondary' => [
                'label' => 'Màu phụ (Accent)',
                'description' => 'Nút CTA, icon, hotline, badge, highlight'
            ],
            'color_secondary_dark' => [
                'label' => 'Màu phụ tối',
                'description' => 'Hover của nút, active states'
            ],
            'color_text' => [
                'label' => 'Màu chữ chính',
                'description' => 'Nội dung văn bản, tiêu đề bài viết'
            ],
            'color_text_secondary' => [
                'label' => 'Màu chữ phụ',
                'description' => 'Mô tả, caption, meta, thời gian đăng'
            ],
            'color_text_tertiary' => [
                'label' => 'Màu chữ thứ 3',
                'description' => 'Footer, text trên nền tối'
            ],
            'color_menu_hover' => [
                'label' => 'Màu hover menu',
                'description' => 'Màu khi di chuột qua các mục menu'
            ],
            'color_icon' => [
                'label' => 'Màu icon',
                'description' => 'Màu của các icon trên giao diện'
            ],
            'color_background' => [
                'label' => 'Màu nền',
                'description' => 'Background chính của trang web'
            ],
        ];
    }

    /**
     * Get theme colors configuration
     */
    public static function getThemeColors(): array
    {
        $defaults = self::getDefaultThemeColors();
        $saved = self::getGroup('theme_colors');

        return array_merge($defaults, $saved);
    }

    /**
     * Set theme colors
     */
    public static function setThemeColors(array $colors): void
    {
        $validKeys = array_keys(self::getDefaultThemeColors());

        foreach ($colors as $key => $value) {
            if (in_array($key, $validKeys) && preg_match('/^#[0-9A-Fa-f]{6}$/', $value)) {
                self::set('theme_colors', $key, $value);
            }
        }
    }

    /**
     * Get CSS string for theme colors (inject into frontend layout)
     */
    public static function getThemeColorsCss(): string
    {
        $colors = self::getThemeColors();
        $defaults = self::getDefaultThemeColors();

        $cssLines = [];

        // Map DB keys to CSS variable names (fully consistent)
        $mapping = [
            'color_primary' => '--color-primary',
            'color_primary_dark' => '--color-primary-dark',
            'color_secondary' => '--color-secondary',
            'color_secondary_dark' => '--color-secondary-dark',
            'color_menu_hover' => '--color-menu-hover',
            'color_icon' => '--color-icon',
            'color_text' => '--color-text',
            'color_text_secondary' => '--color-text-secondary',
            'color_text_tertiary' => '--color-text-tertiary',
            'color_background' => '--color-background',
        ];

        foreach ($mapping as $dbKey => $cssVar) {
            $value = $colors[$dbKey] ?? $defaults[$dbKey];
            // Always output all colors to ensure override works
            $cssLines[] = "{$cssVar}: {$value};";
        }

        return implode("\n            ", $cssLines);
    }
}
