<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;


class Secretaria extends Model
{
    use HasFactory;

    protected $table = "secretarias";

    protected $fillable = [
        'nome',
        'status',
        'unidade_id'
    ];
    public function unidadeGestora()
    {
        return $this->belongsTo(UnidadeGestora::class, 'unidade_id');
    }
    public static function getStatus()
    {
        return [
            true => 'Ativo',
            false => 'Inativo'
        ];
    }
}
