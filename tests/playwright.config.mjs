import { defineConfig, devices } from '@playwright/test';

export default defineConfig({
  testDir: './browser',
  outputDir: './artifacts',
  timeout: 120_000,
  fullyParallel: false,
  workers: 1,
  use: {
    baseURL: process.env.PF_BASE_URL || 'http://127.0.0.1:9426',
    browserName: 'chromium',
    headless: true,
    screenshot: 'only-on-failure',
    trace: 'retain-on-failure',
    video: 'off',
  },
  projects: [
    { name: 'desktop-1440', use: { ...devices['Desktop Chrome'], viewport: { width: 1440, height: 1000 } } },
    { name: 'tablet-768', use: { ...devices['Desktop Chrome'], viewport: { width: 768, height: 1024 }, isMobile: true } },
    { name: 'mobile-390', use: { ...devices['iPhone 13'], viewport: { width: 390, height: 844 } } },
  ],
  reporter: [['list'], ['json', { outputFile: './artifacts/playwright-report.json' }]],
});
