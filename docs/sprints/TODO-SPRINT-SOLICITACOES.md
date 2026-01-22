# Guestlist Pro - Sprint de Solicitações (Sistema de Aprovação)

> **Documento criado em:** 2026-01-21
> **Prioridade:** CRÍTICA (Segurança)
> **Objetivo:** Implementar sistema de aprovação para check-ins e inclusões de convidados
> **Baseado em:** Regra de negócio identificada durante testes

---

## 📋 Resumo Executivo

### Problema Identificado
O sistema atual permite que **Validadores realizem check-in direto**, sem aprovação do Admin. Isso representa uma **vulnerabilidade de segurança** pois:

1. Um validador pode realizar check-in de pessoas não autorizadas
2. Não há camada de aprovação para inclusões de última hora
3. Promoters podem adicionar convidados fora do horário sem supervisão

### Solução Proposta
Criar um **Sistema de Solicitações** com dois fluxos:

| Tipo | Origem | Descrição | Aprovador |
|------|--------|-----------|-----------|
| **Inclusão de Convidado** | Promoter | Adicionar convidado fora do horário ou acima da cota | Admin |
| **Check-in Emergencial** | Validator | Check-in de pessoa não cadastrada na lista | Admin |

---

## 📊 Status das Sprints

| Sprint | Nome | Status | Progresso |
|--------|------|--------|-----------|
| **S.0** | Modelagem e Infraestrutura | ✅ CONCLUÍDO | 100% |
| **S.1** | Fluxo de Solicitação (Validator) | ✅ CONCLUÍDO | 100% |
| **S.2** | Fluxo de Solicitação (Promoter) | ✅ CONCLUÍDO | 100% |
| **S.3** | Painel de Aprovação (Admin) | ✅ CONCLUÍDO | 100% |
| **S.4** | Notificações e Alertas | ✅ CONCLUÍDO | 100% |
| **S.5** | Auditoria e Relatórios | 🏗️ EM ANDAMENTO | 80% |
| **S.6** | Testes e Validação | ❌ PENDENTE | 0% |

---

## 🔒 Regras de Negócio Documentadas

### RN-001: Validador NÃO pode fazer check-in direto
- O botão "ENTRADA" atual deve ser substituído por "SOLICITAR CHECK-IN" para convidados que precisam de aprovação
- Convidados **PRÉ-APROVADOS** (já na lista com status aprovado) podem receber check-in direto
- Convidados **NÃO LISTADOS** requerem solicitação ao Admin

### RN-002: Promoter pode solicitar inclusão fora do prazo
- Quando janela de tempo expirada → Solicitação vai para Admin
- Quando cota excedida → Solicitação vai para Admin
- Inclusões dentro do prazo e cota → Aprovação automática (comportamento atual)

### RN-003: Admin tem visão centralizada
- Menu "Solicitações" com todas as pendências
- Notificação em tempo real de novas solicitações
- Ação de aprovar/rejeitar com motivo obrigatório para rejeição

### RN-004: Fluxo do convidado na portaria
```
Convidado chega → Validador busca na lista
    ├─ Encontrado (aprovado) → Check-in direto ✓
    ├─ Encontrado (pendente) → Aguardar aprovação ⏳
    └─ Não encontrado → Criar solicitação de check-in emergencial
                         └─ Convidado sai da fila e aguarda
                         └─ Admin aprova/rejeita
                         └─ Validador recebe notificação
                         └─ Convidado retorna para check-in ✓
```

### RN-005: Auditoria completa
- Toda solicitação registra: data, hora, usuário, IP, motivo
- Toda aprovação/rejeição registra: data, hora, admin, motivo
- Logs não podem ser alterados ou excluídos

---

# SPRINT S.0: Modelagem e Infraestrutura

**Prioridade:** CRÍTICA
**Objetivo:** Criar estrutura de dados e enums necessários

## S.0.1 Criar Enum de Status de Solicitação
**Arquivo:** `app/Enums/RequestStatus.php`

