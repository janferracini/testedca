var dados;
let modoEdicao = false;
let podeEnviar = false;

$(document).ready(function () {
    $("#lancamentosTable").DataTable({
        language: {
            url: "/vendor/datatables/i18n/pt-BR.json",
        },
        paging: true,
        ordering: true,
        searching: true,
        info: true,
        lengthMenu: [5, 10, 25, 50],
        columnDefs: [
            {
                orderable: false,
                targets: [6],
            },
        ],
    });

    $("#codigo_id").select2({
        placeholder: "Selecione o Código CNAE",
        allowClear: true,
        theme: "bootstrap4",
        minimumInputLength: 2,
        ajax: {
            url: rotaSearchCnaes,
            dataType: "json",
            delay: 250,
            data: function (params) {
                var subtipoId = $("#subtipo_id").val();
                return {
                    search: params.term,
                    subtipo_id: subtipoId,
                };
            },
            processResults: function (data) {
                return {
                    results: $.map(data, function (item) {
                        return {
                            id: item.id,
                            text: item.codigo + " - " + item.nome,
                        };
                    }),
                };
            },
            cache: true,
        },
    });

    $("#modalLancamento").on("show.bs.modal", function () {
        this.removeAttribute("inert");
        $(
            "#secretaria_id option, #tipo_id option, #subtipo_id option, #codigo_id option"
        ).hide();
        $(
            '#secretaria_id option[value=""], #tipo_id option[value=""], #subtipo_id option[value=""], #codigo_id option[value=""]'
        ).show();
    });

    $("#unidade_id").on("change", function () {
        const unidadeId = $(this).val();

        // Desativa o select de secretaria enquanto carrega
        $("#secretaria_id").prop("disabled", true);

        if (!modoEdicao) {
            $("#tipo_id, #subtipo_id, #codigo_id, #secretaria_id")
                .val("")
                .trigger("change");
        }

        if (unidadeId) {
            carregarSecretarias(unidadeId);
        }
    });

    $("#secretaria_id").on("change", function () {
        const secretariaSelecionada = $(this).val();

        if (secretariaSelecionada) {
            $("#tipo_id option").show();
        } else {
            $("#tipo_id").val("");
            $("#subtipo_id").val("");
            $("#codigo_id").val("");
            $("#tipo_id option").hide().filter('[value=""]').show();
            $("#subtipo_id option").hide().filter('[value=""]').show();
            $("#codigo_id option").hide().filter('[value=""]').show();
        }
    });

    $("#tipo_id").on("change", function () {
        const tipoId = $(this).val();
        $("#subtipo_id option").hide();
        $('#subtipo_id option[value=""]').show();
        $(`#subtipo_id option[data-tipo="${tipoId}"]`).show();
        $("#subtipo_id, #codigo_id").val("").trigger("change");
        $("#codigo_id option").hide().filter('[value=""]').show();
    });

    $("#subtipo_id").on("change", function () {
        const subtipoId = $(this).val();
        $("#codigo_id option").hide();
        $('#codigo_id option[value=""]').show();
        $(`#codigo_id option[data-subtipo="${subtipoId}"]`).show();
        $("#codigo_id").val("").trigger("change");
    });

    function abrirModalEdicao(d) {
        dados = d;
        modoEdicao = true;
        console.log("Abrindo modal de edição para o lançamento:", d.id);

        if (!$("#id").length) {
            $("#formModalLancamento").append(
                '<input type="hidden" id="id" name="id">'
            );
        }

        $("#id").val(d.id);

        // Limpa campos, mantendo essenciais
        $("#formModalLancamento")
            .find("input, textarea, select")
            .not('[name="_token"], [name="_method"], #unidade_id, #id')
            .val("")
            .trigger("change");

        // Garante CSRF token
        if (!$('#formModalLancamento input[name="_token"]').length) {
            const token = document
                .querySelector('meta[name="csrf-token"]')
                .getAttribute("content");
            $("#formModalLancamento").append(
                `<input type="hidden" name="_token" value="${token}">`
            );
        }

        // Garante _method com PUT para atualização
        if (!$('#formModalLancamento input[name="_method"]').length) {
            $("#formModalLancamento").append(
                '<input type="hidden" name="_method" value="PUT">'
            );
        } else {
            $('#formModalLancamento input[name="_method"]').val("PUT");
        }

        // Altera a ação do formulário para o update
        $("#formModalLancamento").attr("action", `/lancamentos/${d.id}/update`);

        // Define os valores dos campos principais
        $("#id").val(d.id);
        $("#processo").val(d.processo);
        $("#objeto").val(d.objeto);
        $("#valor").val(d.valor);
        $("#status").val(d.status);

        const unidadeId = d.secretaria.unidade_id;
        const secretariaId = d.secretaria_id;
        const tipoId = d.codigo.subtipo.tipo_id;
        const subtipoId = d.codigo.subtipo_id;
        const codigoId = d.codigo_id;

        // Marca que está em edição
        $("#unidade_id").attr("data-em-edicao", "true");
        $("#unidade_id").val(unidadeId).trigger("change");

        // Carrega secretarias → tipos → subtipos → código
        carregarSecretarias(unidadeId, secretariaId, async () => {
            $("#secretaria_id").val(secretariaId).trigger("change");
            await selecionarTipo(tipoId);
            await selecionarSubtipo(subtipoId);
            await selecionarCodigo(codigoId);
            setTimeout(() => {
                esconderPreloader();
            }, 1000);
        });

        // Abre o modal
        $("#modalLancamento").modal("show");
    }

    function carregarSecretarias(
        unidadeId,
        secretariaSelecionadaId = null,
        callback = null
    ) {
        $.ajax({
            url: `/unidade-gestora/${unidadeId}/secretarias`,
            method: "GET",
            success: function (secretarias) {
                const $select = $("#secretaria_id");
                $select.empty();

                if (secretarias.length > 0) {
                    $select.append(
                        '<option value="">Selecione uma secretaria</option>'
                    );
                    secretarias.forEach((secretaria) => {
                        const selected =
                            secretaria.id == secretariaSelecionadaId
                                ? "selected"
                                : "";
                        $select.append(
                            `<option value="${secretaria.id}" ${selected}>${secretaria.nome}</option>`
                        );
                    });
                } else {
                    $select.append(
                        '<option value="">Nenhuma secretaria encontrada</option>'
                    );
                }
                $select.prop("disabled", false);
                if (typeof callback === "function") {
                    setTimeout(callback, 30);
                }
            },
            error: function () {
                console.error("Erro ao carregar secretarias.");
            },
        });
    }

    $(".btn-editar-lancamento").click(function () {
        const lancamentoId = $(this).data("id");

        $.get(`/lancamentos/${lancamentoId}`, function (data) {
            abrirModalEdicao(data);
        }).fail(function () {
            alert("Erro ao carregar os dados do lançamento.");
        });
    });

    $(document).on("click", ".btn-visualizar-lancamento", function () {
        const lancamentoId = $(this).data("id");
        $.ajax({
            url: `/lancamentos/${lancamentoId}`,
            method: "GET",
            success: function (lancamento) {
                $("#lancamentoProcesso").text(lancamento.processo);
                $("#lancamentoObjeto").text(lancamento.objeto);
                $("#lancamentoTipo").text(lancamento.codigo.subtipo.tipo.nome);
                $("#lancamentoSubtipo").text(lancamento.codigo.subtipo.nome);
                $("#lancamentoCodigo").text(
                    `${lancamento.codigo.codigo} - ${lancamento.codigo.nome}`
                );
                const entidadeSecretaria = lancamento.secretaria.unidade_gestora
                    ? `${lancamento.secretaria.unidade_gestora.nome} - ${lancamento.secretaria.nome}`
                    : lancamento.secretaria.nome;
                $("#lancamentoLocal").text(entidadeSecretaria);
                $("#lancamentoValor").text(lancamento.valor_formatado);
                const statusFormatado =
                    lancamento.status.charAt(0).toUpperCase() +
                    lancamento.status.slice(1);
                $("#lancamentoStatus").text(statusFormatado);
                $("#lancamentoCriadoEm").text(
                    new Date(lancamento.created_at).toLocaleDateString()
                );
                if (lancamento.user) {
                    $("#lancamentoUsuario").text(lancamento.user.name);
                } else {
                    $("#lancamentoUsuario").text("Não informado");
                }
                $("#modalVisualizarLancamento").modal("show");
            },
            error: function () {
                alert("Erro ao buscar os detalhes do lançamento.");
            },
        });
    });

    $("#valor").on("change", function () {
        let valorInformado = parseFloat($(this).val().replace(",", ".")) || 0;
        const unidadeId = $("#unidade_id").val();
        const codigoId = $("#codigo_id").val();

        if (!unidadeId || !codigoId) return;

        $.ajax({
            url: rotaVerificarSaldo,
            method: "GET",
            data: {
                unidade_id: unidadeId,
                codigo_id: codigoId,
            },
            success: function (response) {
                if (response.saldo < valorInformado) {
                    $("#saldoDisponivel").text(
                        (parseFloat(response.saldo) || 0)
                            .toFixed(2)
                            .replace(".", ",")
                    );
                    $("#modalSaldoInsuficiente").modal("show");
                    $("#valor").val("");
                    podeEnviar = false;
                    $("#btnEnviar").prop("disabled", false);
                    return;
                }
            },
        });
    });

    // 1️⃣ Valida campos obrigatórios
    function validaCamposObrigatorios() {
        console.log("Validando campos obrigatórios");
        const campos = [
            $("#unidade_id"),
            $("#secretaria_id"),
            $("#tipo_id"),
            $("#subtipo_id"),
            $("#codigo_id"),
            $("#processo"),
            $("#objeto"),
            $("#status"),
        ];

        let valido = true;

        campos.forEach((campo) => {
            if (campo.val().trim() === "") {
                campo.addClass("is-invalid");
                valido = false;
            } else {
                campo.removeClass("is-invalid");
            }
        });

        return valido;
    }

    // 2️⃣ Valida valor monetário
    function validaValor() {
        console.log("Validando valor");
        let valor = $("#valor");
        let valorInformado = valor.val().trim();
        const regexVirgula = /^\d+,\d{2}$/;
        const regexPonto = /^\d+\.\d{2}$/;

        if (regexVirgula.test(valorInformado)) {
            valorInformado = valorInformado.replace(",", ".");
            valor.val(valorInformado);
            return true;
        } else if (regexPonto.test(valorInformado)) {
            return true;
        } else {
            valor.addClass("is-invalid");
            return false;
        }
    }

    // 3️⃣ Verifica saldo via AJAX e retorna uma Promise
    function verificaSaldo() {
        console.log("Iniciando verificaSaldo");
        return new Promise((resolve, reject) => {
            const unidadeId = $("#unidade_id").val();
            const codigoId = $("#codigo_id").val();
            const valorNovo = parseFloat($("#valor").val());
            const statusNovo = $("#status").val();
            const lancamentoId = $("#id").val() || null;

            console.log(
                `Verificando saldo para unidade: ${unidadeId}, código: ${codigoId}, valor: ${valorNovo}, status: ${statusNovo}, lançamento ID: ${lancamentoId}`
            );

            // Se for cancelado, não precisa validar saldo
            if (statusNovo === "cancelado") {
                resolve(true);
                return;
            }

            $.ajax({
                url: rotaVerificarSaldo,
                method: "GET",
                data: {
                    unidade_id: unidadeId,
                    codigo_id: codigoId,
                    lancamento_id: lancamentoId,
                },
                success: function (response) {
                    const saldo = parseFloat(response.saldo);
                    const valorAntigo = parseFloat(response.valor_antigo || 0); // ← precisa vir do backend
                    const statusAntigo = response.status_antigo || null; // ← também do backend

                    let precisaValidar = true;
                    let diferenca = 0;

                    // Caso de edição
                    if (lancamentoId) {
                        // Se apenas mudou o valor
                        if (valorNovo !== valorAntigo) {
                            if (valorNovo > valorAntigo) {
                                diferenca = valorNovo - valorAntigo;
                            } else {
                                // Valor menor libera saldo — nunca bloqueia
                                precisaValidar = false;
                            }
                        } else {
                            // Valor não mudou, mas status sim
                            if (
                                statusAntigo === "cancelado" &&
                                (statusNovo === "ativo" ||
                                    statusNovo === "reservado")
                            ) {
                                diferenca = valorNovo; // precisa cobrir valor inteiro
                            } else {
                                precisaValidar = false;
                            }
                        }
                    } else {
                        // Novo lançamento
                        diferenca = valorNovo;
                    }

                    // Se não precisa validar saldo, já passa
                    if (!precisaValidar) {
                        resolve(true);
                        return;
                    }

                    // Valida saldo considerando a diferença
                    if (diferenca > saldo) {
                        $("#saldoDisponivel").text(
                            saldo.toLocaleString("pt-BR", {
                                minimumFractionDigits: 2,
                                maximumFractionDigits: 2,
                            })
                        );
                        $("#modalSaldoInsuficiente").modal("show");
                        $("#modalSaldoInsuficiente").one(
                            "hidden.bs.modal",
                            function () {
                                $("#btnEnviar").prop("disabled", false);
                            }
                        );
                        resolve(false);
                    } else {
                        resolve(true);
                    }
                },
                error: function () {
                    alert("Erro ao verificar saldo. Tente novamente.");
                    $("#btnEnviar").prop("disabled", false);
                    reject();
                },
            });
        });
    }

    // 4️⃣ Aciona o submit apenas quando todas as validações passarem
    $("#btnEnviar").on("click", async function (e) {
        console.log("Botão Enviar clicado");
        e.preventDefault();
        $("#btnEnviar").prop("disabled", true);

        if (!validaCamposObrigatorios() || !validaValor()) {
            $("#btnEnviar").prop("disabled", false);
            return;
        }

        try {
            const saldoOk = await verificaSaldo();
            if (saldoOk) {
                // Só aqui o form é enviado
                document.getElementById("formModalLancamento").submit();
            }
        } catch {
            $("#btnEnviar").prop("disabled", false);
        }
    });

    $("#modalLancamento").on("show.bs.modal", function () {
        console.log("modalLancamento lancamentos.js ln. 474");
        var valor = $("#valor").val();
        if (valor) {
            $("#valor").val(valor.replace(".", ","));
        }
    });

    document.getElementById("processo").addEventListener("input", function () {
        const input = this.value;
        const feedback = document.getElementById("processo-feedback");
        const regex = /^([0-9]{4})\/([0-9]{2})\/[0-9]+$/;
        const anoAtual = new Date().getFullYear();
        const match = input.match(regex);

        if (
            match &&
            parseInt(match[1]) === anoAtual &&
            parseInt(match[2]) >= 1 &&
            parseInt(match[2]) <= 12
        ) {
            this.classList.remove("is-invalid");
            feedback.style.display = "none";
        } else {
            this.classList.add("is-invalid");
            feedback.style.display = "block";
        }
    });

    function abreModal() {
        console.log("Abrindo modal de lançamento");
        limparModalLancamento();
        $("#modalLancamento").modal("show");

        // Garante que os selects filtráveis estejam com opções visíveis (caso unidade já esteja selecionada)
        const unidadeId = $("#unidade_id").val();
        const tipoId = $("#tipo_id").val();
        const subtipoId = $("#subtipo_id").val();

        if (unidadeId) $("#unidade_id").trigger("change");
        if (tipoId) $("#tipo_id").trigger("change");
        if (subtipoId) $("#subtipo_id").trigger("change");
    }

    function limparModalLancamento() {
        const form = document.getElementById("formModalLancamento");
        form.reset();

        form.querySelectorAll("input, select, textarea, text").forEach(
            (input) => {
                if (input.tagName === "SELECT") {
                    input.value = "";
                    $(input).val("").trigger("change");
                } else {
                    input.value = "";
                }
            }
        );

        $("#formModalLancamento").attr("action", window.rotaStoreLancamento);
        $("#formMethod").val("POST");
        $("#codigo_id").val(null).trigger("change");
        $("#unidade_id").removeAttr("data-em-edicao");
    }

    //helpers da promisse (abrirModalEdicao)
    function selecionarTipo(tipoId) {
        return new Promise((resolve) => {
            $("#tipo_id").val(tipoId).trigger("change");
            // aguarda o DOM atualizar os subtipos
            setTimeout(resolve, 50);
        });
    }

    function selecionarSubtipo(subtipoId) {
        return new Promise((resolve) => {
            $("#subtipo_id").val(subtipoId).trigger("change");
            setTimeout(resolve, 50);
        });
    }

    function selecionarCodigo(codigoId) {
        return new Promise((resolve) => {
            $("#codigo_id").val(codigoId).trigger("change");
            resolve(); // não precisa esperar nada aqui
        });
    }

    document
        .querySelector('button[data-dismiss="modal"]')
        .addEventListener("click", () => {
            limparModalLancamento();
            modoEdicao = false;
            document
                .getElementById("modalLancamento")
                .setAttribute("inert", "");
            document.body.focus();
        });
    document.getElementById("btnFechar").addEventListener("click", () => {
        limparModalLancamento();
        modoEdicao = false;
        document.getElementById("modalLancamento").setAttribute("inert", "");

        document.body.focus();
    });
});
