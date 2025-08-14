function validaFormulario(arrayItens) {
    for (var i = 0; i < arrayItens.length; i++) {
        $(arrayItens[i]).css("border", "1px solid green");
        if ($(arrayItens[i]).val() == "") {
            $(arrayItens[i]).focus();
            $(arrayItens[i]).css("border", "1px solid red");
            alerta(
                "error",
                "Erro!",
                "Por favor preencha todos os campos obrigatórios"
            );
            return false;
        }

        if ($(arrayItens[i]).attr("type") == "email") {
            if (!validateEmail($(arrayItens[i]).val())) {
                $(arrayItens[i]).focus();
                $(arrayItens[i]).css("border", "1px solid red");
                alerta("error", "Erro!", "Por favor informe um email válido.");
                return false;
            }
        }

        if ($(arrayItens[i]).attr("type") == "number") {
            if (!validateNumber($(arrayItens[i]).val())) {
                $(arrayItens[i]).focus();
                $(arrayItens[i]).css("border", "1px solid red");
                alerta("error", "Erro!", "Por favor informe um número válido.");
                return false;
            }
        }
    }
    return true;
}

function validateEmail(email) {
    var re = /\S+@\S+\.\S+/;
    return re.test(email);
}

function validateNumber(number) {
    var re = /^[0-9]+$/;
    return re.test(number);
}

function alerta(type, title, message) {
    Swal.fire({
        type: type,
        title: title,
        text: message,
        showConfirmButton: true,
        timer: 1500,
    });
}

function mensagem(type, title, message) {
    Toast.fire({
        type: type,
        title: title,
        text: message,
    });
}

// Armazena o HTML original do preloader
let preloaderHTML;

// Aguarda o DOM carregar para capturar o preloader do AdminLTE
window.addEventListener("DOMContentLoaded", function () {
    const preloaderEl = document.querySelector('[class*="preloader"]');
    if (preloaderEl) {
        preloaderHTML = preloaderEl.outerHTML;
    }
});

/**
 * Mostra o preloader novamente, reaproveitando o estilo original do AdminLTE
 */
function mostrarPreloader() {
    if (preloaderHTML && !document.getElementById("custom-preloader")) {
        const wrapper = document.createElement("div");
        wrapper.id = "custom-preloader";
        wrapper.innerHTML = preloaderHTML;

        // aplica estilo fixo para cobrir a tela
        wrapper.style.position = "fixed";
        wrapper.style.top = "0";
        wrapper.style.left = "0";
        wrapper.style.width = "100vw";
        wrapper.style.height = "100vh";
        wrapper.style.zIndex = "9999";
        wrapper.style.backgroundColor = "white"; // mesmo fundo usado pelo AdminLTE

        document.body.appendChild(wrapper);
    }
}

/**
 * Remove o preloader reaproveitado
 */
function esconderPreloader() {
    const preloader = document.getElementById("custom-preloader");
    if (preloader) {
        preloader.remove();
    }
}

// Opcional: esconde o preloader sempre que terminar uma requisição AJAX
// Você pode remover isso se quiser controlar manualmente com esconderPreloader()
// $(document).ajaxStop(function () {
//     esconderPreloader();
// });
