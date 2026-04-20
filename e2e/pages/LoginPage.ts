import { Page, Locator, expect } from '@playwright/test';
import { WAIT_TIMES } from '../config/wait-times';
import { waitForLivewireLoad } from '../helpers/livewire-helpers';

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
    this.errorMessage = page.locator('[class*="error"]:visible, [role="alert"]:visible, p.text-red-400');
    this.appLogo = page.locator('header img, .brand-logo, text=Guest List Pro');
  }

  async goto() {
    await this.page.goto('/login');
    await this.page.waitForLoadState('networkidle');
    await waitForLivewireLoad(this.page);
  }

  async login(email: string, password: string) {
    await this.emailInput.fill(email);
    await this.passwordInput.fill(password);
    await this.submitButton.click();
    await waitForLivewireLoad(this.page);
  }

  async expectToBeOnLoginPage() {
    await expect(this.emailInput).toBeVisible();
    await expect(this.passwordInput).toBeVisible();
    await expect(this.submitButton).toBeVisible();
  }

  async expectLoginToFail() {
    await expect(this.errorMessage).toBeVisible({ timeout: WAIT_TIMES.ELEMENT_VISIBLE });
  }

  async expectLoginSuccess() {
    await this.page.waitForURL(/\/(admin|promoter|validator|bilheteria)/, { timeout: WAIT_TIMES.URL_MATCH });
  }

  async logout() {
    await this.page.waitForLoadState('networkidle');
    
    const userMenuTrigger = this.page.locator('.fi-user-menu-trigger, [aria-label*="user"], button:has-text("Admin")');
    if (await userMenuTrigger.isVisible()) {
      await userMenuTrigger.click();
      await this.page.waitForTimeout(500);
    }
    
    const logoutForm = this.page.locator('form[action*="logout"]');
    if (await logoutForm.isVisible()) {
      const logoutButton = logoutForm.locator('button[type="submit"]');
      await logoutButton.click();
    } else {
      const logoutButton = this.page.locator('button:has-text("Sair"), button:has-text("Logout")');
      await logoutButton.click();
    }
    
    await this.page.waitForLoadState('networkidle');
    await this.page.waitForURL(/\/(login|admin\/login|\/)$/, { timeout: WAIT_TIMES.URL_MATCH });
  }

  async getPageTitle(): Promise<string> {
    return await this.page.title();
  }
}
