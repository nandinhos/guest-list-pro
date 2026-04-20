# Guest List Pro - Complete Codebase Documentation

## Table of Contents
1. [Enums](#1-enums)
2. [Models & Relationships](#2-models--relationships)
3. [Policies & Permissions](#3-policies--permissions)
4. [Rules for Validation](#4-rules-for-validation)
5. [Services](#5-services)
6. [Filament Panels](#6-filament-panels)
7. [Filament Resources](#7-filament-resources)
8. [Filament Widgets](#8-filament-widgets)
9. [Database Migrations](#9-database-migrations)

---

## 1. ENUMS

### UserRole
**File:** `app/Enums/UserRole.php`

| Value | Label | Color | Icon | Description |
|-------|-------|-------|------|-------------|
| `admin` | Administrador | danger (red) | shield-check | Full system access |
| `promoter` | Promoter | warning (yellow) | user-group | Can register guests within limits |
| `validator` | Validador | success (green) | check-badge | Can perform check-ins at events |
| `bilheteria` | Bilheteria | orange | ticket | Ticket sales management |

### EventStatus
**File:** `app/Enums/EventStatus.php`

| Value | Label | Color | Description |
|-------|-------|-------|-------------|
| `draft` | Rascunho | gray | Event being created, not visible |
| `active` | Ativo | success | Active and receiving registrations/check-ins |
| `finished` | Finalizado | info (blue) | Event ended |
| `cancelled` | Cancelado | danger | Event cancelled |

### DocumentType
**File:** `app/Enums/DocumentType.php`

| Value | Label | Mask | Placeholder |
|-------|-------|------|-------------|
| `cpf` | CPF | `###.###.###-##` | `000.000.000-00` |
| `rg` | RG | null (varies by state) | `Ex: 12.345.678-9` |
| `passport` | Passaporte | null (alphanumeric) | `Ex: AB123456` |

**Methods:**
- `getMask()`: Returns display mask
- `getPlaceholder()`: Returns input placeholder
- `detectFromValue(string)`: Auto-detects document type from value

### PaymentMethod
**File:** `app/Enums/PaymentMethod.php`

| Value | Label | Color | Icon |
|-------|-------|-------|------|
| `cash` | Dinheiro | success | banknotes |
| `credit_card` | Cartão de Crédito | warning | credit-card |
| `debit_card` | Cartão de Débito | info | credit-card |
| `pix` | PIX | primary | qr-code |

### RequestStatus
**File:** `app/Enums/RequestStatus.php`

| Value | Label | Color | Icon | Can Be Reviewed |
|-------|-------|-------|------|----------------|
| `pending` | Pendente | warning | clock | Yes |
| `approved` | Aprovado | success | check-circle | No |
| `rejected` | Rejeitado | danger | x-circle | No |
| `cancelled` | Cancelado | gray | minus-circle | No |
| `expired` | Expirado | gray | exclamation-circle | No |

**Methods:**
- `canBeReviewed()`: Returns true only for PENDING
- `canBeCancelled()`: Returns true only for PENDING
- `canBeReconsidered()`: Returns true for REJECTED, CANCELLED
- `canBeReverted()`: Returns true only for APPROVED

### RequestType
**File:** `app/Enums/RequestType.php`

| Value | Label | Color | Icon | Allowed Role | Description |
|-------|-------|-------|------|--------------|-------------|
| `guest_inclusion` | Inclusão de Convidado | primary | user-plus | PROMOTER | Request to add guest outside quota/deadline |
| `emergency_checkin` | Check-in Emergencial | warning | bolt | VALIDATOR | Request to check-in person not on list |

---

## 2. MODELS & RELATIONSHIPS

### User
**File:** `app/Models/User.php`

| Field | Type | Description |
|-------|------|-------------|
| `id` | bigint | Primary key |
| `name` | string | User's full name |
| `email` | string | Unique email |
| `password` | string | Hashed password |
| `role` | UserRole enum | User's role |
| `is_active` | boolean | Account active status |
| `email_verified_at` | datetime | Email verification timestamp |
| `created_at` | timestamp | Creation timestamp |
| `updated_at` | timestamp | Update timestamp |

**Relationships:**
- `permissions()`: HasMany -> PromoterPermission (for promoters)
- `eventAssignments()`: HasMany -> EventAssignment
- `guests()`: HasMany -> Guest (guests created by this promoter)
- `guestsValidated()`: HasMany -> Guest (guests checked in by this validator)

**Key Methods:**
- `canAccessPanel(Panel $panel)`: Checks if user can access specific Filament panel based on role
- `getAssignedEvents()`: Returns events assigned to user based on role

### Event
**File:** `app/Models/Event.php`

| Field | Type | Description |
|-------|------|-------------|
| `id` | bigint | Primary key |
| `name` | string | Event name |
| `banner_path` | string nullable | Local banner image path |
| `banner_url` | string nullable | External banner URL |
| `location` | string nullable | Event location |
| `date` | date | Event date |
| `start_time` | time | Event start time |
| `end_time` | time | Event end time |
| `status` | EventStatus enum | Current status |
| `ticket_price` | decimal(10,2) | Default ticket price |
| `bilheteria_enabled` | boolean | Whether ticket sales are enabled |
| `created_at` | timestamp | Creation timestamp |
| `updated_at` | timestamp | Update timestamp |

**Relationships:**
- `sectors()`: HasMany -> Sector
- `guests()`: HasMany -> Guest
- `permissions()`: HasMany -> PromoterPermission (alias)
- `assignments()`: HasMany -> EventAssignment
- `ticketSales()`: HasMany -> TicketSale

**Key Methods:**
- `bannerDisplayUrl()`: Returns URL for banner display (priority: external URL > local path > null)

### Guest
**File:** `app/Models/Guest.php`

| Field | Type | Description |
|-------|------|-------------|
| `id` | bigint | Primary key |
| `event_id` | bigint | Foreign key to Event |
| `sector_id` | bigint nullable | Foreign key to Sector |
| `promoter_id` | bigint | Foreign key to User (creator) |
| `parent_id` | bigint nullable | Self-reference for +1 companions |
| `name` | string | Guest's full name |
| `document` | string nullable | Document number |
| `document_type` | DocumentType enum | Type of document |
| `email` | string nullable | Guest email |
| `qr_token` | string nullable | Unique QR code token (ULID) |
| `is_checked_in` | boolean | Check-in status |
| `checked_in_at` | datetime nullable | Check-in timestamp |
| `checked_in_by` | bigint nullable | Foreign key to User (validator) |
| `name_normalized` | string nullable | Normalized name for searching |
| `document_normalized` | string nullable | Normalized document for searching |
| `created_at` | timestamp | Creation timestamp |
| `updated_at` | timestamp | Update timestamp |

**Relationships:**
- `event()`: BelongsTo -> Event
- `sector()`: BelongsTo -> Sector
- `promoter()`: BelongsTo -> User
- `validator()`: BelongsTo -> User (who performed check-in)
- `parent()`: BelongsTo -> Guest (the guest this +1 belongs to)
- `companions()`: BelongsToMany -> Guest (the +1 companions)

**Key Methods:**
- `isCompanion()`: Returns true if this guest is a +1

### Sector
**File:** `app/Models/Sector.php`

| Field | Type | Description |
|-------|------|-------------|
| `id` | bigint | Primary key |
| `event_id` | bigint | Foreign key to Event |
| `name` | string | Sector name |
| `capacity` | int nullable | Maximum capacity |
| `created_at` | timestamp | Creation timestamp |
| `updated_at` | timestamp | Update timestamp |

**Relationships:**
- `event()`: BelongsTo -> Event
- `guests()`: HasMany -> Guest
- `permissions()`: HasMany -> PromoterPermission
- `ticketSales()`: HasMany -> TicketSale

### EventAssignment (PromoterPermission)
**File:** `app/Models/EventAssignment.php`

> **Note:** `PromoterPermission` is an alias of `EventAssignment` using the same `event_assignments` table for backward compatibility.

| Field | Type | Description |
|-------|------|-------------|
| `id` | bigint | Primary key |
| `user_id` | bigint | Foreign key to User |
| `role` | string | User's role value |
| `event_id` | bigint | Foreign key to Event |
| `sector_id` | bigint nullable | Foreign key to Sector |
| `guest_limit` | int | Maximum guests allowed |
| `plus_one_enabled` | boolean | Whether +1 is allowed |
| `plus_one_limit` | int | Maximum +1 companions |
| `start_time` | datetime nullable | Registration window start |
| `end_time` | datetime nullable | Registration window end |
| `created_at` | timestamp | Creation timestamp |
| `updated_at` | timestamp | Update timestamp |

**Relationships:**
- `user()`: BelongsTo -> User
- `event()`: BelongsTo -> Event
- `sector()`: BelongsTo -> Sector

### ApprovalRequest
**File:** `app/Models/ApprovalRequest.php`

| Field | Type | Description |
|-------|------|-------------|
| `id` | bigint | Primary key |
| `event_id` | bigint | Foreign key to Event |
| `sector_id` | bigint nullable | Foreign key to Sector |
| `type` | RequestType enum | Type of request |
| `status` | RequestStatus enum | Current status |
| `requester_id` | bigint | Foreign key to User (creator) |
| `guest_name` | string | Name of guest to add |
| `guest_document` | string nullable | Document of guest |
| `guest_document_type` | DocumentType enum nullable | Document type |
| `guest_email` | string nullable | Email of guest |
| `guest_id` | bigint nullable | Foreign key to created Guest |
| `requester_notes` | text nullable | Notes from requester |
| `reviewer_id` | bigint nullable | Foreign key to User (admin reviewer) |
| `reviewed_at` | datetime nullable | Review timestamp |
| `reviewer_notes` | text nullable | Notes from reviewer |
| `ip_address` | string nullable | Requester's IP |
| `user_agent` | string nullable | Requester's user agent |
| `expires_at` | datetime nullable | Expiration timestamp |
| `created_at` | timestamp | Creation timestamp |
| `updated_at` | timestamp | Update timestamp |

**Relationships:**
- `event()`: BelongsTo -> Event
- `sector()`: BelongsTo -> Sector
- `requester()`: BelongsTo -> User
- `reviewer()`: BelongsTo -> User
- `guest()`: BelongsTo -> Guest

**Scopes:**
- `pending()`: Returns pending requests
- `forEvent(int $eventId)`: Filters by event
- `byType(RequestType $type)`: Filters by type
- `expired()`: Returns expired pending requests
- `byRequester(int $requesterId)`: Filters by requester

**Key Methods:**
- `isPending()`, `isApproved()`, `isRejected()`: Status checks
- `canBeReviewed()`, `canBeCancelled()`, `canBeReconsidered()`, `canBeReverted()`: Action checks
- `findExistingGuest()`: Finds guest with same document
- `hasExistingGuest()`, `existingGuestInSameSector()`: Duplicate checks

### TicketSale
**File:** `app/Models/TicketSale.php`

| Field | Type | Description |
|-------|------|-------------|
| `id` | bigint | Primary key |
| `event_id` | bigint | Foreign key to Event |
| `ticket_type_id` | bigint nullable | Foreign key to TicketType |
| `sector_id` | bigint nullable | Foreign key to Sector |
| `guest_id` | bigint nullable | Foreign key to created Guest |
| `sold_by` | bigint | Foreign key to User (seller) |
| `value` | decimal(10,2) | Sale value |
| `payment_method` | string | Payment method |
| `buyer_name` | string | Buyer's name |
| `buyer_document` | string nullable | Buyer's document |
| `notes` | text nullable | Additional notes |
| `created_at` | timestamp | Creation timestamp |
| `updated_at` | timestamp | Update timestamp |

**Relationships:**
- `event()`: BelongsTo -> Event
- `guest()`: BelongsTo -> Guest
- `seller()`: BelongsTo -> User
- `ticketType()`: BelongsTo -> TicketType
- `sector()`: BelongsTo -> Sector
- `paymentSplits()`: HasMany -> PaymentSplit

**Key Methods:**
- `getBuyerDocumentMaskedAttribute()`: Returns masked document (last 4 digits)
- `getBuyerNameMaskedAttribute()`: Returns name with masked initials

### TicketType
**File:** `app/Models/TicketType.php`

| Field | Type | Description |
|-------|------|-------------|
| `id` | bigint | Primary key |
| `event_id` | bigint | Foreign key to Event |
| `name` | string | Ticket type name |
| `description` | string nullable | Description |
| `price` | decimal(10,2) | Price |
| `is_active` | boolean | Whether active |
| `created_at` | timestamp | Creation timestamp |
| `updated_at` | timestamp | Update timestamp |

**Relationships:**
- `event()`: BelongsTo -> Event
- `ticketSales()`: HasMany -> TicketSale

**Scopes:**
- `active()`: Returns only active ticket types

### CheckinAttempt
**File:** `app/Models/CheckinAttempt.php`

| Field | Type | Description |
|-------|------|-------------|
| `id` | bigint | Primary key |
| `event_id` | bigint | Foreign key to Event |
| `validator_id` | bigint | Foreign key to User |
| `guest_id` | bigint nullable | Foreign key to Guest |
| `search_query` | string nullable | Search term used |
| `result` | string | Result (success, already_checked_in, error, estorno) |
| `ip_address` | string nullable | Validator's IP |
| `user_agent` | string nullable | Validator's user agent |
| `created_at` | timestamp | Creation timestamp |
| `updated_at` | timestamp | Update timestamp |

**Relationships:**
- `event()`: BelongsTo -> Event
- `validator()`: BelongsTo -> User
- `guest()`: BelongsTo -> Guest

### PaymentSplit
**File:** `app/Models/PaymentSplit.php`

| Field | Type | Description |
|-------|------|-------------|
| `id` | bigint | Primary key |
| `ticket_sale_id` | bigint | Foreign key to TicketSale |
| `payment_method` | PaymentMethod enum | Payment method used |
| `value` | decimal(10,2) | Split amount |
| `reference` | string nullable | Payment reference |
| `created_at` | timestamp | Creation timestamp |
| `updated_at` | timestamp | Update timestamp |

**Relationships:**
- `ticketSale()`: BelongsTo -> TicketSale

---

## 3. POLICIES & PERMISSIONS

### GuestPolicy
**File:** `app/Policies/GuestPolicy.php`

| Action | ADMIN | VALIDATOR | PROMOTER | BILHETERIA |
|--------|-------|-----------|----------|-------------|
| `viewAny` | Yes | Yes | Yes | No |
| `view` | Yes | Yes | Only own sector | No |
| `create` | Yes | No | Yes | No |
| `update` | Yes | No | Only own sector | No |
| `delete` | Yes | No | No | No |
| `restore` | Yes | No | No | No |
| `forceDelete` | Yes | No | No | No |

**Sector Access for Promoters:** Promoter can only access guests in sectors they have permission for.

### ApprovalRequestPolicy
**File:** `app/Policies/ApprovalRequestPolicy.php`

| Action | ADMIN | VALIDATOR | PROMOTER | BILHETERIA |
|--------|-------|-----------|----------|-------------|
| `viewAny` | Yes | Yes | Yes | Yes |
| `view` | Yes | Own only | Own only | No |
| `create` | Yes | Yes | Yes | No |
| `approve` | Yes (not own) | No | No | No |
| `reject` | Yes (not own) | No | No | No |
| `reconsider` | Yes | No | No | No |
| `revert` | Yes (not own) | No | No | No |
| `cancel` | No | Own only | Own only | No |
| `viewReport` | Yes | No | No | No |

### TicketSalePolicy
**File:** `app/Policies/TicketSalePolicy.php`

| Action | ADMIN | BILHETERIA | Others |
|--------|-------|-------------|--------|
| `viewAny` | Yes | Yes | No |
| `view` | Yes | Event match only | No |
| `create` | Yes | Yes | No |
| `update` | Yes | No | No |
| `delete` | Yes | No | No |

### TicketTypePolicy
**File:** `app/Policies/TicketTypePolicy.php`

| Action | ADMIN | BILHETERIA | Others |
|--------|-------|-------------|--------|
| `viewAny` | Yes | Yes | No |
| `view` | Yes | Event match only | No |
| `create` | Yes | No | No |
| `update` | Yes | No | No |
| `delete` | Yes | No | No |

---

## 4. RULES FOR VALIDATION

### CheckinRule
**File:** `app/Rules/CheckinRule.php`

**Purpose:** Validates check-in operations

**Validation Checks:**
1. User must be ADMIN or VALIDATOR role
2. Guest must exist by QR token
3. Guest must not already be checked in

**Methods:**
- `validate(string $attribute, mixed $qrToken, Closure $fail)`: Standard Laravel validation
- `passes(string $attribute, mixed $qrToken)`: Returns array with 'allowed', 'message', 'guest'
- `validateCheckin(User $validator, string $qrToken)`: Static validation
- `canCheckin(User $validator, Guest $guest)`: Check if specific guest can be checked in

### DocumentValidation
**File:** `app/Rules/DocumentValidation.php`

**Purpose:** Validates documents (CPF, RG, Passaporte)

**Types:**
- CPF: 11 digits with checksum validation
- RG: 5-14 characters
- Passport: 6-9 alphanumeric characters

**Factory Methods:**
- `DocumentValidation::cpf(bool $allowEmpty = false)`
- `DocumentValidation::rg(bool $allowEmpty = false)`
- `DocumentValidation::passport(bool $allowEmpty = false)`
- `DocumentValidation::any(bool $allowEmpty = false)`: Auto-detects type

### GuestLimitRule
**File:** `app/Rules/GuestLimitRule.php`

**Purpose:** Validates promoter guest registration limits

**Validation Checks:**
1. User must be PROMOTER and active
2. User must have permission for the event/sector
3. Current guest count must be below limit

**Methods:**
- `validate(string $attribute, mixed $value, Closure $fail)`
- `passes(string $attribute, mixed $value)`: Returns array with 'allowed', 'message', 'remaining'
- `validateLimit(User $user, int $eventId, int $sectorId)`: Static validation
- `getRemaining(User $user, int $eventId, int $sectorId)`: Returns remaining quota

### PlusOneRule
**File:** `app/Rules/PlusOneRule.php`

**Purpose:** Validates +1 (companion) registration

**Validation Checks:**
1. User must have permission for the sector
2. Plus_one must be enabled for the sector
3. Current companion count must be below limit

**Methods:**
- `validate(string $attribute, mixed $value, Closure $fail)`
- `passes(string $attribute, mixed $value)`: Returns array with 'allowed', 'message', 'remaining'
- `validatePlusOne(User $user, int $eventId, int $sectorId)`: Static validation
- `canAddCompanion(User $user, int $eventId, int $sectorId)`: Check if can add companion

### TimeWindowRule
**File:** `app/Rules/TimeWindowRule.php`

**Purpose:** Validates registration within allowed time window

**Validation Checks:**
1. User must have permission for the event/sector
2. Current time must be within start_time and end_time window

**Methods:**
- `validate(string $attribute, mixed $value, Closure $fail)`
- `passes(string $attribute, mixed $value)`: Returns array with 'allowed', 'message'
- `validateTimeWindow(int $userId, int $eventId, int $sectorId)`: Static validation
- `isWithinWindow(EventAssignment $permission)`: Check if currently within window

---

## 5. SERVICES

### GuestService
**File:** `app/Services/GuestService.php`

**Methods:**
- `checkinByQrToken(string $qrToken, User $validator)`: Performs check-in via QR token
- `canRegisterGuest(User $user, int $eventId, int $sectorId)`: Checks if promoter can register guests
- `getAuthorizedEvents(User $user)`: Returns events where user has permissions
- `getAuthorizedSectors(User $user, int $eventId)`: Returns authorized sectors for event

### GuestValidationService
**File:** `app/Services/GuestValidationService.php`

**Constructor:** `(User $user, int $eventId, int $sectorId)`

**Methods:**
- `for(User $user, int $eventId, int $sectorId)`: Static factory
- `canRegister()`: Checks guest limit and time window
- `canAddCompanion()`: Checks +1 rules
- `canCheckin(string $qrToken)`: Validates check-in
- `getPermission()`: Returns EventAssignment
- `getGuestCount()`: Current guest count
- `getCompanionCount()`: Current companion count
- `getSummary()`: Full quota summary array

### GuestSearchService
**File:** `app/Services/GuestSearchService.php`

**Methods:**
- `normalize(string $value)`: Removes accents, lowercases
- `normalizeDocument(string $value)`: Removes non-alphanumeric
- `applyNormalizedSearch(Builder $query, string $search)`: Exact match ignoring accents
- `applyFuzzySearch(Builder $query, string $search)`: Partial match for each term
- `searchByName(string $query, int $eventId, bool $fuzzy = false)`: Search guests by name
- `searchByDocument(string $query, int $eventId)`: Search by document
- `searchSimilar(string $query, int $eventId, float $threshold = 0.7)`: Find possible duplicates
- `findPossibleDuplicates(int $eventId)`: Find all duplicates in event
- `checkForDuplicatesBatch(int $eventId, array $guests)`: Batch duplicate check for imports
- `calculateSimilarity(string $str1, string $str2)`: Levenshtein-based similarity (0.0-1.0)

### DuplicateGuestValidator
**File:** `app/Services/DuplicateGuestValidator.php`

**Methods:**
- `check(int $eventId, string $name, ?string $document, ?int $excludeGuestId)`: Check for duplicates
  - Returns `null` if no duplicates
  - Returns array with `type` ('document'|'name'), `level` ('error'|'warning'), `message`, `existing`
  - Document match = ERROR (blocks), Name match = WARNING (alerts)
- `checkForRequest(ApprovalRequest $request, ?int $excludeRequestId)`: Check for existing request

### DocumentValidationService
**File:** `app/Services/DocumentValidationService.php`

**Methods:**
- `validate(string $value, ?DocumentType $type = null)`: Validates document
  - Auto-detects type if not provided
  - Returns array with `valid`, `type`, `message`, `formatted`
- `validateCpf(string $value)`: Full CPF checksum validation
- `validateRg(string $value)`: Basic RG format validation
- `validatePassport(string $value)`: Basic passport format validation
- `formatCpf(string $cpf)`: Formats CPF with mask
- `formatRg(string $rg)`: Cleans RG format
- `normalize(string $value)`: Normalizes for comparison
- `isValid(string $value, ?DocumentType $type = null)`: Quick validity check
- `getErrorMessage(string $value, ?DocumentType $type = null)`: Returns error or null

### ApprovalRequestService
**File:** `app/Services/ApprovalRequestService.php`

**Methods:**
- `checkForDuplicates(int $eventId, string $name, ?string $document, ?int $excludeGuestId)`: Check for duplicates
- `createGuestInclusionRequest(User $promoter, int $eventId, int $sectorId, array $guestData, ?string $notes)`: Create GUEST_INCLUSION request
- `createEmergencyCheckinRequest(User $validator, int $eventId, ?int $sectorId, array $guestData, ?string $notes)`: Create EMERGENCY_CHECKIN request
- `approve(ApprovalRequest $request, User $admin, ?string $notes)`: Approve request
  - GUEST_INCLUSION: Creates guest only
  - EMERGENCY_CHECKIN: Creates guest AND performs check-in
- `approveWithSectorUpdate(ApprovalRequest $request, User $admin, ?string $notes)`: Approve when guest exists in another sector
- `reject(ApprovalRequest $request, User $admin, string $reason)`: Reject request
- `cancel(ApprovalRequest $request, User $user)`: Cancel own pending request
- `reconsider(ApprovalRequest $request, User $admin, ?string $notes)`: Reopen rejected/cancelled request
- `revert(ApprovalRequest $request, User $admin, ?string $reason)`: Undo approval, delete created guest
- `expireRequestsForFinishedEvents()`: Mark pending requests as expired for finished events
- `expireOldRequests(int $hoursOld = 24)`: Mark old pending requests as expired
- `getPendingCount(?int $eventId = null)`: Count pending requests
- `getPendingForEvent(int $eventId)`: Get pending for event
- `getRequestsByUser(int $userId, ?int $eventId = null)`: Get user's requests
- `canReview(User $user)`: Check if user can review requests
- `hasPendingSimilarRequest(int $eventId, string $guestDocument, ?int $excludeRequestId)`: Check for pending duplicate

### AuthenticationService
**File:** `app/Services/AuthenticationService.php`

**Methods:**
- `authenticate(string $email, string $password, bool $remember = false)`: Authenticate user
  - Validates credentials
  - Checks if account is active
  - Returns redirect URL based on role
- `getPanelUrl(UserRole $role)`: Get panel URL for role

**Panel Routes:**
- admin -> /admin
- promoter -> /promoter
- validator -> /validator
- bilheteria -> /bilheteria

### FormatService
**File:** `app/Services/FormatService.php`

**Static Methods:**
- `money(float $value)`: Format as BRL currency
- `date(Carbon|string|null $date)`: Format as d/m/Y
- `datetime(Carbon|string|null $date)`: Format as d/m/Y H:i
- `number(float $value, int $decimals = 2)`: Format number with Brazilian locale
- `percent(float $value)`: Format as percentage

---

## 6. FILAMENT PANELS

### Admin Panel
**Provider:** `app/Providers/Filament/AdminPanelProvider.php`

| Setting | Value |
|---------|-------|
| ID | `admin` |
| Path | `/admin` |
| Brand | `Guest List Pro` |
| Color | Indigo |
| Theme Mode | Dark |
| Auth Required | Yes |

**Resources Discovered:** All in `app/Filament/Resources/`

**Widgets:**
- SalesTimelineChart
- AdminOverview
- PendingApprovalsWidget
- ApprovalMetricsChart
- GuestsVsTicketsChart
- SectorMetricsTable
- TicketTypeReportTable

### Promoter Panel
**Provider:** `app/Providers/Filament/PromoterPanelProvider.php`

| Setting | Value |
|---------|-------|
| ID | `promoter` |
| Path | `/promoter` |
| Brand | `Portal do Promoter` |
| Color | Purple |
| Theme Mode | Dark |
| Auth Required | Yes |
| Middleware | EnsureEventSelected |

**Resources Discovered:** All in `app/Filament/Promoter/Resources/`

**Features:**
- Requires event selection before access
- Change event button in header
- User menu hidden

### Validator Panel
**Provider:** `app/Providers/Filament/ValidatorPanelProvider.php`

| Setting | Value |
|---------|-------|
| ID | `validator` |
| Path | `/validator` |
| Brand | `Portal do Validador` |
| Color | Emerald |
| Theme Mode | Dark |
| Auth Required | Yes |
| Middleware | EnsureEventSelected |
| Notifications | Database notifications (30s polling) |

**Resources Discovered:** All in `app/Filament/Validator/Resources/`

**Features:**
- Requires event selection before access
- Change event button in header
- QR Code scanner library included (html5-qrcode)
- Database notifications for approval requests

### Bilheteria Panel
**Provider:** `app/Providers/Filament/BilheteriaPanelProvider.php`

| Setting | Value |
|---------|-------|
| ID | `bilheteria` |
| Path | `/bilheteria` |
| Brand | `Portal da Bilheteria` |
| Color | Orange |
| Theme Mode | Dark |
| Auth Required | Yes |
| Middleware | EnsureEventSelected, ThrottleRequests:bilheteria |

**Resources Discovered:** All in `app/Filament/Bilheteria/Resources/`

**Features:**
- Requires event selection before access
- Change event button in header
- Rate limiting for bilheteria endpoint

---

## 7. FILAMENT RESOURCES

### Admin Resources

#### GuestResource
**Location:** `app/Filament/Resources/Guests/GuestResource.php`

**Model:** `App\Models\Guest`

**Pages:**
- `index` -> ListGuests (`/`)
- `create` -> CreateGuest (`/create`)
- `edit` -> EditGuest (`/{record}/edit`)
- `import` -> ImportGuests (`/import`)

**Actions:**
- Check-in (button)
- Undo Check-in (button)
- Download QR Code
- Edit
- Bulk: Delete, Export CSV

**Filters:**
- Event
- Sector
- Promoter
- Status (Checked-in / Pending)

#### EventResource
**Location:** `app/Filament/Resources/Events/EventResource.php`

**Model:** `App\Models\Event`

**Pages:**
- `index` -> ListEvents (`/`)
- `create` -> CreateEvent (`/create`)
- `edit` -> EditEvent (`/{record}/edit`)

**Display:** Card-based grid layout

#### ApprovalRequestResource
**Location:** `app/Filament/Resources/ApprovalRequests/ApprovalRequestResource.php`

**Model:** `App\Models\ApprovalRequest`

**Pages:**
- `index` -> ListApprovalRequests (`/`)
- `report` -> RequestsReport (`/report`)

**Features:**
- Navigation badge with pending count
- Cannot create, edit, or delete (read-only management via actions)

**Actions (per record):**
- View Details (modal)
- Approve (with optional notes)
- Approve with Sector Update (when guest exists in another sector)
- Reject (requires reason)
- Reconsider (for rejected/cancelled)
- Revert (for approved, deletes created guest)

**Bulk Actions:**
- Approve Selected
- Reject Selected (with reason)
- Reconsider Selected
- Revert Selected (with reason)

**Filters:**
- Status
- Type (GUEST_INCLUSION / EMERGENCY_CHECKIN)
- Event
- Requester

#### UserResource
**Location:** `app/Filament/Resources/Users/UserResource.php`

**Model:** `App\Models\User`

**Pages:**
- `index` -> ListUsers (`/`)
- `create` -> CreateUser (`/create`)
- `edit` -> EditUser (`/{record}/edit`)

#### SectorResource
**Location:** `app/Filament/Resources/Sectors/SectorResource.php`

**Model:** `App\Models\Sector`

**Pages:**
- `index` -> ListSectors (`/`)
- `create` -> CreateSector (`/create`)
- `edit` -> EditSector (`/{record}/edit`)

#### PromoterPermissionResource
**Location:** `app/Filament/Resources/PromoterPermissions/PromoterPermissionResource.php`

**Model:** `App\Models\PromoterPermission` (EventAssignment)

**Pages:**
- `index` -> ListPromoterPermissions (`/`)
- `create` -> CreatePromoterPermission (`/create`)
- `edit` -> EditPromoterPermission (`/{record}/edit`)

**Purpose:** Manage user assignments to events with quotas and time windows

#### AuditResource
**Location:** `app/Filament/Resources/AuditResource.php`

**Model:** `Spatie\Activitylog\Models\Activity`

**Pages:**
- `index` -> ListAudits (`/`)

**Features:**
- Read-only
- Filters by subject_type, event, date range
- Custom mobile card view
- Modal for viewing details

---

### Promoter Resources

#### GuestResource (Promoter)
**Location:** `app/Filament/Promoter/Resources/Guests/GuestResource.php`

**Model:** `App\Models\Guest`

**Query Scope:** Shows only guests created by the authenticated promoter for selected event

**Pages:**
- `index` -> ListGuests (`/`)
- `create` -> CreateGuest (`/create`)
- `edit` -> EditGuest (`/{record}/edit`)
- `import` -> ImportGuests (`/import`)

**Form Features:**
- Event selection (from promoter's assigned events)
- Sector selection (filtered by event permission)
- Quota info display (remaining guests/+1)

**Actions:**
- Download QR Code
- Edit
- Bulk: Delete

**Filters:**
- Sector
- Status (Confirmed/Pending)
- Possible Duplicates

#### MyRequests Page
**Location:** `app/Filament/Promoter/Pages/MyRequests.php`

**Purpose:** View promoter's own approval requests

---

### Validator Resources

#### GuestResource (Validator)
**Location:** `app/Filament/Validator/Resources/Guests/GuestResource.php`

**Model:** `App\Models\Guest`

**Query Scope:** Shows all guests for selected event (no promoter filter)

**Pages:**
- `index` -> ListGuests (`/`)

**Note:** Cannot create or edit guests

**Actions:**
- Check-in (ENTRADA button with confirmation)
- Undo Check-in (ESTORNAR button)
- Emergency Check-in Request ("Não está na lista" button)

**Emergency Check-in Flow:**
1. Validator fills guest data
2. System checks for duplicates
3. If document duplicate -> blocked
4. If name duplicate -> warning, continues
5. Creates EMERGENCY_CHECKIN ApprovalRequest
6. Notifies admins
7. Admin approves -> guest created + auto check-in
8. Admin rejects -> notification sent

**Filters:**
- Sector
- Promoter
- Status (Confirmed/Pending)
- Possible Duplicates

**Header Action:**
- Emergency Check-in Request (creates ApprovalRequest)

#### MyRequests Page (Validator)
**Location:** `app/Filament/Validator/Pages/MyRequests.php`

**Purpose:** View validator's own emergency check-in requests

---

### Bilheteria Resources

#### TicketSaleResource
**Location:** `app/Filament/Bilheteria/Resources/TicketSales/TicketSaleResource.php`

**Model:** `App\Models\TicketSale`

**Query Scope:** Shows only sales for selected event

**Pages:**
- `index` -> ListTicketSales (`/`)
- `create` -> CreateTicketSale (`/create`)

**Note:** Cannot edit or delete sales

**Form Sections:**
1. Buyer Data (name, document type, document)
2. Ticket Data (type, sector, price display)
3. Payment (method, value)
4. Notes

**Filters:**
- Ticket Type
- Sector
- Payment Method
- Seller
- Date Range

---

## 8. FILAMENT WIDGETS

### Admin Panel Widgets

#### AdminOverview
**Location:** `app/Filament/Widgets/AdminOverview.php`
**Type:** StatsOverviewWidget
**Polling:** 30 seconds

**With Event Selected:**
- Convidados Presentes: `checked/total` with presence rate chart
- Vendas Bilheteria: count with revenue and sales trend chart
- Total de Entradas: guests + ticket sales combined
- Solicitações Pendentes: count with link to approval page

**Without Event Selected (Global):**
- Solicitações Pendentes: total pending
- Total de Eventos: event count
- Total de Convidados: with presence rate
- Receita Total: all ticket sales sum

#### PendingApprovalsWidget
**Location:** `app/Filament/Widgets/PendingApprovalsWidget.php`
**Type:** StatsOverviewWidget

**Stats:**
- Total Pendentes: count with link to approvals
- Inclusões (Promoters): GUEST_INCLUSION count
- Check-ins (Validadores): EMERGENCY_CHECKIN count

#### ApprovalMetricsChart
**Location:** `app/Filament/Widgets/ApprovalMetricsChart.php`

#### GuestsVsTicketsChart
**Location:** `app/Filament/Widgets/GuestsVsTicketsChart.php`

#### SectorMetricsTable
**Location:** `app/Filament/Widgets/SectorMetricsTable.php`

#### TicketTypeReportTable
**Location:** `app/Filament/Widgets/TicketTypeReportTable.php`

#### SalesTimelineChart
**Location:** `app/Filament/Widgets/SalesTimelineChart.php`

---

### Promoter Panel Widgets

#### PromoterQuotaOverview
**Location:** `app/Filament/Promoter/Widgets/PromoterQuotaOverview.php`

**Displays:** Guest quota usage for selected event

#### PendingRequestsTableWidget
**Location:** `app/Filament/Promoter/Widgets/PendingRequestsTableWidget.php`

**Displays:** Promoter's pending approval requests

---

### Validator Panel Widgets

#### ValidatorOverview
**Location:** `app/Filament/Validator/Widgets/ValidatorOverview.php`
**Type:** StatsOverviewWidget
**Polling:** 30 seconds

**Stats:**
- Check-ins Realizados: confirmed count
- Total na Lista: total guests
- Minhas Solicitações: user's pending requests

#### PendingRequestsWidget (Validator)
**Location:** `app/Filament/Validator/Widgets/PendingRequestsWidget.php`

**Displays:** Validator's emergency check-in requests

---

### Bilheteria Panel Widgets

#### BilheteriaOverview
**Location:** `app/Filament/Bilheteria/Widgets/BilheteriaOverview.php`
**Type:** StatsOverviewWidget

**Stats:**
- Total de Vendas: count with today sales and revenue
- Receita Total: total with average ticket value
- Vendas Hoje: today's count and revenue
- Tipos Ativos: active ticket types with payment method breakdown

---

## 9. DATABASE MIGRATIONS

### Core Tables

#### users (0001_01_01_000000_create_users_table.php)
```sql
- id (bigint, PK)
- name
- email
- password
- remember_token
- created_at
- updated_at
```

#### events (2026_01_17_050437_create_events_table.php)
```sql
- id (bigint, PK)
- name
- date
- start_time
- end_time
- created_at
- updated_at
```

#### sectors (2026_01_17_050438_create_sectors_table.php)
```sql
- id (bigint, PK)
- event_id (bigint, FK)
- name
- capacity
- created_at
- updated_at
```

#### event_assignments (originally promoter_permissions) (2026_01_17_050439_create_promoter_permissions_table.php, renamed 2026_01_19_042915)
```sql
- id (bigint, PK)
- user_id (bigint, FK)
- role (string)
- event_id (bigint, FK)
- sector_id (bigint, FK, nullable)
- guest_limit
- start_time (datetime, nullable)
- end_time (datetime, nullable)
- created_at
- updated_at
```

#### plus_one_fields (2026_04_17_125137_add_plus_one_fields_to_event_assignments_table.php)
```sql
- plus_one_enabled (boolean)
- plus_one_limit (integer)
```

#### guests (2026_01_17_050440_create_guests_table.php)
```sql
- id (bigint, PK)
- event_id (bigint, FK)
- sector_id (bigint, FK, nullable)
- promoter_id (bigint, FK)
- name
- document
- email
- is_checked_in
- checked_in_at
- checked_in_by
- created_at
- updated_at
```

#### parent_id for companions (2026_04_17_125034_add_parent_id_to_guests_table.php)
```sql
- parent_id (bigint, FK, nullable, self-reference)
```

#### normalized_columns (2026_01_20_033317_add_normalized_columns_to_guests_table.php)
```sql
- name_normalized
- document_normalized
```

#### qr_token (2026_02_20_233059_add_qr_token_to_guests_table.php)
```sql
- qr_token (string, nullable, unique)
```

#### document_type (2026_01_20_141434_add_document_type_to_guests_table.php)
```sql
- document_type (enum: cpf, rg, passport)
```

#### ticket_sales (2026_01_20_035954_create_ticket_sales_table.php)
```sql
- id (bigint, PK)
- event_id (bigint, FK)
- sold_by (bigint, FK)
- value
- payment_method
- buyer_name
- buyer_document
- notes
- created_at
- updated_at
```

#### ticket_types (2026_04_17_130847_create_ticket_types_table.php)
```sql
- id (bigint, PK)
- event_id (bigint, FK)
- name
- description
- price
- is_active
- created_at
- updated_at
```

#### ticket_type_id on ticket_sales (implied relationship)
```sql
- ticket_type_id (bigint, FK, nullable)
- sector_id (bigint, FK, nullable)
- guest_id (bigint, FK, nullable)
```

#### payment_splits (2026_04_17_131507_create_payment_splits_table.php)
```sql
- id (bigint, PK)
- ticket_sale_id (bigint, FK)
- payment_method
- value
- reference
- created_at
- updated_at
```

#### approval_requests (2026_01_21_025445_create_approval_requests_table.php)
```sql
- id (bigint, PK)
- event_id (bigint, FK)
- sector_id (bigint, FK, nullable)
- type (enum: guest_inclusion, emergency_checkin)
- status (enum: pending, approved, rejected, cancelled, expired)
- requester_id (bigint, FK)
- guest_name
- guest_document
- guest_document_type
- guest_email
- guest_id (bigint, FK, nullable)
- requester_notes
- reviewer_id (bigint, FK, nullable)
- reviewed_at
- reviewer_notes
- ip_address
- user_agent
- expires_at
- created_at
- updated_at
```

#### checkin_attempts (2026_01_20_043724_create_checkin_attempts_table.php)
```sql
- id (bigint, PK)
- event_id (bigint, FK)
- validator_id (bigint, FK)
- guest_id (bigint, FK, nullable)
- search_query
- result (string)
- ip_address
- user_agent
- created_at
- updated_at
```

#### activity_log (2026_01_20_035906_create_activity_log_table.php)
```sql
- id (bigint, PK)
- log_name
- description
- subject_type
- subject_id
- causer_type
- causer_id
- properties (json)
- created_at
- updated_at
```

#### Additional activity_log columns:
```sql
- event_id (2026_01_20_035907)
- batch_uuid (2026_01_20_035908)
```

#### role_to_users (2026_01_17_050436_add_role_to_users_table.php)
```sql
- role (enum: admin, promoter, validator, bilheteria)
```

#### Additional event columns:
```sql
- banner_path
- banner_url
- location (2026_01_19_042845)
- ticket_price (2026_01_20_035937)
- bilheteria_enabled (implied)
```

#### notifications (2026_01_21_123733_create_notifications_table.php)
- Laravel default notifications table

#### performance_indexes (2026_01_20_040232_add_performance_indexes_to_guests_table.php, 2026_01_27_050433_add_missing_performance_indexes.php)
- Indexes on: event_id, sector_id, promoter_id, is_checked_in, name_normalized, document_normalized

---

## RELATIONSHIP SUMMARY

```
User
├── permissions() -> PromoterPermission/EventAssignment (1:N)
├── eventAssignments() -> EventAssignment (1:N)
├── guests() -> Guest (1:N, as promoter)
└── guestsValidated() -> Guest (1:N, as validator)

Event
├── sectors() -> Sector (1:N)
├── guests() -> Guest (1:N)
├── assignments() -> EventAssignment (1:N)
├── permissions() -> PromoterPermission (1:N, alias)
└── ticketSales() -> TicketSale (1:N)

Sector
├── event() -> Event (N:1)
├── guests() -> Guest (1:N)
├── permissions() -> PromoterPermission (1:N)
└── ticketSales() -> TicketSale (1:N)

Guest
├── event() -> Event (N:1)
├── sector() -> Sector (N:1)
├── promoter() -> User (N:1)
├── validator() -> User (N:1, checked_in_by)
├── parent() -> Guest (N:1, parent_id for +1)
├── companions() -> Guest (1:N, the +1s)
└── ticketSale() -> TicketSale (1:1, if purchased)

TicketSale
├── event() -> Event (N:1)
├── seller() -> User (N:1)
├── guest() -> Guest (N:1, optional)
├── ticketType() -> TicketType (N:1)
├── sector() -> Sector (N:1)
└── paymentSplits() -> PaymentSplit (1:N)

TicketType
├── event() -> Event (N:1)
└── ticketSales() -> TicketSale (1:N)

ApprovalRequest
├── event() -> Event (N:1)
├── sector() -> Sector (N:1)
├── requester() -> User (N:1)
├── reviewer() -> User (N:1)
└── guest() -> Guest (N:1, after approval)

CheckinAttempt
├── event() -> Event (N:1)
├── validator() -> User (N:1)
└── guest() -> Guest (N:1)

EventAssignment
├── user() -> User (N:1)
├── event() -> Event (N:1)
└── sector() -> Sector (N:1, optional)
```

---

## WORKFLOW DESCRIPTIONS

### Guest Registration Flow (Promoter)
1. Promoter logs in to Promoter Panel
2. Selects event from dropdown
3. Creates guest manually OR imports via file/text
4. System validates:
   - Guest limit (GuestLimitRule)
   - Time window (TimeWindowRule)
   - Duplicate document (error) or name (warning)
5. Guest created with QR token generated

### Check-in Flow (Validator)
1. Validator logs in to Validator Panel
2. Selects event from dropdown
3. Views guest list OR scans QR code
4. Clicks "ENTRADA" button on guest
5. System validates (CheckinRule):
   - User has validator permission
   - Guest exists
   - Guest not already checked in
6. Check-in recorded with timestamp and validator

### Emergency Check-in Flow (Validator)
1. Guest not found in list
2. Validator clicks "Não está na lista"
3. Fills guest data and selects sector
4. System checks duplicates:
   - Document duplicate = BLOCKED
   - Name duplicate = WARNING (continues anyway)
5. Creates EMERGENCY_CHECKIN ApprovalRequest
6. Admins notified via database notification
7. Admin approves:
   - Guest created
   - Automatic check-in performed
   - Request marked approved
8. Admin rejects:
   - Request marked rejected
   - Validator notified

### Approval Request Flow (Admin)
1. Admin sees pending count in navigation badge
2. Reviews request details
3. Can approve, reject, reconsider, or revert
4. Approve creates guest (and may perform check-in for emergency)
5. Reject requires reason
6. Revert deletes created guest and resets to pending

### Ticket Sale Flow (Bilheteria)
1. Bilheteria user selects event
2. Clicks "Nova Venda"
3. Fills buyer data and selects ticket type/sector
4. Selects payment method and confirms value
5. System creates TicketSale
6. Can optionally create Guest for will-call

---

## QUOTA SYSTEM

### Promoter Quotas
Each promoter has per-event-per-sector quotas:
- **guest_limit**: Maximum guests they can register
- **plus_one_enabled**: Whether they can add companions
- **plus_one_limit**: Maximum companions allowed
- **start_time/end_time**: Registration time window

### Quota Display (Promoter Panel)
Shows remaining slots:
```
"X convite(s) disponível(eis) + Y acompanhante(s)"
```

### Quota Enforcement
- **GuestLimitRule**: Checks before creating guest
- **PlusOneRule**: Checks before creating companion
- **TimeWindowRule**: Checks registration time window
- Approvals bypass quota system (admin override)

---

## DUPLICATE DETECTION

### Document Match = ERROR (Blocks)
- Same document number = immediate block
- Prevents double-registration
- Used for both guests and pending requests

### Name Match = WARNING (Alert)
- Similar name triggers warning
- Allows registration with confirmation
- Helps catch homonyms

### Detection Service
- `DuplicateGuestValidator::check()`: Single guest check
- `GuestSearchService::findPossibleDuplicates()`: Event-wide scan
- `GuestSearchService::checkForDuplicatesBatch()`: Import optimization

---

This documentation was generated by analyzing the codebase and should be kept updated when making changes to the system.
