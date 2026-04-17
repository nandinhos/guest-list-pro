# E2E Testing Methodology - guest-list-pro

## 1. Overview

This document describes the End-to-End (E2E) testing methodology used for the guest-list-pro system, a Laravel 12 + Filament 4 guest management application.

## 2. Test Environment

### 2.1 Infrastructure
- **Application URL**: http://localhost:8888
- **Test Database**: Fresh MySQL database with seed data
- **Browser**: Chromium (Playwright Desktop Chrome)
- **Testing Framework**: Playwright Test

### 2.2 Test Users
| Role | Email | Password | Purpose |
|------|-------|----------|---------|
| Admin | admin@guestlist.pro | password | Full system access |
| Promoter | promoter@guestlist.pro | password | Guest registration |
| Validator | validador@guestlist.pro | password | Check-in operations |
| Bilheteria | bilheteria@guestlist.pro | password | Ticket sales |

### 2.3 Test Event
- **Name**: Festival Teste 2026
- **Date**: Tomorrow
- **Sectors**: Pista (500), VIP (100), Camarote (50), Backstage (20)
- **Ticket Types**: Pista Premium (R$ 150), VIP Experience (R$ 350), Camarote Open Bar (R$ 500), Backstage Pass (R$ 800)

## 3. Testing Strategy

### 3.1 Test Categories

#### Authentication Tests (TC-AUTH-*)
- Login page loads correctly
- Valid credentials login successfully
- Invalid credentials show error
- All user roles can authenticate
- Logout redirects to login

#### Admin Panel Tests (TC-ADMIN-*)
- Dashboard with widgets
- Event selection
- Guest management (list, search, view)
- Approval request management
- CRUD operations (events, sectors, users)

#### Promoter Panel Tests (TC-PROM-*)
- Dashboard with quota widget
- Guest list filtered by owner
- Guest creation form validation
- +1 companion functionality
- Quota limit warnings
- Search functionality

#### Validator Panel Tests (TC-VALID-*)
- Guest list with status badges
- Search by name/document
- Status filtering
- Emergency check-in request
- Dashboard stats

#### Bilheteria Panel Tests (TC-BILH-*)
- Dashboard with revenue stats
- Ticket sale creation
- Payment method selection
- Sales filtering
- Sector metrics
- Ticket type reports

### 3.2 Test Case Naming Convention
```
TC-{MODULE}-{SUBMODULE}-{NUMBER}: Description
```

Examples:
- `TC-AUTH-001`: First authentication test
- `TC-ADMIN-005`: Fifth admin panel test
- `TC-PROM-SEARCH-002`: Second promoter search test

## 4. Test Execution

### 4.1 Prerequisites
1. Docker containers running (Sail)
2. Database migrated and seeded
3. Application accessible at port 8888

### 4.2 Running Tests
```bash
# Run all E2E tests
vendor/bin/pail test

# Run specific test file
vendor/bin/pail test e2e/smoke-tests.spec.ts

# Run tests with UI
vendor/bin/pail test --headed

# Run specific test
vendor/bin/pail test --grep "TC-AUTH-001"
```

### 4.3 Report Generation
Reports are automatically generated in:
- HTML Report: `docs/report_e2e/results/html/index.html`
- JSON Results: `docs/report_e2e/results/test-results.json`

## 5. Test Coverage Matrix

| Module | Test Cases | Status |
|--------|-----------|--------|
| Authentication | 7 | ✅ |
| Admin Dashboard | 4 | ✅ |
| Admin Guests | 5 | ✅ |
| Admin Approvals | 3 | ✅ |
| Admin CRUD | 3 | ✅ |
| Promoter Dashboard | 2 | ✅ |
| Promoter Guests | 8 | ✅ |
| Promoter Search | 2 | ✅ |
| Promoter Quota | 2 | ✅ |
| Validator Dashboard | 2 | ✅ |
| Validator Search | 3 | ✅ |
| Validator Emergency | 3 | ✅ |
| Validator Filters | 3 | ✅ |
| Validator Stats | 2 | ✅ |
| Bilheteria Dashboard | 4 | ✅ |
| Bilhaterias Sales | 3 | ✅ |
| Bilheteria Form | 5 | ✅ |
| Bilheteria Payments | 4 | ✅ |
| Bilheteria Filters | 3 | ✅ |
| Bilheteria Reports | 2 | ✅ |
| **TOTAL** | **64** | |

## 6. Page Objects

### 6.1 Page Object Structure
```
e2e/
├── pages/
│   ├── LoginPage.ts
│   ├── AdminPages.ts
│   ├── PromoterPages.ts
│   ├── ValidatorPages.ts
│   └── BilheteriaPages.ts
├── flows/
└── components/
```

### 6.2 Page Object Pattern
Each page object encapsulates:
- Element locators
- Page actions (goto, click, fill, etc.)
- Assertions (expect*)
- Helper methods

## 7. Failure Handling

### 7.1 Screenshot Capture
On test failure, Playwright automatically captures:
- Screenshot of the failure point
- Trace of the execution
- Video recording (if configured)

### 7.2 Error Classification
- **BLOCKER**: Critical functionality broken
- **HIGH**: Important feature not working
- **MEDIUM**: Feature degraded
- **LOW**: Minor issue, cosmetic

## 8. Reporting

### 8.1 Report Structure
```
docs/report_e2e/
├── methodology/
│   └── METHODOLOGY.md (this file)
├── test-cases/
│   └── TEST_CASES.md
├── screenshots/
│   └── [failure screenshots]
└── results/
    ├── html/
    └── test-results.json
```

### 8.2 Report Contents
- Executive summary
- Test execution results
- Pass/fail statistics
- Screenshots of failures
- Error details
- Recommendations

## 9. Best Practices

### 9.1 Test Design
1. Each test is independent
2. Tests can run in any order
3. Use page objects for DRY code
4. Meaningful test names
5. Single assertion focus per test (when possible)

### 9.2 Locator Strategy
1. Prefer semantic locators (role, text)
2. Use data-testid when available
3. Avoid brittle selectors
4. Consider page structure changes

### 9.3 Wait Strategies
1. Use `waitForLoadState('networkidle')` for navigation
2. Use `waitForSelector()` for dynamic content
3. Avoid hard-coded sleep delays
4. Use `expect().toBeVisible()` with timeout

## 10. Maintenance

### 10.1 Updating Tests
When UI changes:
1. Update page objects first
2. Run affected tests
3. Update assertions if needed
4. Verify no regressions

### 10.2 Adding New Tests
1. Follow naming convention
2. Add to appropriate test file
3. Update test cases documentation
4. Run full suite to verify

## 11. Continuous Improvement

### 11.1 Metrics Tracked
- Test pass rate
- Flaky test rate
- Average test duration
- Coverage by module

### 11.2 Improvement Actions
- Review failed tests in each run
- Identify and fix flaky tests
- Add missing test coverage
- Refactor page objects as needed
