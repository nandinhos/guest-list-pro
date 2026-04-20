import { Page, Locator, expect } from '@playwright/test';
import { WAIT_TIMES } from '../config/wait-times';
import { waitForLivewireLoad, waitForLivewireResponse, selectOptionAndWait } from '../helpers/livewire-helpers';

export class BilheteriaDashboardPage {
  readonly page: Page;
  readonly statsWidgets: Locator;
  readonly salesTable: Locator;
  readonly createSaleButton: Locator;
  readonly eventCards: ReturnType<Page['locator']>;
  readonly selectEventHeading: Locator;

  constructor(page: Page) {
    this.page = page;
    this.statsWidgets = page.locator('.fi-widgets-widget, [class*="stats"], .filament-stats-overview-widget');
    this.salesTable = page.locator('table');
    this.createSaleButton = page.locator('a:has-text("Nova Venda"), button:has-text("Vender")');
    this.eventCards = page.locator('button:has-text("Festival")');
    this.selectEventHeading = page.locator('text="Selecionar Evento"');
  }

  async goto() {
    await this.page.goto('/bilheteria');
    await this.page.waitForLoadState('networkidle');
    await waitForLivewireLoad(this.page);

    await this.page.waitForTimeout(1500);

    const eventOverlayVisible = await this.selectEventHeading.isVisible().catch(() => false);
    if (eventOverlayVisible) {
      const eventButton = this.page.locator('button:has-text("Festival"), [class*="event-card"]').first();
      const buttonExists = await eventButton.isVisible({ timeout: 2000 }).catch(() => false);
      if (buttonExists) {
        await eventButton.click();
        await waitForLivewireResponse(this.page);
        await this.page.waitForSelector(this.selectEventHeading, { state: 'hidden', timeout: 8000 }).catch(() => {});
        await waitForLivewireLoad(this.page);
      }
    }

    await this.page.waitForTimeout(1000);
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
  readonly eventCards: ReturnType<Page['locator']>;

  constructor(page: Page) {
    this.page = page;
    this.table = page.locator('table');
    this.createButton = page.locator('a:has-text("Nova Venda"), [href*="/ticket-sales/create"]');
    this.filters = page.locator('select');
    this.eventCards = page.locator('button:has-text("Festival Teste"), [class*="event"]:has(button), button[class*="event"]');
  }

  async goto() {
    await this.page.goto('/bilheteria/ticket-sales');
    await this.page.waitForLoadState('networkidle');
    await waitForLivewireLoad(this.page);

    await this.page.waitForTimeout(1500);

    const eventOverlayVisible = await this.page.locator('text="Selecionar Evento"').isVisible().catch(() => false);
    if (eventOverlayVisible) {
      const eventButton = this.page.locator('button:has-text("Festival"), [class*="event-card"]').first();
      const buttonExists = await eventButton.isVisible({ timeout: 2000 }).catch(() => false);
      if (buttonExists) {
        await eventButton.click();
        await waitForLivewireResponse(this.page);
        await this.page.waitForSelector(this.page.locator('text="Selecionar Evento"'), { state: 'hidden', timeout: 8000 }).catch(() => {});
        await waitForLivewireLoad(this.page);
      }
    }

    await this.page.waitForTimeout(1000);
  }

  async expectTableToBeVisible() {
    await this.page.waitForTimeout(2000);
    await this.page.waitForLoadState('networkidle');
    await waitForLivewireLoad(this.page);
    await expect(this.table).toBeVisible({ timeout: WAIT_TIMES.ELEMENT_VISIBLE });
  }

  async getSalesCount(): Promise<number> {
    return await this.page.locator('table tbody tr').count();
  }

  async filterByTicketType(ticketType: string) {
    const ticketTypeFilter = this.filters.filter({ hasText: /tipo|ingresso/i }).first();
    if (await ticketTypeFilter.isVisible()) {
      await selectOptionAndWait(this.page, ticketTypeFilter, { label: ticketType });
    }
  }

  async filterByPaymentMethod(method: string) {
    const paymentFilter = this.filters.filter({ hasText: /pagamento|payment/i }).first();
    if (await paymentFilter.isVisible()) {
      await selectOptionAndWait(this.page, paymentFilter, { label: method });
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
    const buyerNameVisible = await this.buyerNameInput.isVisible({ timeout: 3000 }).catch(() => false);
    if (!buyerNameVisible) {
      return;
    }

    await this.buyerNameInput.fill(data.buyerName);
    await this.buyerDocumentInput.fill(data.buyerDocument);

    if (data.ticketType) {
      await selectOptionAndWait(this.page, this.ticketTypeSelect, { label: data.ticketType });
    }

    if (data.sector) {
      await selectOptionAndWait(this.page, this.sectorSelect, { label: data.sector });
    }

    if (data.paymentMethod) {
      await selectOptionAndWait(this.page, this.paymentMethodSelect, { label: data.paymentMethod });
    }
  }

  async submitForm() {
    await this.saveButton.click();
    await this.page.waitForTimeout(WAIT_TIMES.LIVEWIRE_FORM_SUBMIT);
  }

  async expectSaleToBeCreated() {
    await this.page.waitForSelector('[class*="success"], .fi-success, text=Sucesso, text=Venda realizada', { timeout: WAIT_TIMES.ELEMENT_VISIBLE });
  }
}

export default {
  BilheteriaDashboardPage,
  BilheteriaSalesPage,
  BilheteriaSaleFormPage
};
