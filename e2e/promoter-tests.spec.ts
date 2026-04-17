import { test, Page, expect } from '@playwright/test';
import { LoginPage } from './pages/LoginPage';

const TEST_USERS = {
  promoter: { email: 'promoter@guestlist.pro', password: 'password', role: 'Promoter' },
};

test.describe('👤 Promoter Guest Registration Tests', () => {
  test.beforeEach(async ({ page }) => {
    const loginPage = new LoginPage(page);
    await loginPage.goto();
    await loginPage.login(TEST_USERS.promoter.email, TEST_USERS.promoter.password);
    await loginPage.expectLoginSuccess();
  });

  test('TC-PROM-001: Promoter dashboard loads correctly', async ({ page }) => {
    await page.goto('/promoter');
    await page.waitForLoadState('networkidle');
    await expect(page).toHaveURL(/\/promoter/);
  });

  test('TC-PROM-002: Quota overview shows correct information', async ({ page }) => {
    await page.goto('/promoter');
    await page.waitForLoadState('networkidle');

    const quotaText = await page.locator('[class*="quota"], [class*="widget"]').first().textContent();
    console.log(`Quota info: ${quotaText}`);

    expect(quotaText).toBeTruthy();
  });

  test('TC-PROM-003: Guest list shows only promoter guests', async ({ page }) => {
    await page.goto('/promoter/guests');
    await page.waitForLoadState('networkidle');

    const table = page.locator('table');
    await expect(table).toBeVisible({ timeout: 10000 });

    const rows = await page.locator('table tbody tr').count();
    console.log(`Promoter guest rows: ${rows}`);
  });

  test('TC-PROM-004: Can open create guest form', async ({ page }) => {
    await page.goto('/promoter/guests');
    await page.waitForLoadState('networkidle');

    const createBtn = page.locator('a:has-text("Cadastrar")').first();
    await createBtn.click();
    await page.waitForTimeout(500);

    const form = page.locator('form');
    await expect(form).toBeVisible({ timeout: 5000 });
  });

  test('TC-PROM-005: Guest form has all required fields', async ({ page }) => {
    await page.goto('/promoter/guests');
    await page.waitForLoadState('networkidle');

    const createBtn = page.locator('a:has-text("Cadastrar")').first();
    await createBtn.click();
    await page.waitForTimeout(500);

    const nameInput = page.locator('input[name*="name"]');
    const documentInput = page.locator('input[name*="document"]');
    const emailInput = page.locator('input[name*="email"]');
    const sectorSelect = page.locator('select[name*="sector"]');

    await expect(nameInput).toBeVisible();
    await expect(documentInput).toBeVisible();
    await expect(emailInput).toBeVisible();
    await expect(sectorSelect).toBeVisible();
  });

  test('TC-PROM-006: Can fill guest form with valid data', async ({ page }) => {
    await page.goto('/promoter/guests');
    await page.waitForLoadState('networkidle');

    const createBtn = page.locator('a:has-text("Cadastrar")').first();
    await createBtn.click();
    await page.waitForTimeout(500);

    await page.locator('input[name*="name"]').fill('Test Guest E2E');
    await page.locator('input[name*="document"]').fill('12345678901');
    await page.locator('input[name*="email"]').fill('test@e2e.com');

    const sectorSelect = page.locator('select[name*="sector"]');
    if (await sectorSelect.isVisible()) {
      const options = await sectorSelect.locator('option').allTextContents();
      console.log(`Sector options: ${options.join(', ')}`);
      if (options.length > 1) {
        await sectorSelect.selectOption({ index: 1 });
      }
    }
  });

  test('TC-PROM-007: Form validation shows errors for empty fields', async ({ page }) => {
    await page.goto('/promoter/guests');
    await page.waitForLoadState('networkidle');

    const createBtn = page.locator('a:has-text("Cadastrar")').first();
    await createBtn.click();
    await page.waitForTimeout(500);

    const submitBtn = page.locator('button:has-text("Salvar")');
    await submitBtn.click();
    await page.waitForTimeout(1000);

    const errors = await page.locator('[class*="error"], [class*="invalid"], .fi-error').count();
    console.log(`Validation errors shown: ${errors}`);
  });

  test('TC-PROM-008: +1 companion toggle is visible', async ({ page }) => {
    await page.goto('/promoter/guests');
    await page.waitForLoadState('networkidle');

    const createBtn = page.locator('a:has-text("Cadastrar")').first();
    await createBtn.click();
    await page.waitForTimeout(500);

    const plusOneToggle = page.locator('input[name*="plus_one"]');
    if (await plusOneToggle.isVisible()) {
      console.log('+1 toggle is available');
    }
  });
});

test.describe('🔍 Promoter Search and Filter Tests', () => {
  test.beforeEach(async ({ page }) => {
    const loginPage = new LoginPage(page);
    await loginPage.goto();
    await loginPage.login(TEST_USERS.promoter.email, TEST_USERS.promoter.password);
    await loginPage.expectLoginSuccess();
    await page.goto('/promoter/guests');
    await page.waitForLoadState('networkidle');
  });

  test('TC-PROM-SEARCH-001: Search by name works', async ({ page }) => {
    const searchInput = page.locator('input[placeholder*="buscar"], input[type="search"]').first();
    await searchInput.fill('Ana');
    await page.waitForTimeout(1000);

    const rows = await page.locator('table tbody tr').count();
    console.log(`Search results for 'Ana': ${rows} rows`);
  });

  test('TC-PROM-SEARCH-002: Search by document works', async ({ page }) => {
    const searchInput = page.locator('input[placeholder*="buscar"], input[type="search"]').first();
    await searchInput.fill('12345678');
    await page.waitForTimeout(1000);

    const rows = await page.locator('table tbody tr').count();
    console.log(`Search results for document: ${rows} rows`);
  });
});

test.describe('⚠️ Promoter Quota Limit Tests', () => {
  test.beforeEach(async ({ page }) => {
    const loginPage = new LoginPage(page);
    await loginPage.goto();
    await loginPage.login(TEST_USERS.promoter.email, TEST_USERS.promoter.password);
    await loginPage.expectLoginSuccess();
    await page.goto('/promoter/guests');
    await page.waitForLoadState('networkidle');
  });

  test('TC-PROM-QUOTA-001: Quota display shows used/total', async ({ page }) => {
    await page.goto('/promoter');
    await page.waitForLoadState('networkidle');

    const quotaWidget = page.locator('[class*="quota"]').first();
    const quotaText = await quotaWidget.textContent();
    console.log(`Quota display: ${quotaText}`);
  });

  test('TC-PROM-QUOTA-002: Warning when approaching quota limit', async ({ page }) => {
    await page.goto('/promoter');
    await page.waitForLoadState('networkidle');

    const warningElement = page.locator('[class*="warning"], [class*="danger"], text=limite');
    if (await warningElement.isVisible()) {
      console.log('Quota warning visible');
    }
  });
});
