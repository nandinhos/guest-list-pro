# SPEC-0016: Reset Database — Step-by-Step Feedback Visual

**Status:** Aprovada — aguardando implementação  
**Data:** 2026-04-24  
**Salvar em:** `docs/superpowers/specs/SPEC-0016-reset-database-step-by-step.md`

---

## Objetivo

Substituir o botão "Zerar Banco de Dados" existente por um modal step-by-step com feedback visual animado, mostrando o progresso de cada etapa do reset em tempo real. Em caso de erro, o modal permanece aberto mostrando exatamente onde ocorreu a falha.

---

## O Que Já Existe (NÃO reescrever)

| Arquivo | O que já funciona |
|---------|-------------------|
| `app/Filament/Admin/Pages/BackupManagement.php` | Página de backup + método `resetDatabase()` atual |
| `resources/views/filament/admin/pages/backup-management.blade.php` | Modal Alpine.js existente com `showModal`, `modalTitle`, `modalMessage` |
| `app/Console/Commands/BackupCreateCommand.php` | Comando `backup:create` já existente |

---

## Arquitetura

### Componentes novos

| Arquivo | Responsabilidade |
|---------|------------------|
| `app/Jobs/ResetDatabaseJob.php` | Job que executa os 4 steps com progress reporting via Session |
| `app/Http/Controllers/Admin/ResetStatusController.php` | Endpoint `GET /admin/reset-status` retorna status do job |
| `routes/web.php` | Nova rota `get /admin/reset-status` |

### Arquivos modificados

| Arquivo | Mudança |
|---------|---------|
| `app/Filament/Admin/Pages/BackupManagement.php` | `resetDatabase()` agora dispara o job e retorna job ID |
| `resources/views/filament/admin/pages/backup-management.blade.php` | Modal reformulado com steps animados + polling |

---

## Steps do Reset (em ordem)

| # | Step | Descrição |
|---|------|-----------|
| 1 | `Limpando banco de dados...` | DROP all tables via `migrate:fresh --no-seed` |
| 2 | `Criando backup de segurança...` | Executa `backup:create` |
| 3 | `Recriando schema...` | Executa `migrate` |
| 4 | `Criando usuário admin...` | Executa `db:seed` |

---

## ResetDatabaseJob — Detalhamento

```php
class ResetDatabaseJob implements ShouldQueue
{
    use InteractsWithQueue;

    public string $status = 'pending';       // pending|running|success|error
    public int $currentStep = 0;             // 1-4
    public array $steps = [];                // [{id, label, status, error?}]
    public ?string $errorMessage = null;
    public ?string $errorTrace = null;

    public function handle(): void
    {
        $this->steps = [
            ['id' => 1, 'label' => 'Limpando banco de dados...', 'status' => 'pending'],
            ['id' => 2, 'label' => 'Criando backup de segurança...', 'status' => 'pending'],
            ['id' => 3, 'label' => 'Recriando schema...', 'status' => 'pending'],
            ['id' => 4, 'label' => 'Criando usuário admin...', 'status' => 'pending'],
        ];

        $this->status = 'running';
        $this->saveProgress();

        // Step 1: Limpando
        $this->updateStep(1, 'running');
        try {
            \Artisan::call('migrate:fresh', ['--no-seed' => true]);
            if (\Artisan::call('migrate:fresh', ['--no-seed' => true]) !== 0) {
                throw new \Exception('migrate:fresh failed');
            }
            $this->updateStep(1, 'done');
        } catch (\Exception $e) {
            $this->failStep(1, $e);
            return;
        }

        // Step 2: Backup
        $this->updateStep(2, 'running');
        try {
            \Artisan::call('backup:create');
            $this->updateStep(2, 'done');
        } catch (\Exception $e) {
            $this->failStep(2, $e);
            return;
        }

        // Step 3: Migrate
        $this->updateStep(3, 'running');
        try {
            \Artisan::call('migrate');
            $this->updateStep(3, 'done');
        } catch (\Exception $e) {
            $this->failStep(3, $e);
            return;
        }

        // Step 4: Seed
        $this->updateStep(4, 'running');
        try {
            \Artisan::call('db:seed');
            $this->updateStep(4, 'done');
        } catch (\Exception $e) {
            $this->failStep(4, $e);
            return;
        }

        $this->status = 'success';
        $this->saveProgress();
    }

    protected function updateStep(int $id, string $status): void
    {
        foreach ($this->steps as &$step) {
            if ($step['id'] === $id) {
                $step['status'] = $status;
                break;
            }
        }
        $this->currentStep = $id;
        $this->saveProgress();
    }

    protected function failStep(int $id, \Exception $e): void
    {
        foreach ($this->steps as &$step) {
            if ($step['id'] === $id) {
                $step['status'] = 'error';
                $step['error'] = $e->getMessage();
                $step['trace'] = $e->getTraceAsString();
                break;
            }
        }
        $this->status = 'error';
        $this->errorMessage = $e->getMessage();
        $this->errorTrace = $e->getTraceAsString();
        $this->saveProgress();
    }

    protected function saveProgress(): void
    {
        session(['reset_database_job' => [
            'status' => $this->status,
            'current_step' => $this->currentStep,
            'steps' => $this->steps,
            'error_message' => $this->errorMessage,
            'error_trace' => $this->errorTrace,
            'updated_at' => now()->toIso8601String(),
        ]]);
    }
}
```

