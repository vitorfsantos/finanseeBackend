<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;

class DatabaseResetDailyCommand extends Command
{
  /**
   * The name and signature of the console command.
   *
   * @var string
   */
  protected $signature = 'database:reset-daily {--force : Force the operation without confirmation}';

  /**
   * The console command description.
   *
   * @var string
   */
  protected $description = 'Reset database daily with fresh migrations and seeders';

  /**
   * Execute the console command.
   */
  public function handle()
  {
    // Verificar se está em ambiente de produção
    if (app()->environment('production')) {
      $this->info('Executando reset do banco de dados em ambiente de produção...');

      try {
        // Log do início da operação
        Log::info('Iniciando reset diário do banco de dados', [
          'timestamp' => now(),
          'environment' => app()->environment()
        ]);

        // Executar migrate:fresh --seed --force
        $this->info('Executando migrate:fresh --seed --force...');

        Artisan::call('migrate:fresh', [
          '--seed' => true,
          '--force' => true
        ]);

        $this->info('Reset do banco de dados concluído com sucesso!');

        // Log do sucesso
        Log::info('Reset diário do banco de dados concluído com sucesso', [
          'timestamp' => now(),
          'environment' => app()->environment()
        ]);

        return Command::SUCCESS;
      } catch (\Exception $e) {
        $this->error('Erro ao executar reset do banco de dados: ' . $e->getMessage());

        // Log do erro
        Log::error('Erro no reset diário do banco de dados', [
          'error' => $e->getMessage(),
          'timestamp' => now(),
          'environment' => app()->environment()
        ]);

        return Command::FAILURE;
      }
    } else {
      $this->warn('Este comando só pode ser executado em ambiente de produção.');
      $this->info('Ambiente atual: ' . app()->environment());

      return Command::FAILURE;
    }
  }
}
