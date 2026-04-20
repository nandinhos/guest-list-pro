import { Page, Locator, expect } from '@playwright/test';
import { WAIT_TIMES } from '../config/wait-times';
import { waitForLivewireLoad, waitForLivewireResponse } from '../helpers/livewire-helpers';

export class AdminDashboardPage {
  readonly page: Page;
  readonly sidebar: Locator;
  readonly dashboardHeading: Locator;
  readonly eventSelector: Locator;
  readonly widgets: Locator;
  readonly salesTimelineChart: Locator;
  readonly sectorMetricsWidget: Locator;
  readonly ticketTypeWidget: Locator;
  readonly statsWidgets: Locator;

  constructor(page: Page) {
    this.page = page;
    this.sidebar = page.locator('aside, nav.sidebar, [class*="sidebar"]');
    this.dashboardHeading = page.locator('h1, h2, text=Dashboard').first();
    this.eventSelector = page.locator('select[id*="event"], [wire\\:model*="event"]').first();
    this.widgets = page.locator('[class*="widget"], section[class], .fi-widget, .chart-container, [class*="chart"]');
    this.salesTimelineChart = page.locator('[class*="chart"], canvas').first();
    this.sectorMetricsWidget = page.locator('text=Métricas por Setor').first();
    this.ticketTypeWidget = page.locator('text=Relatório por Tipo de Ingresso').first();
    this.statsWidgets = page.locator('[class*="stat"], .fi-stat, .filament-stats-overview-widget');
  }

  async goto() {
    await this.page.goto('/admin');
    await this.page.waitForLoadState('networkidle');
    await waitForLivewireLoad(this.page);
  }

  async expectToBeOnAdminDashboard() {
    await expect(this.page).toHaveURL(/\/admin/);
  }

  async expectSalesTimelineVisible() {
    await expect(this.salesTimelineChart).toBeVisible({ timeout: WAIT_TIMES.ELEMENT_VISIBLE });
  }

  async expectSectorMetricsVisible() {
    await expect(this.sectorMetricsWidget).toBeVisible({ timeout: WAIT_TIMES.ELEMENT_VISIBLE });
  }

  async expectTicketTypeReportVisible() {
    await expect(this.ticketTypeWidget).toBeVisible({ timeout: WAIT_TIMES.ELEMENT_VISIBLE });
  }

  async expectStatsWidgetsVisible() {
    await expect(this.statsWidgets.first()).toBeVisible({ timeout: WAIT_TIMES.ELEMENT_VISIBLE });
  }

  async getWidgetCount(): Promise<number> {
    return await this.widgets.count();
  }

  async getPageTitle(): Promise<string> {
    return await this.page.title();
  }

  async selectEvent(eventName: string) {
    if (await this.eventSelector.isVisible()) {
      await waitForLivewireResponse(this.page);
      await this.eventSelector.selectOption({ label: eventName });
      await waitForLivewireLoad(this.page);
    }
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
    await waitForLivewireLoad(this.page);
  }

  async searchGuest(name: string) {
    await this.searchInput.fill(name);
    await waitForLivewireLoad(this.page);
  }

  async expectTableToBeVisible() {
    await expect(this.table).toBeVisible({ timeout: WAIT_TIMES.ELEMENT_VISIBLE });
  }

  async getGuestRow(name: string): Promise<Locator> {
    return this.page.locator(`table tbody tr:has-text("${name}")`).first();
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
  readonly tableContainer: Locator;
  readonly resourceList: Locator;

  constructor(page: Page) {
    this.page = page;
    this.createButton = page.locator('a:has-text("Criar Evento"), [href*="/events/create"]');
    this.table = page.locator('table');
    this.tableContainer = page.locator('.filament-resources-table-container, [class*="table-container"]');
    this.resourceList = page.locator('.filament-resource-list-page, [class*="resource-list"], .filament-page');
  }

  async goto() {
    await this.page.goto('/admin/events');
    await this.page.waitForLoadState('networkidle');
    await waitForLivewireLoad(this.page);
  }

  async expectToBeOnEventsPage() {
    await expect(this.page).toHaveURL(/\/admin\/events/);
    await expect(this.resourceList.or(this.table.or(this.tableContainer))).toBeVisible({ timeout: WAIT_TIMES.ELEMENT_VISIBLE });
  }

  async expectTableToBeVisible() {
    await expect(this.resourceList.or(this.table.or(this.tableContainer))).toBeVisible({ timeout: WAIT_TIMES.ELEMENT_VISIBLE });
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
    await waitForLivewireLoad(this.page);
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
    await waitForLivewireLoad(this.page);
  }

  async getPendingCount(): Promise<number> {
    return await this.pendingApprovals.count();
  }
}

export class AdminTicketTypesPage {
  readonly page: Page;
  readonly table: Locator;
  readonly createButton: Locator;

  constructor(page: Page) {
    this.page = page;
    this.table = page.locator('table');
    this.createButton = page.locator('a:has-text("Criar"), [href*="/create"]');
  }

  async goto() {
    await this.page.goto('/admin/ticket-type/ticket-types');
    await this.page.waitForLoadState('networkidle');
    await waitForLivewireLoad(this.page);
  }
}

export class AdminAuditPage {
  readonly page: Page;
  readonly table: Locator;

  constructor(page: Page) {
    this.page = page;
    this.table = page.locator('table');
  }

  async goto() {
    await this.page.goto('/admin/audits');
    await this.page.waitForLoadState('networkidle');
    await waitForLivewireLoad(this.page);
  }
}

export class AdminUsersPage {
  readonly page: Page;
  readonly table: Locator;
  readonly createButton: Locator;

  constructor(page: Page) {
    this.page = page;
    this.table = page.locator('table');
    this.createButton = page.locator('a:has-text("Criar"), [href*="/create"]');
  }

  async goto() {
    await this.page.goto('/admin/users');
    await this.page.waitForLoadState('networkidle');
    await waitForLivewireLoad(this.page);
  }
}

export class AdminPromoterPermissionsPage {
  readonly page: Page;
  readonly table: Locator;
  readonly createButton: Locator;

  constructor(page: Page) {
    this.page = page;
    this.table = page.locator('table');
    this.createButton = page.locator('a:has-text("Criar"), [href*="/create"]');
  }

  async goto() {
    await this.page.goto('/admin/promoter-permissions');
    await this.page.waitForLoadState('networkidle');
    await waitForLivewireLoad(this.page);
  }
}

export default {
  AdminDashboardPage,
  AdminGuestsPage,
  AdminEventsPage,
  AdminSectorsPage,
  AdminApprovalsPage,
  AdminTicketTypesPage,
  AdminAuditPage,
  AdminUsersPage,
  AdminPromoterPermissionsPage
};