### Tarefas:
- [ ] Criar enum `RequestStatus`
  - **Comando:** `sail artisan make:enum RequestStatus`
  - **Valores:**
    ```php
    case PENDING = 'pending';       // Aguardando aprovação
    case APPROVED = 'approved';     // Aprovado pelo Admin
    case REJECTED = 'rejected';     // Rejeitado pelo Admin
    case CANCELLED = 'cancelled';   // Cancelado pelo solicitante
    case EXPIRED = 'expired';       // Expirado (tempo limite)
    ```
  - **Labels (PT-BR):**
    - PENDING → "Pendente"
    - APPROVED → "Aprovado"
    - REJECTED → "Rejeitado"
    - CANCELLED → "Cancelado"
    - EXPIRED → "Expirado"
  - **Cores:**
    - PENDING → warning (amarelo)
    - APPROVED → success (verde)
    - REJECTED → danger (vermelho)
    - CANCELLED → gray (cinza)
    - EXPIRED → gray (cinza)
  - **Ícones:**
    - PENDING → clock
    - APPROVED → check-circle
    - REJECTED → x-circle
    - CANCELLED → minus-circle
    - EXPIRED → exclamation-circle

### Critérios de Aceite:
- [ ] Enum criado com todos os valores
- [ ] Labels em português funcionando
- [ ] Cores e ícones configurados para Filament

---

## S.0.2 Criar Enum de Tipo de Solicitação
**Arquivo:** `app/Enums/RequestType.php`

### Tarefas:
- [ ] Criar enum `RequestType`
  - **Comando:** `sail artisan make:enum RequestType`
  - **Valores:**
    ```php
    case GUEST_INCLUSION = 'guest_inclusion';     // Promoter: incluir convidado
    case EMERGENCY_CHECKIN = 'emergency_checkin'; // Validator: check-in emergencial
    ```
  - **Labels (PT-BR):**
    - GUEST_INCLUSION → "Inclusão de Convidado"
    - EMERGENCY_CHECKIN → "Check-in Emergencial"
  - **Cores:**
    - GUEST_INCLUSION → primary (azul)
    - EMERGENCY_CHECKIN → warning (amarelo)
  - **Ícones:**
    - GUEST_INCLUSION → user-plus
    - EMERGENCY_CHECKIN → bolt

### Critérios de Aceite:
- [ ] Enum criado com todos os valores
- [ ] Labels em português funcionando

---

## S.0.3 Criar Tabela de Solicitações
**Arquivo:** Nova migration

### Tarefas:
- [ ] Criar migration para tabela `approval_requests`
  - **Comando:** `sail artisan make:migration create_approval_requests_table`
  - **Campos:**
    ```php
    Schema::create('approval_requests', function (Blueprint $table) {
        $table->id();

        // Contexto
        $table->foreignId('event_id')->constrained()->cascadeOnDelete();
        $table->foreignId('sector_id')->nullable()->constrained()->nullOnDelete();

        // Tipo e Status
        $table->string('type');        // RequestType enum
        $table->string('status')->default('pending'); // RequestStatus enum

        // Solicitante
        $table->foreignId('requested_by')->constrained('users')->cascadeOnDelete();
        $table->timestamp('requested_at');

        // Dados do convidado (para ambos os tipos)
        $table->string('guest_name');
        $table->string('guest_document')->nullable();
        $table->string('guest_document_type')->nullable();
        $table->string('guest_email')->nullable();

        // Motivo da solicitação
        $table->text('request_reason')->nullable();

        // Resposta do Admin
        $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
        $table->timestamp('reviewed_at')->nullable();
        $table->text('review_notes')->nullable();

        // Referências opcionais
        $table->foreignId('guest_id')->nullable()->constrained()->nullOnDelete(); // Se já existe
        $table->foreignId('created_guest_id')->nullable()->constrained('guests')->nullOnDelete(); // Guest criado após aprovação

        // Metadados de auditoria
        $table->string('ip_address', 45)->nullable();
        $table->string('user_agent')->nullable();

        // Configuração
        $table->timestamp('expires_at')->nullable(); // Tempo limite para aprovação
        $table->integer('priority')->default(0); // 0=normal, 1=alta, 2=urgente

        $table->timestamps();

        // Índices
        $table->index(['event_id', 'status']);
        $table->index(['requested_by', 'status']);
        $table->index(['type', 'status']);
        $table->index('expires_at');
    });
    ```

### Critérios de Aceite:
- [x] Migration criada corretamente
- [x] Índices otimizados para queries frequentes
- [x] Foreign keys configuradas

---

## S.0.4 Criar Model ApprovalRequest
**Arquivo:** `app/Models/ApprovalRequest.php`

### Tarefas:
- [ ] Criar model com factory
  - **Comando:** `sail artisan make:model ApprovalRequest -f`

