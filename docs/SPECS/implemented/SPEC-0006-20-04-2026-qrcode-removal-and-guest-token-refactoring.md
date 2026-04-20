# SPEC-0006: Remoção do Sistema QR Code e Renomeação para guest_token

**Data:** 2026-04-20
**Status:** ✅ Implementada
**Projeto:** guest-list-pro
**Stack:** Laravel 12 + Filament v4

---

## 1. Contexto

O cliente rejeitou a funcionalidade de QR Code para check-in físico. O identificador único do convidado (`qr_token`) precisava ser renomeado para eliminar toda referência a QR Code do projeto, tanto no backend quanto no frontend.

### Objetivos
- Remover completamente funcionalidades relacionadas a QR Code
- Renomear coluna `qr_token` → `guest_token`
- Atualizar todo o código para usar a nova nomenclatura
- Corrigir notificações usando API errada do Filament
- Ajustar testes para refletir nova realidade

---

## 2. Decisões Arquiteturais

### 2.1 Nome da Coluna
| Opção | Escolhida |
|-------|-----------|
| `guest_token` | ✅ ULID único do convidado (mantido) |
| `access_token` | ✗ Conflita com OAuth |
| `public_id` | ✗ Menos descritivo |
| `invite_code` | ✗ Implica convite |

### 2.2 Coluna Mantida
A coluna **NÃO foi removida**, apenas renomeada, pois:
- É o identificador único do convidado
- Admin/Promoter Panels usam para validação de check-in
- Check-in é feito via botão na tabela (não mais via scanner)

### 2.3 Notificações
Substituído `Notification::sendTo()` por `Notification::sendToDatabase()` conforme documentação Filament v4.

---

## 3. Alterações Realizadas

### 3.1 Database

| Arquivo | Ação |
|---------|------|
| `migrations/2026_04_20_014917_rename_qr_token_to_guest_token_in_guests_table.php` | ✅ Criado |
| `migrations/2026_02_20_233059_add_qr_token_to_guests_table.php` | 📋 Mantido ( histórico ) |

```php
// Migration executada
$table->renameColumn('qr_token', 'guest_token');
```

### 3.2 Backend - Models & Observers

| Arquivo | Ação |
|---------|------|
| `app/Observers/GuestObserver.php` | ✅ `generateQrToken()` → `generateGuestToken()` |
| `app/Models/Guest.php` | 📋 Mantido (coluna no banco) |

```php
// GuestObserver.php
protected function generateGuestToken(Guest $guest): void
{
    if (empty($guest->guest_token)) {
        $guest->guest_token = (string) Str::ulid();
    }
}
```

### 3.3 Backend - Rules

| Arquivo | Ação |
|---------|------|
| `app/Rules/CheckinRule.php` | ✅ Atualizado para usar `guest_token` |

```php
// CheckinRule.php
$guest = Guest::where('guest_token', $qrToken)->first();
```

### 3.4 Backend - Services

| Arquivo | Ação |
|---------|------|
| `app/Services/GuestService.php` | ✅ Removido `checkinByQrToken()` |
| `app/Services/GuestValidationService.php` | ✅ Removido `canCheckin(string $qrToken)` |

### 3.5 Backend - API

| Arquivo | Ação |
|---------|------|
| `app/Http/Controllers/Api/ApiController.php` | ✅ Removido método `checkinByQr()` |
| `routes/api.php` | ✅ Removida rota `POST /api/checkin/qr` |

### 3.6 Backend - Notifications

| Arquivo | Ação |
|---------|------|
| `app/Observers/TicketSaleObserver.php` | ✅ `sendTo()` → `sendToDatabase()` |

```php
// TicketSaleObserver.php
Notification::make()
    ->title('Check-in automático')
    ->sendToDatabase($ticketSale->seller);  // Era: sendTo()
```

### 3.7 Frontend - Tables

