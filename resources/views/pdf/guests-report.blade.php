<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Relatório de Cortesias - {{ $eventName }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'DejaVu Sans', sans-serif; font-size: 11px; }
        .container { padding: 20px; }
        .header { text-align: center; margin-bottom: 25px; border-bottom: 2px solid #f97316; padding-bottom: 15px; }
        .header h1 { font-size: 20px; color: #f97316; margin-bottom: 5px; }
        .header h2 { font-size: 14px; color: #666; font-weight: normal; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background: #f8f8f8; font-weight: bold; text-transform: uppercase; font-size: 10px; }
        .text-center { text-align: center; }
        .font-bold { font-weight: bold; }
        .totals-row { background: #fff3e0 !important; font-weight: bold; }
        .footer { margin-top: 30px; font-size: 10px; color: #888; text-align: center; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Relatório de Cortesias</h1>
            <h2>{{ $eventName }} - {{ $eventDate }}</h2>
        </div>

        <table>
            <thead>
                <tr>
                    <th style="width: 30%">Responsável</th>
                    <th class="text-center">🎟 PISTA</th>
                    <th class="text-center">🎭 BACKSTAGE</th>
                    <th class="text-center">TOTAL</th>
                    <th class="text-center">Entregues</th>
                    <th class="text-center">Validados</th>
                </tr>
            </thead>
            <tbody>
                @foreach($data as $row)
                <tr>
                    <td>{{ $row['promoter_name'] }}</td>
                    <td class="text-center">{{ $row['pista_total'] }}</td>
                    <td class="text-center">{{ $row['backstage_total'] }}</td>
                    <td class="text-center font-bold">{{ $row['total'] }}</td>
                    <td class="text-center">{{ $row['total'] }}</td>
                    <td class="text-center">{{ $row['total_validated'] }}</td>
                </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr class="totals-row">
                    <td>TOTAL GERAL</td>
                    <td class="text-center">{{ $totals['pista_total'] }}</td>
                    <td class="text-center">{{ $totals['backstage_total'] }}</td>
                    <td class="text-center">{{ $totals['grand_total'] }}</td>
                    <td class="text-center">{{ $totals['grand_total'] }}</td>
                    <td class="text-center">{{ $totals['grand_validated'] }}</td>
                </tr>
            </tfoot>
        </table>

        <div class="footer">
            Gerado em: {{ $generatedAt }} | Usuário: {{ $generatedBy }}
        </div>
    </div>
</body>
</html>