- [ ] Configurar fillable e casts:
  ```php
  protected $fillable = [
      'event_id',
      'sector_id',
      'type',
      'status',
      'requested_by',
      'requested_at',
      'guest_name',
      'guest_document',
      'guest_document_type',
      'guest_email',
      'request_reason',
      'reviewed_by',
      'reviewed_at',
      'review_notes',
      'guest_id',
      'created_guest_id',
      'ip_address',
      'user_agent',
      'expires_at',
      'priority',
  ];

  protected function casts(): array
  {
      return [
          'type' => RequestType::class,
          'status' => RequestStatus::class,
          'guest_document_type' => DocumentType::class,
          'requested_at' => 'datetime',
          'reviewed_at' => 'datetime',
          'expires_at' => 'datetime',
      ];
  }
  ```

- [ ] Configurar relacionamentos:
  ```php
  public function event(): BelongsTo
  public function sector(): BelongsTo
  public function requester(): BelongsTo // requested_by
  public function reviewer(): BelongsTo  // reviewed_by
  public function guest(): BelongsTo     // guest_id existente
  public function createdGuest(): BelongsTo // guest criado após aprovação
  ```

- [ ] Adicionar scopes úteis:
  ```php
  public function scopePending($query)
  public function scopeForEvent($query, int $eventId)
  public function scopeExpired($query)
  public function scopeByType($query, RequestType $type)
  ```

- [ ] Configurar Activity Log (Spatie):
  ```php
  use LogsActivity;

  public function getActivitylogOptions(): LogOptions
  {
      return LogOptions::defaults()
          ->logOnly(['status', 'reviewed_by', 'reviewed_at', 'review_notes'])
          ->logOnlyDirty()
          ->dontSubmitEmptyLogs();
  }
  ```

### Critérios de Aceite:
- [x] Model criado com todos os relacionamentos
- [x] Casts configurados corretamente
- [x] Scopes funcionando
- [x] Activity Log registrando mudanças

---

## S.0.5 Criar Service de Solicitações
**Arquivo:** `app/Services/ApprovalRequestService.php`

### Tarefas:
- [ ] Criar service class
  - **Comando:** `sail artisan make:class Services/ApprovalRequestService`

- [ ] Implementar métodos principais:
  ```php
  class ApprovalRequestService
  {
      /**
       * Criar solicitação de inclusão de convidado (Promoter)
       */
      public function createGuestInclusionRequest(
          User $requester,
          int $eventId,
          int $sectorId,
          array $guestData,
          ?string $reason = null
      ): ApprovalRequest

      /**
       * Criar solicitação de check-in emergencial (Validator)
       */
      public function createEmergencyCheckinRequest(
          User $requester,
          int $eventId,
          ?int $sectorId,
          array $guestData,
          ?string $reason = null
      ): ApprovalRequest

      /**
       * Aprovar solicitação (Admin)
       */
      public function approve(
          ApprovalRequest $request,
          User $admin,
          ?string $notes = null
      ): ApprovalRequest

      /**
       * Rejeitar solicitação (Admin)
       */
      public function reject(
          ApprovalRequest $request,
          User $admin,
          string $reason
      ): ApprovalRequest

      /**
       * Cancelar solicitação (Solicitante)
       */
      public function cancel(
          ApprovalRequest $request,
          User $user
      ): ApprovalRequest

      /**
       * Verificar e marcar solicitações expiradas
       */
      public function processExpiredRequests(): int

      /**
       * Obter solicitações pendentes para um evento
       */
      public function getPendingForEvent(int $eventId): Collection

      /**
       * Verificar se usuário pode aprovar/rejeitar
       */
      public function canReview(User $user): bool
  }
  ```

### Critérios de Aceite:
- [x] Service criado com todos os métodos
- [x] Validações de permissão implementadas
- [x] Transações de banco utilizadas
- [x] Exceções personalizadas para erros

---

## S.0.6 Alterar Tabela Guests (Opcional)
**Decisão:** Avaliar necessidade de campo `approval_status`

### Opção A: Usar tabela `approval_requests` como referência
- Guest sem solicitação pendente = pode receber check-in direto
- Guest com solicitação pendente = aguardar

### Opção B: Adicionar campo na tabela guests
- [ ] Criar migration:
  ```php
  $table->string('approval_status')->default('approved');
  // Valores: 'pending', 'approved', 'rejected'
  ```

### Recomendação: **Opção A** (menos alterações no sistema existente)

---

# SPRINT S.1: Fluxo de Solicitação (Validator)

**Prioridade:** CRÍTICA
**Objetivo:** Modificar painel do Validator para usar sistema de solicitações

## S.1.1 Modificar Ação de Check-in
**Arquivo:** `app/Filament/Validator/Resources/Guests/Tables/GuestsTable.php`

### Tarefas:
- [ ] Manter check-in direto para convidados já aprovados (na lista)
  - Verificar: `$guest->exists` e não tem solicitação pendente
  - Comportamento atual permanece

