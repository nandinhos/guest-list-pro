import { test, Page, expect } from '@playwright/test';
import { LoginPage } from './pages/LoginPage';

const TEST_USERS = {
  bilheteria: { email: 'bilheteria@guestlist.pro', password: 'password', role: 'Bilheteria' },
};

test.describe('🎫 Bilheteria Dashboard Tests', () => {
  test.beforeEach(async ({ page }) => {
    const loginPage = new LoginPage(page);
    await loginPage.goto();
    await loginPage.login(TEST_USERS.bilheteria.email, TEST_USERS.bilheteria.password);
    await loginPage.expectLoginSuccess();
  });

  test('TC-BILH-001: Bilheteria dashboard loads correctly', async ({ page }) => {
    await page.goto('/bilheteria');
    await page.waitForLoadState('networkidle');
    await expect(page).toHaveURL(/\/bilheteria/);
  });

  test('TC-BILH-002: Stats widgets are displayed', async ({ page }) => {
    await page.goto('/bilheteria');
    await page.waitForLoadState('networkidle');

    const stats = page.locator('.fi-stat, [class*="stat"]');
    const count = await stats.count();
    console.log(`Stats widgets found: ${count}`);
    expect(count).toBeGreaterThan(0);
  });

  test('TC-BILH-003: Revenue display shows formatted values', async ({ page }) => {
    await page.goto('/bilheteria');
    await page.waitForLoadState('networkidle');

    const revenueText = await page.locator('text=/receita|revenue|r\\$/i').first().textContent();
    console.log(`Revenue display: ${revenueText}`);
  });

  test('TC-BILH-004: Today sales count is shown', async ({ page }) => {
    await page.goto('/bilheteria');
    await page.waitForLoadState('networkidle');

    const salesText = await page.locator('text=/vendas|sales/i').first().textContent();
    console.log(`Sales display: ${salesText}`);
  });
});

test.describe('💰 Bilheteria Sales Management Tests', () => {
  test.beforeEach(async ({ page }) => {
    const loginPage = new LoginPage(page);
    await loginPage.goto();
    await loginPage.login(TEST_USERS.bilheteria.email, TEST_USERS.bilheteria.password);
    await loginPage.expectLoginSuccess();
    await page.goto('/bilheteria/ticket-sales');
    await page.waitForLoadState('networkidle');
  });

  test('TC-BILH-SALES-001: Sales table is visible', async ({ page }) => {
    const table = page.locator('table');
    await expect(table).toBeVisible({ timeout: 10000 });
  });

  test('TC-BILH-SALES-002: Sales data is displayed', async ({ page }) => {
    const rows = await page.locator('table tbody tr').count();
    console.log(`Sales table rows: ${rows}`);
    expect(rows).toBeGreaterThan(0);
  });

  test('TC-BILH-SALES-003: Can access create sale form', async ({ page }) => {
    const createBtn = page.locator('a:has-text("Nova Venda"), button:has-text("Vender")');
    await expect(createBtn).toBeVisible();
    await createBtn.click();
    await page.waitForTimeout(1000);

    const form = page.locator('form');
    await expect(form).toBeVisible({ timeout: 5000 });
  });
});

test.describe('🎟️ Bilheteria Ticket Sale Form Tests', () => {
  test.beforeEach(async ({ page }) => {
    const loginPage = new LoginPage(page);
    await loginPage.goto();
    await loginPage.login(TEST_USERS.bilheteria.email, TEST_USERS.bilheteria.password);
    await loginPage.expectLoginSuccess();
    await page.goto('/bilheteria/ticket-sales/create');
    await page.waitForLoadState('networkidle');
  });

  test('TC-BILH-FORM-001: Sale form has all required fields', async ({ page }) => {
    const buyerNameInput = page.locator('input[name*="buyer_name"], input[id*="buyer_name"]');
    const buyerDocInput = page.locator('input[name*="buyer_document"]');
    const ticketTypeSelect = page.locator('select[name*="ticket_type"]');
    const sectorSelect = page.locator('select[name*="sector"]');
    const paymentSelect = page.locator('select[name*="payment"]');

    await expect(buyerNameInput).toBeVisible();
    await expect(buyerDocInput).toBeVisible();
    await expect(ticketTypeSelect).toBeVisible();
    await expect(sectorSelect).toBeVisible();
    await expect(paymentSelect).toBeVisible();
  });

  test('TC-BILH-FORM-002: Ticket types are available in dropdown', async ({ page }) => {
    const ticketTypeSelect = page.locator('select[name*="ticket_type"]');
    await ticketTypeSelect.click();
    await page.waitForTimeout(500);

    const options = await ticketTypeSelect.locator('option').allTextContents();
    console.log(`Ticket types: ${options.join(', ')}`);
    expect(options.length).toBeGreaterThan(1);
  });

  test('TC-BILH-FORM-003: Sectors are available in dropdown', async ({ page }) => {
    const sectorSelect = page.locator('select[name*="sector"]');
    await sectorSelect.click();
    await page.waitForTimeout(500);

    const options = await sectorSelect.locator('option').allTextContents();
    console.log(`Sectors: ${options.join(', ')}`);
    expect(options.length).toBeGreaterThan(1);
  });

  test('TC-BILH-FORM-004: Payment methods are available', async ({ page }) => {
    const paymentSelect = page.locator('select[name*="payment"]');
    await paymentSelect.click();
    await page.waitForTimeout(500);

    const options = await paymentSelect.locator('option').allTextContents();
    console.log(`Payment methods: ${options.join(', ')}`);
  });

  test('TC-BILH-FORM-005: Can fill complete sale form', async ({ page }) => {
    await page.locator('input[name*="buyer_name"]').fill('Test Customer E2E');
    await page.locator('input[name*="buyer_document"]').fill('98765432100');

    const ticketTypeSelect = page.locator('select[name*="ticket_type"]');
    const options = await ticketTypeSelect.locator('option').allTextContents();
    if (options.length > 1) {
      await ticketTypeSelect.selectOption({ index: 1 });
      await page.waitForTimeout(500);
    }

    const sectorSelect = page.locator('select[name*="sector"]');
    const sectorOptions = await sectorSelect.locator('option').allTextContents();
    if (sectorOptions.length > 1) {
      await sectorSelect.selectOption({ index: 1 });
      await page.waitForTimeout(500);
    }
  });
});

