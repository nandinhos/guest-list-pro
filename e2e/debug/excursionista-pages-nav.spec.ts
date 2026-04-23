
import { test, expect } from '@playwright/test';

test.describe('🔍 Excursionista Pages Navigation', () => {

  test.beforeEach(async ({ page }) => {
    // Login as excursionista
    await page.goto('/login?role=excursionista');
    await page.waitForLoadState('networkidle');
    await page.waitForTimeout(1500);
    await page.fill('input[type="email"], input[name="email"]', 'excursionista@guestlist.pro');
    await page.fill('input[type="password"], input[name="password"]', 'password');
    await page.locator('button[type="submit"]').click();
    await page.waitForTimeout(3000);
  });

  test('Can navigate to Excursões page', async ({ page }) => {
    // Click on Excursões in sidebar
    await page.click('text=Excursões');
    await page.waitForTimeout(2000);
    
    console.log('Current URL:', page.url());
    
    // Check for error
    const errorText = await page.locator('text=Error').isVisible().catch(() => false);
    const internalError = await page.locator('text=Internal Server Error').isVisible().catch(() => false);
    
    if (internalError) {
      console.log('❌ Internal Server Error found');
      throw new Error('Internal Server Error on Excursões page');
    }
    
    expect(page.url()).toContain('/excursaos');
    console.log('✅ Excursões page loaded successfully');
  });

  test('Can navigate to Monitores page', async ({ page }) => {
    // Click on Monitores in sidebar
    await page.click('text=Monitores');
    await page.waitForTimeout(2000);
    
    console.log('Current URL:', page.url());
    
    // Check for error
    const internalError = await page.locator('text=Internal Server Error').isVisible().catch(() => false);
    
    if (internalError) {
      console.log('❌ Internal Server Error found');
      throw new Error('Internal Server Error on Monitores page');
    }
    
    expect(page.url()).toContain('/monitores');
    console.log('✅ Monitores page loaded successfully');
  });

});