- [ ] Desabilitar check-in para convidados com solicitação pendente
  - Mostrar badge "Aguardando Aprovação" na linha
  - Botão desabilitado com tooltip explicativo

### Critérios de Aceite:
- [x] Check-in direto funciona para convidados aprovados
- [x] Convidados pendentes não podem receber check-in
- [x] UI clara indicando status de aprovação

---

## S.1.2 Criar Ação de Solicitação Emergencial
**Arquivo:** `app/Filament/Validator/Resources/Guests/Actions/EmergencyCheckinAction.php`

### Tarefas:
- [ ] Criar Header Action para "Solicitar Check-in"
  - **Comando:** `sail artisan make:filament-action EmergencyCheckinAction --panel=validator`
  - **Ícone:** bolt (raio)
  - **Cor:** warning (amarelo)
  - **Posição:** Header da tabela (ao lado da busca)

- [ ] Implementar formulário modal:
  ```php
  ->form([
      TextInput::make('guest_name')
          ->label('Nome do Convidado')
          ->required()
          ->maxLength(255),

      Select::make('guest_document_type')
          ->label('Tipo de Documento')
          ->options(DocumentType::class)
          ->default(DocumentType::CPF)
          ->required()
          ->live(),

      TextInput::make('guest_document')
          ->label('Documento')
          ->required()
          ->maxLength(50)
          ->mask(fn (Get $get) => $get('guest_document_type') === 'cpf' ? '999.999.999-99' : null),

      Select::make('sector_id')
          ->label('Setor')
          ->options(fn () => Sector::forEvent(session('selected_event_id'))->pluck('name', 'id'))
          ->required()
          ->searchable(),

      Textarea::make('request_reason')
          ->label('Motivo da Solicitação')
          ->placeholder('Ex: Convidado de última hora do promoter X, esqueceu de adicionar à lista')
          ->required()
          ->maxLength(500),
  ])
  ```

- [ ] Implementar action:
  ```php
  ->action(function (array $data) {
      $service = app(ApprovalRequestService::class);

      $request = $service->createEmergencyCheckinRequest(
          requester: auth()->user(),
          eventId: session('selected_event_id'),
          sectorId: $data['sector_id'],
          guestData: [
              'name' => $data['guest_name'],
              'document' => $data['guest_document'],
              'document_type' => $data['guest_document_type'],
          ],
          reason: $data['request_reason']
      );

      Notification::make()
          ->title('Solicitação Enviada')
          ->body("Solicitação #{$request->id} criada. Aguarde aprovação do administrador.")
          ->success()
          ->send();
  })
  ```

### Critérios de Aceite:
- [x] Botão visível no header da tabela
- [x] Formulário abre em modal
- [x] Validação de campos funciona
- [x] Solicitação é criada no banco
- [x] Notificação de sucesso exibida
- [x] IP e User Agent registrados

---

## S.1.3 Criar Página de Minhas Solicitações (Validator)
**Arquivo:** `app/Filament/Validator/Pages/MyRequests.php`

### Tarefas:
- [ ] Criar página para listar solicitações do validador
  - **Comando:** `sail artisan make:filament-page MyRequests --panel=validator`
  - **Menu:** "Minhas Solicitações" no navigation

- [ ] Implementar tabela com:
  - Colunas: ID, Nome do Convidado, Tipo, Status, Data, Ações
  - Filtros: Status, Data
  - Ordenação: Mais recentes primeiro

- [ ] Ações disponíveis:
  - Ver detalhes (modal)
  - Cancelar (apenas para pendentes)

### Critérios de Aceite:
- [x] Página lista apenas solicitações do usuário logado
- [x] Filtros funcionam corretamente
- [x] Pode cancelar solicitações pendentes
- [x] Atualização em tempo real (polling)

---

## S.1.4 Widget de Solicitações Pendentes (Validator)
**Arquivo:** `app/Filament/Validator/Widgets/PendingRequestsWidget.php`

### Tarefas:
- [ ] Criar widget para dashboard
  - **Comando:** `sail artisan make:filament-widget PendingRequestsWidget --panel=validator`
  - **Tipo:** Stats card

- [ ] Mostrar:
  - Quantidade de solicitações pendentes do usuário
  - Link para página de solicitações

### Critérios de Aceite:
- [ ] Widget mostra contagem correta
- [ ] Clique redireciona para página de solicitações

---

# SPRINT S.2: Fluxo de Solicitação (Promoter)

