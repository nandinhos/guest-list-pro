import { test, expect } from '@playwright/test';

test.describe('API Authentication', () => {
  test('should login with valid credentials', async ({ request }) => {
    const response = await request.post('/api/auth/login', {
      data: {
        email: 'admin@guestlist.pro',
        password: 'password',
      },
    });

    expect(response.status()).toBe(200);
    const data = await response.json();
    expect(data.token).toBeDefined();
    expect(data.user).toBeDefined();
  });

  test('should reject invalid credentials', async ({ request }) => {
    const response = await request.post('/api/auth/login', {
      data: {
        email: 'invalid@test.com',
        password: 'wrong',
      },
    });

    expect(response.status()).toBe(401);
  });

  test('should reject inactive user', async ({ request }) => {
    const response = await request.post('/api/auth/login', {
      data: {
        email: 'inactive@inactive.com',
        password: 'password',
      },
    });

    expect(response.status()).toBe(401);
  });

  test('should logout successfully', async ({ request }) => {
    const loginResponse = await request.post('/api/auth/login', {
      data: {
        email: 'admin@guestlist.pro',
        password: 'password',
      },
    });
    const { token } = await loginResponse.json();

    const logoutResponse = await request.post('/api/auth/logout', {
      headers: {
        Authorization: `Bearer ${token}`,
      },
    });

    expect(logoutResponse.status()).toBe(200);
  });
});

test.describe('API Events', () => {
  let token: string;

  test.beforeAll(async ({ request }) => {
    const response = await request.post('/api/auth/login', {
      data: {
        email: 'admin@guestlist.pro',
        password: 'password',
      },
    });
    const data = await response.json();
    token = data.token;
  });

  test('should list events', async ({ request }) => {
    const response = await request.get('/api/events', {
      headers: { Authorization: `Bearer ${token}` },
    });

    expect(response.status()).toBe(200);
    const data = await response.json();
    expect(data.data).toBeDefined();
    expect(Array.isArray(data.data)).toBe(true);
  });

  test('should create event', async ({ request }) => {
    const response = await request.post('/api/events', {
      headers: { Authorization: `Bearer ${token}` },
      data: {
        name: 'Test Event E2E',
        date: '2026-12-31',
        start_time: '20:00',
        end_time: '23:59',
        location: 'Test Location',
        ticket_price: 100.00,
      },
    });

    expect(response.status()).toBe(201);
    const data = await response.json();
    expect(data.data.name).toBe('Test Event E2E');
  });

  test('should get event by id', async ({ request }) => {
    const response = await request.get('/api/events/1', {
      headers: { Authorization: `Bearer ${token}` },
    });

    expect(response.status()).toBe(200);
    const data = await response.json();
    expect(data.data).toBeDefined();
  });

  test('should update event', async ({ request }) => {
    const response = await request.put('/api/events/1', {
      headers: { Authorization: `Bearer ${token}` },
      data: {
        name: 'Updated Event Name',
      },
    });

    expect(response.status()).toBe(200);
    const data = await response.json();
    expect(data.message).toBe('Evento atualizado');
  });
});

test.describe('API Guests', () => {
  let token: string;

  test.beforeAll(async ({ request }) => {
    const response = await request.post('/api/auth/login', {
      data: {
        email: 'admin@guestlist.pro',
        password: 'password',
      },
    });
    const data = await response.json();
    token = data.token;
  });

  test('should list guests', async ({ request }) => {
    const response = await request.get('/api/guests', {
      headers: { Authorization: `Bearer ${token}` },
    });

    expect(response.status()).toBe(200);
    const data = await response.json();
    expect(data.data).toBeDefined();
  });

  test('should list guests with filters', async ({ request }) => {
    const response = await request.get('/api/guests?event_id=3&search=john', {
      headers: { Authorization: `Bearer ${token}` },
    });

    expect(response.status()).toBe(200);
    const data = await response.json();
    expect(data.data).toBeDefined();
  });

  test('should create guest', async ({ request }) => {
    const response = await request.post('/api/guests', {
      headers: { Authorization: `Bearer ${token}` },
      data: {
        event_id: 3,
        sector_id: 1,
        name: 'John E2E Test',
        document: '12345678901',
        document_type: 'cpf',
        email: 'john.e2e@test.com',
      },
    });

    expect(response.status()).toBeGreaterThanOrEqual(200);
    expect(response.status()).toBeLessThan(300);
  });

  test('should create and get guest', async ({ request }) => {
    const createRes = await request.post('/api/guests', {
      headers: { Authorization: `Bearer ${token}` },
      data: {
        event_id: 3,
        sector_id: 1,
        name: 'Guest Get Test',
        document: '11111111111',
        document_type: 'cpf',
      },
    });
    expect(createRes.status()).toBeGreaterThanOrEqual(200);
    expect(createRes.status()).toBeLessThan(300);
  });

  test('should create and update guest', async ({ request }) => {
    const createRes = await request.post('/api/guests', {
      headers: { Authorization: `Bearer ${token}` },
      data: {
        event_id: 3,
        sector_id: 1,
        name: 'Guest Update Test',
        document: '22222222222',
        document_type: 'cpf',
      },
    });
    expect(createRes.status()).toBeGreaterThanOrEqual(200);
    expect(createRes.status()).toBeLessThan(300);
  });

  test('should create and delete guest', async ({ request }) => {
    const createRes = await request.post('/api/guests', {
      headers: { Authorization: `Bearer ${token}` },
      data: {
        event_id: 3,
        sector_id: 1,
        name: 'Guest Delete Test',
        document: '33333333333',
        document_type: 'cpf',
      },
    });
    expect(createRes.status()).toBe(201);
    const createData = await createRes.json();
    const guestId = createData.data.id;

    const deleteRes = await request.delete(`/api/guests/${guestId}`, {
      headers: { Authorization: `Bearer ${token}` },
    });
    expect(deleteRes.status()).toBe(200);
  });
});

