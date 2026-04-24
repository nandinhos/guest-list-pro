# SPEC-0015: Reset Database Button (Dev Tools)

**Status:** Aprovada — aguardando implementação  
**Data:** 2026-04-24  
**Salvar em:** `docs/superpowers/specs/SPEC-0015-reset-database.md`

---

## Objetivo

Criar um botão "Zerar Banco de Dados" acessível apenas em ambiente de desenvolvimento (`APP_ENV=local|development`), posicionado na página de Gestión de Backups (`/admin/backups`). O botão executa `migrate:fresh --seed` após criar um backup automático de segurança.

---

## Formato do Arquivo (validado com `docs/lists/listageral.md`)

O arquivo de teste `docs/lists/listageral.md` é usado para validar a importação após o reset.

---

## O Que Já Existe (NÃO reescrever)

| Arquivo | O que já funciona |
|---------|-------------------|
| `app/Filament/Admin/Pages/BackupManagement.php` | Página de backup com modal Alpine.js existente |
| `resources/views/filament/admin/pages/backup-management.blade.php` | Modal de confirmação Alpine.js já implementado (showModal, modalTitle, modalMessage, modalAction) |
| `app/Console/Commands/BackupCreateCommand.php` | Comando `backup:create` já existente |
| `app/Enums/NavigationGroup.php` | Enum `CONFIGURACOES` já definido |

---

## Mudanças Necessárias

### 1. `app/Filament/Admin/Pages/BackupManagement.php`

**Adicionar método `resetDatabase(): void`:**

```php
public function resetDatabase(): void
{
    if (! app()->environment(['local', 'development'])) {
        \Filament\Notifications\Notification::make()
            ->title('Ação não permitida')
            ->body('Esta ação só está disponível em ambiente de desenvolvimento.')
            ->danger()
            ->send();

        return;
    }

    try {
        // 1. Criar backup automático de segurança antes de zerar
        $timestamp = now()->format('Y-m-d_His');
        $backupFilename = "pre-reset-{$timestamp}.sql";

        \Artisan::call('backup:create', ['filename' => $backupFilename]);

        // 2. Migrate fresh + seed
        \Artisan::call('migrate:fresh', ['--seed' => true]);

        \Filament\Notifications\Notification::make()
            ->title('Banco de dados resetado!')
            ->body("Backup de segurança salvo em: {$backupFilename}")
            ->success()
            ->send();
    } catch (\Exception $e) {
        \Filament\Notifications\Notification::make()
            ->title('Erro ao resetar banco')
            ->body($e->getMessage())
            ->danger()
            ->send();
    }
}
```

---

### 2. `resources/views/filament/admin/pages/backup-management.blade.php`

**Adicionar card de Ferramentas de Desenvolvimento** — após a listagem de backups, com:

- **Posição:** Final da página, após `</x-filament::section>` da lista de backups
- **Espaçamento:** `class="mt-8 mb-6"` para separação visual clara
- **Ícone:** `heroicon-o-exclamation-triangle` (amarelo/perigo)
- **Estilo:** Borda vermelha (`border-red-200 dark:border-red-800`), fundo avermelhado (`bg-red-50 dark:bg-red-950/20`)
- **Visibilidade:** `x-show` ou `@if` — só renderiza se `app()->environment(['local', 'development'])`

**Estrutura do card:**

