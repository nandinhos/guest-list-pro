# Regras Específicas do Projeto - Guest List Pro

Este documento contém regras críticas que devem ser seguidas por todos os agentes e sessões de desenvolvimento.

## 📱 Mobile-First OBRIGATÓRIO
- Todas as tabelas Filament devem ter uma `ViewColumn` chamada `mobile_card`.
- O design deve priorizar a usabilidade em celulares (uso em portaria).

## ⚡ Estabilidade & SPA
- **SPA Desabilitado**: O modo SPA do Filament (`->spa(true)`) é PROIBIDO neste projeto devido a conflitos de redeclaração de JavaScript. Mantenha sempre `->spa(false)` nos `PanelProviders`.

## 🎫 QR Code & Identificação
- **ULID para Identificação**: Sempre use ULID para tokens de QR Code.
- **Geração Automática**: O `qr_token` deve ser gerado no `GuestObserver`.
- **Biblioteca**: Use `simplesoftwareio/simple-qrcode`.

## 📂 Organização de Código
- **Services**: Toda lógica de check-in e validação de permissões deve residir em `App\Services\GuestService`.
- **TDD**: Nenhum código deve ser escrito sem testes primeiro (RED-GREEN-REFACTOR).

## 🌐 Idioma
- **Português (Brasil)**: Mensagens de erro, labels de interface, commits e documentação devem ser exclusivamente em Português do Brasil.
