# Licao: Sincronização de Temas Tailwind no Filament

**Data**: 2026-02-21
**Stack**: Laravel 12, Filament v4, TailwindCSS v4 @tailwindcss/vite
**Tags**: [success-pattern, config, frontend]

## Contexto
Durante o desenvolvimento de componentes Blade customizados (como `guest-mobile-card.blade.php`), observou-se que as classes CSS aplicadas aos componentes não eram refletidas no navegador após o comando `npm run build`.

## Problema
Os estilos customizados eram removidos pelo processo de "purge" do Tailwind. O compilador do Vite não estava rastreando os arquivos Blade localizados fora das pastas padrão de recursos do Filament.

## Causa Raiz
### Analise (5 Whys)
1. **Por que falhou?** O CSS do componente não apareceu no build.
2. **Por que?** O Tailwind não gerou as classes para esse arquivo.
3. **Por que?** O arquivo Blade não estava na lista de conteúdo a ser escaneado.
4. **Por que?** No Tailwind v4 com `@tailwindcss/vite`, o escaneamento automático pode ignorar pastas profundas de componentes se não forem explicitamente linkadas no tema.
5. **Por que?** Falta da diretiva `@source` apontando para o diretório de componentes globais.

## Solucao
### Correcao Aplicada
Adicionar a diretiva `@source` nos arquivos de tema do Filament (`admin/theme.css`, `promoter/theme.css`, etc.):

```css
@import "../../app.css";
@import "../../../vendor/filament/filament/resources/css/theme.css";

@theme {
    /* ... colors ... */
}

@source "../../../resources/views/components/**/*.blade.php";
```

### Por Que Funciona
Isso instrui o motor do Tailwind a incluir especificamente esses arquivos na fase de análise de classes, garantindo que mesmo componentes compartilhados tenham seus estilos preservados no bundle final.

## Prevencao
Como evitar no futuro:
- [ ] Sempre que criar um novo diretório de componentes Blade (`resources/views/widgets`, `resources/views/emails`, etc), verificar se ele está coberto pelo `@source` nos temas ativos.
- [ ] Validar o build com `sail npm run build` antes de finalizar refatorações visuais.

## Referencias
- [Vite Config](file:///home/nandodev/projects/guest-list-pro/vite.config.js)
- [Promoter Theme](file:///home/nandodev/projects/guest-list-pro/resources/css/filament/promoter/theme.css)