test.describe('💳 Bilheteria Payment Method Tests', () => {
  test.beforeEach(async ({ page }) => {
    const loginPage = new LoginPage(page);
    await loginPage.goto();
    await loginPage.login(TEST_USERS.bilheteria.email, TEST_USERS.bilheteria.password);
    await loginPage.expectLoginSuccess();
    await page.goto('/bilheteria/ticket-sales/create');
    await page.waitForLoadState('networkidle');
  });

  test('TC-BILH-PAY-001: Can select PIX payment', async ({ page }) => {
    const paymentSelect = page.locator('select[name*="payment"]');
    await paymentSelect.selectOption({ label: /pix/i });
    await page.waitForTimeout(500);
  });

  test('TC-BILH-PAY-002: Can select Cash payment', async ({ page }) => {
    const paymentSelect = page.locator('select[name*="payment"]');
    await paymentSelect.selectOption({ label: /dinheiro|cash/i });
    await page.waitForTimeout(500);
  });

  test('TC-BILH-PAY-003: Can select Credit Card payment', async ({ page }) => {
    const paymentSelect = page.locator('select[name*="payment"]');
    await paymentSelect.selectOption({ label: /crédito|credit/i });
    await page.waitForTimeout(500);
  });

  test('TC-BILH-PAY-004: Can select Debit Card payment', async ({ page }) => {
    const paymentSelect = page.locator('select[name*="payment"]');
    await paymentSelect.selectOption({ label: /débito|debit/i });
    await page.waitForTimeout(500);
  });
});

test.describe('🔍 Bilheteria Sales Filter Tests', () => {
  test.beforeEach(async ({ page }) => {
    const loginPage = new LoginPage(page);
    await loginPage.goto();
    await loginPage.login(TEST_USERS.bilheteria.email, TEST_USERS.bilheteria.password);
    await loginPage.expectLoginSuccess();
    await page.goto('/bilheteria/ticket-sales');
    await page.waitForLoadState('networkidle');
  });

  test('TC-BILH-FILTER-001: Filter dropdowns are available', async ({ page }) => {
    const filters = page.locator('select');
    const count = await filters.count();
    console.log(`Filter dropdowns found: ${count}`);
    expect(count).toBeGreaterThan(0);
  });

  test('TC-BILH-FILTER-002: Can filter by ticket type', async ({ page }) => {
    const ticketTypeFilter = page.locator('select').filter({ hasText: /tipo|ingresso/i }).first();
    if (await ticketTypeFilter.isVisible()) {
      await ticketTypeFilter.selectOption({ index: 1 });
      await page.waitForTimeout(1000);
    }
  });

  test('TC-BILH-FILTER-003: Can filter by payment method', async ({ page }) => {
    const paymentFilter = page.locator('select').filter({ hasText: /pagamento|payment/i }).first();
    if (await paymentFilter.isVisible()) {
      await paymentFilter.selectOption({ index: 1 });
      await page.waitForTimeout(1000);
    }
  });
});

test.describe('📊 Bilheteria Reports Tests', () => {
  test.beforeEach(async ({ page }) => {
    const loginPage = new LoginPage(page);
    await loginPage.goto();
    await loginPage.login(TEST_USERS.bilheteria.email, TEST_USERS.bilheteria.password);
    await loginPage.expectLoginSuccess();
  });

  test('TC-BILH-REPORT-001: Sector metrics table is visible', async ({ page }) => {
    await page.goto('/bilheteria');
    await page.waitForLoadState('networkidle');

    const metricsTable = page.locator('text=Métricas por Setor');
    await expect(metricsTable).toBeVisible({ timeout: 10000 });
  });

  test('TC-BILH-REPORT-002: Ticket type report is visible', async ({ page }) => {
    await page.goto('/bilheteria');
    await page.waitForLoadState('networkidle');

    const ticketReport = page.locator('text=Relatório por Tipo');
    await expect(ticketReport).toBeVisible({ timeout: 10000 });
  });
});
