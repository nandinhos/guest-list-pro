<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Fechamento de Caixa - {{ $event->name ?? 'Evento' }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 12px;
            line-height: 1.4;
            color: #333;
        }
        .container {
            padding: 20px;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #f97316;
            padding-bottom: 15px;
        }
        .header h1 {
            font-size: 24px;
            color: #f97316;
            margin-bottom: 5px;
        }
        .header h2 {
            font-size: 16px;
            color: #666;
            font-weight: normal;
        }
        .header .period {
            font-size: 14px;
            color: #888;
            margin-top: 10px;
        }
        .summary-grid {
            display: table;
            width: 100%;
            margin-bottom: 25px;
        }
        .summary-item {
            display: table-cell;
            width: 25%;
            padding: 10px;
            text-align: center;
            border: 1px solid #ddd;
        }
        .summary-item .label {
            font-size: 11px;
            color: #666;
            text-transform: uppercase;
        }
        .summary-item .value {
            font-size: 16px;
            font-weight: bold;
            color: #333;
            margin-top: 5px;
        }
        .summary-item .count {
            font-size: 10px;
            color: #888;
        }
        .totals {
            background: #f8f8f8;
            padding: 15px;
            margin-bottom: 25px;
            border-radius: 5px;
        }
        .totals-row {
            display: table;
            width: 100%;
        }
        .totals-col {
            display: table-cell;
            width: 50%;
        }
        .totals .label {
            font-size: 12px;
            color: #666;
        }
        .totals .value {
            font-size: 22px;
            font-weight: bold;
            color: #f97316;
        }
        .totals .value.secondary {
            color: #333;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }
        table th {
            background: #f97316;
            color: white;
            padding: 10px 8px;
            text-align: left;
            font-size: 11px;
            text-transform: uppercase;
        }
        table th:last-child {
            text-align: right;
        }
        table td {
            padding: 8px;
            border-bottom: 1px solid #eee;
            font-size: 11px;
        }
        table td:last-child {
            text-align: right;
            font-weight: bold;
        }
        table tr:nth-child(even) {
            background: #fafafa;
        }
        table tfoot td {
            font-weight: bold;
            font-size: 14px;
            background: #f8f8f8;
            border-top: 2px solid #f97316;
        }
        .footer {
            margin-top: 30px;
            padding-top: 15px;
            border-top: 1px solid #ddd;
            font-size: 10px;
            color: #888;
        }
        .footer-row {
            display: table;
            width: 100%;
        }
        .footer-col {
            display: table-cell;
            width: 50%;
        }
        .footer-col:last-child {
            text-align: right;
        }
        .badge {
            display: inline-block;
            padding: 2px 8px;
            border-radius: 10px;
            font-size: 10px;
            font-weight: bold;
        }
        .badge-success { background: #dcfce7; color: #166534; }
        .badge-warning { background: #fef3c7; color: #92400e; }
        .badge-info { background: #dbeafe; color: #1e40af; }
        .badge-primary { background: #ffedd5; color: #c2410c; }
        .section-title {
            font-size: 14px;
            font-weight: bold;
            color: #333;
            margin-bottom: 10px;
            padding-bottom: 5px;
            border-bottom: 1px solid #ddd;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Fechamento de Caixa</h1>
            <h2>{{ $event->name ?? 'Evento' }}</h2>
            <div class="period">
                Data: {{ \Carbon\Carbon::parse($date)->format('d/m/Y') }} |
                Periodo: {{ $start_time }} - {{ $end_time }}
            </div>
        </div>

        <div class="section-title">Resumo por Forma de Pagamento</div>
        <div class="summary-grid">
            @foreach($salesByPaymentMethod as $method => $data)
                <div class="summary-item">
                    <div class="label">{{ $data['label'] }}</div>
                    <div class="value">R$ {{ number_format($data['total'], 2, ',', '.') }}</div>
                    <div class="count">{{ $data['count'] }} {{ $data['count'] === 1 ? 'venda' : 'vendas' }}</div>
                </div>
            @endforeach
        </div>

        <div class="totals">
            <div class="totals-row">
                <div class="totals-col">
                    <div class="label">Total Geral</div>
                    <div class="value">R$ {{ number_format($totalSales, 2, ',', '.') }}</div>
                </div>
                <div class="totals-col" style="text-align: right;">
                    <div class="label">Total de Vendas</div>
                    <div class="value secondary">{{ $totalCount }}</div>
                </div>
            </div>
        </div>

        <div class="section-title">Detalhamento das Vendas</div>
        @if($sales->isEmpty())
            <p style="text-align: center; color: #888; padding: 20px;">
                Nenhuma venda encontrada no periodo selecionado.
            </p>
        @else
            <table>
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Hora</th>
                        <th>Comprador</th>
                        <th>Documento</th>
                        <th>Pagamento</th>
                        <th>Vendedor</th>
                        <th>Valor</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($sales as $sale)
                        <tr>
                            <td>{{ $sale->id }}</td>
                            <td>{{ $sale->created_at->format('H:i') }}</td>
                            <td>{{ $sale->buyer_name }}</td>
                            <td>{{ $sale->buyer_document }}</td>
                            <td>
                                @php
                                    $paymentMethod = \App\Enums\PaymentMethod::tryFrom($sale->payment_method);
                                    $badgeClass = match($paymentMethod?->value) {
                                        'cash' => 'badge-success',
                                        'credit_card' => 'badge-warning',
                                        'debit_card' => 'badge-info',
                                        'pix' => 'badge-primary',
                                        default => ''
                                    };
                                @endphp
                                <span class="badge {{ $badgeClass }}">
                                    {{ $paymentMethod?->getLabel() ?? $sale->payment_method }}
                                </span>
                            </td>
                            <td>{{ $sale->seller?->name ?? '-' }}</td>
                            <td>R$ {{ number_format($sale->value, 2, ',', '.') }}</td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr>
                        <td colspan="6" style="text-align: right;">Total:</td>
                        <td>R$ {{ number_format($totalSales, 2, ',', '.') }}</td>
                    </tr>
                </tfoot>
            </table>
        @endif

        <div class="footer">
            <div class="footer-row">
                <div class="footer-col">
                    Gerado por: {{ $generatedBy }}
                </div>
                <div class="footer-col">
                    Data/Hora: {{ $generatedAt }}
                </div>
            </div>
        </div>
    </div>
</body>
</html>
