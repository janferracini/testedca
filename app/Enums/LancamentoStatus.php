<?php

namespace App\Enums;

enum LancamentoStatus: string
{
    case Ativo = 'ativo';
    case Cancelado = 'cancelado';
    case Reservado = 'reservado';
}
