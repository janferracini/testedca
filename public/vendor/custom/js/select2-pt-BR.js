/*! Select2 4.0 | https://github.com/select2/select2/blob/develop/LICENSE.md */

(function () {
    if (jQuery && jQuery.fn && jQuery.fn.select2 && jQuery.fn.select2.amd)
        var e = jQuery.fn.select2.amd;
    e.define("select2/i18n/pt-BR", [], function () {
        return {
            errorLoading: function () {
                return "Os resultados não puderam ser carregados.";
            },
            inputTooLong: function (e) {
                var n = e.input.length - e.maximum;
                return "Apague " + n + " caracter" + (n == 1 ? "" : "es");
            },
            inputTooShort: function (e) {
                var n = e.minimum - e.input.length;
                return "Digite " + n + " ou mais caracteres";
            },
            loadingMore: function () {
                return "Carregando mais resultados…";
            },
            maximumSelected: function (e) {
                return "Você só pode selecionar " + e.maximum + " item" + (e.maximum == 1 ? "" : "s");
            },
            noResults: function () {
                return "Nenhum resultado encontrado";
            },
            searching: function () {
                return "Buscando…";
            },
            removeAllItems: function () {
                return "Remover todos os itens";
            }
        };
    });
})();
