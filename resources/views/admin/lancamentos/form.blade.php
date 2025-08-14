<form method="post" action="{{ route('lancamentos.store') }}" id="formModalLancamento" autocomplete="on">
    @csrf
    <input type="hidden" name="_method" id="formMethod" value="POST">
    <x-adminlte-modal id="modalLancamento" title="Cadastrar/Editar Lançamento" size="lg" v-centered
        data-backdrop="static">
        <div class="modal-body">
            <div class="row">
                <div class="col" hidden>
                    <label for="id">Id:</label>
                    <input type="text" id="id" name="id" class="form-control">
                </div>

                <div class="form-group col-12 col-md-6">
                    <label for="unidade_id">Unidade Gestora</label>
                    <select name="unidade_id" id="unidade_id" class="form-control" required>
                        <option value="">Selecione a Unidade Gestora</option>
                        @foreach ($unidades as $unidade)
                        <option value="{{ $unidade->id }}">{{ $unidade->nome }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="form-group col-12 col-md-6">
                    <label for="secretaria_id">Secretaria</label>
                    <select name="secretaria_id" id="secretaria_id" class="form-control" required>
                        <option value="">Selecione a Secretaria</option>
                        @foreach ($secretarias as $secretaria)
                        <option value="{{ $secretaria->id }}" data-unidade="{{ $secretaria->unidade_id }}">
                            {{ $secretaria->nome }}
                        </option>
                        @endforeach
                    </select>
                </div>

                <div class="form-group col-12 col-md-6">
                    <label for="tipo_id">Tipo</label>
                    <select name="tipo_id" id="tipo_id" class="form-control" required>
                        <option value="">Selecione o Tipo</option>
                        @foreach ($tipos as $tipo)
                        <option value="{{ $tipo->id }}">{{ $tipo->nome }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="form-group col-12 col-md-6">
                    <label for="subtipo_id">Subtipo</label>
                    <select name="subtipo_id" id="subtipo_id" class="form-control" required>
                        <option value="">Selecione o Subtipo</option>
                        @foreach ($subtipos as $subtipo)
                        <option value="{{ $subtipo->id }}" data-tipo="{{ $subtipo->tipo_id }}">
                            {{ $subtipo->nome }}
                        </option>
                        @endforeach
                    </select>
                </div>

                <div class="form-group col-12 col-md-12">
                    <label for="codigo_id">Código CNAE</label>
                    <select name="codigo_id" id="codigo_id" class="form-control select2" required>
                        <option value="">Selecione o Código CNAE</option>
                        @foreach ($codigos as $codigo)
                        <option value="{{ $codigo->id }}" data-subtipo="{{ $codigo->subtipo_id }}">
                            {{ $codigo->codigo }} - {{ $codigo->nome }}
                        </option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group col-12 col-md-6">
                    <label for="valor">Valor R$</label>
                    <input type="text" name="valor" id="valor" class="form-control">
                    <div class="invalid-feedback">
                        Insira um valor monetário válido. Ex.: 1234,56
                    </div>
                </div>
                <div class="form-group col-12 col-md-6">
                    <label for="processo">Processo</label>
                    <input type="text" id="processo" name="processo" class="form-control"
                        placeholder="Formato: 2025/07/1234" required>
                    <div class="invalid-feedback" id="processo-feedback">
                        O formato do processo deve ser: AAAA/MM/NNNN dentro do ano vigente
                    </div>
                </div>
                <div class="form-group col-12 col-md-6">
                    <label for="objeto">Objeto</label>
                    <input type="text" name="objeto" id="objeto" class="form-control" required>
                    <div class="invalid-feedback">
                        Informe o Objeto do Lançamento
                    </div>
                </div>
                <div class="form-group col-12 col-md-6">
                    <label for="status">Status</label>
                    <select class="form-control" id="status" name="status">
                        @foreach (App\Enums\LancamentoStatus::cases() as $status)
                        <option value="{{ $status->value }}"
                            {{ old('status', $lancamento->status ?? 'reservado') == $status->value ? 'selected' : '' }}>
                            {{ ucfirst($status->name) }}
                        </option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>

        <x-slot name="footerSlot">
            <x-adminlte-button type="button" id="btnFechar" theme="secondary" icon="fas fa-times" label="Fechar"
                data-dismiss="modal" />
            <x-adminlte-button type="button" id="btnEnviar" theme="success" icon="fas fa-check" label="Salvar" />
        </x-slot>
    </x-adminlte-modal>
</form>
<script>
    const rotaSearchCnaes = "{{ route('searchCnaes') }}";
    const rotaVerificarSaldo = "{{ route('verificarSaldo') }}";
    const rotaStoreLancamento = "{{ route('lancamentos.store') }}"
</script>