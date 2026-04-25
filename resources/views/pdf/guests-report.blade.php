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
        .cards-table { width: 100%; border-collapse: separate; border-spacing: 8px; margin-bottom: 20px; }
        .card { border-radius: 6px; padding: 10px 15px; text-align: center; }
        .card-total { background: #fef3c7; border: 2px solid #f97316; }
        .card-pista { background: #dbeafe; border: 2px solid #2563eb; }
        .card-backstage { background: #f3e8ff; border: 2px solid #9333ea; }
        .card-validacao { background: #dcfce7; border: 2px solid #16a34a; }
        .card-label { font-size: 9px; font-weight: bold; text-transform: uppercase; color: #666; }
        .card-value { font-size: 16px; font-weight: bold; }
        .card-total .card-value { color: #ea580c; }
        .card-pista .card-value { color: #2563eb; }
        .card-backstage .card-value { color: #9333ea; }
        .card-validacao .card-value { color: #16a34a; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        th, td { border: 1px solid #ddd; padding: 6px 8px; text-align: left; }
        th { background: #f8f8f8; font-weight: bold; text-transform: uppercase; font-size: 10px; }
        .text-center { text-align: center; }
        .font-bold { font-weight: bold; }
        .pista { color: #2563eb; }
        .backstage { color: #9333ea; }
        .total-row { background: #fff3e0 !important; font-weight: bold; }
        .grand-total { background: #fef3c7 !important; }
        .footer { margin-top: 30px; font-size: 10px; color: #888; text-align: center; }
        .promoter-cell { vertical-align: middle; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Relatório de Cortesias</h1>
            <h2>{{ $eventName }} - {{ $eventDate }}</h2>
        </div>

        <table class="cards-table">
            <tr>
                <td class="card card-total">
                    <div class="card-label">Total</div>
                    <div class="card-value">{{ $totals['grand_total'] }}</div>
                </td>
                <td class="card card-pista">
                    <div class="card-label">PISTA</div>
                    <div class="card-value">{{ $totals['pista_total'] }}</div>
                </td>
                <td class="card card-backstage">
                    <div class="card-label">BACKSTAGE</div>
                    <div class="card-value">{{ $totals['backstage_total'] }}</div>
                </td>
                <td class="card card-validacao">
                    <div class="card-label">Validação</div>
                    <div class="card-value">{{ $totals['grand_total'] > 0 ? round(($totals['grand_validated'] / $totals['grand_total']) * 100, 1) : 0 }}%</div>
                </td>
            </tr>
        </table>

        <table>
            <thead>
                <tr>
                    <th style="width: 20%">RESPONSÁVEL</th>
                    <th class="text-center pista" style="width: 20%">PISTA (TOT/CHK)</th>
                    <th class="text-center backstage" style="width: 20%">BACKSTAGE (TOT/CHK)</th>
                    <th class="text-center" style="width: 15%">TOTAL</th>
                    <th class="text-center" style="width: 25%">% POR SETOR</th>
                </tr>
            </thead>
            <tbody>
                @foreach($data as $row)
                <tr>
                    <td class="font-bold">{{ $row['promoter_name'] }}</td>
                    <td class="text-center">{{ $row['pista_total'] }}/{{ $row['pista_validated'] }}</td>
                    <td class="text-center">{{ $row['backstage_total'] }}/{{ $row['backstage_validated'] }}</td>
                    <td class="text-center font-bold">{{ $row['total'] }}</td>
                    <td class="text-center">
                        <span class="pista">{{ $row['pista_total'] > 0 ? round(($row['pista_validated'] / $row['pista_total']) * 100) . '%' : '0%' }}</span>
                        <span> / </span>
                        <span class="backstage">{{ $row['backstage_total'] > 0 ? round(($row['backstage_validated'] / $row['backstage_total']) * 100) . '%' : '0%' }}</span>
                    </td>
                </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr class="grand-total">
                    <td class="font-bold">TOTAL GERAL</td>
                    <td class="text-center font-bold">{{ $totals['pista_total'] }}/{{ $totals['pista_validated'] }}</td>
                    <td class="text-center font-bold">{{ $totals['backstage_total'] }}/{{ $totals['backstage_validated'] }}</td>
                    <td class="text-center font-bold">{{ $totals['grand_total'] }}</td>
                    <td class="text-center font-bold">
                        <span class="pista">{{ $totals['pista_total'] > 0 ? round(($totals['pista_validated'] / $totals['pista_total']) * 100) . '%' : '0%' }}</span>
                        <span> / </span>
                        <span class="backstage">{{ $totals['backstage_total'] > 0 ? round(($totals['backstage_validated'] / $totals['backstage_total']) * 100) . '%' : '0%' }}</span>
                    </td>
                </tr>
            </tfoot>
        </table>

        <div class="footer">
            Gerado em: {{ $generatedAt }} | Usuário: {{ $generatedBy }}
        </div>
    </div>
</body>
</html>