import { Page, Locator, expect } from '@playwright/test';

export class PromoterDashboardPage {
  readonly page: Page;
  readonly quotaWidget: Locator;
  readonly myGuestsButton: Locator;
  readonly createGuestButton: Locator;

  constructor(page: Page) {
    this.page = page;
    this.quotaWidget = page.locator('[class*="quota"], [class*="widget"]');
    this.myGuestsButton = page.locator('a:has-text("Meus Convidados"), [href*="/guests"]');
    this.createGuestButton = page.locator('a:has-text("Cadastrar"), button:has-text("Novo")');
  }

  async goto() {
    await this.page.goto('/promoter');
    await this.page.waitForLoadState('networkidle');
  }

  async expectToBeOnPromoterDashboard() {
    await expect(this.page).toHaveURL(/\/promoter/);
  }

  async getQuotaInfo(): Promise<{ used: number; total: number }> {
    const quotaText = await this.quotaWidget.textContent();
    const match = quotaText?.match(/(\d+)\s*\/\s*(\d+)/);
    if (match) {
      return { used: parseInt(match[1]), total: parseInt(match[2]) };
    }
    return { used: 0, total: 0 };
  }
}

export class PromoterGuestListPage {
  readonly page: Page;
  readonly table: Locator;
  readonly createButton: Locator;
  readonly searchInput: Locator;

  constructor(page: Page) {
    this.page = page;
    this.table = page.locator('table');
    this.createButton = page.locator('a:has-text("Cadastrar Convidado"), [href*="/create"]');
    this.searchInput = page.locator('input[placeholder*="buscar"], input[placeholder*="nome"]');
  }

  async goto() {
    await this.page.goto('/promoter/guests');
    await this.page.waitForLoadState('networkidle');
  }

  async expectTableToBeVisible() {
    await expect(this.table).toBeVisible({ timeout: 10000 });
  }

  async clickCreateGuest() {
    await this.createButton.click();
    await this.page.waitForTimeout(500);
  }

  async searchGuest(name: string) {
    await this.searchInput.fill(name);
    await this.page.waitForTimeout(500);
  }
}

export class PromoterGuestFormPage {
  readonly page: Page;
  readonly form: Locator;
  readonly nameInput: Locator;
  readonly documentInput: Locator;
  readonly emailInput: Locator;
  readonly sectorSelect: Locator;
  readonly saveButton: Locator;
  readonly plusOneToggle: Locator;

  constructor(page: Page) {
    this.page = page;
    this.form = page.locator('form');
    this.nameInput = page.locator('input[name*="name"], input[id*="name"]');
    this.documentInput = page.locator('input[name*="document"], input[id*="document"]');
    this.emailInput = page.locator('input[name*="email"], input[id*="email"]');
    this.sectorSelect = page.locator('select[name*="sector"], select[id*="sector"]');
    this.saveButton = page.locator('button:has-text("Salvar"), button:has-text("Cadastrar")');
    this.plusOneToggle = page.locator('input[name*="plus_one"], input[id*="plus_one"]');
  }

  async fillGuestForm(data: {
    name: string;
    document: string;
    email?: string;
    sector?: string;
    hasPlusOne?: boolean;
  }) {
    await this.nameInput.fill(data.name);

    if (data.document) {
      await this.documentInput.fill(data.document);
    }

    if (data.email) {
      await this.emailInput.fill(data.email);
    }

    if (data.sector) {
      await this.sectorSelect.selectOption({ label: data.sector });
    }

    if (data.hasPlusOne && await this.plusOneToggle.isVisible()) {
      await this.plusOneToggle.click();
    }
  }

  async submitForm() {
    await this.saveButton.click();
    await this.page.waitForTimeout(1000);
  }

  async expectFormToHaveValidationError() {
    const errorElements = this.page.locator('[class*="error"], [class*="invalid"], .fi-error');
    return await errorElements.count() > 0;
  }
}

export default {
  PromoterDashboardPage,
  PromoterGuestListPage,
  PromoterGuestFormPage
};
