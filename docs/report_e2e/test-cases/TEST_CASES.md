# E2E Test Cases - guest-list-pro

## Total: 64 Test Cases

---

## 🔐 Authentication Tests (TC-AUTH)

| ID | Test Case | Priority | Status |
|----|-----------|----------|--------|
| TC-AUTH-001 | Login page loads correctly | HIGH | ✅ |
| TC-AUTH-002 | Admin can login successfully | HIGH | ✅ |
| TC-AUTH-003 | Invalid credentials show error | HIGH | ✅ |
| TC-AUTH-004 | Promoter can login successfully | HIGH | ✅ |
| TC-AUTH-005 | Validator can login successfully | HIGH | ✅ |
| TC-AUTH-006 | Bilheteria can login successfully | HIGH | ✅ |
| TC-AUTH-007 | Logout redirects to login | MEDIUM | ✅ |

---

## 👨‍💼 Admin Panel Tests (TC-ADMIN)

### Dashboard (TC-ADMIN)
| ID | Test Case | Priority | Status |
|----|-----------|----------|--------|
| TC-ADMIN-001 | Dashboard loads with widgets | HIGH | ✅ |
| TC-ADMIN-002 | Can select event from dropdown | HIGH | ✅ |
| TC-ADMIN-003 | Guests page loads and shows table | HIGH | ✅ |
| TC-ADMIN-004 | Can search for a specific guest | HIGH | ✅ |
| TC-ADMIN-005 | Events page loads correctly | HIGH | ✅ |
| TC-ADMIN-006 | Approval requests page loads | HIGH | ✅ |
| TC-ADMIN-007 | Can view guest details | MEDIUM | ✅ |

### Widgets (TC-WIDGET)
| ID | Test Case | Priority | Status |
|----|-----------|----------|--------|
| TC-WIDGET-001 | Sales Timeline Chart is visible | MEDIUM | ✅ |
| TC-WIDGET-002 | Sector Metrics Table shows data | MEDIUM | ✅ |
| TC-WIDGET-003 | Ticket Type Report Table is visible | MEDIUM | ✅ |
| TC-WIDGET-004 | Admin Overview stats are displayed | MEDIUM | ✅ |

### Guests (TC-GUEST)
| ID | Test Case | Priority | Status |
|----|-----------|----------|--------|
| TC-GUEST-001 | Can view all guests in table | HIGH | ✅ |
| TC-GUEST-002 | Guest search returns correct results | HIGH | ✅ |
| TC-GUEST-003 | Filter by event works | HIGH | ✅ |
| TC-GUEST-004 | Can view checked-in guests | HIGH | ✅ |
| TC-GUEST-005 | Direct check-in button works | HIGH | ✅ |

### Approvals (TC-APPROVAL)
| ID | Test Case | Priority | Status |
|----|-----------|----------|--------|
| TC-APPROVAL-001 | Approval requests page loads | HIGH | ✅ |
| TC-APPROVAL-002 | Can view pending approvals | HIGH | ✅ |
| TC-APPROVAL-003 | Can approve a request | HIGH | ✅ |

### CRUD Operations (TC-CRUD)
| ID | Test Case | Priority | Status |
|----|-----------|----------|--------|
| TC-CRUD-001 | Can navigate to events management | MEDIUM | ✅ |
| TC-CRUD-002 | Can navigate to sectors management | MEDIUM | ✅ |
| TC-CRUD-003 | Can navigate to users management | MEDIUM | ✅ |

---

## 👤 Promoter Panel Tests (TC-PROM)

### Dashboard (TC-PROM)
| ID | Test Case | Priority | Status |
|----|-----------|----------|--------|
| TC-PROM-001 | Promoter dashboard loads correctly | HIGH | ✅ |
| TC-PROM-002 | Quota overview shows correct information | HIGH | ✅ |
| TC-PROM-003 | Guest list shows only promoter guests | HIGH | ✅ |
| TC-PROM-004 | Can open create guest form | HIGH | ✅ |
| TC-PROM-005 | Guest form has all required fields | HIGH | ✅ |
| TC-PROM-006 | Can fill guest form with valid data | HIGH | ✅ |
| TC-PROM-007 | Form validation shows errors for empty fields | HIGH | ✅ |
| TC-PROM-008 | +1 companion toggle is visible | MEDIUM | ✅ |

### Search (TC-PROM-SEARCH)
| ID | Test Case | Priority | Status |
|----|-----------|----------|--------|
| TC-PROM-SEARCH-001 | Search by name works | HIGH | ✅ |
| TC-PROM-SEARCH-002 | Search by document works | HIGH | ✅ |

### Quota (TC-PROM-QUOTA)
| ID | Test Case | Priority | Status |
|----|-----------|----------|--------|
| TC-PROM-QUOTA-001 | Quota display shows used/total | HIGH | ✅ |
| TC-PROM-QUOTA-002 | Warning when approaching quota limit | HIGH | ✅ |

---

## ✅ Validator Panel Tests (TC-VALID)

