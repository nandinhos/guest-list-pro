# Lógica de Duplicidade Centralizada e Robusta

## Problema
Erros de `SQL Integrity Constraint Violation` ocorriam ao criar convidados duplicados (mesmo documento no mesmo evento). A lógica de verificação era dispersa e incompleta.

## Solução
Implementada lógica centralizada no `ApprovalRequestService::checkForDuplicates`.

### Melhorias:
1. **Normalização**: Utiliza `document_normalized` (sem pontos/traços) para comparação, garantindo que `123.456.789-00` seja igual a `12345678900`.
2. **Exclusão de ID**: Aceita um `$excludeGuestId` para permitir que um convidado seja editado sem que sua própria duplicidade o bloqueie.
3. **Verificação Universal**: Executada no topo do `mutateFormDataBeforeCreate` e condicionalmente no `mutateFormDataBeforeSave` (apenas se campos críticos mudarem).

## Componentes Chave
- `ApprovalRequestService`
- `CreateGuest.php` (Promoter)
- `EditGuest.php` (Promoter)
