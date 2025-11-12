<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CashMovement extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'cash_opening_id',
        'user_id',
        'type',
        'amount',
        'concept',
        'description',
        'reference',
        'sale_id',
        'movement_date',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'id' => 'integer',
        'cash_opening_id' => 'integer',
        'user_id' => 'integer',
        'sale_id' => 'integer',
        'amount' => 'decimal:2',
        'movement_date' => 'datetime',
    ];

    /**
     * Relación con CashOpening (Apertura de Caja)
     */
    public function cashOpening(): BelongsTo
    {
        return $this->belongsTo(CashOpening::class);
    }

    /**
     * Relación con User (Usuario que registra)
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Relación con SalesHeader (Venta relacionada - opcional)
     */
    public function sale(): BelongsTo
    {
        return $this->belongsTo(SalesHeader::class, 'sale_id');
    }

    /**
     * Verificar si es un ingreso
     */
    public function isIncome(): bool
    {
        return $this->type === 'income';
    }

    /**
     * Verificar si es un egreso
     */
    public function isExpense(): bool
    {
        return $this->type === 'expense';
    }

    /**
     * Scope para filtrar por tipo
     */
    public function scopeIncomes($query)
    {
        return $query->where('type', 'income');
    }

    public function scopeExpenses($query)
    {
        return $query->where('type', 'expense');
    }

    /**
     * Scope para filtrar por fecha
     */
    public function scopeByDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('movement_date', [$startDate, $endDate]);
    }
}
