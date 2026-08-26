<?php

namespace App\Enums;

enum ItemStatus: string
{
    case Pendente = 'pendente';
    case EmAndamento = 'em_andamento';
    case Concluido = 'concluido';
}
