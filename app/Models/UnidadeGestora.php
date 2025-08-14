<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UnidadeGestora extends Model
{
    use HasFactory;

    protected $table = "unidades";

    protected $fillable = [
        'nome',
        'status'
    ];

    public static function getStatus()
    {
        return [
            1 => 'Ativo',
            0 => 'Inativo'
        ];
    }

    public function secretarias()
    {
        return $this->hasMany(Secretaria::class, 'unidade_id');
    }
}
