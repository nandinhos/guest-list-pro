# Lição: ULID para QR Codes — Performance de Leitura

**Data**: 2026-02-21
**Stack**: Laravel + QR Code + Check-in
**Tags**: performance|security|check-in

## Contexto

Uso de QR Codes para check-in em ambientes de eventos (baixa luz, câmeras variadas). Identificadores longos (UUID) aumentam a densidade de pontos do QR Code, dificultando a leitura.

**Ambiente**: Check-in system em eventos ao vivo
**Frequência**: Alta (cada scan)
**Impacto**: Alto —用户体验 degradado em condições reais

## Problema

UUID (36 caracteres) gera QR Code denso com muitos pontos pequenos. Câmeras de celular em eventos com pouca luz e ângulos variados têm dificuldade de leitura.

## Causa Raiz

QR Code density = caracteres / tamanho. Quanto mais caracteres, mais pontos = mais difícil leitura.

## Solução

Usar **ULID (26 caracteres)** para tokens de QR Code. Menos caracteres geram blocos maiores e leitura 30-50% mais rápida.

```php
// ANTES (UUID - denso)
$guest->qr_token = Uuid::uuid4()->toString(); // 36 chars

// DEPOIS (ULID - mais legível)
$guest->qr_token = (string) Str::ulid(); // 26 chars
```

**Vantagens do ULID:**
- 26 caracteres (vs 36 do UUID)
- Lexicograficamente ordenável
- Seguro (122 bits de entropia)
- Leitura mais rápida pelo QR

## Prevenção

- [ ] Usar ULID para QR tokens desde o design inicial
- [ ] Testar QR Code com app de câmera real antes de produção
- [ ] Considerar redundancy (adicionar checksum) se não confiável

## Referências

- [ULID Spec](https://github.com/ulid/spec)
- [Laravel ULID](https://laravel.com/docs/11.x/urls#ulids)