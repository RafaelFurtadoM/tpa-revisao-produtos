<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Venda extends Model
{
    protected $fillable = ['cliente_id'];

    public function cliente()
    {
        return $this->belongsTo(User::class, 'cliente_id');
    }

    public function produtos()
    {
        return $this->belongsToMany(Produto::class)
            ->withPivot('quantidade', 'preco_unitario')
            ->withTimestamps();
    }
}
