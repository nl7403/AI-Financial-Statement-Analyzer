<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Financial Health Report
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8 space-y-6">

            <div class="bg-white shadow-sm sm:rounded-lg p-6">
                @php
                    $health = $report['overall_health'] ?? 'unknown';
                    $healthColor = match($health) {
                        'healthy' => 'bg-green-100 text-green-800',
                        'watch' => 'bg-yellow-100 text-yellow-800',
                        'concern' => 'bg-red-100 text-red-800',
                        default => 'bg-gray-100 text-gray-800',
                    };
                @endphp
                <span class="inline-block px-3 py-1 rounded-full text-sm font-semibold {{ $healthColor }}">
                    Overall: {{ ucfirst($health) }}
                </span>
                <p class="mt-4 text-gray-700">{{ $report['summary'] ?? '' }}</p>
            </div>

            @if (!empty($report['cross_statement_insights']))
                <div class="bg-white shadow-sm sm:rounded-lg p-6 border-l-4 border-indigo-400">
                    <h3 class="font-semibold text-lg mb-4">Cross-Statement Insights</h3>
                    <div class="space-y-4">
                        @foreach ($report['cross_statement_insights'] as $insight)
                            @php
                                $sev = $insight['severity'] ?? 'green';
                                $dot = match($sev) {
                                    'green' => 'bg-green-500',
                                    'yellow' => 'bg-yellow-500',
                                    'red' => 'bg-red-500',
                                    default => 'bg-gray-400',
                                };
                            @endphp
                            <div class="border-b pb-3 last:border-0">
                                <div class="flex items-center gap-2">
                                    <span class="inline-block w-2.5 h-2.5 rounded-full {{ $dot }}"></span>
                                    <span class="font-medium">{{ $insight['title'] ?? '' }}</span>
                                </div>
                                <p class="text-gray-600 text-sm mt-1">{{ $insight['finding'] ?? '' }}</p>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            @if (!empty($report['ratios']))
                <div class="bg-white shadow-sm sm:rounded-lg p-6">
                    <h3 class="font-semibold text-lg mb-4">Key Ratios</h3>
                    <div class="space-y-4">
                        @foreach ($report['ratios'] as $ratio)
                            @php
                                $sev = $ratio['severity'] ?? 'green';
                                $badge = match($sev) {
                                    'green' => 'bg-green-100 text-green-800',
                                    'yellow' => 'bg-yellow-100 text-yellow-800',
                                    'red' => 'bg-red-100 text-red-800',
                                    default => 'bg-gray-100 text-gray-800',
                                };
                            @endphp
                            <div class="border-b pb-3">
                                <div class="flex items-center justify-between">
                                    <span class="font-medium">{{ $ratio['name'] ?? '' }}</span>
                                    <span class="px-2 py-1 rounded text-sm font-semibold {{ $badge }}">
                                        {{ $ratio['value'] ?? '' }}
                                    </span>
                                </div>
                                <p class="text-gray-600 text-sm mt-1">{{ $ratio['finding'] ?? '' }}</p>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            @if (!empty($report['anomalies']))
                <div class="bg-white shadow-sm sm:rounded-lg p-6">
                    <h3 class="font-semibold text-lg mb-4">Warning Signs</h3>
                    <ul class="list-disc list-inside text-gray-700 space-y-1">
                        @foreach ($report['anomalies'] as $anomaly)
                            <li>{{ $anomaly }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            @if (!empty($report['recommendations']))
                <div class="bg-white shadow-sm sm:rounded-lg p-6">
                    <h3 class="font-semibold text-lg mb-4">Recommendations</h3>
                    <ul class="list-disc list-inside text-gray-700 space-y-1">
                        @foreach ($report['recommendations'] as $rec)
                            <li>{{ $rec }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <a href="{{ route('analyses.create') }}" class="inline-block bg-gray-800 text-white px-4 py-2 rounded">
                Run Another Analysis
            </a>

        </div>
    </div>
</x-app-layout>