**Prioridade:** ALTA
**Objetivo:** Adicionar sistema de solicitações ao painel do Promoter

## S.2.1 Modificar Criação de Convidados
**Arquivo:** `app/Filament/Promoter/Resources/Guests/Pages/CreateGuest.php`

### Tarefas:
- [ ] Verificar permissões antes de criar diretamente:
  ```php
  $guestService = app(GuestService::class);
  $validation = $guestService->canRegisterGuest(
      auth()->user(),
      session('selected_event_id'),
      $data['sector_id']
  );

  if (!$validation['can_register']) {
      // Redirecionar para fluxo de solicitação
      return $this->createApprovalRequest($data, $validation['reason']);
  }
  ```

- [ ] Se fora da janela de tempo OU cota excedida:
  - Criar `ApprovalRequest` em vez de `Guest`
  - Mostrar mensagem explicativa
  - Redirecionar para página de solicitações

### Critérios de Aceite:
- [x] Criação direta funciona quando permitido
- [x] Solicitação criada quando fora do prazo/cota
- [x] Mensagem clara sobre o que aconteceu

---

## S.2.2 Criar Página de Minhas Solicitações (Promoter)
**Arquivo:** `app/Filament/Promoter/Pages/MyRequests.php`

### Tarefas:
- [ ] Similar ao Validator (S.1.3)
- [ ] Filtrar por tipo: apenas `GUEST_INCLUSION`

### Critérios de Aceite:
- [x] Lista solicitações do promoter
- [x] Pode cancelar pendentes
- [x] Vê status atualizado

---

## S.2.3 Indicador Visual na Lista de Convidados
**Arquivo:** `app/Filament/Promoter/Resources/Guests/Tables/GuestsTable.php`

### Tarefas:
- [ ] Adicionar badge para convidados com solicitação pendente
- [ ] Diferenciar convidados "na lista" vs "em aprovação"

### Critérios de Aceite:
- [ ] Badge amarelo "Aguardando Aprovação" visível
- [ ] Lista clara e fácil de entender

---

# SPRINT S.3: Painel de Aprovação (Admin)

**Prioridade:** CRÍTICA
**Objetivo:** Criar área de aprovação centralizada no Admin

## S.3.1 Criar Resource de Solicitações
**Arquivo:** `app/Filament/Resources/ApprovalRequests/ApprovalRequestResource.php`

### Tarefas:
- [ ] Criar resource completo
  - **Comando:** `sail artisan make:filament-resource ApprovalRequest --panel=admin`
  - **Menu:** "Solicitações" com ícone bell ou inbox
  - **Somente leitura:** Sem create/edit direto (apenas via actions)

