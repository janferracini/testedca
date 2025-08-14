<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Subtipo extends Model
{
    use HasFactory;

    protected $table = 'subtipos';

    protected $fillable = ['nome', 'tipo_id'];

    public function tipo()
    {
        return $this->belongsTo(Tipo::class);
    }

    public function codigos()
    {
        return $this->hasMany(Codigo::class);
    }
}
