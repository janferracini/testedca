<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\LancamentoController;
use App\Http\Controllers\RelatorioAnoController;
use App\Http\Controllers\RelatorioCnaeController;
use App\Http\Controllers\SaldoController;
use App\Http\Controllers\SecretariaController;
use App\Http\Controllers\ValorController;
use App\Http\Controllers\UnidadeGestoraController;
use App\Http\Controllers\TipoController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::middleware(['auth', 'checkUserStatus'])->group(function () {
    Route::get('/index', [LancamentoController::class, 'index'])->name('lancamentos.home');

    // perfil
    Route::get('/usuarios/perfil', [ProfileController::class, 'perfil'])->name('usuarios.perfil');
    Route::post('/usuarios/update', [ProfileController::class, 'update'])->name('usuarios.update');

    // Relatórios
    Route::get('/relatorio/anual', [RelatorioAnoController::class, 'indexAnual'])->name('relatorio.anual');
    Route::post('/relatorio/anual/filtro', [RelatorioAnoController::class, 'filtroAnual'])->name('filtro.anual');
    Route::get('/relatorio/exportar-anual', [RelatorioAnoController::class, 'exportarRelatorioPdf'])->name('relatorio.exportar.anual');
    Route::get('/relatorio/cnae', [RelatorioCnaeController::class, 'indexCnae'])->name('relatorio.cnae');
    Route::post('/relatorio/cnae/filtro', [RelatorioCnaeController::class, 'filtroCnae'])->name('filtro.cnae');
    Route::get('/relatorio/exportar-cnae', [RelatorioCnaeController::class, 'exportarRelatorioPdf'])->name('relatorio.exportar.cnae');

    // Relatórios
    Route::get('/saldos', [SaldoController::class, 'index'])->name('indexSaldo');
    // #TODO validar onde usa a rota acima, anteriormente (verificarSaldo)
    Route::post('/saldos', [SaldoController::class, 'showSaldo'])->name('buscar-saldo');
    Route::get('/cnaes-buscar', [SaldoController::class, 'buscarCnae'])->name('buscar-cnae');

    // Lançamento
    // listar lançamentos
    Route::get('/lancamentos', [LancamentoController::class, 'index'])->name('lancamentos.index');
    // criar lançamento
    Route::post('/lancamentos', [LancamentoController::class, 'store'])->name('lancamentos.store');
    //  consulta de saldo
    Route::get('/verificar-saldo', [LancamentoController::class, 'verificarSaldo'])->name('verificarSaldo');
    // exibe detalhe de um lançamento
    Route::get('/lancamentos/{id}', [LancamentoController::class, 'show'])->name('lancamentos.show');
    //Editar lançamento
    Route::get('/lancamentos/{id}/edit', [LancamentoController::class, 'edit'])->name('lancamentos.editar');
    Route::put('/lancamentos/{id}/update', [LancamentoController::class, 'update'])->name('lancamentos.update');
    // atualiza o status do lançamento
    Route::patch('/lancamentos/{id}/status', [LancamentoController::class, 'updateStatus'])->name('lancamentos.updateStatus');
    // retorna subtipos de um tipo
    Route::get('subtipos/{tipo_id}', [LancamentoController::class, 'getSubtiposByTipo'])->name('getSubtipos');
    // retorna cnaes de um subtipo
    Route::get('/get-cnaes', [LancamentoController::class, 'searchCnaes'])->name('searchCnaes');

    Route::get('/unidade-gestora/{id}/secretarias', [SecretariaController::class, 'getSecretarias'])->name('getSecretarias');

    Route::get('/tipos', [TipoController::class, 'getTipos'])->name('getTipos');

    Route::middleware(['checkType:1', 'checkUserStatus'])->group(function () {
        Route::get('/usuarios/gerenciar', [ProfileController::class, 'index'])->name('usuarios.index');
        Route::post('/usuarios/store', [ProfileController::class, 'store'])->name('usuarios.store');
        Route::patch('/usuarios/{id}', [ProfileController::class, 'updateStatus'])->name('usuarios.updateStatus');

        // unidade_gestora
        Route::get('/unidade-gestora', [UnidadeGestoraController::class, 'index'])->name('unidade_gestora.index');
        Route::post('/unidade-gestora', [UnidadeGestoraController::class, 'store'])->name('unidade_gestora.store');
        Route::patch('/unidade-gestora/{id}', [UnidadeGestoraController::class, 'updateStatus'])->name('ug.updateStatus');

        // secretaria
        Route::get('/secretaria', [SecretariaController::class, 'index'])->name('secretaria.index');
        Route::post('/secretaria', [SecretariaController::class, 'store'])->name('secretaria.store');
        Route::patch('/secretaria/{id}', [SecretariaController::class, 'updateStatus'])->name('secretaria.updateStatus');

        // valor_anual
        Route::get('/valor-anual', [ValorController::class, 'index'])->name('valores.index');
        Route::post('/valor-anual', [ValorController::class, 'store'])->name('valores.store');
        Route::patch('/valor-anual/{id}', [ValorController::class, 'updateStatus'])->name('valores.updateStatus');
    });
});

// Default route
Route::get('/', function () {
    return view('auth.login');
})->name('site');

require __DIR__ . '/auth.php';
