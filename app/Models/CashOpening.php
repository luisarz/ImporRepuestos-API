<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CashOpening extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'cash_register_id',
        'user_id',
        'opened_at',
        'closed_at',
        'opening_amount',
        'closing_amount',
        'expected_amount',
        'difference_amount',
        'opening_notes',
        'closing_notes',
        'status',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'id' => 'integer',
        'cash_register_id' => 'integer',
        'user_id' => 'integer',
        'opened_at' => 'datetime',
        'closed_at' => 'datetime',
        'opening_amount' => 'decimal:2',
        'closing_amount' => 'decimal:2',
        'expected_amount' => 'decimal:2',
        'difference_amount' => 'decimal:2',
    ];

    /**
     * Relación con CashRegister (Caja)
     */
    public function cashRegister(): BelongsTo
    {
        return $this->belongsTo(CashRegister::class);
    }

    /**
     * Relación con User (Usuario que abre/cierra)
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Relación con CashMovements (Movimientos de Caja)
     */
    public function cashMovements(): HasMany
    {
        return $this->hasMany(CashMovement::class);
    }

    /**
     * Calcular el monto esperado basado en los movimientos
     */
    public function calculateExpectedAmount(): float
    {
        $incomes = $this->cashMovements()
            ->where('type', 'income')
            ->sum('amount');

        $expenses = $this->cashMovements()
            ->where('type', 'expense')
            ->sum('amount');

        return (float) ($this->opening_amount + $incomes - $expenses);
    }

    /**
     * Obtener resumen de movimientos
     */
    public function getMovementsSummary()
    {
        return [
            'total_incomes' => $this->cashMovements()->where('type', 'income')->sum('amount'),
            'total_expenses' => $this->cashMovements()->where('type', 'expense')->sum('amount'),
            'count_incomes' => $this->cashMovements()->where('type', 'income')->count(),
            'count_expenses' => $this->cashMovements()->where('type', 'expense')->count(),
        ];
    }

    /**
     * Verificar si la apertura está cerrada
     */
    public function isClosed(): bool
    {
        return $this->status === 'closed';
    }

    /**
     * Verificar si la apertura está abierta
     */
    public function isOpen(): bool
    {
        return $this->status === 'open';
    }
}
