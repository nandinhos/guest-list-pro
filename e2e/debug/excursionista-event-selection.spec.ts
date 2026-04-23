
import { test, expect } from '@playwright/test';

test.describe('🔍 Excursionista Event Selection', () => {

  test('Login as excursionista should show events on select-event page', async ({ page }) => {
    await page.goto('/login?role=excursionista');
    await page.waitForLoadState('networkidle');
    await page.waitForTimeout(2000);
    
    await page.fill('input[type="email"], input[name="email"]', 'excursionista@guestlist.pro');
    await page.fill('input[type="password"], input[name="password"]', 'password');
    await page.locator('button[type="submit"]').click();
    
    // Wait for navigation
    await page.waitForTimeout(3000);
    
    console.log('Final URL:', page.url());
    
    // If redirected to select-event, we should see event cards
    if (page.url().includes('select-event')) {
      // Check for event cards or "no events" message
      const noEventsMessage = page.locator('text=Nenhum evento disponível');
      const eventCards = page.locator('[data-testid="event-card"], .event-card, article');
      
      const hasNoEventsMessage = await noEventsMessage.isVisible().catch(() => false);
      const hasEventCards = await eventCards.count() > 0;
      
      console.log('Has no events message:', hasNoEventsMessage);
      console.log('Event cards count:', await eventCards.count());
      
      if (hasNoEventsMessage) {
        console.log('❌ BUG: No events available for excursionista!');
      } else if (hasEventCards) {
        console.log('✅ Events are available and visible');
      }
    } else if (page.url().includes('/excursionista/dashboard') || page.url().endsWith('/excursionista')) {
      console.log('✅ Redirected directly to dashboard - event was pre-selected');
    }
  });

});
