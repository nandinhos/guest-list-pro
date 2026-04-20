# 🚀 QUICK START — guest-list-pro

*Leia este arquivo PRIMEIRO antes de começar a trabalhar.*

---

## Para agentes autônomos: Comece Aqui

### 1. Leia o STATUS (2 min)
```bash
cat .devorq/state/STATUS.md
```

### 2. Leia as regras (1 min)
```bash
cat .devorq/rules/project.md
```

### 3. Consultar lições relevantes (se issue novo)
```bash
cat .devorq/state/lessons-learned/2026-04-20-e2e-tests-and-seeder-fixes.md
```

### 4. Pronto para trabalhar!

---

## Se você precisa...

### ... debugar um E2E test quebrando
→ Verificar: Seeder criou EventAssignment? URL correta? Regex captura formato?

### ... adicionar uma feature nova
→ Verificar: SPEC já existe? Criar se não. Usar TDD.

### ... entender o fluxo de check-in
→ Consultar: `app/Http/Middleware/EnsureEventSelected.php` + `app/Livewire/EventSelectorGrid.php`

### ... rodar testes
```bash
vendor/bin/sail test tests/Unit --no-coverage
npx playwright test e2e/smoke-tests.spec.ts
```

---

## ⚠️ REGRAS DE OURO

1. **`vendor/bin/sail` — sempre!**
2. **TDD — vermelho, verde, refatora**
3. **Commitar com tests passando**
4. **Documentar lições novas**

---

*Versão: 2026-04-20*
*HANDOVER completo: HANDOVER.md*