<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Produto extends Model
{
    protected $fillable = [
        'nome',
        'categoria',
        'garantia_meses',
        'preco',
        'estoque',
        'vendedor_id',
    ];

    public function vendedor()
    {
        return $this->belongsTo(User::class, 'vendedor_id');
    }

    public function vendas()
    {
        return $this->belongsToMany(Venda::class)
            ->withPivot('quantidade', 'preco_unitario')
            ->withTimestamps();
    }
}
