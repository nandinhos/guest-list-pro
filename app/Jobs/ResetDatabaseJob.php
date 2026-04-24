<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Artisan;

class ResetDatabaseJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function handle(): void
    {
        $steps = [
            ['id' => 1, 'label' => 'Limpando banco de dados...', 'status' => 'pending'],
            ['id' => 2, 'label' => 'Criando backup de segurança...', 'status' => 'pending'],
            ['id' => 3, 'label' => 'Recriando schema...', 'status' => 'pending'],
            ['id' => 4, 'label' => 'Criando usuário admin...', 'status' => 'pending'],
        ];

        $this->saveProgress('running', 0, $steps);

        // Step 1: Limpando banco
        $this->updateStep(1, 'running', $steps);
        try {
            $exitCode = Artisan::call('migrate:fresh', ['--no-seed' => true]);
            if ($exitCode !== 0) {
                throw new \Exception('migrate:fresh falhou: '.Artisan::output());
            }
            $this->updateStep(1, 'done', $steps);
        } catch (\Exception $e) {
            $this->failStep(1, $e, $steps);

            return;
        }

        // Step 2: Backup
        $this->updateStep(2, 'running', $steps);
        try {
            Artisan::call('backup:create');
            $this->updateStep(2, 'done', $steps);
        } catch (\Exception $e) {
            $this->failStep(2, $e, $steps);

            return;
        }

        // Step 3: Migrate
        $this->updateStep(3, 'running', $steps);
        try {
            $exitCode = Artisan::call('migrate');
            if ($exitCode !== 0) {
                throw new \Exception('migrate falhou: '.Artisan::output());
            }
            $this->updateStep(3, 'done', $steps);
        } catch (\Exception $e) {
            $this->failStep(3, $e, $steps);

            return;
        }

        // Step 4: Seed
        $this->updateStep(4, 'running', $steps);
        try {
            $exitCode = Artisan::call('db:seed');
            if ($exitCode !== 0) {
                throw new \Exception('db:seed falhou: '.Artisan::output());
            }
            $this->updateStep(4, 'done', $steps);
        } catch (\Exception $e) {
            $this->failStep(4, $e, $steps);

            return;
        }

        $this->saveProgress('success', 4, $steps);
    }

    protected function updateStep(int $id, string $status, array &$steps): void
    {
        foreach ($steps as &$step) {
            if ($step['id'] === $id) {
                $step['status'] = $status;
                break;
            }
        }
        $this->saveProgress('running', $id, $steps);
    }

    protected function failStep(int $id, \Exception $e, array &$steps): void
    {
        foreach ($steps as &$step) {
            if ($step['id'] === $id) {
                $step['status'] = 'error';
                $step['error'] = $e->getMessage();
                $step['trace'] = $e->getTraceAsString();
                break;
            }
        }
        $this->saveProgress('error', $id, $steps, $e->getMessage(), $e->getTraceAsString());
    }

    protected function saveProgress(
        string $status,
        int $currentStep,
        array $steps,
        ?string $errorMessage = null,
        ?string $errorTrace = null
    ): void {
        session(['reset_database_job' => [
            'status' => $status,
            'current_step' => $currentStep,
            'steps' => $steps,
            'error_message' => $errorMessage,
            'error_trace' => $errorTrace,
            'updated_at' => now()->toIso8601String(),
        ]]);
    }
}
