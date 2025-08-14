<?php

namespace App\Services;

use App\Models\Lancamento;
use App\Models\Valor;
use App\Models\Saldo;
use App\Enums\LancamentoStatus;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class LancamentoService
{
    public function criarLancamento(array $dados)
    {
        Log::info('Service Lançamento: criarLancamento com dados: ' . json_encode($dados));
        DB::beginTransaction();

        try {
            $saldo = $this->verificarOuCriarSaldo($dados['unidade_id'], $dados['codigo_id']);
            $status = LancamentoStatus::tryFrom($dados['status']) ?? LancamentoStatus::Reservado;


            //valida saldo para ativar ou reservar
            if (in_array($status, [LancamentoStatus::Ativo, LancamentoStatus::Reservado])) {
                if ($saldo->saldo < $dados['valor']) {
                    throw new \Exception('Saldo insuficiente para criar o lançamento.');
                }
                $saldo->decrement('saldo', $dados['valor']);
            }

            Lancamento::create($dados);
            DB::commit();

            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erro ao criar lançamento: ' . $e->getMessage());
            throw $e;
        }
    }

    public function atualizarLancamento(array $dados, $id): bool
    {
        Log::info('Atualizando Service lançamento com ID: ' . $id);
        DB::beginTransaction();

        try {
            /**
             * ================================
             * 1. Carrega os dados atuais
             * ================================
             */
            $lancamento = Lancamento::with('secretaria.unidadeGestora')->findOrFail($id);

            // Status antigo e novo
            $statusAtual = LancamentoStatus::tryFrom($lancamento->status);
            $statusNovo = LancamentoStatus::tryFrom($dados['status']) ?? LancamentoStatus::Reservado;

            // Valor antigo e novo
            $valorAntigo = $lancamento->valor;
            $valorNovo = $dados['valor'];

            // Unidade e código antigos (do lançamento atual)
            $codigoAntigo = $lancamento->codigo_id;
            $unidadeAntiga = $lancamento->secretaria->unidadeGestora->id;

            // Unidade e código novos (dados do formulário)
            $codigoNovo = $dados['codigo_id'];
            $unidadeNova = $dados['unidade_id'];

            // Busca saldos antigo e novo
            $saldoAntigo = Saldo::buscarPorUnidadeECodigo($unidadeAntiga, $codigoAntigo);
            $saldoNovo = Saldo::buscarPorUnidadeECodigo($unidadeNova, $codigoNovo);

            /**
             * =======================================
             * 2. Verificação: mudança de unidade/código
             * =======================================
             */
            if ($unidadeAntiga != $unidadeNova || $codigoAntigo != $codigoNovo) {
                // Se o status atual consome saldo
                if (in_array($statusAtual, [LancamentoStatus::Ativo, LancamentoStatus::Reservado])) {
                    // Devolve o valor para o saldo da unidade/código antigos
                    $saldoAntigo?->increment('saldo', $valorAntigo);

                    // Verifica se o novo saldo cobre o valor
                    if (!$saldoNovo || $saldoNovo->saldo < $valorNovo) {
                        throw new \Exception('Saldo insuficiente na nova unidade/código.');
                    }

                    // Debita o valor do saldo da unidade/código novos
                    $saldoNovo->decrement('saldo', $valorNovo);
                }
            } else {
                /**
                 * ====================================
                 * 3. Verificação: mudança de status
                 * (apenas se não mudou a unidade)
                 * ====================================
                 */
                $saldo = $saldoNovo ?? $saldoAntigo;

                if ($statusAtual !== $statusNovo) {
                    // Se estava cancelado e agora está sendo ativado ou reservado
                    if ($statusAtual === LancamentoStatus::Cancelado && in_array($statusNovo, [LancamentoStatus::Ativo, LancamentoStatus::Reservado])) {
                        if ($saldo->saldo < $valorNovo) {
                            throw new \Exception('Saldo insuficiente para Ativar ou Reservar o lançamento.');
                        }
                        $saldo->decrement('saldo', $valorNovo);
                    }

                    // Se estava ativo/reservado e foi cancelado
                    if (in_array($statusAtual, [LancamentoStatus::Ativo, LancamentoStatus::Reservado]) && $statusNovo === LancamentoStatus::Cancelado) {
                        $saldo->increment('saldo', $valorAntigo);
                    }
                }

                /**
                 * ====================================
                 * 4. Verificação: mudança de valor
                 * (apenas se não mudou a unidade)
                 * ====================================
                 */
                if ($valorNovo !== $valorAntigo) {
                    if ($valorNovo > $valorAntigo) {
                        $diferenca = $valorNovo - $valorAntigo;

                        if ($saldo->saldo < $diferenca) {
                            throw new \Exception('Saldo insuficiente para atualizar o lançamento.');
                        }

                        $saldo->decrement('saldo', $diferenca);
                    } else {
                        $diferenca = $valorAntigo - $valorNovo;
                        $saldo->increment('saldo', $diferenca);
                    }
                }
            }

            /**
             * =============================
             * 5. Atualiza os dados no banco
             * =============================
             */
            $lancamento->update([
                'objeto' => $dados['objeto'],
                'processo' => $dados['processo'],
                'valor' => $valorNovo,
                'status' => $statusNovo->value,
                'secretaria_id' => $dados['secretaria_id'],
                'codigo_id' => $dados['codigo_id'],
                'user_id' => $dados['user_id'],
            ]);

            DB::commit();
            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erro ao atualizar lançamento: ' . $e->getMessage());
            throw $e;
        }
    }

    public function obterSaldoDisponivel($unidadeId, $codigoId)
    {
        Log::info("Obtendo saldo disponível para unidade: $unidadeId, código: $codigoId");
        $anoAtual = now()->year;

        $saldo = Saldo::buscarPorUnidadeECodigo($unidadeId, $codigoId);

        if ($saldo) {
            return $saldo->saldo;
        }
        return Valor::where('ano', $anoAtual)->value('valor') ?? 0;
    }

    public function verificarOuCriarSaldo(int $unidadeId, int $codigoId): Saldo
    {
        Log::info("Verificando ou criando saldo para unidade: $unidadeId, código: $codigoId");
        $anoAtual = now()->year;

        $saldo = Saldo::buscarPorUnidadeECodigo($unidadeId, $codigoId);

        if (!$saldo) {
            $valorAnual = Valor::where('ano', $anoAtual)
                ->where('status', 1)
                ->value('valor') ?? 0;

            if (!$valorAnual) {
                Log::warning("Valor anual não encontrado para o ano $anoAtual.");
            }

            $saldo = Saldo::create([
                'unidades_id' => $unidadeId,
                'codigos_id' => $codigoId,
                'saldo' => $valorAnual,
                'ano' => $anoAtual
            ]);

            Log::info("Saldo criado automaticamente para unidade {$unidadeId}, código {$codigoId} no valor de {$valorAnual}.");
        }

        return $saldo;
    }

    public function atualizarSaldoStatus(Lancamento $lancamento, $novoValor, $novoStatus)
    {
        Log::info('Atualizando saldo por mudança de status do lançamento: ' . $lancamento->id);
        $saldo = Saldo::buscarPorUnidadeECodigo($lancamento->unidades_id, $lancamento->codigos_id);

        if (!$saldo) {
            throw new \Exception('Saldo não encontrado para a unidade e código especificados.');
        }

        $statusAtual = LancamentoStatus::tryFrom($lancamento->status);
        $statusNovo = LancamentoStatus::tryFrom($novoStatus);
        $valorAntigo = $lancamento->valor;

        //mudança de valor
        if ($novoValor != $valorAntigo) {
            $diferenca = $novoValor - $valorAntigo;
            if ($diferenca > 0 && $saldo->saldo < $diferenca) {
                throw new \Exception('Saldo insuficiente para atualizar o lançamento.');
            }
            $saldo->increment('saldo', -$diferenca);
        }

        //mudança de status
        if ($statusAtual !== $statusNovo) {
            if ($statusAtual === LancamentoStatus::Cancelado && in_array($statusNovo, [LancamentoStatus::Ativo, LancamentoStatus::Reservado])) {
                if ($saldo->saldo < $novoValor) {
                    throw new \Exception('Saldo insuficiente para ativar o lançamento.');
                }
                $saldo->decrement('saldo', $novoValor);
            }
            if (in_array($statusAtual, [LancamentoStatus::Ativo, LancamentoStatus::Reservado]) && $statusNovo === LancamentoStatus::Cancelado) {
                $saldo->increment('saldo', $valorAntigo);
            }
        }
        return true;
    }

    //alteração do status pelo usuário (apenas de reservado para ativo na listagem de lançamentos)
    public function atualizarStatus(int $id, string $novoStatus): void
    {
        Log::info('Atualizando status do lançamento: ' . $id . ' para ' . $novoStatus);
        $lancamento = Lancamento::findOrFail($id);

        if ($lancamento->status === 'reservado' && $novoStatus === 'ativo') {
            $lancamento->status = $novoStatus;
            $lancamento->save();
        } else {
            throw new \Exception('Status inválido para a atualização.');
        }
    }
}
