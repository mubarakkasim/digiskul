<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Crypt;

class PlatformSetting extends Model
{
    use HasFactory;

    protected $table = 'platform_settings';
    protected $primaryKey = 'id';

    protected $fillable = [
        'key_name',
        'value',
        'category',
        'type',
        'is_encrypted',
        'description',
        'updated_by',
    ];

    protected $casts = [
        'is_encrypted' => 'boolean',
    ];

    /**
     * Categories
     */
    const CATEGORY_GENERAL = 'general';
    const CATEGORY_EMAIL = 'email';
    const CATEGORY_SMS = 'sms';
    const CATEGORY_PAYMENT = 'payment';
    const CATEGORY_SECURITY = 'security';
    const CATEGORY_APPEARANCE = 'appearance';
    const CATEGORY_AI = 'ai';
    const CATEGORY_STORAGE = 'storage';

    /**
     * Types
     */
    const TYPE_STRING = 'string';
    const TYPE_JSON = 'json';
    const TYPE_BOOLEAN = 'boolean';
    const TYPE_INTEGER = 'integer';
    const TYPE_ENCRYPTED = 'encrypted';

    /**
     * Get a setting value by key
     */
    public static function getValue(string $key, $default = null)
    {
        $cacheKey = "platform_setting_{$key}";
        
        return Cache::remember($cacheKey, 3600, function () use ($key, $default) {
            $setting = self::where('key_name', $key)->first();
            
            if (!$setting) {
                return $default;
            }

            return $setting->getCastedValue();
        });
    }

    /**
     * Set a setting value
     */
    public static function setValue(string $key, $value, string $type = 'string', int $updatedBy = null): self
    {
        $isEncrypted = in_array($type, ['encrypted', 'password', 'secret']);
        
        if ($isEncrypted && !empty($value)) {
            $value = Crypt::encryptString($value);
        } elseif ($type === 'json' && is_array($value)) {
            $value = json_encode($value);
        } elseif ($type === 'boolean') {
            $value = $value ? '1' : '0';
        }

        $setting = self::updateOrCreate(
            ['key_name' => $key],
            [
                'value' => $value,
                'type' => $isEncrypted ? 'encrypted' : $type,
                'is_encrypted' => $isEncrypted,
                'updated_by' => $updatedBy,
            ]
        );

        // Clear cache
        Cache::forget("platform_setting_{$key}");

        return $setting;
    }

    /**
     * Get casted value based on type
     */
    public function getCastedValue()
    {
        $value = $this->value;

        if ($this->is_encrypted && !empty($value)) {
            try {
                $value = Crypt::decryptString($value);
            } catch (\Exception $e) {
                return null;
            }
        }

        return match($this->type) {
            'json' => json_decode($value, true),
            'boolean' => (bool) $value,
            'integer' => (int) $value,
            default => $value,
        };
    }

    /**
     * Get all settings by category
     */
    public static function getByCategory(string $category): array
    {
        $settings = self::where('category', $category)->get();
        
        $result = [];
        foreach ($settings as $setting) {
            $result[$setting->key_name] = $setting->getCastedValue();
        }

        return $result;
    }

    /**
     * Bulk update settings
     */
    public static function bulkUpdate(array $settings, int $updatedBy = null): void
    {
        foreach ($settings as $key => $data) {
            $value = is_array($data) ? ($data['value'] ?? null) : $data;
            $type = is_array($data) ? ($data['type'] ?? 'string') : 'string';
            
            self::setValue($key, $value, $type, $updatedBy);
        }
    }

    /**
     * Scope by category
     */
    public function scopeInCategory($query, string $category)
    {
        return $query->where('category', $category);
    }
}
