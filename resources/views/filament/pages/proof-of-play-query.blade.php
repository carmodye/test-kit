<x-filament-panels::page>
    <div class="space-y-8">
        <div class="text-center">
            <h1 class="text-2xl font-bold mb-4">Proof of Play Query</h1>
            <p class="text-gray-600 dark:text-gray-400">
                Query proof of play data by selecting mode, client, date range, and parameters.
            </p>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
            <h2 class="text-xl font-semibold mb-4">Query Results</h2>
            @if($lastQuery)
                <div class="mb-4 p-4 bg-gray-100 dark:bg-gray-700 rounded">
                    <h3 class="font-medium mb-2">Last Query Parameters:</h3>
                    <ul class="text-sm space-y-1">
                        <li><strong>Mode:</strong>
                            {{ $lastQuery['mode'] === 'sitesBySlide' ? 'Sites by Slide' : 'Slides by Site' }}</li>
                        <li><strong>Client:</strong> {{ $lastQuery['client'] }}</li>
                        <li><strong>Date Range:</strong> {{ $lastQuery['start'] }} to {{ $lastQuery['end'] }}</li>
                        @if($lastQuery['mode'] === 'sitesBySlide')
                            <li><strong>Slide ID:</strong> {{ $lastQuery['slideId'] }}</li>
                        @else
                            <li><strong>Site ID:</strong> {{ $lastQuery['siteId'] }}</li>
                        @endif
                    </ul>
                </div>
            @endif
            {{ $this->table }}
        </div>
    </div>
</x-filament-panels::page>