<?php

namespace Modules\Settings\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property string $key
 * @property string|null $value
 * @property string $type
 * @property string $group
 * @property string|null $label
 * @property string|null $description
 * @property array|null $options
 * @property int $sort_order
 * @property bool $is_required
 * @property bool $is_active
 * @property string|null $created_at
 * @property string|null $updated_at
 */
class SiteSetting extends Model
{
    protected $fillable = [
        'key',
        'value',
        'type',
        'group',
        'label',
        'description',
        'options',
        'sort_order',
        'is_required',
        'is_active',
    ];

    protected $casts = [
        'options' => 'array',
        'is_required' => 'boolean',
        'is_active' => 'boolean',
    ];

    /**
     * Scope: Active settings
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope: By group
     */
    public function scopeByGroup(Builder $query, string $group): Builder
    {
        return $query->where('group', $group);
    }

    /**
     * Scope: Ordered
     */
    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('sort_order')->orderBy('label');
    }

    /**
     * Get setting value with type casting
     */
    public function getTypedValueAttribute()
    {
        return match ($this->type) {
            'boolean' => (bool) $this->value,
            'number' => (float) $this->value,
            'json' => json_decode($this->value, true),
            default => $this->value,
        };
    }

    /**
     * Set setting value with type casting
     */
    public function setTypedValueAttribute($value)
    {
        $this->value = match ($this->type) {
            'boolean' => $value ? '1' : '0',
            'number' => (string) $value,
            'json' => json_encode($value),
            default => (string) $value,
        };
    }

    /**
     * Get all settings as key-value array
     */
    public static function getAllSettings(): array
    {
        return static::active()
            ->get()
            ->pluck('value', 'key')
            ->toArray();
    }

    /**
     * Get setting by key
     */
    public static function getSetting(string $key, $default = null)
    {
        $setting = static::where('key', $key)->active()->first();

        return $setting ? $setting->typed_value : $default;
    }

    /**
     * Set setting by key
     */
    public static function setSetting(string $key, $value): bool
    {
        $setting = static::where('key', $key)->first();

        if ($setting) {
            $setting->typed_value = $value;

            return $setting->save();
        }

        return false;
    }

    /**
     * Get settings by group
     */
    public static function getSettingsByGroup(string $group): array
    {
        return static::active()
            ->byGroup($group)
            ->ordered()
            ->get()
            ->toArray();
    }
}
