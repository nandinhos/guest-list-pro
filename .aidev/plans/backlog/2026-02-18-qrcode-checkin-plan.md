# Plano: Sistema de Check-in por QR Code

> **Data**: 2026-02-18
> **Status**: BACKLOG (aguardando refinamento)
> **Responsável**: -
> **Estimativa**: -

---

## Motivação

O sistema atual permite check-in manual via busca por nome/documento. Para dispositivos móveis (celular/tablet) na portaria, é necessário um fluxo mais ágil via QR Code.

**Requisitos do usuário**:
- QR Code Simples (UUID)
- Gerado automaticamente na importação de lista
- Fluxo híbrido no mobile: QR + busca manual

---

## Escopo

### Inside
- [ ] Gerar QR Code (UUID único) automaticamente na importação de guests
- [ ] Armazenar `qr_token` na tabela `guests`
- [ ] API de check-in via QR
- [ ] Interface mobile com leitor de câmera
- [ ] Toggle: modo QR ↔ modo busca manual

### Outside
- [ ] QR Code rico (com dados do guest) - fugiu do escopo
- [ ] Envio de QR por email/SMS
- [ ] Sistema de convite digital

---

## Arquitetura

```
┌─────────────────────────────────────────────────────────────┐
│                    FLUXO DE IMPORTAÇÃO                      │
├─────────────────────────────────────────────────────────────┤
│  Upload Excel → GuestsImport → Guest + QR Code (UUID)    │
│                      ↓                                      │
│              Armazenar: qr_token (uuid)                    │
└─────────────────────────────────────────────────────────────┘

┌─────────────────────────────────────────────────────────────┐
│                  FLUXO DE CHECK-IN MOBILE                   │
├─────────────────────────────────────────────────────────────┤
│  ┌──────────┐    ┌──────────────┐    ┌─────────────────┐   │
│  │ Câmera   │───▶│ Leitor QR    │───▶│ Busca por UUID │   │
│  └──────────┘    └──────────────┘    └─────────────────┘   │
│                                              │              │
│  ┌──────────┐    ┌──────────────┐           ↓              │
│  │ Busca    │───▶│ Campo texto  │    ┌─────────────────┐   │
│  │ manual   │    │ (nome/doc)   │───▶│ Guest::find()  │   │
│  └──────────┘    └──────────────┘    └─────────────────┘   │
└─────────────────────────────────────────────────────────────┘
```

---

## Plano de Implementação

### Fase 1: Backend (Modelo + Migration)

| # | Tarefa | Arquivo |
|---|--------|---------|
| 1.1 | Adicionar coluna `qr_token` (uuid, unique) na tabela guests | `database/migrations/*_add_qr_token_to_guests.php` |
| 1.2 | Gerar UUID automaticamente na criação do Guest | `app/Models/Guest.php` (boot method ou observer) |
| 1.3 | Adicionar campo `qr_token` ao `$fillable` | `app/Models/Guest.php` |
| 1.4 | Adicionar escopo `findByQrToken()` | `app/Models/Guest.php` |

### Fase 2: Geração QR Code (PHP)

| # | Tarefa | Arquivo |
|---|--------|---------|
| 2.1 | Instalar dependência: `chillerlan/php-qrcode` | `composer.json` |
| 2.2 | Criar service `QrCodeService` | `app/Services/QrCodeService.php` |
| 2.3 | Método: `generateForGuest(Guest $guest): string` (retorna base64) | `app/Services/QrCodeService.php` |
| 2.4 | Método: `generateSvgForGuest(Guest $guest): string` | `app/Services/QrCodeService.php` |
| 2.5 | Hook na importação: gerar QR após criar guest | `app/Imports/GuestsImport.php` |

### Fase 3: API para Check-in via QR

| # | Tarefa | Arquivo |
|---|--------|---------|
| 3.1 | Criar endpoint: `POST /api/checkin/qr` | `routes/api.php` |
| 3.2 | Validar token → buscar guest → executar check-in | Controller/Action |
| 3.3 | Retornar dados do guest + status check-in | JSON response |

### Fase 4: Frontend Mobile (Scanner)

| # | Tarefa | Arquivo |
|---|--------|---------|
| 4.1 | Instalar lib: `html5-qrcode` | `package.json` |
| 4.2 | Criar componente Livewire `QrScanner` | `app/Livewire/QrScanner.php` |
| 4.3 | Criar view com câmera e leitor | `resources/views/livewire/qr-scanner.blade.php` |
| 4.4 | Integrar no painel Validator como página de check-in | `app/Filament/Validator/Pages/QrCheckin.php` |
| 4.5 | Botão toggle: modo QR ↔ modo busca manual | UI |

### Fase 5: Exibição/Exportação

| # | Tarefa | Arquivo |
|---|--------|---------|
| 5.1 | Exibir QR na listagem de guests (Admin/Promoter) | `GuestResource.php` ou tabela |
| 5.2 | Ação "Baixar QR" (PDF individual) | Action no GuestResource |
| 5.3 | Exportar lista com QR codes (PDF em lote) | `app/Filament/Resources/Guests/Pages/ExportQRCodes.php` |

---

## Testes (TDD - OBRIGATÓRIO)

| # | Teste | Local |
|---|-------|-------|
| T1 | Guest cria qr_token automaticamente | `tests/Unit/GuestTest.php` |
| T2 | QrCodeService gera QR válido | `tests/Unit/QrCodeServiceTest.php` |
| T3 | API retorna erro para QR inválido | `tests/Feature/QrCheckinApiTest.php` |
| T4 | API faz check-in com QR válido | `tests/Feature/QrCheckinApiTest.php` |
| T5 | Importação gera qr_token para cada guest | `tests/Unit/GuestsImportTest.php` |

---

## Riscos e Mitigações

| Risco | Mitigação |
|-------|-----------|
| UUID pode ser adivinhado | Usar `Str::uuid()` (aleatório, 122 bits) |
| Câmera não funciona em alguns dispositivos | Manter modo busca manual como fallback |
| Performance com muitos QRs | Gerar QR sob demanda (lazy), não na importação |
| QR impresso ilegível | Permitir re-gerar QR no painel |

---

## Decisões Pendentes (Refinar)

- [ ] **Performance**: Gerar QR na importação (pronto) ou sob demanda (lazy)?
- [ ] **Tamanho QR**: Qual densidade? ( Guests com 1000+ = QR pequeno = difícil ler)
- [ ] **Fallback visual**: Se QR falhar, quanto tempo esperar antes de sugerir busca manual?
- [ ] **Permissões**: Quem pode ver/gerar QR? (Admin/Promoter = sim, Validator = só ler?)
- [ ] **Mobile-first**: O painel Validator atual já é mobile-friendly?

---

## Estimativa

| Fase | Complexidade | Estimativa |
|------|--------------|-------------|
| 1 | Baixa | 2h |
| 2 | Média | 4h |
| 3 | Média | 3h |
| 4 | Alta | 6h |
| 5 | Média | 4h |
| **Total** | - | **19h** |

---

## Referências

- Documentação: `chillerlan/php-qrcode`
- Leitor JS: `html5-qrcode` (https://github.com/mebjas/html5-qrcode)
- Modelos existentes: `app/Models/Guest.php`, `app/Models/CheckinAttempt.php`
- Importação: `app/Imports/GuestsImport.php`
- Check-in atual: `app/Filament/Validator/Resources/Guests/Tables/GuestsTable.php`
