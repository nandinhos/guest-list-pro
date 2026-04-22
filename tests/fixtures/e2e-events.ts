/**
 * E2E Test Event Names — Sincronizados com E2ETestSeeder.php
 *
 * @see E2ETestSeeder.php — toda alteracao de nome de evento aqui
 *                             precisa ser refletida neste arquivo.
 * @see ValidatorPages.ts — locators usam estas constantes.
 * @see P5 (DEVORQ review 2026-04-21): Hardcoded strings removidas.
 */

export const E2E_EVENTS = {
  /** Evento principal de teste E2E */
  E2E_TEST: 'E2E Test Event',

  /** Festival de teste */
  FESTIVAL: 'Festival Teste 2026',
} as const;

export type E2eEventName = typeof E2E_EVENTS[keyof typeof E2E_EVENTS];

/**
 * partialMatch de nomes para locators mais resilientes.
 * Usa substring para tolerar variacoes menores de nome.
 */
export const E2E_EVENT_PARTIALS = {
  FESTIVAL: 'Festival',
} as const;
