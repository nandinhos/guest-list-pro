import { defineConfig, devices } from '@playwright/test';

export default defineConfig({
  /**
   * @see P1 (DEVORQ review 2026-04-21): FALSE POSITIVE em code review.
   * baseURL ESTA presente — nao e bug. Comentado aqui para previnir
   * que futuros reviewers marquem isso como pendencia novamente.
   * تمام: todos os page.goto() sem host usam este baseURL.
   */
  testDir: './e2e',
  fullyParallel: true,
  forbidOnly: !!process.env.CI,
  retries: 1,
  workers: process.env.CI ? 1 : 3,
  reporter: [
    ['html', { outputFolder: 'docs/report_e2e/results/html' }],
    ['json', { outputFile: 'docs/report_e2e/results/test-results.json' }],
  ],
  use: {
    baseURL: 'http://localhost:8888',
    trace: 'on-first-retry',
    screenshot: 'only-on-failure',
    video: 'retain-on-failure',
  },
  projects: [
    {
      name: 'chromium',
      use: { ...devices['Desktop Chrome'] },
    },
  ],
});