| Arquivo | Ação |
|---------|------|
| `app/Filament/Resources/Guests/Tables/GuestsTable.php` | ✅ Atualizado para usar `CheckinRule::canCheckin()` |
| `app/Filament/Promoter/Resources/Guests/Tables/GuestsTable.php` | ✅ Removido `downloadQr` action |

### 3.8 Remoções Completas

| Arquivo | Ação |
|---------|------|
| `app/Livewire/QrScannerModal.php` | ✅ Deletado |
| `app/Livewire/QrScannerModalWrapper.php` | ✅ Deletado |
| `resources/views/livewire/qr-scanner-modal.blade.php` | ✅ Deletado |
| `resources/views/livewire/qr-scanner-modal-wrapper.blade.php` | ✅ Deletado |
| `tests/Feature/QrScannerModalTest.php` | ✅ Deletado |
| `tests/Feature/GuestCheckinTest.php` | ✅ Deletado |
| `tests/Unit/GuestQrTokenTest.php` | ✅ Deletado |
| `composer.json` | ✅ Removido `simplesoftwareio/simple-qrcode` |
| `app/Providers/Filament/ValidatorPanelProvider.php` | ✅ Removido script `html5-qrcode` |

---

## 4. Testes

### 4.1 Testes Removidos

| Arquivo | Testes Removidos |
|---------|-----------------|
| `GuestCheckinTest.php` | 3 (inteiro) |
| `GuestServiceTest.php` | 7 (checkinByQrToken) |

### 4.2 Testes Ajustados

| Arquivo | Ajuste |
|---------|--------|
| `GuestServiceTest.php` | Mantidos 9 testes de `canRegisterGuest`, `getAuthorized*` |
| `TicketSalesMobileViewTest.php` | Adicionado `role: BILHETERIA, is_active: true` |

### 4.3 Resultado Final

```
Tests: 72 passed (208 assertions)
Duration: 10.42s
```

---

## 5. Issues Corrigidos

### Issue: TicketSalesMobileViewTest 403
- **Causa:** `UserFactory` cria users sem `role` ou `is_active`
- **Policy:** `TicketSalePolicy::viewAny()` exige `role ∈ [ADMIN, BILHETERIA]` E `is_active = true`
- **Fix:** `User::factory()->create(['role' => UserRole::BILHETERIA, 'is_active' => true])`

---

## 6. Fluxo de Check-in Atual

```
Admin/Promoter Panel
    ↓
GuestsTable → Click "ENTRADA"
    ↓
CheckinRule::canCheckin($user, $record)
    ↓
DB::transaction() → update is_checked_in = true
    ↓
Notification::make()->success()->send()
    ↓
$livewire->resetTable() → UI atualiza
```

---

## 7. Checklist de Verificação

- [x] Migration executada com sucesso
- [x] Coluna renomeada no banco (`guest_token` existe)
- [x] `generateGuestToken()` gera ULID automaticamente
- [x] Check-in via Admin Panel funciona
- [x] Check-in via Validator Panel funciona
- [x] Notificações aparecem no sino (sendToDatabase)
- [x] UI atualiza após check-in (resetTable)
- [x] QR Scanner removido de toda UI
- [x] QR Code references removidos do frontend
- [x] API endpoint removido
- [x] Tests passando (72/72)

---

## 8. Lições Aprendidas

### Lição 1: UserFactory sem role/is_active
- **Problema:** Testes de Resources com Policy falham com 403
- **Causa:** `UserFactory` cria users com `role=NULL, is_active=NULL`
- **Solução:** Sempre especificar `role` e `is_active` ao criar users para testes

### Lição 2: Notification::sendTo() não existe no Filament v4
- **Problema:** `BadMethodCallException: Method sendTo does not exist`
- **Causa:** API mudou do Filament v3 para v4
- **Solução:** Usar `sendToDatabase()` ou `broadcast()`

---

**Documento criado em:** 2026-04-20
**Revisado por:** DEVORQ Gate 5
