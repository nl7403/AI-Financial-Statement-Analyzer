<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                My Analyses
            </h2>
            <a href="{{ route('analyses.create') }}" class="bg-gray-800 text-white px-4 py-2 rounded text-sm">
                New Analysis
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white shadow-sm sm:rounded-lg p-6">

                @if ($analyses->isEmpty())
                    <p class="text-gray-600">You haven't run any analyses yet.
                        <a href="{{ route('analyses.create') }}" class="text-blue-600 underline">Create your first one.</a>
                    </p>
                @else
                    <div class="space-y-3">
                        @foreach ($analyses as $analysis)
                            @php
                                $report = json_decode($analysis->report_text, true);
                                $health = $report['overall_health'] ?? 'unknown';
                                $badge = match($health) {
                                    'healthy' => 'bg-green-100 text-green-800',
                                    'watch' => 'bg-yellow-100 text-yellow-800',
                                    'concern' => 'bg-red-100 text-red-800',
                                    default => 'bg-gray-100 text-gray-800',
                                };
                            @endphp
                            <a href="{{ route('analyses.show', $analysis) }}" class="block border rounded p-4 hover:bg-gray-50">
                                <div class="flex items-center justify-between">
                                    <span class="font-medium">
                                        {{ ucwords(str_replace('_', ' ', $analysis->statement_type)) }}
                                    </span>
                                    <span class="px-2 py-1 rounded text-xs font-semibold {{ $badge }}">
                                        {{ ucfirst($health) }}
                                    </span>
                                </div>
                                <p class="text-gray-500 text-sm mt-1">
                                    {{ $analysis->created_at->format('M j, Y g:i A') }}
                                </p>
                            </a>
                        @endforeach
                    </div>
                @endif

            </div>
        </div>
    </div>
</x-app-layout>