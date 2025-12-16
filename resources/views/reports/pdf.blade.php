<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <title>Report</title>
        <style>
            body { font-family: DejaVu Sans, sans-serif; font-size: 12px; }
            h1 { font-size: 18px; margin-bottom: 10px; }
            table { width: 100%; border-collapse: collapse; margin-top: 10px; }
            th, td { border: 1px solid #ccc; padding: 4px 6px; text-align: left; }
            th { background-color: #f5f5f5; }
        </style>
    </head>
    <body>
        <h1>Report ({{ strtoupper($type) }})</h1>
        <p>From {{ $from }} to {{ $to }}</p>

        <table>
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Category / Source</th>
                    <th>Amount</th>
                    <th>Note</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($rows as $row)
                    <tr>
                        <td>{{ $row->date }}</td>
                        <td>{{ $row->category_name ?? $row->source ?? '' }}</td>
                        <td>{{ number_format($row->amount, 2) }}</td>
                        <td>{{ $row->note ?? '' }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </body>
</html>


