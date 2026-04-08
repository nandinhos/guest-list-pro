import { test, expect } from '@playwright/test';

test('debug auth', async ({ request }) => {
  const res = await request.get('http://localhost:8200/api/events');
  console.log('Status:', res.status());
  const body = await res.text();
  console.log('Body:', body.substring(0, 500));
});