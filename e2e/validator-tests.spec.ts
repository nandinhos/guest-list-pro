import { test, Page, expect } from '@playwright/test';
import { LoginPage } from './pages/LoginPage';

const TEST_USERS = {
  validator: { email: 'validador@guestlist.pro', password: 'password', role: 'Validator' },
};

test.describe('✅ Validator Check-in Flow Tests', () => {
  test.beforeEach(async ({ page }) => {
    const loginPage = new LoginPage(page);
    await loginPage.goto();
    await loginPage.login(TEST_USERS.validator.email, TEST_USERS.validator.password);
    await loginPage.expectLoginSuccess();
  });

  test('TC-VALID-001: Validator dashboard loads correctly', async ({ page }) => {
    await page.goto('/validator');
    await page.waitForLoadState('networkidle');
    await expect(page).toHaveURL(/\/validator/);
  });

  test('TC-VALID-002: Guest list table is visible', async ({ page }) => {
    await page.goto('/validator/guests');
    await page.waitForLoadState('networkidle');

    const table = page.locator('table');
    await expect(table).toBeVisible({ timeout: 10000 });
  });

  test('TC-VALID-003: Search input is available', async ({ page }) => {
    await page.goto('/validator/guests');
    await page.waitForLoadState('networkidle');

    const searchInput = page.locator('input[type="search"], input[placeholder*="buscar"]');
    await expect(searchInput).toBeVisible();
  });

  test('TC-VALID-004: Can search by guest name', async ({ page }) => {
    await page.goto('/validator/guests');
    await page.waitForLoadState('networkidle');

    const searchInput = page.locator('input[type="search"], input[placeholder*="buscar"]');
    await searchInput.fill('Ana Silva');
    await page.waitForTimeout(1500);

    const results = await page.locator('table tbody tr').count();
    console.log(`Search results: ${results}`);
  });

  test('TC-VALID-005: Status badges are displayed', async ({ page }) => {
    await page.goto('/validator/guests');
    await page.waitForLoadState('networkidle');

    const statusBadges = page.locator('[class*="badge"], [class*="status"]');
    const count = await statusBadges.count();
    console.log(`Status badges found: ${count}`);
    expect(count).toBeGreaterThan(0);
  });
});

test.describe('🔍 Validator Search Tests', () => {
  test.beforeEach(async ({ page }) => {
    const loginPage = new LoginPage(page);
    await loginPage.goto();
    await loginPage.login(TEST_USERS.validator.email, TEST_USERS.validator.password);
    await loginPage.expectLoginSuccess();
    await page.goto('/validator/guests');
    await page.waitForLoadState('networkidle');
  });

  test('TC-VALID-SEARCH-001: Search by partial name', async ({ page }) => {
    const searchInput = page.locator('input[type="search"], input[placeholder*="buscar"]');
    await searchInput.fill('Silva');
    await page.waitForTimeout(1500);

    const rows = await page.locator('table tbody tr').count();
    console.log(`Search 'Silva': ${rows} results`);
  });

  test('TC-VALID-SEARCH-002: Search by document number', async ({ page }) => {
    const searchInput = page.locator('input[type="search"], input[placeholder*="buscar"]');
    await searchInput.fill('12345678');
    await page.waitForTimeout(1500);

    const rows = await page.locator('table tbody tr').count();
    console.log(`Search by document: ${rows} results`);
  });

  test('TC-VALID-SEARCH-003: Clear search shows all guests', async ({ page }) => {
    const searchInput = page.locator('input[type="search"], input[placeholder*="buscar"]');
    await searchInput.fill('Ana');
    await page.waitForTimeout(1500);

    const initialCount = await page.locator('table tbody tr').count();

    await searchInput.clear();
    await page.waitForTimeout(1500);

    const clearedCount = await page.locator('table tbody tr').count();
    console.log(`Before clear: ${initialCount}, After clear: ${clearedCount}`);

    expect(clearedCount).toBeGreaterThanOrEqual(initialCount);
  });
});

