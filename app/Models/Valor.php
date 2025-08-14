<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Valor extends Model
{
    use HasFactory;

    protected $table = "valores";

    protected $fillable = [
        'ano',
        'status',
        'valor',
    ];



    public static function getStatus()
    {
        return [
            1 => 'Ativo',
            0 => 'Inativo'
        ];
    }
}
