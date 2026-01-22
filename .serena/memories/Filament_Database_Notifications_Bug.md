# Bug: Filament Database Notifications com Actions

## Contexto
Durante a Sprint de Solicitações, a aprovação em massa falhava silenciosamente.

## Problema
**Sintoma**: Bulk approve retornava "0 aprovadas, X ignoradas" sem mensagem de erro visível.

**Erro Real** (via Laravel Boost `last-error`):
```
Class "Filament\Notifications\Actions\Action" not found
```

**Causa Raiz**: O arquivo `NewApprovalRequestNotification.php` usava `Filament\Actions\Action` no método `toArray()` para notificações de banco de dados.

## Código Problemático
```php
// ❌ ERRADO - Actions não funcionam em database notifications
public function toArray(object $notifiable): array
{
    return FilamentNotification::make()
        ->title('Nova Solicitação')
        ->body('...')
        ->actions([
            Action::make('view')  // ❌ Isso causa o erro!
                ->button()
                ->url(...)
        ])
        ->getDatabaseMessage();
}
```

## Solução
```php
// ✅ CORRETO - Sem actions em database notifications
public function toArray(object $notifiable): array
{
    return FilamentNotification::make()
        ->title('Nova Solicitação de Aprovação')
        ->body(sprintf(
            '%s: %s solicita aprovação para %s',
            $this->approvalRequest->type->getLabel(),
            $this->approvalRequest->requester->name,
            $this->approvalRequest->guest_name
        ))
        ->icon($this->approvalRequest->type->getIcon())
        ->warning()
        ->getDatabaseMessage();  // SEM actions!
}
```

## Regra
- **Database Notifications (`toArray`)**: NUNCA usar Actions
- **Flash Notifications (`toFilament`)**: Pode usar Actions normalmente

## Como Diagnosticar
1. Usar MCP Laravel Boost: `mcp__laravel-boost__last-error`
2. Verificar se há notificações sendo disparadas na ação que falha
3. Revisar métodos `toArray()` das notifications

## Arquivos Afetados
- `app/Notifications/NewApprovalRequestNotification.php`
- `app/Notifications/ApprovalRequestStatusNotification.php`

## Checklist Pré-Notificação
- [ ] Método `toArray()` usa apenas `getDatabaseMessage()` sem actions?
- [ ] Actions estão apenas em métodos `toFilament()` ou flash notifications?
- [ ] Testou a funcionalidade que dispara notificações após a mudança?

---
*Criado: Janeiro 2026*
*Sprint: S.6 - Bug Fix Aprovação em Massa*
