<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Subtipo;
use App\Models\Tipo;

class SubtipoSeeder extends Seeder
{
    public function run()
    {
        // Recuperando o id dos tipos criados
        $produtos = Tipo::where('nome', 'Produtos')->first();
        $servicos = Tipo::where('nome', 'Serviços')->first();

        // Subtipos de Produtos
        $subtiposProdutos = [
            'Atacado',
            'Varejo'
        ];

        // Subtipos de Serviços
        $subtiposServicos = [
            'Fabricação',
            'Obras e Serviços de Engenharia',
            'Produção e Cultivo',
            'Representação Comercial',
            'Serviços',
            'Serviços Públicos',
        ];

        // Inserindo subtipos para Produtos
        foreach ($subtiposProdutos as $subtipo) {
            Subtipo::create([
                'nome' => $subtipo,
                'tipo_id' => $produtos->id
            ]);
        }

        // Inserindo subtipos para Serviços
        foreach ($subtiposServicos as $subtipo) {
            Subtipo::create([
                'nome' => $subtipo,
                'tipo_id' => $servicos->id
            ]);
        }
    }
}
