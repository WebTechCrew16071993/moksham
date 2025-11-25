<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SelfDeclarationContainer extends Model
{
    use HasFactory;

    protected $fillable = [
        'self_declaration_id','serial_no','container_no','quantity_kgs',
    ];

    protected $casts = [
        'quantity_kgs' => 'decimal:3',
    ];

    public function selfDeclaration(){ return $this->belongsTo(SelfDeclaration::class); }
}
