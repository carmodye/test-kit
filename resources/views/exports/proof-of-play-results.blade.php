@php
    $mode = $mode ?? null;
@endphp
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Proof of Play Results</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #ccc; padding: 6px 8px; text-align: left; }
        th { background: #f5f5f5; }
    </style>
</head>
<body>
    <h2>Proof of Play Results</h2>
    <table>
        <thead>
        <tr>
            @if($mode === 'slidesBySite')
                <th>Slide ID</th>
                <th>Slide Name</th>
                <th>Duration (hours)</th>
                <th>Play Count</th>
            @elseif($mode === 'sitesBySlide')
                <th>Device ID</th>
                <th>Device Name</th>
                <th>Display ID</th>
                <th>Display Name</th>
                <th>Site Name</th>
                <th>Duration (hours)</th>
                <th>Play Count</th>
            @else
                <th>Site Name</th>
                <th>Slide Name</th>
                <th>Played At</th>
                <th>Duration (hours)</th>
            @endif
        </tr>
        </thead>
        <tbody>
        @foreach($records as $record)
            <tr>
                @if($mode === 'slidesBySite')
                    <td>{{ $record->slide_id }}</td>
                    <td>{{ $record->slide_name }}</td>
                    <td>{{ $record->duration_seconds ? number_format($record->duration_seconds / 3600, 2) : '0.00' }}</td>
                    <td>{{ $record->play_count }}</td>
                @elseif($mode === 'sitesBySlide')
                    <td>{{ $record->device_id }}</td>
                    <td>{{ $record->device_name }}</td>
                    <td>{{ $record->display_id }}</td>
                    <td>{{ $record->display_name }}</td>
                    <td>{{ $record->site_name }}</td>
                    <td>{{ $record->duration_seconds ? number_format($record->duration_seconds / 3600, 2) : '0.00' }}</td>
                    <td>{{ $record->play_count }}</td>
                @else
                    <td>{{ $record->site_name }}</td>
                    <td>{{ $record->slide_name }}</td>
                    <td>{{ $record->played_at }}</td>
                    <td>
                        @php
                            $duration = $record->duration;
                            $durationHours = '0.00';
                            if ($duration) {
                                if (is_string($duration) && !is_numeric($duration)) {
                                    if (preg_match('/(\d+)h/', $duration, $matches)) {
                                        $hours = (int)$matches[1];
                                        if (preg_match('/(\d+)m/', $duration, $matches)) {
                                            $hours += $matches[1] / 60;
                                        }
                                        if (preg_match('/(\d+)s/', $duration, $matches)) {
                                            $hours += $matches[1] / 3600;
                                        }
                                        $durationHours = number_format($hours, 2);
                                    } else {
                                        $durationHours = $duration;
                                    }
                                } else {
                                    $durationHours = number_format($duration / 3600, 2);
                                }
                            }
                        @endphp
                        {{ $durationHours }}
                    </td>
                @endif
            </tr>
        @endforeach
        </tbody>
    </table>
</body>
</html>
