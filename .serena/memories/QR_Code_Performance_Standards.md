# Standard: QR Code Performance & Readability

## Contexto
O sistema de check-in depende da velocidade de leitura de QR Codes em ambientes de eventos (pouca luz, câmeras de celular variadas).

## Lições Aprendidas
1. **ULID sobre UUID**: O uso de ULID (26 caracteres) em vez de UUID (36 caracteres) reduz a densidade dos pontos no QR Code. Isso torna a leitura 30-50% mais rápida em condições adversas.
2. **Formato SVG**: Sempre preferir o download em SVG para permitir escalabilidade sem perda de qualidade na impressão.
3. **Geração no Observer**: Automatizar a geração do `qr_token` (ULID) via `GuestObserver` no evento `saving` garante que convidados importados ou criados manualmente sempre tenham um token válido.
4. **Data Repair**: Sempre que adicionar uma nova coluna essencial (como `qr_token`), executar um script de reparo de dados (via Tinker) para preencher registros legados.

## Configuração Recomendada
```php
// No GuestObserver
$guest->qr_token = $guest->qr_token ?? (string) Str::ulid();

// No Download
QrCode::format('svg')->size(200)->generate($record->qr_token);
```
