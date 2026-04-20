import type { Page } from '@playwright/test';

/**
 * Wait for Livewire loading indicators to disappear
 */
export async function waitForLivewireLoad(page: Page): Promise<void> {
  await page.waitForLoadState('networkidle');

  try {
    await page.waitForFunction(
      () => {
        const loadingElements = document.querySelectorAll('[wire\\:loading]');
        return Array.from(loadingElements).every(
          (el) => window.getComputedStyle(el).display === 'none' || window.getComputedStyle(el).visibility === 'hidden'
        );
      },
      { timeout: 10000 }
    );
  } catch {
    // If no loading elements found, continue
  }
}

/**
 * Wait for a specific Livewire response
 */
export async function waitForLivewireResponse(page: Page): Promise<void> {
  const [response] = await Promise.all([
    page.waitForResponse(
      (resp) => resp.url().includes('/livewire/') || resp.url().includes('/__livewire/'),
      { timeout: 10000 }
    ),
  ].filter(Boolean));

  if (response) {
    await response.finished();
  }

  await page.waitForLoadState('networkidle');
}

/**
 * Wait for a widget to be fully rendered
 */
export async function waitForWidget(page: Page, widgetSelector: string): Promise<void> {
  await page.waitForSelector(widgetSelector, { state: 'visible', timeout: 10000 });

  try {
    await page.waitForFunction(
      (sel) => {
        const widget = document.querySelector(sel);
        return widget && !widget.getAttribute('wire:loading');
      },
      widgetSelector,
      { timeout: 10000 }
    );
  } catch {
    // Widget may not have wire:loading attribute, continue
  }
}

/**
 * Fill an input and wait for Livewire to process
 */
export async function fillAndWaitForLivewire(
  page: Page,
  input: Parameters<Page['locator']>[0],
  value: string
): Promise<void> {
  const locator = page.locator(input);
  await locator.fill(value);
  await waitForLivewireLoad(page);
}

/**
 * Select an option and wait for Livewire to process
 */
export async function selectOptionAndWait(
  page: Page,
  select: Parameters<Page['locator']>[0],
  value: string
): Promise<void> {
  const [response] = await Promise.all([
    page.waitForResponse(
      (resp) => resp.url().includes('/livewire/') || resp.url().includes('/__livewire/'),
      { timeout: 10000 }
    ),
    page.locator(select).selectOption(value),
  ]).catch(() => [null]);

  if (response && 'finished' in response) {
    await response.finished().catch(() => {});
  }

  await page.waitForLoadState('networkidle');
}
