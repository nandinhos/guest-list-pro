import { Page, Locator, expect } from '@playwright/test';

export class AdminDashboardPage {
  readonly page: Page;
  readonly sidebar: Locator;
  readonly dashboardHeading: Locator;
  readonly eventSelector: Locator;
  readonly widgets: Locator;

  constructor(page: Page) {
    this.page = page;
    this.sidebar = page.locator('aside, nav.sidebar, [class*="sidebar"]');
    this.dashboardHeading = page.locator('h1, h2, text=Dashboard').first();
    this.eventSelector = page.locator('select[id*="event"], [wire\\:model*="event"]').first();
    this.widgets = page.locator('[class*="widget"], .fi-widget');
  }

  async goto() {
    await this.page.goto('/admin');
    await this.page.waitForLoadState('networkidle');
  }

  async selectEvent(eventName: string) {
    if (await this.eventSelector.isVisible()) {
      await this.eventSelector.selectOption({ label: eventName });
      await this.page.waitForTimeout(1000);
    }
  }

  async expectToBeOnAdminDashboard() {
    await expect(this.page).toHaveURL(/\/admin/);
  }

  async getWidgetCount(): Promise<number> {
    return await this.widgets.count();
  }

  async getPageTitle(): Promise<string> {
    return await this.page.title();
  }
}

export class AdminGuestsPage {
  readonly page: Page;
  readonly table: Locator;
  readonly createButton: Locator;
  readonly searchInput: Locator;
  readonly filterSelects: Locator;

  constructor(page: Page) {
    this.page = page;
    this.table = page.locator('table');
    this.createButton = page.locator('a:has-text("Criar"), button:has-text("Novo"), [href*="/create"]');
    this.searchInput = page.locator('input[placeholder*="buscar"], input[placeholder*="search"], input[type="search"]');
    this.filterSelects = page.locator('select');
  }

  async goto() {
    await this.page.goto('/admin/guests');
    await this.page.waitForLoadState('networkidle');
  }

  async searchGuest(name: string) {
    await this.searchInput.fill(name);
    await this.page.waitForTimeout(500);
  }

  async expectTableToBeVisible() {
    await expect(this.table).toBeVisible({ timeout: 10000 });
  }

  async getGuestRow(name: string): Promise<Locator> {
    return this.page.locator(`table >> text=${name}`).first();
  }

  async clickGuest(name: string) {
    const row = await this.getGuestRow(name);
    await row.click();
  }

  async getTableRows(): Promise<Locator[]> {
    return await this.page.locator('table tbody tr').all();
  }
}

export class AdminEventsPage {
  readonly page: Page;
  readonly createButton: Locator;
  readonly table: Locator;

  constructor(page: Page) {
    this.page = page;
    this.createButton = page.locator('a:has-text("Criar Evento"), [href*="/events/create"]');
    this.table = page.locator('table');
  }

  async goto() {
    await this.page.goto('/admin/events');
    await this.page.waitForLoadState('networkidle');
  }

  async expectTableToBeVisible() {
    await expect(this.table).toBeVisible({ timeout: 10000 });
  }
}

export class AdminSectorsPage {
  readonly page: Page;
  readonly table: Locator;

  constructor(page: Page) {
    this.page = page;
    this.table = page.locator('table');
  }

  async goto() {
    await this.page.goto('/admin/sectors');
    await this.page.waitForLoadState('networkidle');
  }
}

export class AdminApprovalsPage {
  readonly page: Page;
  readonly pendingApprovals: Locator;
  readonly table: Locator;

  constructor(page: Page) {
    this.page = page;
    this.pendingApprovals = page.locator('[class*="pending"], [class*="approval"]');
    this.table = page.locator('table');
  }

  async goto() {
    await this.page.goto('/admin/approval-requests');
    await this.page.waitForLoadState('networkidle');
  }

  async getPendingCount(): Promise<number> {
    return await this.pendingApprovals.count();
  }
}

export default {
  AdminDashboardPage,
  AdminGuestsPage,
  AdminEventsPage,
  AdminSectorsPage,
  AdminApprovalsPage
};
