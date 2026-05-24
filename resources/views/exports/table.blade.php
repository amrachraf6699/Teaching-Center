<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>{{ $title }}</title>
    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            color: #172033;
            font-size: 12px;
        }

        h1 {
            margin: 0 0 16px;
            font-size: 20px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th,
        td {
            border: 1px solid #dbe7f3;
            padding: 8px 10px;
            text-align: left;
            vertical-align: top;
        }

        th {
            background: #dbeafe;
            color: #2563eb;
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: 0.04em;
        }

        tr:nth-child(even) td {
            background: #f8fbff;
        }
    </style>
</head>
<body>
    <h1>{{ $title }}</h1>

    <table>
        <thead>
            <tr>
                @foreach ($headers as $header)
                    <th>{{ $header }}</th>
                @endforeach
            </tr>
        </thead>
        <tbody>
            @foreach ($rows as $row)
                <tr>
                    @foreach ($row as $cell)
                        <td>{{ $cell }}</td>
                    @endforeach
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
