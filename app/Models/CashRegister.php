<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CashRegister extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'code',
        'name',
        'warehouse_id',
        'description',
        'is_active',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'id' => 'integer',
        'warehouse_id' => 'integer',
        'is_active' => 'boolean',
    ];

    /**
     * Relación con Warehouse (Sucursal)
     */
    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class);
    }

    /**
     * Relación con Cash Openings (Aperturas de Caja)
     */
    public function cashOpenings(): HasMany
    {
        return $this->hasMany(CashOpening::class);
    }

    /**
     * Obtener la apertura actual (abierta) de esta caja
     */
    public function currentOpening()
    {
        return $this->hasOne(CashOpening::class)
            ->where('status', 'open')
            ->latest('opened_at');
    }

    /**
     * Verificar si la caja tiene una apertura activa
     */
    public function hasOpenCash(): bool
    {
        return $this->cashOpenings()
            ->where('status', 'open')
            ->exists();
    }

    /**
     * Obtener estadísticas de la caja
     */
    public function getStats()
    {
        $totalOpenings = $this->cashOpenings()->count();
        $currentOpening = $this->currentOpening()->first();

        return [
            'total_openings' => $totalOpenings,
            'has_open_cash' => $this->hasOpenCash(),
            'current_opening' => $currentOpening,
        ];
    }
}