**Nota:** Usar Session ao invés de Redis para simplicidade — `session(['reset_database_job' => $data])`.

---

## ResetStatusController

```php
class ResetStatusController
{
    public function __invoke(): Response
    {
        $status = session('reset_database_job', [
            'status' => 'idle',
            'current_step' => 0,
            'steps' => [],
            'error_message' => null,
            'error_trace' => null,
            'updated_at' => null,
        ]);

        return response()->json($status);
    }
}
```

**Rota:**
```php
Route::get('/admin/reset-status', ResetStatusController::class)->middleware(['auth', 'admin']);
```

---

## BackupManagement — Método Atualizado

```php
public function resetDatabase(): void
{
    if (! app()->environment(['local', 'development'])) {
        \Filament\Notifications\Notification::make()
            ->title('Ação não permitida')
            ->body('Apenas em ambiente de desenvolvimento.')
            ->danger()
            ->send();
        return;
    }

    // Limpa status anterior
    session()->forget('reset_database_job');

    // Dispara job sincronamente (para feedback em tempo real)
    dispatch_sync(new \App\Jobs\ResetDatabaseJob());

    // O modal no blade faz polling via /admin/reset-status
}
```

---

## Blade — Modal Step-by-Step

```html
{{-- Modal Step-by-Step --}}
<div x-show="showModal"
     x-cloak
     class="fixed inset-0 z-50 flex items-center justify-center"
     x-on:keydown.escape.window="showModal = false">
    <div class="fixed inset-0 bg-black/50 backdrop-blur-sm"
         x-on:click="showModal = false"></div>
    <div class="relative z-10 w-full max-w-lg mx-4 bg-white dark:bg-gray-800 rounded-2xl shadow-2xl overflow-hidden"
         x-show="showModal"
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0 scale-95"
         x-transition:enter-end="opacity-100 scale-100">

        {{-- Header --}}
        <div class="p-6 pb-4">
            <div class="flex items-center gap-4">
                <div class="w-12 h-12 rounded-full flex items-center justify-center"
                     :class="resetStatus.status === 'error' ? 'bg-red-100 dark:bg-red-900/30' : 'bg-primary-100 dark:bg-primary-900/30'">
                    <template x-if="resetStatus.status !== 'success'">
                        <x-filament::icon icon="heroicon-o-exclamation-triangle"
                                          class="w-6 h-6 text-primary-600 dark:text-primary-400" />
                    </template>
                    <template x-if="resetStatus.status === 'success'">
                        <x-filament::icon icon="heroicon-o-check-circle"
                                          class="w-6 h-6 text-success-600 dark:text-success-400" />
                    </template>
                </div>
                <div>
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white"
                        x-text="modalTitle"></h3>
                    <p class="text-sm text-gray-500 dark:text-gray-400">Não feche esta janela durante o processo</p>
                </div>
            </div>
        </div>

        {{-- Steps --}}
        <div class="px-6 pb-6">
            <template x-if="resetStatus.status !== 'success' && resetStatus.status !== 'error'">
                <div class="space-y-3">
                    <template x-for="step in resetStatus.steps" :key="step.id">
                        <div class="flex items-center gap-3 p-3 rounded-lg transition-all duration-300"
                             :class="step.status === 'done' ? 'bg-success-50 dark:bg-success-900/20' :
                                     step.status === 'running' ? 'bg-warning-50 dark:bg-warning-900/20' :
                                     step.status === 'error' ? 'bg-red-50 dark:bg-red-900/20' :
                                     'bg-gray-50 dark:bg-gray-800/50'">
                            {{-- Icon --}}
                            <div class="w-6 h-6 flex items-center justify-center shrink-0">
                                <template x-if="step.status === 'done'">
                                    <x-filament::icon icon="heroicon-o-check"
                                                      class="w-5 h-5 text-success-600 dark:text-success-400" />
                                </template>
                                <template x-if="step.status === 'running'">
                                    <div class="w-5 h-5 border-2 border-warning-500 border-t-transparent rounded-full animate-spin"></div>
                                </template>
                                <template x-if="step.status === 'error'">
                                    <x-filament::icon icon="heroicon-o-x-circle"
                                                      class="w-5 h-5 text-red-600 dark:text-red-400" />
                                </template>
                                <template x-if="step.status === 'pending'">
                                    <div class="w-5 h-5 border-2 border-gray-300 dark:border-gray-600 rounded-full"></div>
                                </template>
                            </div>
                            {{-- Label --}}
                            <span class="text-sm font-medium"
                                  :class="step.status === 'done' ? 'text-success-700 dark:text-success-300' :
                                          step.status === 'running' ? 'text-warning-700 dark:text-warning-300' :
                                          step.status === 'error' ? 'text-red-700 dark:text-red-300' :
                                          'text-gray-500 dark:text-gray-400'"
                                  x-text="step.label"></span>
                        </div>
                    </template>
                </div>
            </template>

            {{-- Success Screen --}}
            <template x-if="resetStatus.status === 'success'">
                <div class="space-y-4">
                    <div class="p-4 rounded-lg bg-success-50 dark:bg-success-900/20 border border-success-200 dark:border-success-800">
                        <p class="text-sm text-success-800 dark:text-success-200 text-center">
                            Banco resetado com sucesso!
                        </p>
                    </div>
                    <div class="p-4 rounded-lg bg-gray-50 dark:bg-gray-800 border border-gray-200 dark:border-gray-700">
                        <p class="text-xs text-gray-500 dark:text-gray-400 mb-2">Credenciais de acesso:</p>
                        <div class="flex items-center gap-2">
                            <code class="flex-1 text-sm bg-gray-100 dark:bg-gray-700 px-3 py-2 rounded-lg font-mono text-gray-800 dark:text-gray-200">
                                admin@guestlist.pro / password
                            </code>
                            <button
                                type="button"
                                x-on:click="navigator.clipboard.writeText('admin@guestlist.pro / password')"
                                class="p-2 rounded-lg bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 transition-colors"
                                title="Copiar credenciais">
                                <x-filament::icon icon="heroicon-o-clipboard" class="w-4 h-4 text-gray-500 dark:text-gray-400" />
                            </button>
                        </div>
                    </div>
                    <button
                        type="button"
                        x-on:click="window.location.href = '/admin/logout'"
                        class="w-full px-4 py-2.5 text-sm font-semibold text-white bg-primary-600 rounded-lg hover:bg-primary-700 transition-colors">
                        Ir para Login
                    </button>
                </div>
            </template>

            {{-- Error Screen --}}
            <template x-if="resetStatus.status === 'error'">
                <div class="space-y-4">
                    <div class="p-4 rounded-lg bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800">
                        <p class="text-sm text-red-800 dark:text-red-200 font-medium mb-1">Erro no step:</p>
                        <p class="text-xs text-red-600 dark:text-red-400"
                           x-text="resetStatus.steps.find(s => s.status === 'error')?.label || 'Erro desconhecido'"></p>
                        <p class="text-xs text-red-600 dark:text-red-400 mt-2 font-mono"
                           x-text="resetStatus.error_message"></p>
                    </div>
                    <div class="flex gap-3">
                        <button
                            type="button"
                            x-on:click="navigator.clipboard.writeText(resetStatus.error_trace || resetStatus.error_message || '')"
                            class="flex-1 px-4 py-2.5 text-sm font-medium text-gray-700 dark:text-gray-300 bg-gray-100 dark:bg-gray-700 rounded-lg hover:bg-gray-200 dark:hover:bg-gray-600 transition-colors">
                            Copiar Log
                        </button>
                        <button
                            type="button"
                            x-on:click="window.location.href = '/admin/logout'"
                            class="flex-1 px-4 py-2.5 text-sm font-semibold text-white bg-gray-600 rounded-lg hover:bg-gray-700 transition-colors">
                            Fechar
                        </button>
                    </div>
                </div>
            </template>
        </div>

        {{-- Cancel Button (only while running) --}}
        <div class="px-6 pb-6">
            <template x-if="resetStatus.status === 'running'">
                <button
                    type="button"
                    x-on:click="showModal = false"
                    class="w-full px-4 py-2 text-sm font-medium text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-200 transition-colors">
                    Cancelar
                </button>
            </template>
        </div>
    </div>
</div>
```

