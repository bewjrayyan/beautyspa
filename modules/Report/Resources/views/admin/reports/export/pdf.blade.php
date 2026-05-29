<!DOCTYPE html>
<html lang="{{ locale() }}">
<head>
    <meta charset="utf-8">
    <title>{{ $title }}</title>
    <style>
        body {
            font-family: Helvetica, Arial, sans-serif;
            font-size: 11px;
            color: #111827;
            margin: 24px;
        }

        h1 {
            font-size: 18px;
            margin: 0 0 6px;
        }

        .meta {
            color: #64748b;
            margin-bottom: 18px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th,
        td {
            border: 1px solid #e2e8f0;
            padding: 7px 8px;
            text-align: left;
            vertical-align: top;
        }

        td.cell-multiline {
            line-height: 1.45;
        }

        th {
            background: #f8fafc;
            font-weight: 700;
        }

        tr:nth-child(even) td {
            background: #fcfdfe;
        }
    </style>
</head>
<body>
    <h1>{{ $title }}</h1>
    <p class="meta">{{ trans('report::admin.export_generated_at') }}: {{ $generatedAt->format('Y-m-d H:i') }}</p>

    <table>
        <thead>
            <tr>
                @foreach ($headings as $heading)
                    <th>{{ $heading }}</th>
                @endforeach
            </tr>
        </thead>
        <tbody>
            @forelse ($rows as $row)
                <tr>
                    @foreach ($row as $cell)
                        <td @if (is_string($cell) && str_contains($cell, "\n")) class="cell-multiline" @endif>{!! nl2br(e($cell)) !!}</td>
                    @endforeach
                </tr>
            @empty
                <tr>
                    <td colspan="{{ count($headings) }}">{{ trans('report::admin.no_data') }}</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>
