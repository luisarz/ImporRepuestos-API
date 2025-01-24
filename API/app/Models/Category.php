<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Category extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'code',
        'description',
        'comission_porcentage',
        'category_parent_id',
        'is_active',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'id' => 'integer',
        'comission_porcentage' => 'decimal',
        'category_parent_id' => 'integer',
        'is_active' => 'boolean',
    ];

    public function categoryParent(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }
}
