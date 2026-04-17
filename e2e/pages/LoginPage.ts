import { Page, Locator, expect } from '@playwright/test';

export class LoginPage {
  readonly page: Page;
  readonly emailInput: Locator;
  readonly passwordInput: Locator;
  readonly submitButton: Locator;
  readonly errorMessage: Locator;
  readonly appLogo: Locator;

  constructor(page: Page) {
    this.page = page;
    this.emailInput = page.locator('input[type="email"], input[name="email"]');
    this.passwordInput = page.locator('input[type="password"], input[name="password"]');
    this.submitButton = page.locator('button[type="submit"]');
    this.errorMessage = page.locator('[role="alert"], .error-message, .alert-danger');
    this.appLogo = page.locator('header img, .brand-logo, text=Guest List Pro');
  }

  async goto() {
    await this.page.goto('/login');
    await this.page.waitForLoadState('networkidle');
  }

  async login(email: string, password: string) {
    await this.emailInput.fill(email);
    await this.passwordInput.fill(password);
    await this.submitButton.click();
    await this.page.waitForLoadState('networkidle');
  }

  async expectToBeOnLoginPage() {
    await expect(this.emailInput).toBeVisible();
    await expect(this.passwordInput).toBeVisible();
    await expect(this.submitButton).toBeVisible();
  }

  async expectLoginToFail() {
    await expect(this.errorMessage).toBeVisible({ timeout: 5000 });
  }

  async expectLoginSuccess() {
    await this.page.waitForURL(/\/(admin|promoter|validator|bilheteria)/, { timeout: 10000 });
  }

  async logout() {
    const logoutButton = this.page.locator('button:has-text("Sair"), a:has-text("Logout"), [href="/logout"]');
    if (await logoutButton.isVisible()) {
      await logoutButton.click();
    }
    await this.page.waitForURL(/\/login/, { timeout: 5000 });
  }
}