**x-data atualizado:**
```html
<div x-data="{
    showModal: false,
    modalTitle: '',
    modalMessage: '',
    modalAction: null,
    modalActionParams: {},
    resetStatus: {
        status: 'idle',
        current_step: 0,
        steps: [],
        error_message: null,
        error_trace: null
    },
    pollingInterval: null,

    startPolling() {
        this.pollingInterval = setInterval(() => {
            fetch('/admin/reset-status')
                .then(r => r.json())
                .then(data => {
                    this.resetStatus = data;
                    if (this.resetStatus.status === 'success' || this.resetStatus.status === 'error') {
                        this.stopPolling();
                    }
                });
        }, 500);
    },

    stopPolling() {
        if (this.pollingInterval) {
            clearInterval(this.pollingInterval);
            this.pollingInterval = null;
        }
    }
}">
```

**Trigger do botão:**
```html
<button
    type="button"
    x-on:click="
        showModal = true;
        modalTitle = 'Zerar Banco de Dados';
        modalMessage = '';
        modalAction = 'resetDatabase';
        modalActionParams = {};
        resetStatus = { status: 'running', current_step: 0, steps: [
            {id:1, label:'Limpando banco de dados...', status:'pending'},
            {id:2, label:'Criando backup de segurança...', status:'pending'},
            {id:3, label:'Recriando schema...', status:'pending'},
            {id:4, label:'Criando usuário admin...', status:'pending'}
        ], error_message: null, error_trace: null};
        startPolling();
        $wire.resetDatabase();
    "
    ...>
```