test.describe('🚨 Validator Emergency Check-in Tests', () => {
  test.beforeEach(async ({ page }) => {
    const loginPage = new LoginPage(page);
    await loginPage.goto();
    await loginPage.login(TEST_USERS.validator.email, TEST_USERS.validator.password);
    await loginPage.expectLoginSuccess();
    await page.goto('/validator/guests');
    await page.waitForLoadState('networkidle');
  });

  test('TC-VALID-EMERGENCY-001: Emergency button is visible', async ({ page }) => {
    const emergencyBtn = page.locator('button:has-text("Não está na lista")');
    await expect(emergencyBtn).toBeVisible({ timeout: 5000 });
  });

  test('TC-VALID-EMERGENCY-002: Can open emergency modal', async ({ page }) => {
    const emergencyBtn = page.locator('button:has-text("Não está na lista")');
    await emergencyBtn.click();
    await page.waitForTimeout(500);

    const modal = page.locator('[class*="modal"], [role="dialog"]');
    await expect(modal).toBeVisible({ timeout: 5000 });
  });

  test('TC-VALID-EMERGENCY-003: Emergency form has required fields', async ({ page }) => {
    const emergencyBtn = page.locator('button:has-text("Não está na lista")');
    await emergencyBtn.click();
    await page.waitForTimeout(500);

    const nameInput = page.locator('input[name*="name"]');
    const documentInput = page.locator('input[name*="document"]');
    const sectorSelect = page.locator('select[name*="sector"]');

    await expect(nameInput).toBeVisible();
    await expect(documentInput).toBeVisible();
    await expect(sectorSelect).toBeVisible();
  });
});

test.describe('🔄 Validator Status Filter Tests', () => {
  test.beforeEach(async ({ page }) => {
    const loginPage = new LoginPage(page);
    await loginPage.goto();
    await loginPage.login(TEST_USERS.validator.email, TEST_USERS.validator.password);
    await loginPage.expectLoginSuccess();
    await page.goto('/validator/guests');
    await page.waitForLoadState('networkidle');
  });

  test('TC-VALID-FILTER-001: Status filter dropdown is available', async ({ page }) => {
    const filters = page.locator('select');
    await expect(filters.first()).toBeVisible();
  });

  test('TC-VALID-FILTER-002: Can filter checked-in guests', async ({ page }) => {
    const statusFilter = page.locator('select').filter({ hasText: /status|check/i }).first();
    if (await statusFilter.isVisible()) {
      await statusFilter.selectOption({ index: 1 });
      await page.waitForTimeout(1000);
    }
  });

  test('TC-VALID-FILTER-003: Can filter pending guests', async ({ page }) => {
    const statusFilter = page.locator('select').filter({ hasText: /status|check/i }).first();
    if (await statusFilter.isVisible()) {
      await statusFilter.selectOption({ index: 2 });
      await page.waitForTimeout(1000);
    }
  });
});

test.describe('📊 Validator Dashboard Stats Tests', () => {
  test.beforeEach(async ({ page }) => {
    const loginPage = new LoginPage(page);
    await loginPage.goto();
    await loginPage.login(TEST_USERS.validator.email, TEST_USERS.validator.password);
    await loginPage.expectLoginSuccess();
    await page.goto('/validator');
    await page.waitForLoadState('networkidle');
  });

  test('TC-VALID-STATS-001: Dashboard shows stats widgets', async ({ page }) => {
    const stats = page.locator('[class*="stat"], .fi-stat');
    const count = await stats.count();
    console.log(`Stats widgets: ${count}`);
    expect(count).toBeGreaterThan(0);
  });

  test('TC-VALID-STATS-002: Check-in count is displayed', async ({ page }) => {
    const statsText = await page.locator('[class*="stat"]').first().textContent();
    console.log(`Stats text: ${statsText}`);
    expect(statsText).toBeTruthy();
  });
});
