<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Relatório Anual</title>
    <style>
        body {
            font-family: Arial, Helvetica, sans-serif;
            font-size: 12px;
        }

        .header-table {
            width: 100%;
            margin-bottom: 20px;
            border-collapse: collapse;
            border: none;
        }

        .header-table td {
            vertical-align: middle;
            text-align: center;
            border: none;
        }

        .header-table img {
            width: 80px;
            height: auto;
        }

        h1 {
            margin: 0;
            font-size: 16px;
        }

        h2 {
            margin: 5px 0;
            font-size: 14px;
        }

        .header-table p {
            margin: 5px 0;
            font-size: 10px;
            color: #555;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }

        table th,
        table td {
            border: 1px solid #ddd;
            text-align: left;
            padding: 8px;
        }

        table th {
            background-color: #f2f2f2;
        }

        .total {
            margin-top: 20px;
            text-align: right;
            font-size: 14px;
            font-weight: bold;
        }
    </style>
</head>

<body>
    {{-- Cabeçalho --}}
    <table class="header-table">
        <tr>
            <td>
                {{-- Logo --}}
                <img src="{{ public_path('img/brasao.png') }}" alt="Brasão da Prefeitura">
            </td>
            <td>
                {{-- Texto do cabeçalho --}}
                <h1>Diretoria de Compras e Almoxarifado | Prefeitura de Umuarama</h1>
                <h2>Relatório de Lançamentos do ano de {{ $validated['ano'] }}</h2>
                <p>Gerado em {{ date('d/m/Y H:i') }} por {{ auth()->user()->name }}</p>
            </td>
        </tr>
    </table>

    <h4>
        {{ $tipoNome }} - {{ $subtipoNome }} <br>
        {{ $codigoCnae }} - {{ $nomeCnae }}
    </h4>

    {{-- Tabela de Dados --}}
    <table>
        <thead>
            <tr>
                <th>Data</th>
                <th>Processo</th>
                <th>Objeto</th>
                <th>Local</th>
                <th>Status</th>
                <th>Valor</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($lancamentos as $lancamento)
                <tr>
                    <td>{{ $lancamento->created_at->format('d/m/Y') }}</td>
                    <td>{{ $lancamento->processo }}</td>
                    <td>{{ $lancamento->objeto }}</td>
                    <td>{{ $lancamento->secretaria->unidadeGestora->nome }} - {{ $lancamento->secretaria->nome }}</td>
                    <td>{{ ucfirst($lancamento->status) }}</td>
                    <td>{{ $lancamento->getValorFormatadoAttribute() }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    {{-- Total --}}
    <div class="total">
        <p>Total: {{ $total }}</p>
    </div>


</body>

</html>
