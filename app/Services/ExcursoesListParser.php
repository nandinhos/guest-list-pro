<?php

namespace App\Services;

use App\Enums\DocumentType;
use App\Enums\TipoVeiculo;

class ExcursoesListParser
{
    public function parse(string $raw): array
    {
        $blocks = $this->splitIntoBlocks($raw);
        $entries = [];
        $seenDocuments = [];

        foreach ($blocks as $block) {
            $entry = $this->parseBlock($block);
            if ($entry === null || $entry['document_number'] === null) {
                continue;
            }

            if (isset($seenDocuments[$entry['document_number']])) {
                continue;
            }
            $seenDocuments[$entry['document_number']] = true;

            $entries[] = $entry;
        }

        return $entries;
    }

    public function report(array $entries): array
    {
        $excursoes = [];
        $vehiclesByType = [];
        $noVehicleType = 0;
        $monitorCount = count($entries);

        foreach ($entries as $entry) {
            if ($entry['excursao'] !== null) {
                $normalized = mb_strtolower(trim($entry['excursao']));
                $excursoes[$normalized] = $entry['excursao'];
            }

            if ($entry['vehicle_type'] !== null) {
                $type = $entry['vehicle_type']->value;
                $vehiclesByType[$type] = ($vehiclesByType[$type] ?? 0) + 1;
            } else {
                $noVehicleType++;
            }
        }

        return [
            'total_entries' => $monitorCount,
            'excursoes_total' => count($excursoes),
            'excursoes' => array_values($excursoes),
            'vehicles_by_type' => $vehiclesByType,
            'vehicles_no_type' => $noVehicleType,
            'monitors_total' => $monitorCount,
        ];
    }

    private function splitIntoBlocks(string $raw): array
    {
        $blocks = preg_split('/\n\s*\n/', $raw);

        return array_values(array_filter($blocks, fn ($b) => trim($b) !== ''));
    }

    private function parseBlock(string $blockText): ?array
    {
        $lines = array_values(array_filter(
            explode("\n", $blockText),
            fn ($l) => trim($l) !== ''
        ));

        if (count($lines) < 2) {
            return null;
        }

        // Find document line
        $docIndex = null;
        $docType = null;
        $docNumber = null;

        foreach ($lines as $i => $line) {
            if (! $this->isDocumentLine(trim($line))) {
                continue;
            }
            $type = DocumentType::detectFromValue(trim($line));
            if ($type !== null) {
                $docIndex = $i;
                $docType = $type;
                $docNumber = DocumentType::normalizeValue(trim($line), $type);
                break;
            }
        }

        if ($docIndex === null || $docNumber === '') {
            return null;
        }

        // Find vehicle type
        $vehicleType = null;
        $vehicleTypeIndex = null;
        foreach ($lines as $i => $line) {
            $vt = $this->detectVehicleType(trim($line));
            if ($vt !== null) {
                $vehicleType = $vt;
                $vehicleTypeIndex = $i;
                break;
            }
        }

        // Find vehicle code (short alphanumeric, no spaces, mixed letters+digits)
        $vehicleCode = null;
        $vehicleCodeIndex = null;
        foreach ($lines as $i => $line) {
            if ($i === $docIndex || $i === $vehicleTypeIndex) {
                continue;
            }
            if ($this->isVehicleCode(trim($line))) {
                $vehicleCode = strtoupper(trim($line));
                $vehicleCodeIndex = $i;
                break;
            }
        }

        // Collect remaining lines (not doc, vehicle type, or vehicle code)
        $usedIndices = array_filter(
            [$docIndex, $vehicleTypeIndex, $vehicleCodeIndex],
            fn ($i) => $i !== null
        );

        $remaining = [];
        foreach ($lines as $i => $line) {
            if (! in_array($i, $usedIndices)) {
                $remaining[] = ['index' => $i, 'line' => trim($line)];
            }
        }

        [$monitorName, $excursaoName] = $this->extractNames($remaining, $lines, $docIndex);

        return [
            'excursao' => $excursaoName,
            'monitor' => $monitorName,
            'document_type' => $docType,
            'document_number' => $docNumber,
            'vehicle_type' => $vehicleType,
            'vehicle_code' => $vehicleCode,
        ];
    }

