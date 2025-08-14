@extends('adminlte::page')
@section('title', 'Saldo CNAE')

@section('content')
    <div class="container">
        <h1>Verificar Saldo</h1>
        <form method="POST" action="{{ route('buscar-saldo') }}">
            @csrf
            <div class="form-group">
                <label for="unidade_id">Entidade:</label>
                <select name="unidade_id" id="unidade_id" class="form-control" required>
                    <option value="">Selecione uma entidade</option>
                    @foreach ($unidades as $unidade)
                        <option value="{{ $unidade->id }}">{{ $unidade->nome }}</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group">
                <label for="codigo_id">Código CNAE:</label>
                <select name="codigo_id" id="codigo_id" class="form-control" required></select>
            </div>

            <button type="submit" class="btn btn-primary">Buscar</button>
        </form>

        @if (isset($message))
            <div class="alert alert-danger mt-4">{{ $message }}</div>
        @endif

        @if (isset($saldo))
            <div class="mt-4">
                <h2><b>Resultado</b></h2>
                <p><b>Entidade:</b> {{ $unidade_busca->nome }} <br>
                    <b>Código CNAE:</b> {{ $codigoCnae->codigo }} - {{ $codigoCnae->nome }}<br>
                    <b>Saldo:</b> R${{ number_format($saldo->saldo, 2, ',', '.') }}<br>
                    <b>Ano:</b> {{ $saldo->ano }}
                </p>
            </div>
        @endif
    </div>
@endsection
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

    <script>
        $(document).ready(function() {
            $('#codigo_id').select2({
                placeholder: 'Digite o código ou nome do CNAE',
                theme: 'bootstrap4',
                minimumInputLength: 2,
                width: '100%',
                ajax: {
                    url: '{{ route('buscar-cnae') }}',
                    dataType: 'json',
                    delay: 250,
                    data: function(params) {
                        return {
                            q: params.term // Termo digitado
                        };
                    },
                    processResults: function(data) {
                        return {
                            results: data
                        };
                    },
                    cache: true
                }
            });
        });
    </script>

@endsection