```html
{{-- Ferramentas de Desenvolvimento — só visível em dev --}}
@if(app()->environment(['local', 'development']))
    <div class="mt-8 mb-6">
        <x-filament::section variant="bordered" class="overflow-hidden border-red-200 dark:border-red-800 bg-red-50 dark:bg-red-950/20">
            <x-slot name="header">
                <div class="flex items-center gap-3">
                    <div class="p-2 rounded-lg bg-red-100 dark:bg-red-900/30">
                        <x-filament::icon icon="heroicon-o-exclamation-triangle" class="w-5 h-5 text-red-600 dark:text-red-400" />
                    </div>
                    <div>
                        <h3 class="text-base font-semibold text-red-900 dark:text-red-300">Ferramentas de Desenvolvimento</h3>
                        <p class="text-sm text-red-700 dark:text-red-400">Ambiente de teste — ação irreversível</p>
                    </div>
                </div>
            </x-slot>

            <div class="space-y-4">
                <div class="flex items-start gap-3 p-3 rounded-lg bg-red-100/50 dark:bg-red-900/20">
                    <x-filament::icon icon="heroicon-o-shield-exclamation" class="w-5 h-5 text-red-500 mt-0.5 shrink-0" />
                    <div>
                        <p class="text-sm font-medium text-red-800 dark:text-red-300">Zona de Perigo</p>
                        <p class="text-xs text-red-600 dark:text-red-400 mt-0.5">Esta ação não pode ser desfeita. Um backup automático será criado antes do reset.</p>
                    </div>
                </div>

                <button
                    type="button"
                    x-on:click="showModal = true; modalTitle = 'Zerar Banco de Dados'; modalMessage = 'Tem certeza? Esta ação vai apagar TODOS os dados e recriar o banco com apenas o usuário admin. Um backup de segurança será criado automaticamente.'; modalAction = 'resetDatabase'; modalActionParams = {}"
                    class="w-full inline-flex items-center justify-center gap-2 px-4 py-2.5 text-sm font-semibold text-white bg-red-600 rounded-lg hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 transition-all duration-200 dark:bg-red-700 dark:hover:bg-red-600 dark:focus:ring-red-400"
                >
                    <x-filament::icon icon="heroicon-o-trash" class="w-5 h-5" />
                    Zerar Banco de Dados
                </button>
            </div>
        </x-filament::section>
    </div>
@endif
```

**Nota:** O modal existente (`showModal`) já está configurado na página — não precisa criar um novo. O `modalAction = 'resetDatabase'` vai chamar o método Livewire diretamente.

---

## Regras de Negócio

- **Ambiente:** Botão **só aparece** se `APP_ENV=local` ou `APP_ENV=development`
- **Produção:** O botão **nunca** é renderizado (nem no HTML)
- **Backup automático:** Sempre cria um backup `pre-reset-{timestamp}.sql` antes de fazer o reset
- **Seed:** Após `migrate:fresh`, roda `--seed` para recriar o admin padrão (`nando@guestlist.pro`)
- **Segurança:** Método `resetDatabase()` também verifica ambiente internamente (defense in depth)
- **Transaction:** O `migrate:fresh` já é atômico via Laravel

---

## Verificação

1. `vendor/bin/sail artisan migrate:fresh --seed`
2. Acessar `/admin/backups` como `nando@guestlist.pro`
3. **Sem estar em dev:** O card "Ferramentas de Desenvolvimento" NÃO aparece
4. **Em dev (`APP_ENV=local`):** O card aparece com ícone de perigo
5. Clicar em "Zerar Banco de Dados" → modal de confirmação abre
6. Confirmar → banco é resetado, backup criado, notification de sucesso
7. Verificar que só existe `nando@guestlist.pro` como usuário
8. `vendor/bin/sail artisan test --compact` — 73 testes passando

---

## HANDOVER para Implementação

### Contexto do Projeto

- **Stack:** Laravel 12 + Filament 4 + Livewire 3 + MySQL + Sail
- **Credencial admin:** `nando@guestlist.pro` / `password`
- **Banco:** pode ter dados residuais
- **CI:** GitHub Actions verde (73 testes)

### O Que Foi Feito Nesta Sessão

1. ✅ SPEC-0015 criada com design e comportamento detalhado
2. ✅ Card de Ferramentas de Desenvolvimento posicionado após lista de backups
3. ✅ Modal de confirmação Alpine.js existente será reutilizado
4. ✅ Lógica de environment check em duas camadas (Blade + PHP method)

### Comandos Essenciais

```bash
# SEMPRE via Sail
vendor/bin/sail artisan test --compact
vendor/bin/sail artisan migrate:fresh --seed
vendor/bin/sail bin pint --dirty
```

### Arquivos Críticos

```
app/Filament/Admin/Pages/BackupManagement.php    ← adicionar resetDatabase()
resources/views/filament/admin/pages/backup-management.blade.php ← adicionar card dev
app/Console/Commands/BackupCreateCommand.php      ← ler só (já existe)
```

---

*Spec gerada em: 2026-04-24*