- [ ] Configurar tabela:
  ```php
  public static function table(Table $table): Table
  {
      return $table
          ->columns([
              TextColumn::make('id')
                  ->label('#')
                  ->sortable(),

              TextColumn::make('type')
                  ->label('Tipo')
                  ->badge(),

              TextColumn::make('guest_name')
                  ->label('Convidado')
                  ->searchable()
                  ->description(fn ($record) => $record->guest_document),

              TextColumn::make('sector.name')
                  ->label('Setor')
                  ->badge(),

              TextColumn::make('requester.name')
                  ->label('Solicitante')
                  ->description(fn ($record) => $record->requester->role->getLabel()),

              TextColumn::make('status')
                  ->label('Status')
                  ->badge(),

              TextColumn::make('requested_at')
                  ->label('Solicitado em')
                  ->dateTime('d/m/Y H:i')
                  ->sortable(),

              TextColumn::make('priority')
                  ->label('Prioridade')
                  ->badge()
                  ->formatStateUsing(fn ($state) => match($state) {
                      0 => 'Normal',
                      1 => 'Alta',
                      2 => 'Urgente',
                  })
                  ->color(fn ($state) => match($state) {
                      0 => 'gray',
                      1 => 'warning',
                      2 => 'danger',
                  }),
          ])
          ->defaultSort('requested_at', 'desc')
          ->filters([
              SelectFilter::make('status')
                  ->options(RequestStatus::class)
                  ->default('pending'),

              SelectFilter::make('type')
                  ->options(RequestType::class),

              SelectFilter::make('event_id')
                  ->relationship('event', 'name')
                  ->searchable()
                  ->preload(),

              Filter::make('created_today')
                  ->query(fn ($query) => $query->whereDate('requested_at', today())),
          ])
          ->actions([
              // Ver detalhes
              Action::make('view')
                  ->icon('heroicon-o-eye')
                  ->modalContent(fn ($record) => view('filament.modals.request-details', ['request' => $record])),

              // Aprovar
              Action::make('approve')
                  ->icon('heroicon-o-check-circle')
                  ->color('success')
                  ->visible(fn ($record) => $record->status === RequestStatus::PENDING)
                  ->requiresConfirmation()
                  ->form([
                      Textarea::make('notes')
                          ->label('Observações (opcional)')
                          ->maxLength(500),
                  ])
                  ->action(function ($record, array $data) {
                      app(ApprovalRequestService::class)->approve(
                          $record,
                          auth()->user(),
                          $data['notes'] ?? null
                      );

                      Notification::make()
                          ->title('Solicitação Aprovada')
                          ->success()
                          ->send();
                  }),

              // Rejeitar
              Action::make('reject')
                  ->icon('heroicon-o-x-circle')
                  ->color('danger')
                  ->visible(fn ($record) => $record->status === RequestStatus::PENDING)
                  ->requiresConfirmation()
                  ->form([
                      Textarea::make('reason')
                          ->label('Motivo da Rejeição')
                          ->required()
                          ->maxLength(500),
                  ])
                  ->action(function ($record, array $data) {
                      app(ApprovalRequestService::class)->reject(
                          $record,
                          auth()->user(),
                          $data['reason']
                      );

                      Notification::make()
                          ->title('Solicitação Rejeitada')
                          ->warning()
                          ->send();
                  }),
          ])
          ->bulkActions([
              BulkAction::make('approve_all')
                  ->label('Aprovar Selecionados')
                  ->icon('heroicon-o-check')
                  ->color('success')
                  ->requiresConfirmation()
                  ->action(function (Collection $records) {
                      $service = app(ApprovalRequestService::class);
                      foreach ($records as $record) {
                          if ($record->status === RequestStatus::PENDING) {
                              $service->approve($record, auth()->user());
                          }
                      }
                      Notification::make()
                          ->title('Solicitações aprovadas: ' . $records->count())
                          ->success()
                          ->send();
                  }),
          ])
          ->poll('30s'); // Atualização automática
  }
  ```

### Critérios de Aceite:
- [x] Resource criado com tabela completa
- [x] Filtros funcionando (status, tipo, evento)
- [x] Ações de aprovar/rejeitar funcionando
- [x] Aprovação em massa funciona
- [x] Polling ativo para atualizações

---

## S.3.2 Criar Navigation Badge (Contador)
**Arquivo:** `app/Filament/Resources/ApprovalRequests/ApprovalRequestResource.php`

### Tarefas:
- [ ] Adicionar badge no menu com contagem de pendentes:
  ```php
  public static function getNavigationBadge(): ?string
  {
      $count = ApprovalRequest::pending()
          ->whereNull('expires_at')
          ->orWhere('expires_at', '>', now())
          ->count();

      return $count > 0 ? (string) $count : null;
  }

  public static function getNavigationBadgeColor(): ?string
  {
      $count = (int) static::getNavigationBadge();

      if ($count >= 10) return 'danger';
      if ($count >= 5) return 'warning';
      return 'primary';
  }
  ```

### Critérios de Aceite:
- [ ] Badge mostra quantidade de pendentes
- [ ] Cor muda baseada na quantidade
- [ ] Atualiza ao navegar

---

## S.3.3 Widget de Solicitações no Dashboard Admin
**Arquivo:** `app/Filament/Widgets/PendingApprovalsWidget.php`

### Tarefas:
- [ ] Criar widget de stats:
  ```php
  Stat::make('Solicitações Pendentes', ApprovalRequest::pending()->count())
      ->description('Aguardando sua aprovação')
      ->color('warning')
      ->icon('heroicon-o-inbox')
      ->url(ApprovalRequestResource::getUrl('index'))
  ```

- [ ] Mostrar breakdown por tipo:
  - Inclusões de convidados: X
  - Check-ins emergenciais: Y

### Critérios de Aceite:
- [ ] Widget visível no dashboard (A verificar/implementar se necessário)
- [ ] Números corretos
- [ ] Link para listagem funciona

---

## S.3.4 Página de Detalhes da Solicitação
**Arquivo:** `app/Filament/Resources/ApprovalRequests/Pages/ViewRequest.php`

### Tarefas:
- [ ] Criar página de visualização detalhada:
  - Informações do convidado
  - Informações do solicitante
  - Timeline de eventos (criação, aprovação/rejeição)
  - Histórico de Activity Log

### Critérios de Aceite:
- [x] Todas as informações visíveis (via modal)
- [x] Timeline clara (via Activity Log)
- [x] Dados de auditoria acessíveis

