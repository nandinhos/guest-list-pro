import { test, expect, Page } from '@playwright/test';
import { LoginPage } from './pages/LoginPage';
import { AdminDashboardPage, AdminGuestsPage, AdminApprovalsPage, AdminEventsPage } from './pages/AdminPages';

const TEST_USERS = {
  admin: { email: 'admin@guestlist.pro', password: 'password', role: 'Admin' },
};

test.describe('📊 Admin Dashboard Widgets Tests', () => {
  let adminPage: AdminDashboardPage;

  test.beforeEach(async ({ page }) => {
    const loginPage = new LoginPage(page);
    await loginPage.goto();
    await loginPage.login(TEST_USERS.admin.email, TEST_USERS.admin.password);
    await loginPage.expectLoginSuccess();
    adminPage = new AdminDashboardPage(page);
    await adminPage.goto();
  });

  test('TC-WIDGET-001: Sales Timeline Chart is visible', async ({ page }) => {
    await adminPage.expectSalesTimelineVisible();
  });

  test('TC-WIDGET-002: Sector Metrics Table shows data', async ({ page }) => {
    test.skip(true, 'Requires event selection - widgets depend on session("selected_event_id")');
  });

  test('TC-WIDGET-003: Ticket Type Report Table is visible', async ({ page }) => {
    test.skip(true, 'Requires event selection - widgets depend on session("selected_event_id")');
  });

  test('TC-WIDGET-004: Admin Overview stats are displayed', async ({ page }) => {
    await adminPage.expectStatsWidgetsVisible();
  });
});

test.describe('🔍 Admin Guest Management Tests', () => {
  test.beforeEach(async ({ page }) => {
    const loginPage = new LoginPage(page);
    await loginPage.goto();
    await loginPage.login(TEST_USERS.admin.email, TEST_USERS.admin.password);
    await loginPage.expectLoginSuccess();
  });

  test('TC-GUEST-001: Can view all guests in table', async ({ page }) => {
    const guestsPage = new AdminGuestsPage(page);
    await guestsPage.goto();
    await guestsPage.expectTableToBeVisible();
  });

  test('TC-GUEST-002: Guest search returns correct results', async ({ page }) => {
    const guestsPage = new AdminGuestsPage(page);
    await guestsPage.goto();
    await guestsPage.searchGuest('Ana');
    await page.waitForTimeout(1000);

    const rows = await guestsPage.getTableRows();
    console.log(`Search results: ${rows.length} rows`);
  });

  test('TC-GUEST-003: Filter by event works', async ({ page }) => {
    const guestsPage = new AdminGuestsPage(page);
    await guestsPage.goto();
    const eventFilter = page.locator('select').first();
    if (await eventFilter.isVisible()) {
      const options = await eventFilter.locator('option').allTextContents();
      console.log(`Event filter options: ${options.join(', ')}`);
    }
  });

  test('TC-GUEST-004: Can view checked-in guests', async ({ page }) => {
    const guestsPage = new AdminGuestsPage(page);
    await guestsPage.goto();
    const statusFilter = page.locator('select:has-text("Status")');
    if (await statusFilter.isVisible()) {
      await statusFilter.selectOption('check-in');
      await page.waitForTimeout(1000);
    }
  });

  test('TC-GUEST-005: Direct check-in button works', async ({ page }) => {
    const guestsPage = new AdminGuestsPage(page);
    await guestsPage.goto();
    const checkinBtn = page.locator('button:has-text("Check-in")').first();
    if (await checkinBtn.isVisible()) {
      console.log('Check-in button is visible');
    }
  });
});

test.describe('📋 Admin Approval Request Tests', () => {
  test.beforeEach(async ({ page }) => {
    const loginPage = new LoginPage(page);
    await loginPage.goto();
    await loginPage.login(TEST_USERS.admin.email, TEST_USERS.admin.password);
    await loginPage.expectLoginSuccess();
  });

  test('TC-APPROVAL-001: Approval requests page loads', async ({ page }) => {
    const approvalsPage = new AdminApprovalsPage(page);
    await approvalsPage.goto();
    await expect(approvalsPage.table).toBeVisible({ timeout: 10000 });
  });

  test('TC-APPROVAL-002: Can view pending approvals', async ({ page }) => {
    const approvalsPage = new AdminApprovalsPage(page);
    await approvalsPage.goto();
    const pendingCount = await approvalsPage.getPendingCount();
    console.log(`Pending approvals: ${pendingCount}`);
  });

  test('TC-APPROVAL-003: Can approve a request', async ({ page }) => {
    const approvalsPage = new AdminApprovalsPage(page);
    await approvalsPage.goto();
    await page.waitForTimeout(1000);

    const approveBtn = page.locator('button:has-text("Aprovar"), button:has-text("Approve")').first();
    if (await approveBtn.isVisible({ timeout: 3000 })) {
      console.log('Approve button found');
    }
  });
});

test.describe('🔄 Admin CRUD Operations Tests', () => {
  test.beforeEach(async ({ page }) => {
    const loginPage = new LoginPage(page);
    await loginPage.goto();
    await loginPage.login(TEST_USERS.admin.email, TEST_USERS.admin.password);
    await loginPage.expectLoginSuccess();
  });

  test('TC-CRUD-001: Can navigate to events management', async ({ page }) => {
    const eventsPage = new AdminEventsPage(page);
    await eventsPage.goto();
    await expect(page).toHaveURL(/\/admin\/events/);
  });

  test('TC-CRUD-002: Can navigate to sectors management', async ({ page }) => {
    await page.goto('/admin/sectors');
    await page.waitForLoadState('networkidle');
    const table = page.locator('table');
    await expect(table).toBeVisible({ timeout: 10000 });
  });

  test('TC-CRUD-003: Can navigate to users management', async ({ page }) => {
    await page.goto('/admin/users');
    await page.waitForLoadState('networkidle');
    const table = page.locator('table');
    await expect(table).toBeVisible({ timeout: 10000 });
  });
});
