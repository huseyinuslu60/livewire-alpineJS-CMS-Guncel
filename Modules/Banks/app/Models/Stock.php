<?php

namespace Modules\Banks\Models;

use App\Traits\AuditFields;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $stock_id
 * @property string $name
 * @property string|null $unvan
 * @property string|null $kurulus_tarihi
 * @property string|null $ilk_islem_tarihi
 * @property string|null $merkez_adres
 * @property string|null $web
 * @property string|null $telefon
 * @property string|null $faks
 * @property int|null $personel_sayisi
 * @property string|null $genel_mudur
 * @property string|null $yonetim_kurulu
 * @property string|null $faaliyet_alani
 * @property string|null $endeksler
 * @property string|null $details
 * @property int|null $created_by
 * @property int|null $updated_by
 * @property string|null $last_status
 * @property string|null $created_at
 * @property string|null $updated_at
 * @property-read string|null $formatted_kurulus_tarihi
 * @property-read string|null $formatted_ilk_islem_tarihi
 *
 * @method static \Illuminate\Database\Eloquent\Builder|Stock ofStatus($status)
 * @method static \Illuminate\Database\Eloquent\Builder|Stock sortedLatest($column = 'created_at')
 * @method static \Illuminate\Database\Eloquent\Builder|Stock search(?string $term)
 * @method static \Illuminate\Database\Eloquent\Builder|Stock active()
 * @method static \Illuminate\Database\Eloquent\Builder|Stock inactive()
 */
class Stock extends Model
{
    use AuditFields, HasFactory;

    protected $table = 'stocks';

    protected $primaryKey = 'stock_id';

    protected $fillable = [
        'name',
        'unvan',
        'kurulus_tarihi',
        'ilk_islem_tarihi',
        'merkez_adres',
        'web',
        'telefon',
        'faks',
        'personel_sayisi',
        'genel_mudur',
        'yonetim_kurulu',
        'faaliyet_alani',
        'endeksler',
        'details',
        'created_by',
        'updated_by',
        'last_status',
    ];

    protected $casts = [
        'kurulus_tarihi' => 'date',
        'ilk_islem_tarihi' => 'date',
        'personel_sayisi' => 'integer',
    ];

    // Relationships
    public function creator(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class, 'created_by', 'id');
    }

    public function updater(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class, 'updated_by', 'id');
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('last_status', 'active');
    }

    public function scopeInactive($query)
    {
        return $query->where('last_status', 'inactive');
    }

    public function scopeSearch($query, ?string $term)
    {
        $term = trim((string) $term);
        if ($term === '') {
            return $query;
        }

        $safe = str_replace(['%', '_'], ['\\%', '\\_'], $term);
        $driver = $query->getModel()->getConnection()->getDriverName();
        $likeOp = $driver === 'pgsql' ? 'ILIKE' : 'LIKE';

        return $query->where(function ($q) use ($safe, $likeOp) {
            $q->where('name', $likeOp, "%{$safe}%")
                ->orWhere('unvan', $likeOp, "%{$safe}%");
        });
    }

    public function scopeOfStatus($query, $status)
    {
        return (is_null($status) || $status === '') ? $query : $query->where('last_status', $status);
    }

    public function scopeSortedLatest($query, $column = 'created_at')
    {
        return $query->latest($column);
    }

    // Accessors
    public function getFormattedKurulusTarihiAttribute()
    {
        if (! $this->kurulus_tarihi) {
            return null;
        }

        return is_string($this->kurulus_tarihi)
            ? Carbon::parse($this->kurulus_tarihi)->format('d.m.Y')
            : $this->kurulus_tarihi->format('d.m.Y');
    }

    public function getFormattedIlkIslemTarihiAttribute()
    {
        if (! $this->ilk_islem_tarihi) {
            return null;
        }

        return is_string($this->ilk_islem_tarihi)
            ? Carbon::parse($this->ilk_islem_tarihi)->format('d.m.Y')
            : $this->ilk_islem_tarihi->format('d.m.Y');
    }
}
