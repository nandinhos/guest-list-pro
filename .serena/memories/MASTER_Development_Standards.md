# Guest List Pro: Padrões de Desenvolvimento & Lições Aprendidas

Documento consolidado para guiar o Agente de IA.

## Índice de Memórias Serena
1. `JS_SPA_Stability_Lessons.md` - SPA desabilitado por padrão
2. `Filament_Enum_TypeError_Fix.md` - Verificação de instância em Enums
3. `Centralized_Guest_Duplicity_Logic.md` - Duplicidade centralizada em Services
4. `Filament_Table_ViewColumn_Standards.md` - ViewColumn para mobile cards
5. `Testing_Factories_Patterns.md` - Padrões de testes e factories
6. `Filament_Database_Notifications_Bug.md` - Bug de Actions em database notifications

## Checklist Pré-Implementação
1. SPA está desabilitado nos PanelProviders?
2. Enums em Selects usam verificação de instância?
3. Duplicidade usa `ApprovalRequestService::checkForDuplicates`?
4. Mobile usa `ViewColumn` (não `Layout\View`)?
5. Factories existem para todos os models usados em testes?
6. Database notifications NÃO usam Actions?

## Guia Completo
Veja: `docs/DEVELOPMENT_STANDARDS.md`

---
*Última atualização: Janeiro 2026*
