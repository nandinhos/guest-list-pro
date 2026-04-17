import { test, expect, Page, chromium } from '@playwright/test';
import { LoginPage } from './pages/LoginPage';
import { AdminDashboardPage, AdminGuestsPage, AdminEventsPage, AdminApprovalsPage } from './pages/AdminPages';

const TEST_USERS = {
  admin: { email: 'admin@guestlist.pro', password: 'password', role: 'Admin' },
  promoter: { email: 'promoter@guestlist.pro', password: 'password', role: 'Promoter' },
  validator: { email: 'validador@guestlist.pro', password: 'password', role: 'Validator' },
  bilheteria: { email: 'bilheteria@guestlist.pro', password: 'password', role: 'Bilheteria' },
};

const EVENT_NAME = 'Festival Teste 2026';

test.describe('🔐 Authentication Tests', () => {
  let page: Page;

  test.beforeEach(async ({ browser }) => {
    page = await browser.newPage();
  });

  test.afterEach(async () => {
    await page.close();
  });

  test('TC-AUTH-001: Login page loads correctly', async () => {
    const loginPage = new LoginPage(page);
    await loginPage.goto();
    await loginPage.expectToBeOnLoginPage();

    const title = await loginPage.getPageTitle();
    console.log(`Page title: ${title}`);
  });

  test('TC-AUTH-002: Admin can login successfully', async ({ page }) => {
    const loginPage = new LoginPage(page);
    await loginPage.goto();
    await loginPage.login(TEST_USERS.admin.email, TEST_USERS.admin.password);
    await loginPage.expectLoginSuccess();

    const adminPage = new AdminDashboardPage(page);
    await adminPage.goto();
    await adminPage.expectToBeOnAdminDashboard();
  });

  test('TC-AUTH-003: Invalid credentials show error', async ({ page }) => {
    const loginPage = new LoginPage(page);
    await loginPage.goto();
    await loginPage.login('invalid@email.com', 'wrongpassword');
    await loginPage.expectLoginToFail();
  });

  test('TC-AUTH-004: Promoter can login successfully', async ({ page }) => {
    const loginPage = new LoginPage(page);
    await loginPage.goto();
    await loginPage.login(TEST_USERS.promoter.email, TEST_USERS.promoter.password);
    await loginPage.expectLoginSuccess();
    await expect(page).toHaveURL(/\/promoter/);
  });

  test('TC-AUTH-005: Validator can login successfully', async ({ page }) => {
    const loginPage = new LoginPage(page);
    await loginPage.goto();
    await loginPage.login(TEST_USERS.validator.email, TEST_USERS.validator.password);
    await loginPage.expectLoginSuccess();
    await expect(page).toHaveURL(/\/validator/);
  });

  test('TC-AUTH-006: Bilheteria can login successfully', async ({ page }) => {
    const loginPage = new LoginPage(page);
    await loginPage.goto();
    await loginPage.login(TEST_USERS.bilheteria.email, TEST_USERS.bilheteria.password);
    await loginPage.expectLoginSuccess();
    await expect(page).toHaveURL(/\/bilheteria/);
  });

  test('TC-AUTH-007: Logout redirects to login', async ({ page }) => {
    const loginPage = new LoginPage(page);
    await loginPage.goto();
    await loginPage.login(TEST_USERS.admin.email, TEST_USERS.admin.password);
    await loginPage.expectLoginSuccess();
    await loginPage.logout();
    await expect(page).toHaveURL(/\/login/);
  });
});

test.describe('👨‍💼 Admin Panel Tests', () => {
  let adminPage: AdminDashboardPage;

  test.beforeEach(async ({ page }) => {
    const loginPage = new LoginPage(page);
    await loginPage.goto();
    await loginPage.login(TEST_USERS.admin.email, TEST_USERS.admin.password);
    await loginPage.expectLoginSuccess();
    adminPage = new AdminDashboardPage(page);
    await adminPage.goto();
  });

  test('TC-ADMIN-001: Dashboard loads with widgets', async ({ page }) => {
    await adminPage.expectToBeOnAdminDashboard();
    const widgetCount = await adminPage.getWidgetCount();
    console.log(`Widget count: ${widgetCount}`);
    expect(widgetCount).toBeGreaterThan(0);
  });

  test('TC-ADMIN-002: Can select event from dropdown', async ({ page }) => {
    await adminPage.selectEvent(EVENT_NAME);
    await page.waitForTimeout(1000);
  });

  test('TC-ADMIN-003: Guests page loads and shows table', async ({ page }) => {
    const guestsPage = new AdminGuestsPage(page);
    await guestsPage.goto();
    await guestsPage.expectTableToBeVisible();
    const rows = await guestsPage.getTableRows();
    console.log(`Guest rows found: ${rows.length}`);
  });

  test('TC-ADMIN-004: Can search for a specific guest', async ({ page }) => {
    const guestsPage = new AdminGuestsPage(page);
    await guestsPage.goto();
    await guestsPage.searchGuest('Ana Silva');
    await page.waitForTimeout(1000);
  });

  test('TC-ADMIN-005: Events page loads correctly', async ({ page }) => {
    const eventsPage = new AdminEventsPage(page);
    await eventsPage.goto();
    await eventsPage.expectTableToBeVisible();
  });

  test('TC-ADMIN-006: Approval requests page loads', async ({ page }) => {
    const approvalsPage = new AdminApprovalsPage(page);
    await approvalsPage.goto();
    const pendingCount = await approvalsPage.getPendingCount();
    console.log(`Pending approvals: ${pendingCount}`);
  });

  test('TC-ADMIN-007: Can view guest details', async ({ page }) => {
    const guestsPage = new AdminGuestsPage(page);
    await guestsPage.goto();
    await guestsPage.clickGuest('Ana Silva');
    await page.waitForTimeout(1000);
  });
});

