<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Enums\LancamentoStatus;
use App\Models\Secretaria;
use App\Models\UnidadeGestora;


class Lancamento extends Model
{
    use HasFactory;

    protected $table = "lancamentos";

    protected $fillable = [
        'objeto',
        'processo',
        'valor',
        'status',
        'secretaria_id',
        'user_id',
        'codigo_id',
    ];

    public function setStatus($value)
    {
        $this->attributes['status'] = LancamentoStatus::tryFrom($value)?->value ?? LancamentoStatus::Ativo->value;
    }

    public function getStatus()
    {
        return LancamentoStatus::tryFrom($this->status)?->value;
    }

    public function secretaria()
    {
        return $this->belongsTo(Secretaria::class, 'secretaria_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function tipo()
    {
        return $this->codigo->subtipo->tipo();
    }

    public function codigo()
    {
        return $this->belongsTo(Codigo::class);
    }

    public function unidade()
    {
        return $this->hasOneThrough(UnidadeGestora::class, Secretaria::class, 'id', 'id', 'secretaria_id', 'unidade_id');
    }
    public function getValorFormatadoAttribute()
    {
        return 'R$ ' . number_format($this->valor, 2, ',', '.');
    }
}
