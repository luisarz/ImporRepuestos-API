<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Employee extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'warehouse_id',
        'job_title_id',
        'name',
        'last_name',
        'gender',
        'dui',
        'nit',
        'phone',
        'email',
        'photo',
        'district_id',
        'address',
        'comision_porcentage',
        'is_active',
        'marital_status',
        'marital_name',
        'marital_phone',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'id' => 'integer',
        'warehouse_id' => 'integer',
        'job_title_id' => 'integer',
        'photo' => 'array',
        'district_id' => 'integer',
        'comision_porcentage' => 'float',
        'is_active' => 'boolean',
    ];

    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function jobTitle(): BelongsTo
    {
        return $this->belongsTo(JobsTitle::class);
    }

    public function district(): BelongsTo
    {
        return $this->belongsTo(District::class);
    }
}
