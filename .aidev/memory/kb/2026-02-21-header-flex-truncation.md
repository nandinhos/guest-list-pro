# Licao: Alinhamento de Topbar em Layouts Fullscreen

**Data**: 2026-02-21
**Stack**: TailwindCSS v4, Filament Layouts
**Tags**: [bug, ui, quality]

## Contexto
O layout `fullscreen.blade.php` (usado nas páginas de Selecionar Evento) apresentava problemas de espaçamento no mobile.

## Problema
O nome do portal/marca ficava "colado" no nome do usuário ou no botão de sair em telas menores, dificultando a leitura e parecendo um erro de interface.

## Causa Raiz
### Analise (5 Whys)
1. **Por que falhou?** Texto encavalado no cabeçalho.
2. **Por que?** O container pai não forçava o distanciamento entre as extremidades.
3. **Por que?** O uso de `ml-auto` no bloco da direita não é confiável quando o bloco da esquerda (marca) também cresce.
4. **Por que?** Não havia uma regra de `justify-between` e `gap` definida.
5. **Por que?** O layout foi desenhado pensando apenas em resoluções desktop onde sobrava espaço.

## Solucao
### Correcao Aplicada
Reestruturação para Flexbox robusto e truncagem por `min-w-0`:

```blade
<div class="flex w-full justify-between gap-4 ...">
     <div class="flex items-center gap-x-4 min-w-0">
        <!-- Brand (com truncate) -->
     </div>
     <div class="flex items-center gap-x-4 shrink-0">
        <!-- User Options (não encolhe) -->
     </div>
</div>
```

### Por Que Funciona
`justify-between` garante que os dois blocos fiquem nas pontas. `min-w-0` no bloco da esquerda permite que o `truncate` funcione corretamente quando o título é maior que o espaço disponível, enquanto o `shrink-0` na direita garante que as opções do usuário nunca sumam da tela.

## Prevencao
Como evitar no futuro:
- [ ] Sempre usar `min-w-0` em containers que contenham textos com `truncate` dentro de flexboxes.
- [ ] Testar headers com nomes de usuários longos ou nomes de portais extensos.

## Referencias
- [Fullscreen Layout](file:///home/nandodev/projects/guest-list-pro/resources/views/layouts/fullscreen.blade.php)
