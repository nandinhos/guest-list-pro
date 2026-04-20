# Lição: Notificações de Banco de Dados — Actions NÃO Suportadas

**Data**: 2026-02-21
**Stack**: Laravel + Filament Notifications
**Tags**: backend|notifications|critical

## Contexto

Erros de serialização ao usar Actions em notificações persistentes no banco de dados. Notificações que armazenam dados serializados em `notifications` table falham se contiverem objetos `Filament\Actions\Action`.

**Ambiente**: Database Notifications (Laravel Notifications table)
**Frequência**: Baixa (notificações persistentes)
**Impacto**: **CRÍTICO** — quebra notificação e causa erro 500

## Problema

```php
// ERRADO: notification com Action não funciona
public function toArray(object $notifiable): array
{
    return [
        'message' => 'Nova solicitação',
        'action' => Action::make('view') // 💥 SERIALIZATION ERROR
            ->url('/solicitacoes'),
    ];
}
```

## Causa Raiz

`toArray()` serializa dados para salvar no banco. Objetos `Action` do Filament contêm closures e referências que não são serializáveis.

## Solução

**Regra 1**: Notificações que vão para o banco (`toArray()`) NÃO suportam `Filament\Actions\Action`.

**Regra 2**: Actions são permitidas apenas em notificações flash (via `toFilament()`).

```php
// CORRETO: usar dados primitivos
public function toArray(object $notifiable): array
{
    return [
        'message' => 'Nova solicitação pendente',
        'solicitacao_id' => $this->solicitacao->id,
        'action_url' => '/solicitacoes/' . $this->solicitacao->id,
    ];
}

// CORRETO: Action apenas em toFilament (flash)
public function toFilament(object $notifiable, string $databaseAlias): array
{
    return [
        'message' => 'Nova solicitação pendente',
        'actions' => [
            Action::make('view')
                ->url('/solicitacoes/' . $this->solicitacao->id),
        ],
    ];
}
```

## Prevenção

- [ ] Nunca colocar `Action` em `toArray()`
- [ ] Manter `toArray()` com dados primitivos (strings, ints, arrays)
- [ ] Usar `toFilament()` para ações interativas

## Referências

- [Laravel Notifications](https://laravel.com/docs/11.x/notifications)
- [Filament Notifications](https://filament.dev/docs/notifications)