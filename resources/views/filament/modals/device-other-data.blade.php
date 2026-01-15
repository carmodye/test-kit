<div class="p-6">
    <div class="flex justify-between items-center mb-6">
        <div>
            <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">
                Full Device Data (other_data)
            </h3>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                Raw JSON with syntax highlighting
            </p>
        </div>

        @php
            $decodedOtherData = $otherData;
            if (is_string($otherData)) {
                $maybe = json_decode($otherData, true);
                if (json_last_error() === JSON_ERROR_NONE) {
                    $decodedOtherData = $maybe;
                }
            }
            $copyJson = json_encode($decodedOtherData, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
            $attrJson = json_encode($decodedOtherData, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        @endphp

        <button type="button" x-data @click="
                navigator.clipboard.writeText($el.dataset.json);
                $el.querySelector('span').textContent = 'Copied!';
                setTimeout(() => $el.querySelector('span').textContent = 'Copy', 2000);
            " data-json="{!! $attrJson !!}"
            class="inline-flex items-center gap-2 px-4 py-2 bg-primary-600 hover:bg-primary-700 text-white text-sm font-medium rounded-lg transition">
            <x-heroicon-o-clipboard-document class="w-5 h-5" />
            <span>Copy</span>
        </button>
    </div>

    <div class="bg-gray-900 rounded-xl overflow-hidden border border-gray-800">
        <pre
            class="p-6 overflow-x-auto max-h-96 scrollbar-thin"><code class="language-json">{!! $copyJson !!}</code></pre>
    </div>
</div>