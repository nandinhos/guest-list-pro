export const WAIT_TIMES = {
  // Livewire/Network waits (after async operations)
  LIVEWIRE_NETWORK: 500,
  LIVEWIRE_FORM_SUBMIT: 1500,
  LIVEWIRE_SEARCH: 800,

  // Page navigation waits
  NAVIGATION: 1000,
  URL_MATCH: 10000,

  // Element visibility waits
  ELEMENT_VISIBLE: 10000,
  MODAL_OPEN: 500,

  // Legacy (evitar, usar preferencialmente os de cima)
  ARBITRARY_SHORT: 300,
  ARBITRARY_MEDIUM: 1000,
} as const;

export type WaitTimeKey = keyof typeof WAIT_TIMES;