---

# SPRINT S.4: Notificações e Alertas

**Prioridade:** ALTA
**Objetivo:** Garantir que Admin seja notificado de novas solicitações

## S.4.1 Criar Notificação de Nova Solicitação
**Arquivo:** `app/Notifications/NewApprovalRequestNotification.php`

### Tarefas:
- [ ] Criar notificação
  - **Comando:** `sail artisan make:notification NewApprovalRequestNotification`
  - **Canais:** database, broadcast (opcional: mail)

- [ ] Enviar para todos os admins quando solicitação criada
  ```php
  $admins = User::where('role', UserRole::ADMIN)->where('is_active', true)->get();
  Notification::send($admins, new NewApprovalRequestNotification($request));
  ```

### Critérios de Aceite:
- [x] Admins recebem notificação no painel
- [x] Notificação contém link para solicitação
- [x] Informações relevantes na notificação

---

## S.4.2 Notificação de Status para Solicitante
**Arquivo:** `app/Notifications/ApprovalRequestStatusNotification.php`

### Tarefas:
- [ ] Criar notificação de mudança de status
- [ ] Enviar quando aprovado/rejeitado

### Critérios de Aceite:
- [x] Solicitante recebe notificação de aprovação
- [x] Solicitante recebe notificação de rejeição com motivo
- [x] Notificação aparece no painel correto (Validator/Promoter)

---

## S.4.3 Configurar Filament Notifications
**Arquivos:** Panel Providers

### Tarefas:
- [ ] Habilitar Database Notifications em todos os painéis
- [ ] Configurar polling para notificações (se necessário)

### Critérios de Aceite:
- [x] Bell icon visível em todos os painéis
- [x] Notificações aparecem em tempo real
- [x] Marcação como lida funciona

---

# SPRINT S.5: Auditoria e Relatórios

**Prioridade:** MÉDIA
**Objetivo:** Garantir rastreabilidade completa

## S.5.1 Configurar Activity Log para Solicitações
**Arquivo:** `app/Models/ApprovalRequest.php`

### Tarefas:
- [ ] Garantir que todas as mudanças são logadas
- [ ] Logar: criação, aprovação, rejeição, cancelamento

### Critérios de Aceite:
- [x] Todos os eventos registrados no activity_log
- [x] Usuário responsável identificado
- [x] Timestamp preciso

---

## S.5.2 Relatório de Solicitações
**Arquivo:** `app/Filament/Resources/ApprovalRequests/Pages/RequestsReport.php`

### Tarefas:
- [ ] Criar página de relatório com:
  - Total de solicitações por período
  - Taxa de aprovação/rejeição
  - Tempo médio de resposta
  - Solicitantes mais ativos
  - Motivos de rejeição mais comuns

### Critérios de Aceite:
- [ ] Relatório exportável (PDF/CSV)
- [ ] Filtros por período e evento
- [ ] Gráficos visuais

---

## S.5.3 Widget de Métricas de Aprovação
**Arquivo:** `app/Filament/Widgets/ApprovalMetricsChart.php`

### Tarefas:
- [ ] Gráfico de pizza: aprovados vs rejeitados
- [ ] Gráfico de linha: solicitações por dia
- [ ] Tempo médio de resposta

### Critérios de Aceite:
- [ ] Gráficos renderizam corretamente
- [ ] Dados filtrados por evento selecionado

---

# SPRINT S.6: Testes e Validação

**Prioridade:** ALTA
**Objetivo:** Garantir funcionamento correto e seguro

## S.6.1 Testes Unitários
**Arquivo:** `tests/Unit/Services/ApprovalRequestServiceTest.php`

### Tarefas:
- [ ] Testar criação de solicitação
- [ ] Testar aprovação
- [ ] Testar rejeição
- [ ] Testar cancelamento
- [ ] Testar expiração
- [ ] Testar permissões

### Critérios de Aceite:
- [ ] 100% de cobertura no Service
- [ ] Todos os testes passando

---

## S.6.2 Testes de Feature
**Arquivo:** `tests/Feature/ApprovalRequestFlowTest.php`

### Tarefas:
- [ ] Testar fluxo completo: Validator cria → Admin aprova → Check-in liberado
- [ ] Testar fluxo: Promoter cria → Admin rejeita → Notificação enviada
- [ ] Testar permissões: Validator não pode aprovar
- [ ] Testar expiração de solicitações

### Critérios de Aceite:
- [ ] Fluxos E2E testados
- [ ] Todos os testes passando

---

