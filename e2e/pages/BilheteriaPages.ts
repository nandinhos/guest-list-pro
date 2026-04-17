import { Page, Locator, expect } from '@playwright/test';

export class BilheteriaDashboardPage {
  readonly page: Page;
  readonly statsWidgets: Locator;
  readonly salesTable: Locator;
  readonly createSaleButton: Locator;

  constructor(page: Page) {
    this.page = page;
    this.statsWidgets = page.locator('[class*="stat"], .fi-stat');
    this.salesTable = page.locator('table');
    this.createSaleButton = page.locator('a:has-text("Nova Venda"), button:has-text("Vender")');
  }

  async goto() {
    await this.page.goto('/bilheteria');
    await this.page.waitForLoadState('networkidle');
  }

  async expectToBeOnBilheteriaDashboard() {
    await expect(this.page).toHaveURL(/\/bilheteria/);
  }

  async getTotalRevenue(): Promise<string> {
    const revenueWidget = this.statsWidgets.filter({ hasText: /receita|revenue/i }).first();
    if (await revenueWidget.isVisible()) {
      return await revenueWidget.textContent();
    }
    return '';
  }

  async getTodaySalesCount(): Promise<number> {
    const salesWidget = this.statsWidgets.filter({ hasText: /vendas|sales/i }).first();
    if (await salesWidget.isVisible()) {
      const text = await salesWidget.textContent();
      const match = text?.match(/(\d+)/);
      return match ? parseInt(match[1]) : 0;
    }
    return 0;
  }
}

export class BilheteriaSalesPage {
  readonly page: Page;
  readonly table: Locator;
  readonly createButton: Locator;
  readonly filters: Locator;

  constructor(page: Page) {
    this.page = page;
    this.table = page.locator('table');
    this.createButton = page.locator('a:has-text("Nova Venda"), [href*="/ticket-sales/create"]');
    this.filters = page.locator('select');
  }

  async goto() {
    await this.page.goto('/bilheteria/ticket-sales');
    await this.page.waitForLoadState('networkidle');
  }

  async expectTableToBeVisible() {
    await expect(this.table).toBeVisible({ timeout: 10000 });
  }

  async getSalesCount(): Promise<number> {
    return await this.page.locator('table tbody tr').count();
  }

  async filterByTicketType(ticketType: string) {
    const ticketTypeFilter = this.filters.filter({ hasText: /tipo|ingresso/i }).first();
    if (await ticketTypeFilter.isVisible()) {
      await ticketTypeFilter.selectOption({ label: ticketType });
      await this.page.waitForTimeout(500);
    }
  }

  async filterByPaymentMethod(method: string) {
    const paymentFilter = this.filters.filter({ hasText: /pagamento|payment/i }).first();
    if (await paymentFilter.isVisible()) {
      await paymentFilter.selectOption({ label: method });
      await this.page.waitForTimeout(500);
    }
  }
}

export class BilheteriaSaleFormPage {
  readonly page: Page;
  readonly form: Locator;
  readonly buyerNameInput: Locator;
  readonly buyerDocumentInput: Locator;
  readonly ticketTypeSelect: Locator;
  readonly sectorSelect: Locator;
  readonly paymentMethodSelect: Locator;
  readonly valueInput: Locator;
  readonly saveButton: Locator;
  readonly splitPaymentToggle: Locator;

  constructor(page: Page) {
    this.page = page;
    this.form = page.locator('form');
    this.buyerNameInput = page.locator('input[name*="buyer_name"], input[id*="buyer_name"]');
    this.buyerDocumentInput = page.locator('input[name*="buyer_document"], input[id*="buyer_document"]');
    this.ticketTypeSelect = page.locator('select[name*="ticket_type"], select[id*="ticket_type"]');
    this.sectorSelect = page.locator('select[name*="sector"], select[id*="sector"]');
    this.paymentMethodSelect = page.locator('select[name*="payment_method"], select[id*="payment_method"]');
    this.valueInput = page.locator('input[name*="value"], input[id*="value"]');
    this.saveButton = page.locator('button:has-text("Vender"), button:has-text("Salvar")');
    this.splitPaymentToggle = page.locator('input[name*="split"], input[name*=" Split"]');
  }

  async fillSaleForm(data: {
    buyerName: string;
    buyerDocument: string;
    ticketType: string;
    sector: string;
    paymentMethod: string;
    value?: string;
  }) {
    await this.buyerNameInput.fill(data.buyerName);
    await this.buyerDocumentInput.fill(data.buyerDocument);

    if (data.ticketType) {
      await this.ticketTypeSelect.selectOption({ label: data.ticketType });
      await this.page.waitForTimeout(300);
    }

    if (data.sector) {
      await this.sectorSelect.selectOption({ label: data.sector });
      await this.page.waitForTimeout(300);
    }

    if (data.paymentMethod) {
      await this.paymentMethodSelect.selectOption({ label: data.paymentMethod });
    }
  }

  async submitForm() {
    await this.saveButton.click();
    await this.page.waitForTimeout(1500);
  }

  async expectSaleToBeCreated() {
    await this.page.waitForSelector('[class*="success"], .fi-success, text=Sucesso, text=Venda realizada', { timeout: 5000 });
  }
}

export default {
  BilheteriaDashboardPage,
  BilheteriaSalesPage,
  BilheteriaSaleFormPage
};
