<?php

namespace App\Models;


use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class DocumentType extends Model
{
    use softDeletes;
    protected $table = 'dte_document_types';
    protected $fillable = ['code', 'name','is_active'];
}
