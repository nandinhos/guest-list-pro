import { test, expect, Page, ConsoleMessage } from '@playwright/test';
import { LoginPage } from './pages/LoginPage';
import * as fs from 'fs';
import * as path from 'path';

const TEST_USER = { email: 'admin@guestlist.pro', password: 'password' };
const SCREENSHOT_DIR = 'docs/report_e2e/screenshots/admin';

const consoleErrors: { page: string; error: string }[] = [];
const navigationErrors: string[] = [];

async function capturePageScreenshot(page: Page, pageName: string, step: string) {
  const filename = `${SCREENSHOT_DIR}/${pageName.toLowerCase().replace(/\s+/g, '-')}-${step.toLowerCase().replace(/\s+/g, '-')}.png`;
  await page.screenshot({ path: filename, fullPage: true });
  console.log(`📸 Screenshot: ${filename}`);
  return filename;
}

async function setupPageListeners(page: Page, pageName: string) {
  page.on('console', (msg: ConsoleMessage) => {
    if (msg.type() === 'error') {
      consoleErrors.push({ page: pageName, error: msg.text() });
    }
  });
  page.on('pageerror', (err: Error) => {
    navigationErrors.push(`[${pageName}] Page error: ${err.message}`);
  });
}

test.describe('🔍 E2E Full Admin Panel Audit', () => {

  test.beforeAll(async () => {
    if (!fs.existsSync(SCREENSHOT_DIR)) {
      fs.mkdirSync(SCREENSHOT_DIR, { recursive: true });
    }
  });

  test('AUDIT-001: Login no Admin Panel', async ({ page }) => {
    await setupPageListeners(page, 'Login');
    const loginPage = new LoginPage(page);
    await loginPage.goto();
    await capturePageScreenshot(page, 'Login', 'before-login');
    await loginPage.login(TEST_USER.email, TEST_USER.password);
    await loginPage.expectLoginSuccess();
    await capturePageScreenshot(page, 'Login', 'after-login');
    console.log('✅ Login realizado com sucesso');
  });

  test('AUDIT-002: Dashboard - Visão Geral', async ({ page }) => {
    await setupPageListeners(page, 'Dashboard');
    await page.goto('/admin');
    await page.waitForLoadState('networkidle');
    await page.waitForTimeout(2000);
    await capturePageScreenshot(page, 'Dashboard', 'overview');
    
    // Verificar widgets
    const widgets = await page.locator('.fi-widget, [class*="widget"], section[class]').count();
    console.log(`📊 Widgets encontrados: ${widgets}`);
    
    // Verificar gráfico de timeline
    const timelineChart = await page.locator('canvas, [class*="chart"]').first().isVisible().catch(() => false);
    console.log(`📈 Timeline chart visível: ${timelineChart}`);
    
    // Verificar stats
    const statsWidgets = await page.locator('.filament-stats-overview-widget, [class*="stat"]').count();
    console.log(`📉 Stats widgets: ${statsWidgets}`);
  });

  test('AUDIT-003: Dashboard - Seletor de Evento', async ({ page }) => {
    await setupPageListeners(page, 'Dashboard-EventSelector');
    await page.goto('/admin');
    await page.waitForLoadState('networkidle');
    await capturePageScreenshot(page, 'Dashboard', 'event-selector');
    
    const eventSelector = await page.locator('select[id*="event"], [wire\\:model*="event"]').first();
    if (await eventSelector.isVisible().catch(() => false)) {
      const options = await eventSelector.locator('option').allTextContents();
      console.log(`🎫 Opções de evento: ${options.length}`);
      await capturePageScreenshot(page, 'Dashboard', 'event-selector-open');
    }
  });

  test('AUDIT-004: Eventos - Listagem', async ({ page }) => {
    await setupPageListeners(page, 'Events');
    await page.goto('/admin/events');
    await page.waitForLoadState('networkidle');
    await page.waitForTimeout(1000);
    await capturePageScreenshot(page, 'Events', 'list');
    
    const table = await page.locator('table').isVisible().catch(() => false);
    console.log(`📋 Tabela de eventos visível: ${table}`);
    
    const createBtn = await page.locator('a:has-text("Criar"), [href*="/create"]').first().isVisible().catch(() => false);
    console.log(`➕ Botão criar visível: ${createBtn}`);
  });

  test('AUDIT-005: Eventos - Criar Novo', async ({ page }) => {
    await setupPageListeners(page, 'Events-Create');
    await page.goto('/admin/events');
    await page.waitForLoadState('networkidle');
    await capturePageScreenshot(page, 'Events', 'list-before-create');
    
    const createBtn = page.locator('a:has-text("Criar"), [href*="/events/create"]').first();
    if (await createBtn.isVisible().catch(() => false)) {
      await createBtn.click();
      await page.waitForLoadState('networkidle');
      await capturePageScreenshot(page, 'Events', 'create-form');
      
      const formFields = await page.locator('input, select, textarea').count();
      console.log(`📝 Campos no formulário: ${formFields}`);
    }
  });

  test('AUDIT-006: Setores - Listagem', async ({ page }) => {
    await setupPageListeners(page, 'Sectors');
    await page.goto('/admin/sectors');
    await page.waitForLoadState('networkidle');
    await page.waitForTimeout(1000);
    await capturePageScreenshot(page, 'Sectors', 'list');
    
    const table = await page.locator('table').isVisible().catch(() => false);
    console.log(`📋 Tabela de setores visível: ${table}`);
    
    const rows = await page.locator('table tbody tr').count();
    console.log(`📊 Linhas na tabela: ${rows}`);
  });

  test('AUDIT-007: Setores - Criar Novo', async ({ page }) => {
    await setupPageListeners(page, 'Sectors-Create');
    await page.goto('/admin/sectors');
    await page.waitForLoadState('networkidle');
    
    const createBtn = page.locator('a:has-text("Criar"), [href*="/sectors/create"]').first();
    if (await createBtn.isVisible().catch(() => false)) {
      await createBtn.click();
      await page.waitForLoadState('networkidle');
      await capturePageScreenshot(page, 'Sectors', 'create-form');
      
      const formFields = await page.locator('input, select, textarea').count();
      console.log(`📝 Campos no formulário: ${formFields}`);
    }
  });

  test('AUDIT-008: Tipos de Ingresso - Listagem', async ({ page }) => {
    await setupPageListeners(page, 'TicketTypes');
    await page.goto('/admin/ticket-types');
    await page.waitForLoadState('networkidle');
    await page.waitForTimeout(1000);
    await capturePageScreenshot(page, 'TicketTypes', 'list');
    
    const table = await page.locator('table').isVisible().catch(() => false);
    console.log(`📋 Tabela de tipos de ingresso visível: ${table}`);
    
    const rows = await page.locator('table tbody tr').count();
    console.log(`📊 Linhas na tabela: ${rows}`);
  });

  test('AUDIT-009: Tipos de Ingresso - Criar Novo', async ({ page }) => {
    await setupPageListeners(page, 'TicketTypes-Create');
    await page.goto('/admin/ticket-types');
    await page.waitForLoadState('networkidle');
    
    const createBtn = page.locator('a:has-text("Criar"), [href*="/ticket-types/create"]').first();
    if (await createBtn.isVisible().catch(() => false)) {
      await createBtn.click();
      await page.waitForLoadState('networkidle');
      await capturePageScreenshot(page, 'TicketTypes', 'create-form');
      
      const formFields = await page.locator('input, select, textarea').count();
      console.log(`📝 Campos no formulário: ${formFields}`);
    }
  });

  test('AUDIT-010: Convidados - Listagem', async ({ page }) => {
    await setupPageListeners(page, 'Guests');
    await page.goto('/admin/guests');
    await page.waitForLoadState('networkidle');
    await page.waitForTimeout(1000);
    await capturePageScreenshot(page, 'Guests', 'list');
    
    const table = await page.locator('table').isVisible().catch(() => false);
    console.log(`📋 Tabela de convidados visível: ${table}`);
    
    const rows = await page.locator('table tbody tr').count();
    console.log(`📊 Linhas na tabela: ${rows}`);
    
    const searchInput = await page.locator('input[placeholder*="buscar"], input[type="search"]').first().isVisible().catch(() => false);
    console.log(`🔍 Campo de busca visível: ${searchInput}`);
  });

  test('AUDIT-011: Convidados - Busca', async ({ page }) => {
    await setupPageListeners(page, 'Guests-Search');
    await page.goto('/admin/guests');
    await page.waitForLoadState('networkidle');
    
    const searchInput = page.locator('input[placeholder*="buscar"], input[type="search"]').first();
    if (await searchInput.isVisible().catch(() => false)) {
      await searchInput.fill('Ana');
      await page.waitForTimeout(1500);
      await capturePageScreenshot(page, 'Guests', 'search-results');
      
      const rows = await page.locator('table tbody tr').count();
      console.log(`🔍 Resultados da busca: ${rows} linhas`);
    }
  });

  test('AUDIT-012: Convidados - Filtros', async ({ page }) => {
    await setupPageListeners(page, 'Guests-Filters');
    await page.goto('/admin/guests');
    await page.waitForLoadState('networkidle');
    await capturePageScreenshot(page, 'Guests', 'filters');
    
    const filters = await page.locator('select').count();
    console.log(`🎛️ Filtros disponíveis: ${filters}`);
  });

  test('AUDIT-013: Solicitações de Aprovação - Listagem', async ({ page }) => {
    await setupPageListeners(page, 'Approvals');
    await page.goto('/admin/approval-requests');
    await page.waitForLoadState('networkidle');
    await page.waitForTimeout(1000);
    await capturePageScreenshot(page, 'Approvals', 'list');
    
    const table = await page.locator('table').isVisible().catch(() => false);
    console.log(`📋 Tabela de aprovações visível: ${table}`);
    
    const pendingCount = await page.locator('[class*="pending"]').count();
    console.log(`⏳ Itens pendentes: ${pendingCount}`);
  });

  test('AUDIT-014: Solicitações de Aprovação - Aprovar/Rejeitar', async ({ page }) => {
    await setupPageListeners(page, 'Approvals-Action');
    await page.goto('/admin/approval-requests');
    await page.waitForLoadState('networkidle');
    
    const approveBtn = page.locator('button:has-text("Aprovar"), button:has-text("Approve")').first();
    const rejectBtn = page.locator('button:has-text("Rejeitar"), button:has-text("Reject")').first();
    
    const hasApprove = await approveBtn.isVisible().catch(() => false);
    const hasReject = await rejectBtn.isVisible().catch(() => false);
    
    console.log(`✅ Botão Aprovar visível: ${hasApprove}`);
    console.log(`❌ Botão Rejeitar visível: ${hasReject}`);
    
    if (hasApprove) {
      await capturePageScreenshot(page, 'Approvals', 'action-buttons');
    }
  });

  test('AUDIT-015: Usuários - Listagem', async ({ page }) => {
    await setupPageListeners(page, 'Users');
    await page.goto('/admin/users');
    await page.waitForLoadState('networkidle');
    await page.waitForTimeout(1000);
    await capturePageScreenshot(page, 'Users', 'list');
    
    const table = await page.locator('table').isVisible().catch(() => false);
    console.log(`📋 Tabela de usuários visível: ${table}`);
    
    const rows = await page.locator('table tbody tr').count();
    console.log(`👥 Usuários na lista: ${rows}`);
  });

  test('AUDIT-016: Usuários - Criar Novo', async ({ page }) => {
    await setupPageListeners(page, 'Users-Create');
    await page.goto('/admin/users');
    await page.waitForLoadState('networkidle');
    
    const createBtn = page.locator('a:has-text("Criar"), [href*="/users/create"]').first();
    if (await createBtn.isVisible().catch(() => false)) {
      await createBtn.click();
      await page.waitForLoadState('networkidle');
      await capturePageScreenshot(page, 'Users', 'create-form');
      
      const formFields = await page.locator('input, select, textarea').count();
      console.log(`📝 Campos no formulário: ${formFields}`);
    }
  });

  test('AUDIT-017: Permissões de Promoters - Listagem', async ({ page }) => {
    await setupPageListeners(page, 'PromoterPermissions');
    await page.goto('/admin/promoter-permissions');
    await page.waitForLoadState('networkidle');
    await page.waitForTimeout(1000);
    await capturePageScreenshot(page, 'PromoterPermissions', 'list');
    
    const table = await page.locator('table').isVisible().catch(() => false);
    console.log(`📋 Tabela de permissões visível: ${table}`);
  });

  test('AUDIT-018: Auditoria - Listagem', async ({ page }) => {
    await setupPageListeners(page, 'Audit');
    await page.goto('/admin/audit-logs');
    await page.waitForLoadState('networkidle');
    await page.waitForTimeout(1000);
    await capturePageScreenshot(page, 'Audit', 'list');
    
    const table = await page.locator('table').isVisible().catch(() => false);
    console.log(`📋 Tabela de auditoria visível: ${table}`);
    
    const rows = await page.locator('table tbody tr').count();
    console.log(`📊 Registros de auditoria: ${rows}`);
  });

  test('AUDIT-019: Sidebar - Navegação', async ({ page }) => {
    await setupPageListeners(page, 'Sidebar');
    await page.goto('/admin');
    await page.waitForLoadState('networkidle');
    await capturePageScreenshot(page, 'Sidebar', 'full');
    
    const menuItems = await page.locator('aside a, nav a, [class*="sidebar"] a').count();
    console.log(`📑 Itens de menu: ${menuItems}`);
    
    // Verificar cada link
    const links = await page.locator('aside a, nav a, [class*="sidebar"] a').allTextContents();
    console.log(`📑 Links encontrados: ${links.slice(0, 10).join(', ')}...`);
  });

  test('AUDIT-020: Verificação de Permissões - Accesso Negado', async ({ page }) => {
    await setupPageListeners(page, 'Permissions-Denied');
    
    // Tentar acessar página que não deveria (simulando usuário sem permissão)
    await page.goto('/admin/bilheteria');
    await page.waitForLoadState('networkidle');
    await page.waitForTimeout(1000);
    
    const accessDenied = await page.locator('text=Accesso Negado, text=Acesso Negado, text=Unauthorized, text=403').isVisible().catch(() => false);
    const notFound = await page.locator('text=404, text=Página não encontrada').isVisible().catch(() => false);
    
    console.log(`🚫 Accesso Negado visível: ${accessDenied}`);
    console.log(`❓ 404 visível: ${notFound}`);
    
    if (accessDenied || notFound) {
      await capturePageScreenshot(page, 'Permissions', 'access-denied');
    }
  });

  test.afterAll(async () => {
    console.log('\n=== 📊 RESUMO DO AUDIT ===');
    console.log(`❌ Console Errors: ${consoleErrors.length}`);
    consoleErrors.forEach(e => console.log(`  - [${e.page}] ${e.error}`));
    console.log(`❌ Navigation Errors: ${navigationErrors.length}`);
    navigationErrors.forEach(e => console.log(`  - ${e}`));
    
    // Salvar relatório
    const report = {
      timestamp: new Date().toISOString(),
      consoleErrors,
      navigationErrors,
      screenshotDir: SCREENSHOT_DIR
    };
    
    fs.writeFileSync(
      'docs/report_e2e/audit-errors.json',
      JSON.stringify(report, null, 2)
    );
    console.log('\n📄 Relatório de erros salvo em docs/report_e2e/audit-errors.json');
  });
});