<div class="modal fade" id="modalVisualizarLancamento" role="dialog" aria-labelledby="modalTitle"
    aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalTitle">Detalhes do Lançamento</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <p><strong>Processo: </strong> <span id="lancamentoProcesso"></span><br>
                    <strong>Objeto: </strong> <span id="lancamentoObjeto"></span><br>
                    <strong>Local: </strong> <span id="lancamentoLocal"></span><br>
                </p>
                <p><strong>Tipo: </strong> <span id="lancamentoTipo"></span><br>
                    <strong>Subtipo: </strong> <span id="lancamentoSubtipo"></span><br>
                    <strong>CNAE: </strong> <span id="lancamentoCodigo"></span>
                </p>

                <p><strong>Valor:</strong> <span id="lancamentoValor"></span><br>
                    <strong>Status:</strong> <span id="lancamentoStatus"></span><br>
                </p>
                <p><strong>Criado em:</strong> <span id="lancamentoCriadoEm"></span><br>
                    <strong>Usuário Responsável:</strong> <span id="lancamentoUsuario"></span>
                </p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Fechar</button>
            </div>
        </div>
    </div>
</div>