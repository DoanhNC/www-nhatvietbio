<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Blade;

class EEmailTemplate extends Model
{
    protected $table = 'e_email_templates';

    protected $fillable = [
        'name',
        'label',
        'subject',
        'cc_emails',
        'bcc_emails',
        'content',
        'is_active',
    ];

    protected $casts = [
        'cc_emails' => 'array',
        'bcc_emails' => 'array',
        'is_active' => 'boolean',
    ];

    /**
     * Get template by name
     */
    public static function getByName(string $name): ?self
    {
        return self::where('name', $name)->first();
    }

    /**
     * Render template content with variables
     */
    public function render(array $data = []): string
    {
        return Blade::render($this->content, $data);
    }

    /**
     * Get rendered subject with variables
     */
    public function getRenderedSubject(array $data = []): string
    {
        return Blade::render($this->subject, $data);
    }

    /**
     * Get all CC emails as array
     */
    public function getCcEmailsArray(): array
    {
        return is_array($this->cc_emails) ? $this->cc_emails : [];
    }

    /**
     * Get all BCC emails as array
     */
    public function getBccEmailsArray(): array
    {
        return is_array($this->bcc_emails) ? $this->bcc_emails : [];
    }

    /**
     * Get dropdown list
     */
    public static function dropdown(): array
    {
        return self::where('is_active', true)
            ->pluck('label', 'name')
            ->toArray();
    }
}
