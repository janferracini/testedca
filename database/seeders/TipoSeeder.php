<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Tipo;

class TipoSeeder extends Seeder
{
    public function run()
    {
        $tipos = [
            'Produtos',
            'Serviços',
        ];

        foreach ($tipos as $tipo) {
            Tipo::create(['nome' => $tipo]);
        }
    }
}