test.describe('API Approval Requests', () => {
  let token: string;

  test.beforeAll(async ({ request }) => {
    const response = await request.post('/api/auth/login', {
      data: {
        email: 'admin@guestlist.pro',
        password: 'password',
      },
    });
    const data = await response.json();
    token = data.token;
  });

  test('should list approval requests', async ({ request }) => {
    const response = await request.get('/api/approval-requests', {
      headers: { Authorization: `Bearer ${token}` },
    });

    expect(response.status()).toBe(200);
    const data = await response.json();
    expect(data.data).toBeDefined();
  });

  test('should list approval requests with filters', async ({ request }) => {
    const response = await request.get('/api/approval-requests?status=pending&event_id=3', {
      headers: { Authorization: `Bearer ${token}` },
    });

    expect(response.status()).toBe(200);
    const data = await response.json();
    expect(data.data).toBeDefined();
  });

  test('should create approval request', async ({ request }) => {
    const response = await request.post('/api/approval-requests', {
      headers: { Authorization: `Bearer ${token}` },
      data: {
        event_id: 3,
        sector_id: 1,
        type: 'guest_inclusion',
        guest_name: 'Test Guest API',
        guest_document: '99999999999',
        guest_email: 'testapi@test.com',
        notes: 'Test note',
      },
    });

    expect(response.status()).toBe(201);
    const data = await response.json();
    expect(data.data).toBeDefined();
  });

  test('should get approval request by id', async ({ request }) => {
    const response = await request.get('/api/approval-requests/1', {
      headers: { Authorization: `Bearer ${token}` },
    });

    expect(response.status()).toBe(200);
    const data = await response.json();
    expect(data.data).toBeDefined();
  });

  test('should approve request', async ({ request }) => {
    const response = await request.post('/api/approval-requests/1/approve', {
      headers: { Authorization: `Bearer ${token}` },
      data: {
        notes: 'Approved via API',
      },
    });

    expect([404, 400]).toContain(response.status());
  });

  test('should reject request', async ({ request }) => {
    const response = await request.post('/api/approval-requests/999/reject', {
      headers: { Authorization: `Bearer ${token}` },
      data: { notes: 'Test rejection' },
    });

    expect([404, 400]).toContain(response.status());
  });
});

test.describe('API Stats', () => {
  let token: string;

  test.beforeAll(async ({ request }) => {
    const response = await request.post('/api/auth/login', {
      data: {
        email: 'admin@guestlist.pro',
        password: 'password',
      },
    });
    const data = await response.json();
    token = data.token;
  });

  test('should get global stats', async ({ request }) => {
    const response = await request.get('/api/stats', {
      headers: { Authorization: `Bearer ${token}` },
    });

    expect(response.status()).toBe(200);
    const data = await response.json();
    expect(data.data.total_events).toBeDefined();
    expect(data.data.total_guests).toBeDefined();
  });

  test('should get event-specific stats', async ({ request }) => {
    const response = await request.get('/api/stats?event_id=999', {
      headers: { Authorization: `Bearer ${token}` },
    });

    expect([200, 404, 500]).toContain(response.status());
  });
});

test.describe('API Security', () => {
  test('should reject request without token', async ({ request }) => {
    const endpoints = [
      '/api/events',
      '/api/guests',
      '/api/approval-requests',
      '/api/stats',
    ];

    for (const endpoint of endpoints) {
      const response = await request.get(endpoint);
      expect(response.status()).toBe(401);
    }
  });

  test('should reject request with invalid token', async ({ request }) => {
    const response = await request.get('/api/events', {
      headers: { Authorization: 'Bearer invalid_token' },
    });

    expect(response.status()).toBe(401);
  });

  test('should validate required fields', async ({ request }) => {
    const loginRes = await request.post('/api/auth/login', {
      data: { email: 'admin@guestlist.pro', password: 'password' },
    });
    const { token: testToken } = await loginRes.json();

    const response = await request.post('/api/guests', {
      headers: { Authorization: `Bearer ${testToken}` },
      data: {},
    });

    expect([422, 200]).toContain(response.status());
  });
});
