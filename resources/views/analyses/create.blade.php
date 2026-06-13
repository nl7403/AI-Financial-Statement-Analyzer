<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            New Financial Analysis
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">

                @if ($errors->any())
                    <div class="mb-4 p-4 bg-red-100 text-red-700 rounded">
                        @foreach ($errors->all() as $error)
                            <div>{{ $error }}</div>
                        @endforeach
                    </div>
                @endif

                <form method="POST" action="{{ route('analyses.store') }}">
                    @csrf

                    <div class="mb-4">
                        <label class="block font-medium mb-1">Statement Type</label>
                        <select name="statement_type" id="statement_type" class="border rounded w-full p-2">
                            <option value="income_statement">Income Statement</option>
                            <option value="balance_sheet">Balance Sheet</option>
                        </select>
                    </div>

                    <div id="income_fields">
                        <h3 class="font-semibold mt-4 mb-2">Income Statement Figures</h3>
                        <div class="grid grid-cols-2 gap-4">
                            <div><label class="block mb-1">Revenue</label><input type="number" step="any" name="revenue" class="border rounded w-full p-2"></div>
                            <div><label class="block mb-1">Cost of Goods Sold</label><input type="number" step="any" name="cogs" class="border rounded w-full p-2"></div>
                            <div><label class="block mb-1">Operating Expenses</label><input type="number" step="any" name="operating_expenses" class="border rounded w-full p-2"></div>
                            <div><label class="block mb-1">Interest Expense</label><input type="number" step="any" name="interest" class="border rounded w-full p-2"></div>
                            <div><label class="block mb-1">Tax</label><input type="number" step="any" name="tax" class="border rounded w-full p-2"></div>
                        </div>
                    </div>

                    <div id="balance_fields" style="display:none;">
                        <h3 class="font-semibold mt-4 mb-2">Balance Sheet Figures</h3>
                        <div class="grid grid-cols-2 gap-4">
                            <div><label class="block mb-1">Current Assets</label><input type="number" step="any" name="current_assets" class="border rounded w-full p-2"></div>
                            <div><label class="block mb-1">Total Assets</label><input type="number" step="any" name="total_assets" class="border rounded w-full p-2"></div>
                            <div><label class="block mb-1">Current Liabilities</label><input type="number" step="any" name="current_liabilities" class="border rounded w-full p-2"></div>
                            <div><label class="block mb-1">Total Liabilities</label><input type="number" step="any" name="total_liabilities" class="border rounded w-full p-2"></div>
                            <div><label class="block mb-1">Equity</label><input type="number" step="any" name="equity" class="border rounded w-full p-2"></div>
                        </div>
                    </div>

                    <button type="submit" class="mt-6 bg-gray-800 text-white px-4 py-2 rounded">
                        Analyze
                    </button>
                </form>

                <script>
                    const select = document.getElementById('statement_type');
                    const income = document.getElementById('income_fields');
                    const balance = document.getElementById('balance_fields');
                    select.addEventListener('change', function () {
                        if (this.value === 'balance_sheet') {
                            income.style.display = 'none';
                            balance.style.display = 'block';
                        } else {
                            income.style.display = 'block';
                            balance.style.display = 'none';
                        }
                    });
                </script>
            </div>
        </div>
    </div>
</x-app-layout>