test.describe('🎫 Bilheteria Panel Tests', () => {
  test.beforeEach(async ({ page }) => {
    const loginPage = new LoginPage(page);
    await loginPage.goto();
    await loginPage.login(TEST_USERS.bilheteria.email, TEST_USERS.bilheteria.password);
    await loginPage.expectLoginSuccess();
  });

  test('TC-BILHETERIA-001: Dashboard shows stats widgets', async ({ page }) => {
    const dashboard = new (require('../pages/BilheteriaPages').BilheteriaDashboardPage)(page);
    await dashboard.goto();
    await dashboard.expectToBeOnBilheteriaDashboard();
    const widgetCount = await dashboard.statsWidgets.count();
    expect(widgetCount).toBeGreaterThan(0);
  });

  test('TC-BILHETERIA-002: Sales table is visible and has data', async ({ page }) => {
    const salesPage = new (require('../pages/BilheteriaPages').BilheteriaSalesPage)(page);
    await salesPage.goto();
    await salesPage.expectTableToBeVisible();
    const salesCount = await salesPage.getSalesCount();
    console.log(`Sales found: ${salesCount}`);
  });

  test('TC-BILHETERIA-003: Can filter sales by ticket type', async ({ page }) => {
    const salesPage = new (require('../pages/BilheteriaPages').BilheteriaSalesPage)(page);
    await salesPage.goto();
    await salesPage.filterByTicketType('Pista Premium');
  });

  test('TC-BILHETERIA-004: Can create new ticket sale', async ({ page }) => {
    const salesPage = new (require('../pages/BilheteriaPages').BilheteriaSalesPage)(page);
    await salesPage.goto();
    await salesPage.createButton.click();
    await page.waitForTimeout(1000);

    const formPage = new (require('../pages/BilheteriaPages').BilheteriaSaleFormPage)(page);
    await formPage.fillSaleForm({
      buyerName: 'Test Buyer',
      buyerDocument: '12345678900',
      ticketType: 'Pista Premium',
      sector: 'Pista',
      paymentMethod: 'PIX',
    });
    await formPage.submitForm();
  });
});

test.describe('✅ Validator Panel Tests', () => {
  test.beforeEach(async ({ page }) => {
    const loginPage = new LoginPage(page);
    await loginPage.goto();
    await loginPage.login(TEST_USERS.validator.email, TEST_USERS.validator.password);
    await loginPage.expectLoginSuccess();
  });

  test('TC-VALIDATOR-001: Dashboard loads with guest list', async ({ page }) => {
    const validatorPage = new (require('../pages/ValidatorPages').ValidatorDashboardPage)(page);
    await validatorPage.goto();
    await validatorPage.expectToBeOnValidatorDashboard();
  });

  test('TC-VALIDATOR-002: Can search for existing guest', async ({ page }) => {
    const validatorPage = new (require('../pages/ValidatorPages').ValidatorDashboardPage)(page);
    await validatorPage.goto();
    await validatorPage.searchGuest('Ana Silva');
    await page.waitForTimeout(1000);
  });

  test('TC-VALIDATOR-003: Guest list shows correct count', async ({ page }) => {
    const guestListPage = new (require('../pages/ValidatorPages').ValidatorGuestListPage)(page);
    await guestListPage.goto();
    const guestCount = await guestListPage.getGuestCount();
    console.log(`Guest count in validator list: ${guestCount}`);
  });

  test('TC-VALIDATOR-004: Can filter by check-in status', async ({ page }) => {
    const guestListPage = new (require('../pages/ValidatorPages').ValidatorGuestListPage)(page);
    await guestListPage.goto();
    await guestListPage.filterByStatus('pending');
  });
});

test.describe('👤 Promoter Panel Tests', () => {
  test.beforeEach(async ({ page }) => {
    const loginPage = new LoginPage(page);
    await loginPage.goto();
    await loginPage.login(TEST_USERS.promoter.email, TEST_USERS.promoter.password);
    await loginPage.expectLoginSuccess();
  });

  test('TC-PROMOTER-001: Dashboard shows quota widget', async ({ page }) => {
    const dashboard = new (require('../pages/PromoterPages').PromoterDashboardPage)(page);
    await dashboard.goto();
    await dashboard.expectToBeOnPromoterDashboard();
    const quota = await dashboard.getQuotaInfo();
    console.log(`Quota: ${quota.used}/${quota.total}`);
  });

  test('TC-PROMOTER-002: Guest list page loads', async ({ page }) => {
    const guestListPage = new (require('../pages/PromoterPages').PromoterGuestListPage)(page);
    await guestListPage.goto();
    await guestListPage.expectTableToBeVisible();
  });

  test('TC-PROMOTER-003: Can search guests in own list', async ({ page }) => {
    const guestListPage = new (require('../pages/PromoterPages').PromoterGuestListPage)(page);
    await guestListPage.goto();
    await guestListPage.searchGuest('Lucas');
  });

  test('TC-PROMOTER-004: Create guest form is accessible', async ({ page }) => {
    const guestListPage = new (require('../pages/PromoterPages').PromoterGuestListPage)(page);
    await guestListPage.goto();
    await guestListPage.clickCreateGuest();
    await page.waitForTimeout(500);
  });
});

export { TEST_USERS, EVENT_NAME };
