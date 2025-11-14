<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Modelo Order - Apunta a la tabla sales_header
 * Se usa para manejar órdenes (ventas pendientes/no facturadas)
 * Diferenciado del modelo SalesHeader para separar lógica de negocio
 */
class Order extends Model
{
    use HasFactory;

    /**
     * Nombre de la tabla en la base de datos
     */
    protected $table = 'sales_headers';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'cashbox_open_id',
        'sale_date',
        'document_type_id',
        'warehouse_id',
        'document_internal_number',
        'order_number',
        'seller_id',
        'customer_id',
        'operation_condition_id',
        'sale_status',
        'net_amount',
        'tax',
        'discount',
        'have_retention',
        'retention',
        'sale_total',
        'payment_method_id',
        'payment_status',
        'credit_days',
        'due_date',
        'pending_balance',
        'is_order',
        'is_order_closed_without_invoiced',
        'is_invoiced_order',
        'discount_percentage',
        'discount_money',
        'total_order_after_discount',
        'billing_model',
        'transmision_type',
        'is_dte',
        'is_dte_send',
        'generationCode',
        'is_active',
    ];

    /**
     * Valores por defecto para los atributos
     */
    protected $attributes = [
        'is_active' => true,
        'is_order' => true,
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'sale_date' => 'datetime',
        'due_date' => 'datetime',
        'have_retention' => 'boolean',
        'is_order' => 'boolean',
        'is_order_closed_without_invoiced' => 'boolean',
        'is_invoiced_order' => 'boolean',
        'is_dte' => 'boolean',
        'is_dte_send' => 'boolean',
        'is_active' => 'boolean',
    ];

    /**
     * Boot del modelo - Scope global para solo órdenes
     */
    protected static function booted()
    {
        // Filtrar solo registros que son órdenes
        static::addGlobalScope('orders_only', function ($builder) {
            $builder->where('is_order', true);
        });

        // Al crear, marcar automáticamente como orden
        static::creating(function ($order) {
            if (!isset($order->is_order)) {
                $order->is_order = true;
            }
        });
    }

    /**
     * Relación con Customer
     */
    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class, 'customer_id');
    }

    /**
     * Relación con Employee (vendedor)
     */
    public function seller(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'seller_id');
    }

    /**
     * Relación con Warehouse
     */
    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class, 'warehouse_id');
    }

    /**
     * Relación con DocumentType
     */
    public function documentType(): BelongsTo
    {
        return $this->belongsTo(DocumentType::class, 'document_type_id');
    }

    /**
     * Relación con OperationCondition
     */
    public function operationCondition(): BelongsTo
    {
        return $this->belongsTo(OperationCondition::class, 'operation_condition_id');
    }

    /**
     * Relación con PaymentMethod
     */
    public function paymentMethod(): BelongsTo
    {
        return $this->belongsTo(PaymentMethod::class, 'payment_method_id');
    }

    /**
     * Relación con OrderItems (items de la orden)
     */
    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class, 'sale_id');
    }

    /**
     * Relación con CashboxOpen
     */
    public function cashboxOpen(): BelongsTo
    {
        return $this->belongsTo(CashboxOpen::class, 'cashbox_open_id');
    }
}
