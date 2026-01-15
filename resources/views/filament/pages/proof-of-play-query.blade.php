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
            {{ $this->table }}
        </div>
    </div>
</x-filament-panels::page>