---

## Regras de Negócio

- **Ambiente:** Funcionalidade só disponível em `APP_ENV=local|development`
- **Polling:** Atualiza a cada 500ms via `fetch('/admin/reset-status')`
- **Logout:** Ao clicar "Ir para Login", faz logout e redirect para `/admin/login`
- **Erro:** Modal permanece aberto com `✗` no step que falhou + botão "Copiar Log"
- **Sucesso:** Modal mostra credenciais + botão "Ir para Login"
- **Cancelamento:** Usuário pode fechar o modal durante execução (não para o job)

---

## Verificação

1. `vendor/bin/sail artisan migrate:fresh --seed`
2. Acessar `/admin/backups` como `admin@guestlist.pro`
3. Clicar em "Zerar Banco de Dados"
4. Ver modal com 4 steps todos em `pending`
5. Steps devem ir preenchendo com ícones animados (spinner → check)
6. Ao completar, modal muda para tela de sucesso com credenciais
7. Clicar "Ir para Login" → redirect para login
8. Login com `admin@guestlist.pro` / `password` funciona
9. `vendor/bin/sail artisan test --compact` — 73 testes passando

---

## HANDOFF para Implementação

### Contexto do Projeto
- **Stack:** Laravel 12 + Filament 4 + Livewire 3 + MySQL + Sail
- **Credencial admin:** `admin@guestlist.pro` / `password`
- **CI:** GitHub Actions verde (73 testes)

### Comandos Essenciais
```bash
vendor/bin/sail artisan test --compact
vendor/bin/sail artisan migrate:fresh --seed
vendor/bin/sail bin pint --dirty
```

### Arquivos Críticos
```
app/Jobs/ResetDatabaseJob.php                        ← CRIAR
app/Http/Controllers/Admin/ResetStatusController.php ← CRIAR
routes/web.php                                        ← MODIFICAR
app/Filament/Admin/Pages/BackupManagement.php        ← MODIFICAR
resources/views/filament/admin/pages/backup-management.blade.php ← MODIFICAR
```

---

*Spec gerada em: 2026-04-24*
