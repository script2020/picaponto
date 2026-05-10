<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <title>Relatório de Horas — {{ $utilizador->name }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            margin: 20px;
            color: #333;
        }
        .header {
            border-bottom: 2px solid #333;
            padding-bottom: 15px;
            margin-bottom: 30px;
        }
        .header h1 {
            margin: 0;
            font-size: 18px;
            color: #1f2937;
        }
        .header p {
            margin: 5px 0;
            font-size: 11px;
            color: #666;
        }
        .info-box {
            background: #f3f4f6;
            padding: 10px;
            border-radius: 5px;
            margin-bottom: 20px;
            display: inline-block;
        }
        .info-box strong {
            display: block;
            margin-bottom: 3px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 30px;
        }
        table thead {
            background: #1f2937;
            color: white;
        }
        table th {
            padding: 10px;
            text-align: left;
            font-weight: bold;
            font-size: 11px;
        }
        table td {
            padding: 8px 10px;
            border-bottom: 1px solid #ddd;
            font-size: 11px;
        }
        table tbody tr:nth-child(even) {
            background: #f9fafb;
        }
        .totals {
            background: #f3f4f6;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
        }
        .total-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 8px;
            font-size: 12px;
        }
        .total-row strong {
            font-weight: bold;
        }
        .total-grand {
            border-top: 2px solid #333;
            padding-top: 10px;
            margin-top: 10px;
        }
        .footer {
            margin-top: 40px;
            border-top: 1px solid #ddd;
            padding-top: 15px;
            text-align: center;
            font-size: 10px;
            color: #999;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Relatório de Horas Trabalhadas</h1>
        <p>Data do relatório: {{ now()->format('d/m/Y H:i') }}</p>
    </div>

    <div class="info-box">
        <strong>Utilizador:</strong>
        {{ $utilizador->name }}<br>
        <strong>Email:</strong>
        {{ $utilizador->email }}
        @if ($periodo['data_inicio'] || $periodo['data_fim'])
            <br>
            <strong>Período:</strong>
            {{ $periodo['data_inicio'] ? \Carbon\Carbon::parse($periodo['data_inicio'])->format('d/m/Y') : '—' }}
            até
            {{ $periodo['data_fim'] ? \Carbon\Carbon::parse($periodo['data_fim'])->format('d/m/Y') : '—' }}
        @endif
    </div>

    @if ($registos->isEmpty())
        <p style="padding: 20px; background: #fef3c7; border-radius: 5px;">
            Não existem registos para este período.
        </p>
    @else
        <table>
            <thead>
                <tr>
                    <th>Data</th>
                    <th>Entrada</th>
                    <th>Saída</th>
                    <th>Duração</th>
                    <th>Observações</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($registos as $registo)
                    <tr>
                        <td>{{ $registo->entrada->format('d/m/Y') }}</td>
                        <td>{{ $registo->entrada->format('H:i:s') }}</td>
                        <td>{{ $registo->saida->format('H:i:s') }}</td>
                        <td>{{ $registo->duracao }}</td>
                        <td>{{ $registo->observacoes ?? '—' }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <div class="totals">
            <div class="total-row">
                <span>Total de dias:</span>
                <strong>{{ $registos->count() }}</strong>
            </div>
            <div class="total-row">
                <span>Horas totais:</span>
                <strong>{{ $horasTotal }}</strong>
            </div>
            <div class="total-row">
                <span>Média diária:</span>
                <strong>{{ $horasMedia }}</strong>
            </div>
        </div>
    @endif

    <div class="footer">
        <p>Este documento foi gerado automaticamente pelo sistema de gestão de horas.</p>
    </div>
</body>
</html>
