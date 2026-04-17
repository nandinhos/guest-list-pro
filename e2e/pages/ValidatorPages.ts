import { Page, Locator, expect } from '@playwright/test';

export class ValidatorDashboardPage {
  readonly page: Page;
  readonly guestList: Locator;
  readonly searchInput: Locator;
  readonly checkinButton: Locator;
  readonly statsWidget: Locator;

  constructor(page: Page) {
    this.page = page;
    this.guestList = page.locator('table');
    this.searchInput = page.locator('input[placeholder*="buscar"], input[placeholder*="nome"], input[type="search"]');
    this.checkinButton = page.locator('button:has-text("ENTRADA"), button:has-text("Check-in")');
    this.statsWidget = page.locator('[class*="stat"], [class*="widget"]');
  }

  async goto() {
    await this.page.goto('/validator');
    await this.page.waitForLoadState('networkidle');
  }

  async expectToBeOnValidatorDashboard() {
    await expect(this.page).toHaveURL(/\/validator/);
  }

  async searchGuest(nameOrDocument: string) {
    await this.searchInput.fill(nameOrDocument);
    await this.page.waitForTimeout(1000);
  }

  async performCheckin(guestName: string) {
    await this.searchGuest(guestName);
    const row = this.page.locator(`table >> text=${guestName}`).first();
    if (await row.isVisible({ timeout: 3000 })) {
      await row.click();
      const checkinBtn = this.checkinButton.first();
      if (await checkinBtn.isVisible()) {
        await checkinBtn.click();
        await this.page.waitForTimeout(1000);
      }
    }
  }
}

export class ValidatorGuestListPage {
  readonly page: Page;
  readonly table: Locator;
  readonly searchInput: Locator;
  readonly statusFilters: Locator;
  readonly guestRows: Locator;

  constructor(page: Page) {
    this.page = page;
    this.table = page.locator('table');
    this.searchInput = page.locator('input[placeholder*="buscar"], input[placeholder*="nome"], input[placeholder*="documento"]');
    this.statusFilters = page.locator('select');
    this.guestRows = page.locator('table tbody tr');
  }

  async goto() {
    await this.page.goto('/validator/guests');
    await this.page.waitForLoadState('networkidle');
  }

  async getGuestCount(): Promise<number> {
    return await this.guestRows.count();
  }

  async filterByStatus(status: 'checked_in' | 'pending' | 'all') {
    const statusFilter = this.page.locator('select:has-text("Status"), select:has-text("check-in")');
    if (await statusFilter.isVisible()) {
      await statusFilter.selectOption(status);
      await this.page.waitForTimeout(500);
    }
  }
}

export class ValidatorEmergencyRequestPage {
  readonly page: Page;
  readonly emergencyButton: Locator;
  readonly form: Locator;
  readonly nameInput: Locator;
  readonly documentInput: Locator;
  readonly submitButton: Locator;

  constructor(page: Page) {
    this.page = page;
    this.emergencyButton = page.locator('button:has-text("Não está na lista"), a:has-text("Emergência")');
    this.form = page.locator('form, [class*="form"]');
    this.nameInput = page.locator('input[name*="name"], input[id*="name"]');
    this.documentInput = page.locator('input[name*="document"], input[id*="document"]');
    this.submitButton = page.locator('button:has-text("Enviar"), button:has-text("Solicitar")');
  }

  async goto() {
    await this.page.goto('/validator/guests');
    await this.page.waitForLoadState('networkidle');
  }

  async openEmergencyModal() {
    if (await this.emergencyButton.isVisible()) {
      await this.emergencyButton.click();
      await this.page.waitForTimeout(500);
    }
  }

  async submitEmergencyRequest(data: { name: string; document: string; notes: string }) {
    await this.nameInput.fill(data.name);
    await this.documentInput.fill(data.document);
    await this.submitButton.click();
    await this.page.waitForTimeout(1000);
  }
}

export default {
  ValidatorDashboardPage,
  ValidatorGuestListPage,
  ValidatorEmergencyRequestPage
};
