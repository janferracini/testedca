<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Functions extends Model
{
    static function removeHtmlTags($htmlString)
    {
        // remove tags HTML e mantém somente o texto
        $cleanText = strip_tags($htmlString);
        // remove espaços e quebras de linha desnecessários
        $cleanText = trim(preg_replace('/\s+/', ' ', $cleanText));
        // decodifica entidades HTML
        $cleanText = html_entity_decode($cleanText, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        return $cleanText;
    }

    static function buscaAno($local)
    {
        // Padrão de busca para duas sequências de dois dígitos separados por barra, representando mês e ano
        $padrao = '/\d{2}\/(\d{4})/';

        if (preg_match($padrao, $local, $matches)) {
            return $matches[1];
        } else {
            return null;
        }
    }
}
