import { Page, expect } from '@playwright/test';
import { WAIT_TIMES } from '../config/wait-times';
import { waitForLivewireLoad, waitForLivewireResponse } from '../helpers/livewire-helpers';

export class SelectEventPage {
  readonly page: Page;
  readonly eventCards: ReturnType<Page['locator']>;
  readonly noEventsMessage: ReturnType<Page['locator']>;

  constructor(page: Page) {
    this.page = page;
    this.eventCards = page.locator('button:has-text("Festival Teste"), [class*="event"]:has(button), button[class*="event"]');
    this.noEventsMessage = page.locator('text=Nenhum evento disponível');
  }

  async goto(panel: 'validator' | 'promoter' | 'bilheteria') {
    await this.page.goto(`/${panel}`);
    await this.page.waitForLoadState('networkidle');
    await waitForLivewireLoad(this.page);
  }

  async isOnSelectEventPage(): Promise<boolean> {
    return await this.eventCards.count() > 0 || await this.noEventsMessage.isVisible();
  }

  async selectFirstEvent(): Promise<void> {
    const count = await this.eventCards.count();
    if (count === 0) {
      throw new Error('No event cards found to select');
    }
    await this.eventCards.first().click();
    await waitForLivewireResponse(this.page);
    await this.page.waitForLoadState('networkidle');
  }

  async expectNoEventsAvailable(): Promise<void> {
    await expect(this.noEventsMessage).toBeVisible({ timeout: WAIT_TIMES.ELEMENT_VISIBLE });
  }
}
