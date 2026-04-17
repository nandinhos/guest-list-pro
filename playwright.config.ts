import { defineConfig, devices } from '@playwright/test';

export default defineConfig({
  testDir: './e2e',
  fullyParallel: true,
  forbidOnly: !!process.env.CI,
  retries: 0,
  workers: 1,
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
  webServer: {
    command: 'vendor/bin/sail up -d',
    url: 'http://localhost:8888',
    reuseExistingServer: true,
    stdout: 'ignore',
    stderr: 'ignore',
    timeout: 120000,
  },
});
