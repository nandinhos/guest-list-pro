# Licao: Centralização de Cartões Customizados no Mobile (Filament)

**Data**: 2026-02-21
**Stack**: TailwindCSS v4, Filament v4
**Tags**: [success-pattern, visual, css]

## Contexto
Desenvolvimento de cartões mobile personalizados para substituir as linhas padrão da tabela do Filament.

## Problema
Os cartões pareciam "deslocados" para a direita, dando uma percepção de falta de centralização. Isso ocorria porque a coluna da tabela do Filament possui um `padding` interno que o container `w-full` do card respeitava, resultando em um recuo indesejado na esquerda.

## Solucao
### Correcao Aplicada
Uso de margem negativa e cálculo de largura compensatória:

```blade
<div class="-ml-3 w-[calc(100%+0.75rem)] flex justify-between ...">
    <!-- Conteúdo do Card -->
</div>
```

### Por Que Funciona
O `-ml-3` (margem negativa de 12px) puxa o card para cima do padding da célula pai. O `w-[calc(100%+0.75rem)]` (largura total + 12px) garante que o card ocupe o espaço recuperado na esquerda e preencha até a borda direita, resultando em uma simetria perfeita em relação às bordas da tela do celular.

## Prevencao
Como evitar no futuro:
- [ ] Ao converter tabelas em cards mobile no Filament, inspecionar o `padding` da classe `.fi-ta-col-wrp` ou similar.
- [ ] Aplicar a compensação no componente de card para que ele seja agnóstico ao container pai.

## Referencias
- [Guest Mobile Card Component](file:///home/nandodev/projects/guest-list-pro/resources/views/components/guest-mobile-card.blade.php)
