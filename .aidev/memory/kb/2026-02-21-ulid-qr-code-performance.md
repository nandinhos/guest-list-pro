# Licao: Otimizacao de Performance de Leitura de QR Codes com ULID

**Data**: 2026-02-21
**Stack**: Laravel 12 + simple-qrcode
**Tags**: [success-pattern, performance, qr-code, ux]

## Contexto
Implementacao de sistema de check-in para eventos onde a velocidade de leitura em celulares e condicoes de luz variaveis e critica.

## Problema
QR Codes gerados com identificadores longos (ex: UUID de 36 chars ou strings complexas) resultam em maior densidade de pontos (pixels), tornando a leitura mais dificil para cameras inferiores ou em ambientes escuros.

## Causa Raiz
### Analise (5 Whys)
1. **Por que a leitura falha?** A camera nao consegue focar nos pontos pequenos.
2. **Por que os pontos sao pequenos?** Porque a densidade do QR Code e alta.
3. **Por que a densidade e alta?** Porque a string de dados e longa.
4. **Por que a string e longa?** Uso de UUID ou hashes complexos.
5. **Por que?** Nao houve otimizacao focada na midia de saida (QR Code).

## Solucao
### Correcao Aplicada
Uso de **ULID (26 caracteres)** em vez de UUID.
```php
// No GuestObserver
$guest->qr_token = (string) Str::ulid();
```

### Por Que Funciona
O ULID e menor e lexicograficamente ordenavel. Menos caracteres geram um QR Code com blocos maiores, o que aumenta significativamente a tolerancia a falhas na captura da imagem e acelera o processamento pelo scanner.

## Prevencao
- [ ] Checklist: Sempre preferir identificadores curtos (ULID ou IDs numericos ofuscados) para QR Codes.
- [ ] Usar formato SVG para download para manter nitidez.

## Referencias
- Laravel Docs: Generating ULIDs
- simple-qrcode package