    /**
     * @param  array<int, array{index: int, line: string}>  $remaining
     * @return array{0: ?string, 1: ?string}
     */
    private function extractNames(array $remaining, array $allLines, int $docIndex): array
    {
        $monitorName = null;
        $excursaoName = null;
        $usedInRemaining = [];

        // Check for lines with "Monitor:", "Monitora:", "Minitor:" (or without colon)
        foreach ($remaining as $rIdx => $r) {
            if ($this->hasMonitorPrefix($r['line'])) {
                $name = $this->stripMonitorPrefix($r['line']);
                $usedInRemaining[] = $rIdx;

                if (trim($name) !== '') {
                    $monitorName = trim($name);
                } else {
                    // Prefix alone on a line — name is on the next remaining line
                    foreach ($remaining as $rIdx2 => $r2) {
                        if ($rIdx2 > $rIdx && ! $this->hasMonitorPrefix($r2['line'])) {
                            $monitorName = $r2['line'];
                            $usedInRemaining[] = $rIdx2;
                            break;
                        }
                    }
                }
                break;
            }
        }

        // Excursão is the first remaining line not consumed by monitor detection
        foreach ($remaining as $rIdx => $r) {
            if (in_array($rIdx, $usedInRemaining)) {
                continue;
            }
            $excursaoName = $r['line'];
            break;
        }

        // If no monitor found via prefix, the line immediately before the document is the monitor
        if ($monitorName === null && $docIndex > 0) {
            $prevLine = trim($allLines[$docIndex - 1] ?? '');
            if ($prevLine !== '' && ! $this->isVehicleType($prevLine) && ! $this->isVehicleCode($prevLine)) {
                foreach ($remaining as $rIdx => $r) {
                    if ($r['index'] === $docIndex - 1) {
                        $monitorName = $r['line'];
                        $usedInRemaining[] = $rIdx;
                        break;
                    }
                }
                // Re-find excursão after monitor was determined
                $excursaoName = null;
                foreach ($remaining as $rIdx => $r) {
                    if (in_array($rIdx, $usedInRemaining)) {
                        continue;
                    }
                    $excursaoName = $r['line'];
                    break;
                }
            }
        }

        return [$monitorName, $excursaoName];
    }

    private function isDocumentLine(string $line): bool
    {
        $line = trim($line);

        // Explicit CPF/RG/Passaporte prefix
        if (preg_match('/^(cpf|rg)\s*:?\s*[\d]/i', $line)) {
            return true;
        }
        if (preg_match('/^passaporte\s+/i', $line)) {
            return true;
        }

        // Pure digits: 7–14 (RG, CPF, CNH)
        if (preg_match('/^\d{7,14}$/', $line)) {
            return true;
        }

        // CPF with punctuation: 000.000.000-00
        if (preg_match('/^\d{3}[.\-]\d{3}[.\-]\d{3}[.\-]\d{2}$/', $line)) {
            return true;
        }

        // Passport: 1-3 uppercase letters followed by 5-10 digits
        if (preg_match('/^[A-Z]{1,3}\d{5,10}$/i', $line)) {
            return true;
        }

        return false;
    }

    private function detectVehicleType(string $line): ?TipoVeiculo
    {
        $lower = mb_strtolower(trim($line));

        return match ($lower) {
            'ônibus', 'onibus' => TipoVeiculo::ONIBUS,
            'microônibus', 'microonibus', 'micro-ônibus', 'micro ônibus' => TipoVeiculo::MICROONIBUS,
            'van' => TipoVeiculo::VAN,
            default => null,
        };
    }

    private function isVehicleType(string $line): bool
    {
        return $this->detectVehicleType($line) !== null;
    }

    private function isVehicleCode(string $line): bool
    {
        $line = trim($line);
        // No spaces, 4-10 chars, alphanumeric only
        if (! preg_match('/^[a-z0-9]{4,10}$/i', $line)) {
            return false;
        }
        // Must contain at least one letter AND one digit
        if (! preg_match('/[a-z]/i', $line) || ! preg_match('/\d/', $line)) {
            return false;
        }

        return ! $this->isVehicleType($line);
    }

    private function hasMonitorPrefix(string $line): bool
    {
        return (bool) preg_match('/^(monitor[ao]?|minitor)\b/i', $line);
    }

    private function stripMonitorPrefix(string $line): string
    {
        return preg_replace('/^(monitor[ao]?|minitor)\s*[:;]?\s*/i', '', $line);
    }
}
