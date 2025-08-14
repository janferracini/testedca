<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Saldo extends Model
{
    use HasFactory;

    protected $table = "saldos";

    protected $fillable = [
        'unidades_id',
        'codigos_id',
        'saldo',
        'ano',
    ];

    public static function buscarPorUnidadeECodigo(int $unidadeId, int $codigoId): ?self
    {
        return self::where('unidades_id', $unidadeId)
            ->where('codigos_id', $codigoId)
            ->where('ano', now()->year)
            ->first();
    }

    public function unidades()
    {
        return $this->belongsTo(UnidadeGestora::class);
    }

    public function codigos()
    {
        return $this->belongsTo(Codigo::class);
    }
}
