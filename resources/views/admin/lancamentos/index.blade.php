@extends('adminlte::page')
@section('plugins.CustomScripts', true)
@section('plugins.Customcss', true)
@section('plugins.DataTables', true)
@section('title', 'Lançamentos')

@section('content')
    @if ($errors->any())
        <div class="alert alert-danger">
            @foreach ($errors->all() as $error)
                {{ $error }}<br>
            @endforeach
        </div>
    @endif
    <div class="container">
        <h1>Lançamentos</h1>
        @if (session('error'))
            <div class="alert alert-danger">
                {{ session('error') }}
            </div>
        @endif
        @if (session('success'))
            <div class="alert alert-success">
                {{ session('success') }}
            </div>
        @endif

        <div class="float-right">
            <button class="btn btn-primary" data-toggle="modal" data-target="#modalLancamento">
                Adicionar</button>
        </div>
        <div class="clearfix mb-3"></div>
        <table class="table table-sm table-hover table-responsive " id="lancamentosTable">
            <thead>
                <th style="width: 10%">Processo</th>
                <th style="width: 10%">Data</th>
                <th style="width: 20%">Objeto</th>
                <th style="width: 20%">Código</th>
                <th style="width: 10%">Local</th>
                <th style="width: 10%">Valor</th>
                @if (auth()->user()->type == 1)
                    <th style="width: 3%">Ações</th>
                @endif
            </thead>
            <tbody>
                @foreach ($lancamentos as $lancamento)
                    @php
                        $status = $lancamento->getStatus();
                        $rowClass = 'bg-success';
                        if ($status === 'cancelado') {
                            $rowClass = 'bg-danger';
                        } elseif ($status === 'reservado') {
                            $rowClass = 'bg-primary';
                        }
                    @endphp
                    <tr>
                        <td>{{ $lancamento->processo }} <br>
                            <span class="badge {{ $rowClass }}">
                                @if ($status === 'reservado')
                                    <a href="{{ route('lancamentos.updateStatus', $lancamento->id) }}"
                                        onclick="event.preventDefault(); document.getElementById('update-status-{{ $lancamento->id }}').submit();"
                                        class="text-decoration-none">
                                        {{ ucfirst($status) }}
                                    </a>
                                    <form id="update-status-{{ $lancamento->id }}"
                                        action="{{ route('lancamentos.updateStatus', $lancamento->id) }}" method="POST"
                                        style="display: none;">
                                        @csrf
                                        @method('PATCH')
                                        <input type="hidden" name="status" value="ativo">
                                    </form>
                                @else
                                    {{ ucfirst($status) }}
                                @endif
                            </span>
                        </td>
                        <td>{{ $lancamento->created_at->format('d/m/Y') }}</td>
                        <td>{{ $lancamento->objeto }}</td>
                        <td>{{ $lancamento->codigo['codigo'] }} - {{ $lancamento->codigo['nome'] }}</td>
                        <td>{{ $lancamento->secretaria->unidadeGestora->nome }} - {{ $lancamento->secretaria->nome }}</td>
                        <td>{{ $lancamento->valor_formatado }}</td>
                        @if (auth()->user()->type == 1)
                            <td>
                                <button class="btn btn-outline-primary btn-sm dropdown-toggle" type="button"
                                    id="dropdownMenuButton" data-toggle="dropdown" aria-haspopup="true"
                                    aria-expanded="false">
                                    Ações
                                </button>
                                <div class="dropdown-menu" aria-labelledby="dropdownMenuButton">
                                    <button type="button" class="dropdown-item btn-editar-lancamento"
                                        data-id="{{ $lancamento->id }}" onclick="mostrarPreloader();">Editar</button>
                                    <button type="submit" class="dropdown-item btn-visualizar-lancamento"
                                        data-id="{{ $lancamento->id }}">Visualizar</button>
                                </div>
                            </td>
                        @endif
                @endforeach
            </tbody>
        </table>
    </div>

    <!-- Modal Formulário -->
    @include('admin.lancamentos.form')

    <!-- Modal Visualização -->
    @include('admin.lancamentos.show')

    <!-- Modal Saldo  -->
    <x-adminlte-modal id="modalSaldoInsuficiente" title="Saldo Insuficiente" size="md" v-centered
        data-backdrop="static">
        <div class="modal-body">
            <p>O saldo disponível para este lançamento é de <strong>R$ <span id="saldoDisponivel"></span></strong></p>
        </div>
        <x-slot name="footerSlot">
            <x-adminlte-button theme="secondary" label="Fechar" data-dismiss="modal" />
        </x-slot>
    </x-adminlte-modal>
@endsection
@extends('components.footer')
@section('css')
    <!-- Incluindo o CSS do Select2 -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/@ttskch/select2-bootstrap4-theme@1.3.2/dist/select2-bootstrap4.min.css"
        rel="stylesheet" />
@stop
@section('js')
    <!-- Incluindo o Select2 -->
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/i18n/pt-BR.js"></script>


@endsection