### Dashboard (TC-VALID)
| ID | Test Case | Priority | Status |
|----|-----------|----------|--------|
| TC-VALID-001 | Validator dashboard loads correctly | HIGH | ✅ |
| TC-VALID-002 | Guest list table is visible | HIGH | ✅ |
| TC-VALID-003 | Search input is available | HIGH | ✅ |
| TC-VALID-004 | Can search by guest name | HIGH | ✅ |
| TC-VALID-005 | Status badges are displayed | MEDIUM | ✅ |

### Search (TC-VALID-SEARCH)
| ID | Test Case | Priority | Status |
|----|-----------|----------|--------|
| TC-VALID-SEARCH-001 | Search by partial name | HIGH | ✅ |
| TC-VALID-SEARCH-002 | Search by document number | HIGH | ✅ |
| TC-VALID-SEARCH-003 | Clear search shows all guests | HIGH | ✅ |

### Emergency (TC-VALID-EMERGENCY)
| ID | Test Case | Priority | Status |
|----|-----------|----------|--------|
| TC-VALID-EMERGENCY-001 | Emergency button is visible | HIGH | ✅ |
| TC-VALID-EMERGENCY-002 | Can open emergency modal | HIGH | ✅ |
| TC-VALID-EMERGENCY-003 | Emergency form has required fields | HIGH | ✅ |

### Filters (TC-VALID-FILTER)
| ID | Test Case | Priority | Status |
|----|-----------|----------|--------|
| TC-VALID-FILTER-001 | Status filter dropdown is available | MEDIUM | ✅ |
| TC-VALID-FILTER-002 | Can filter checked-in guests | MEDIUM | ✅ |
| TC-VALID-FILTER-003 | Can filter pending guests | MEDIUM | ✅ |

### Stats (TC-VALID-STATS)
| ID | Test Case | Priority | Status |
|----|-----------|----------|--------|
| TC-VALID-STATS-001 | Dashboard shows stats widgets | MEDIUM | ✅ |
| TC-VALID-STATS-002 | Check-in count is displayed | MEDIUM | ✅ |

---

## 🎫 Bilheteria Panel Tests (TC-BILH)

### Dashboard (TC-BILH)
| ID | Test Case | Priority | Status |
|----|-----------|----------|--------|
| TC-BILH-001 | Bilheteria dashboard loads correctly | HIGH | ✅ |
| TC-BILH-002 | Stats widgets are displayed | HIGH | ✅ |
| TC-BILH-003 | Revenue display shows formatted values | MEDIUM | ✅ |
| TC-BILH-004 | Today's sales count is shown | MEDIUM | ✅ |

### Sales (TC-BILH-SALES)
| ID | Test Case | Priority | Status |
|----|-----------|----------|--------|
| TC-BILH-SALES-001 | Sales table is visible | HIGH | ✅ |
| TC-BILH-SALES-002 | Sales data is displayed | HIGH | ✅ |
| TC-BILH-SALES-003 | Can access create sale form | HIGH | ✅ |

### Form (TC-BILH-FORM)
| ID | Test Case | Priority | Status |
|----|-----------|----------|--------|
| TC-BILH-FORM-001 | Sale form has all required fields | HIGH | ✅ |
| TC-BILH-FORM-002 | Ticket types are available in dropdown | HIGH | ✅ |
| TC-BILH-FORM-003 | Sectors are available in dropdown | HIGH | ✅ |
| TC-BILH-FORM-004 | Payment methods are available | HIGH | ✅ |
| TC-BILH-FORM-005 | Can fill complete sale form | HIGH | ✅ |

### Payments (TC-BILH-PAY)
| ID | Test Case | Priority | Status |
|----|-----------|----------|--------|
| TC-BILH-PAY-001 | Can select PIX payment | HIGH | ✅ |
| TC-BILH-PAY-002 | Can select Cash payment | HIGH | ✅ |
| TC-BILH-PAY-003 | Can select Credit Card payment | HIGH | ✅ |
| TC-BILH-PAY-004 | Can select Debit Card payment | HIGH | ✅ |

### Filters (TC-BILH-FILTER)
| ID | Test Case | Priority | Status |
|----|-----------|----------|--------|
| TC-BILH-FILTER-001 | Filter dropdowns are available | MEDIUM | ✅ |
| TC-BILH-FILTER-002 | Can filter by ticket type | MEDIUM | ✅ |
| TC-BILH-FILTER-003 | Can filter by payment method | MEDIUM | ✅ |

### Reports (TC-BILH-REPORT)
| ID | Test Case | Priority | Status |
|----|-----------|----------|--------|
| TC-BILH-REPORT-001 | Sector metrics table is visible | MEDIUM | ✅ |
| TC-BILH-REPORT-002 | Ticket type report is visible | MEDIUM | ✅ |

---

## Test Priority Legend

| Priority | Description |
|----------|-------------|
| HIGH | Critical path, must pass |
| MEDIUM | Important feature, should pass |
| LOW | Nice to have, can fail |

---

## Test Status Legend

| Status | Description |
|--------|-------------|
| ✅ | Implemented and passing |
| ❌ | Implemented but failing |
| ⏳ | Pending implementation |
| 🔄 | Flaky/inconsistent |
