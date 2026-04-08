import { test, expect } from '@playwright/test';

test.describe('Landing Page', () => {
  test('should display landing page', async ({ page }) => {
    await page.goto('/');
    await expect(page).toHaveTitle(/Guest List Pro/i);
  });

  test('should navigate to login', async ({ page }) => {
    await page.goto('/');
    await page.click('text=Login');
    await expect(page.locator('input[type="email"]')).toBeVisible();
  });
});

test.describe('Authentication', () => {
  test('should login as admin', async ({ page }) => {
    await page.goto('/login');
    await page.fill('input[type="email"]', 'admin@guestlistpro.com');
    await page.fill('input[type="password"]', 'password');
    await page.click('button[type="submit"]');
    
    await expect(page).toHaveURL(/admin/);
  });

  test('should show error with invalid credentials', async ({ page }) => {
    await page.goto('/login');
    await page.fill('input[type="email"]', 'invalid@test.com');
    await page.fill('input[type="password"]', 'wrong');
    await page.click('button[type="submit"]');
    
    await expect(page.locator('text=Credenciais')).toBeVisible();
  });
});

test.describe('Admin Dashboard', () => {
  test.beforeEach(async ({ page }) => {
    await page.goto('/login');
    await page.fill('input[type="email"]', 'admin@guestlistpro.com');
    await page.fill('input[type="password"]', 'password');
    await page.click('button[type="submit"]');
  });

  test('should display admin dashboard', async ({ page }) => {
    await expect(page).toHaveURL(/admin/);
    await expect(page.locator('text=Dashboard')).toBeVisible();
  });

  test('should navigate to events', async ({ page }) => {
    await page.click('text=Eventos');
    await expect(page).toHaveURL(/admin\/events/);
  });

  test('should navigate to guests', async ({ page }) => {
    await page.click('text=Convidados');
    await expect(page).toHaveURL(/admin\/guests/);
  });

  test('should navigate to approval requests', async ({ page }) => {
    await page.click('text=Solicitações');
    await expect(page).toHaveURL(/admin\/approval-requests/);
  });
});

test.describe('Events Management', () => {
  test.beforeEach(async ({ page }) => {
    await page.goto('/login');
    await page.fill('input[type="email"]', 'admin@guestlistpro.com');
    await page.fill('input[type="password"]', 'password');
    await page.click('button[type="submit"]');
  });

  test('should list events', async ({ page }) => {
    await page.click('text=Eventos');
    await expect(page.locator('table')).toBeVisible();
  });

  test('should create new event', async ({ page }) => {
    await page.click('text=Eventos');
    await page.click('text=Novo evento');
    
    await page.fill('input[name="name"]', 'Test Event Playwright');
    await page.fill('input[name="date"]', '2026-12-31');
    await page.fill('input[name="start_time"]', '20:00');
    await page.fill('input[name="end_time"]', '23:59');
    
    await page.click('button:has-text("Salvar")');
    
    await expect(page.locator('text=Test Event Playwright')).toBeVisible();
  });
});

test.describe('Guests Management', () => {
  test.beforeEach(async ({ page }) => {
    await page.goto('/login');
    await page.fill('input[type="email"]', 'admin@guestlistpro.com');
    await page.fill('input[type="password"]', 'password');
    await page.click('button[type="submit"]');
  });

  test('should list guests', async ({ page }) => {
    await page.click('text=Convidados');
    await expect(page.locator('table')).toBeVisible();
  });

  test('should search guests', async ({ page }) => {
    await page.click('text=Convidados');
    await page.fill('input[type="search"]', 'Test');
    await page.press('input[type="search"]', 'Enter');
    
    await page.waitForTimeout(500);
  });

  test('should filter guests by event', async ({ page }) => {
    await page.click('text=Convidados');
    await page.selectOption('select', '1');
    await page.waitForTimeout(500);
  });
});

test.describe('Approval Requests', () => {
  test.beforeEach(async ({ page }) => {
    await page.goto('/login');
    await page.fill('input[type="email"]', 'admin@guestlistpro.com');
    await page.fill('input[type="password"]', 'password');
    await page.click('button[type="submit"]');
  });

  test('should list pending requests', async ({ page }) => {
    await page.click('text=Solicitações');
    await expect(page.locator('table')).toBeVisible();
  });

  test('should filter by status', async ({ page }) => {
    await page.click('text=Solicitações');
    await page.selectOption('select[name="status"]', 'pending');
    await page.waitForTimeout(500);
  });

  test('should approve request', async ({ page }) => {
    await page.click('text=Solicitações');
    await page.waitForTimeout(500);
    
    const approveButton = page.locator('button:has-text("Aprovar")').first();
    if (await approveButton.isVisible()) {
      await approveButton.click();
      await page.click('button:has-text("Confirmar")');
      await expect(page.locator('text=Solicitação aprovada')).toBeVisible();
    }
  });

  test('should reject request', async ({ page }) => {
    await page.click('text=Solicitações');
    await page.waitForTimeout(500);
    
    const rejectButton = page.locator('button:has-text("Rejeitar")').first();
    if (await rejectButton.isVisible()) {
      await rejectButton.click();
      await page.fill('textarea[name="notes"]', 'Rejected via E2E');
      await page.click('button:has-text("Confirmar")');
      await expect(page.locator('text=Solicitação rejeitada')).toBeVisible();
    }
  });
});

test.describe('Check-in Flow', () => {
  test.beforeEach(async ({ page }) => {
    await page.goto('/login');
    await page.fill('input[type="email"]', 'validator@guestlistpro.com');
    await page.fill('input[type="password"]', 'password');
    await page.click('button[type="submit"]');
  });

  test('should access validator panel', async ({ page }) => {
    await expect(page).toHaveURL(/validator/);
    await expect(page.locator('text=Check-in')).toBeVisible();
  });

  test('should scan QR code', async ({ page }) => {
    await page.click('text=Check-in');
    await page.fill('input[name="qr_token"]', 'test-token-123');
    await page.click('button:has-text("Verificar")');
    await page.waitForTimeout(500);
  });
});

test.describe('Responsive Design', () => {
  test('should work on mobile', async ({ page }) => {
    await page.setViewportSize({ width: 375, height: 667 });
    await page.goto('/');
    await expect(page).toHaveTitle(/Guest List Pro/i);
  });

  test('should work on tablet', async ({ page }) => {
    await page.setViewportSize({ width: 768, height: 1024 });
    await page.goto('/');
    await expect(page).toHaveTitle(/Guest List Pro/i);
  });
});
