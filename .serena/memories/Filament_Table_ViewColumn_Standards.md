# Lição Aprendida: ViewColumn vs Layout\View no Filament v4

Ao implementar visualizações customizadas em tabelas do Filament, é fundamental distinguir entre componentes de **Layout** e componentes de **Coluna**.

## O Erro (BadMethodCallException)
Usar `Filament\Tables\Columns\Layout\View` dentro do array `columns()` de uma tabela e tentar chamar métodos como `label()`, `hiddenFrom()` ou `visibleFrom()` resultará em erro, pois componentes de layout não possuem esses métodos de infraestrutura de coluna.

## O Erro (LogicException)
Ao usar `Filament\Tables\Columns\ViewColumn`, o primeiro argumento de `make()` deve ser um identificador (geralmente o nome da coluna ou um nome fictício como 'mobile_card'). O caminho da view Blade **deve** ser passado através do método `->view()`.
- **Errado**: `ViewColumn::make('caminho.da.view')`
- **Certo**: `ViewColumn::make('mobile_card')->view('caminho.da.view')`

## Padrão Mobile-First
Para uma experiência responsiva ideal:
1. Defina a `ViewColumn` como a primeira coluna.
2. Use `->hiddenFrom('md')` na `ViewColumn`.
3. Use `->visibleFrom('md')` em todas as colunas de texto/clássicas do desktop.
4. Utilize `->extraAttributes(['class' => 'hidden md:inline-flex'])` nas `recordActions` padrão para forçar o uso das ações integradas no card mobile.
