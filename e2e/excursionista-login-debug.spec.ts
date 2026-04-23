import { test, expect, Page } from '@playwright/test';
import * as fs from 'fs';

const SCREENSHOT_DIR = 'docs/report_e2e/screenshots/excursionista-debug';

async function capturePageScreenshot(page: Page, name: string) {
  if (!fs.existsSync(SCREENSHOT_DIR)) {
    fs.mkdirSync(SCREENSHOT_DIR, { recursive: true });
  }
  const filename = `${SCREENSHOT_DIR}/${name}.png`;
  await page.screenshot({ path: filename, fullPage: true });
  console.log(`📸 Screenshot: ${filename}`);
  return filename;
}

test.describe('🔍 Excursionista E2E - Fluxo completo', () => {

  test('Landing page - todos os cards de role visíveis', async ({ page }) => {
    await page.goto('/');
    await page.waitForLoadState('networkidle');
    await capturePageScreenshot(page, 'landing-page');

    const adminCard = page.locator('a[href="/login?role=admin"]');
    const promoterCard = page.locator('a[href="/login?role=promoter"]');
    const validatorCard = page.locator('a[href="/login?role=validator"]');
    const bilheteriaCard = page.locator('a[href="/login?role=bilheteria"]');
    const excursionistaCard = page.locator('a[href="/login?role=excursionista"]');

    await expect(adminCard).toBeVisible();
    await expect(promoterCard).toBeVisible();
    await expect(validatorCard).toBeVisible();
    await expect(bilheteriaCard).toBeVisible();
    await expect(excursionistaCard).toBeVisible();
    console.log('✅ Todos os 5 cards visíveis');
  });

  test('Card Excursionista redireciona para /login?role=excursionista', async ({ page }) => {
    await page.goto('/');
    await page.waitForLoadState('networkidle');

    await page.locator('a[href="/login?role=excursionista"]').click();
    await page.waitForURL('**/login?role=excursionista', { timeout: 10000 });

    expect(page.url()).toContain('/login?role=excursionista');
    console.log('✅ Redirecionou para /login?role=excursionista');
  });

  test('CTA "Começar Agora" redireciona para /login', async ({ page }) => {
    await page.goto('/');
    await page.waitForLoadState('networkidle');

    await page.locator('a[href="/login"]').first().click();
    await page.waitForURL('**/login', { timeout: 10000 });

    expect(page.url()).toContain('/login');
    console.log('✅ Redirecionou para /login');
  });

  test('Login excursionista@guestlist.pro redireciona para /excursionista', async ({ page }) => {
    await page.goto('/login?role=excursionista');
    await page.waitForLoadState('networkidle');
    await page.waitForTimeout(1500); // Livewire init
    await capturePageScreenshot(page, 'login-ready');

    await page.fill('input[type="email"], input[name="email"]', 'excursionista@guestlist.pro');
    await page.fill('input[type="password"], input[name="password"]', 'password');

    await page.locator('button[type="submit"]').click();
    await page.waitForURL('**/excursionista**', { timeout: 15000 });
    await page.waitForLoadState('networkidle');

    expect(page.url()).toContain('/excursionista');
    console.log(`✅ Login funcionou! URL: ${page.url()}`);
  });

  test('Acesso direto a /excursionista sem login redireciona para login', async ({ page }) => {
    await page.goto('/excursionista');
    await page.waitForLoadState('networkidle');
    await page.waitForTimeout(1000);

    expect(page.url()).toContain('/login');
    console.log('✅ Middleware de autenticação funciona');
  });

  test('Portal Excursionista carrega apos login', async ({ page }) => {
    // Login first
    await page.goto('/login');
    await page.waitForLoadState('networkidle');
    await page.waitForTimeout(1500);

    await page.fill('input[type="email"], input[name="email"]', 'excursionista@guestlist.pro');
    await page.fill('input[type="password"], input[name="password"]', 'password');
    await page.locator('button[type="submit"]').click();
    await page.waitForURL('**/excursionista**', { timeout: 15000 });
    await page.waitForLoadState('networkidle');
    await capturePageScreenshot(page, 'portal-excursionista');

    // Verify we're NOT on login page
    expect(page.url()).toContain('/excursionista');
    expect(page.url()).not.toContain('/login');
    console.log('✅ Portal Excursionista carregou corretamente');
  });
});