## S.6.3 Testes de Interface (Opcional)
**Arquivo:** `tests/Feature/Filament/ApprovalRequestResourceTest.php`

### Tarefas:
- [ ] Testar listagem de solicitações
- [ ] Testar ação de aprovar via UI
- [ ] Testar ação de rejeitar via UI
- [ ] Testar filtros

### Critérios de Aceite:
- [ ] Componentes Filament testados
- [ ] Todos os testes passando

---

# Resumo de Arquivos Criados / Modificados

## Enums
- [x] `app/Enums/RequestStatus.php`
- [x] `app/Enums/RequestType.php`

## Models
- [x] `app/Models/ApprovalRequest.php`
- [x] `database/factories/ApprovalRequestFactory.php`

## Migrations
- [x] `database/migrations/xxxx_create_approval_requests_table.php`

## Services
- [x] `app/Services/ApprovalRequestService.php`

## Filament Resources (Admin)
- [x] `app/Filament/Resources/ApprovalRequests/ApprovalRequestResource.php`
- [x] `app/Filament/Resources/ApprovalRequests/Pages/ListApprovalRequests.php`
- [x] `app/Filament/Resources/ApprovalRequests/Tables/ApprovalRequestsTable.php`

## Filament Pages
- [x] `app/Filament/Validator/Pages/MyRequests.php`
- [x] `app/Filament/Promoter/Pages/MyRequests.php`

## Notifications
- [x] `app/Notifications/NewApprovalRequestNotification.php`
- [x] `app/Notifications/ApprovalRequestStatusNotification.php`

## Tests
- [ ] `tests/Unit/Services/ApprovalRequestServiceTest.php` (Pendente)
- [ ] `tests/Feature/ApprovalRequestFlowTest.php` (Pendente)

---

# Ordem de Execução Recomendada

```
Sprint S.0 (Infraestrutura) ─────► Sprint S.1 (Validator)
                                        │
                                        ▼
Sprint S.2 (Promoter) ◄─────────► Sprint S.3 (Admin)
                                        │
                                        ▼
                              Sprint S.4 (Notificações)
                                        │
                                        ▼
                              Sprint S.5 (Auditoria)
                                        │
                                        ▼
                              Sprint S.6 (Testes)
```

## Dependências Críticas

| Sprint | Depende de |
|--------|------------|
| S.1 | S.0 (Model e Service) |
| S.2 | S.0 (Model e Service) |
| S.3 | S.0 (Model e Service) |
| S.4 | S.0, S.1, S.2, S.3 |
| S.5 | S.0, S.3 |
| S.6 | Todos os anteriores |

---

# Comandos Úteis

```bash
# Criar migration
sail artisan make:migration create_approval_requests_table

# Criar model com factory
sail artisan make:model ApprovalRequest -f

# Criar enum
sail artisan make:enum RequestStatus
sail artisan make:enum RequestType

# Criar service
sail artisan make:class Services/ApprovalRequestService

# Criar resource Filament
sail artisan make:filament-resource ApprovalRequest --panel=admin --view

# Criar page Filament
sail artisan make:filament-page MyRequests --panel=validator
sail artisan make:filament-page MyRequests --panel=promoter

# Criar widget Filament
sail artisan make:filament-widget PendingApprovalsWidget --panel=admin
sail artisan make:filament-widget PendingRequestsWidget --panel=validator

# Criar notification
sail artisan make:notification NewApprovalRequestNotification
sail artisan make:notification ApprovalRequestStatusNotification

# Criar testes
sail artisan make:test ApprovalRequestServiceTest --unit
sail artisan make:test ApprovalRequestFlowTest

# Rodar migrations
sail artisan migrate

# Rodar testes
sail artisan test --filter=ApprovalRequest

# Formatar código
sail bin pint
```

---

# Considerações de Segurança

## Pontos Críticos
1. **Validação de Permissões:** Apenas Admin pode aprovar/rejeitar
2. **Auditoria Completa:** Todas as ações logadas com IP e User Agent
3. **Transações:** Operações de aprovação usam DB transactions
4. **Expiração:** Solicitações antigas devem expirar automaticamente
5. **Rate Limiting:** Limitar quantidade de solicitações por usuário/período

## Checklist de Segurança
- [ ] Middleware verifica role antes de aprovar
- [ ] Não é possível aprovar própria solicitação
- [ ] Logs não podem ser editados ou excluídos
- [ ] IP e User Agent sempre registrados
- [ ] Motivo obrigatório para rejeição

---

**Documento criado por:** Sistema de Desenvolvimento
**Data:** 2026-01-21
**Versão:** 1.0
