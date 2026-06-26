<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ELanguage extends Model
{
    protected $table = 'e_languages';

    protected $fillable = [
        'code',
        'name',
        'flag',
        'flag_icon',
        'is_default',
        'is_active',
        'position',
        'translations',
        'translations_hash',
    ];

    /**
     * Boot the model - auto-generate hash when translations change
     */
    protected static function boot()
    {
        parent::boot();

        static::saving(function ($model) {
            // Auto-generate hash from translations
            if ($model->isDirty('translations')) {
                $model->translations_hash = $model->generateHash();
            }
        });
    }

    /**
     * Generate MD5 hash from translations
     */
    public function generateHash(): string
    {
        return md5(json_encode($this->translations ?? [], JSON_UNESCAPED_UNICODE));
    }

    protected $casts = [
        'is_default' => 'boolean',
        'is_active' => 'boolean',
        'position' => 'integer',
        'translations' => 'array',
    ];

    /**
     * Get all active languages ordered by position
     */
    public static function getActiveLanguages()
    {
        return self::where('is_active', 1)
            ->orderBy('position')
            ->get();
    }

    /**
     * Get the default language
     */
    public static function getDefault()
    {
        return self::where('is_default', 1)->first();
    }

    /**
     * Set this language as default
     */
    public function setAsDefault(): void
    {
        // Remove default from all languages
        self::where('is_default', 1)->update(['is_default' => 0]);
        // Set this as default
        $this->update(['is_default' => 1, 'is_active' => 1]);
    }

    /**
     * Get a specific translation key
     */
    public function getTranslation(string $key, $default = null)
    {
        $translations = $this->translations ?? [];
        return data_get($translations, $key, $default);
    }

    /**
     * Set a specific translation key
     */
    public function setTranslation(string $key, $value): void
    {
        $translations = $this->translations ?? [];
        data_set($translations, $key, $value);
        $this->translations = $translations;
    }

    /**
     * Get all translations as flat array for display
     */
    public function getFlatTranslations(): array
    {
        return $this->flattenArray($this->translations ?? [], '');
    }

    /**
     * Helper to flatten nested array with dot notation keys
     */
    private function flattenArray(array $array, string $prefix = ''): array
    {
        $result = [];
        foreach ($array as $key => $value) {
            $newKey = $prefix === '' ? $key : $prefix . '.' . $key;
            if (is_array($value)) {
                $result = array_merge($result, $this->flattenArray($value, $newKey));
            } else {
                $result[$newKey] = $value;
            }
        }
        return $result;
    }
}
