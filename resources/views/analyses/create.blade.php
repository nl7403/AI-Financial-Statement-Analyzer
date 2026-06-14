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
                            <option value="income_statement" @selected(old('statement_type') === 'income_statement')>Income Statement</option>
                            <option value="balance_sheet" @selected(old('statement_type') === 'balance_sheet')>Balance Sheet</option>
                            <option value="cash_flow_statement" @selected(old('statement_type') === 'cash_flow_statement')>Cash Flow Statement</option>
                        </select>
                        <p class="text-gray-500 text-sm mt-1">Enter the figures you have. Fields you leave blank are simply skipped.</p>
                    </div>

                    <div id="income_fields">
                        <h3 class="font-semibold mt-4 mb-2">Income Statement Figures</h3>
                        <div class="grid grid-cols-2 gap-4">
                            <div><label class="block mb-1">Revenue</label><input type="number" step="any" name="revenue" value="{{ old('revenue') }}" class="border rounded w-full p-2"></div>
                            <div><label class="block mb-1">Cost of Goods Sold</label><input type="number" step="any" name="cogs" value="{{ old('cogs') }}" class="border rounded w-full p-2"></div>
                            <div><label class="block mb-1">Operating Expenses (excl. D&amp;A)</label><input type="number" step="any" name="operating_expenses" value="{{ old('operating_expenses') }}" class="border rounded w-full p-2"></div>
                            <div><label class="block mb-1">Depreciation &amp; Amortization <span class="text-gray-400 text-sm">(optional)</span></label><input type="number" step="any" name="depreciation_amortization" value="{{ old('depreciation_amortization') }}" class="border rounded w-full p-2"></div>
                            <div><label class="block mb-1">Interest Expense</label><input type="number" step="any" name="interest" value="{{ old('interest') }}" class="border rounded w-full p-2"></div>
                            <div><label class="block mb-1">Income Tax</label><input type="number" step="any" name="tax" value="{{ old('tax') }}" class="border rounded w-full p-2"></div>
                        </div>
                    </div>

                    <div id="balance_fields" style="display:none;">
                        <h3 class="font-semibold mt-4 mb-2">Balance Sheet Figures</h3>
                        <div class="grid grid-cols-2 gap-4">
                            <div><label class="block mb-1">Cash &amp; Cash Equivalents</label><input type="number" step="any" name="cash" value="{{ old('cash') }}" class="border rounded w-full p-2"></div>
                            <div><label class="block mb-1">Inventory</label><input type="number" step="any" name="inventory" value="{{ old('inventory') }}" class="border rounded w-full p-2"></div>
                            <div><label class="block mb-1">Current Assets</label><input type="number" step="any" name="current_assets" value="{{ old('current_assets') }}" class="border rounded w-full p-2"></div>
                            <div><label class="block mb-1">Total Assets</label><input type="number" step="any" name="total_assets" value="{{ old('total_assets') }}" class="border rounded w-full p-2"></div>
                            <div><label class="block mb-1">Current Liabilities</label><input type="number" step="any" name="current_liabilities" value="{{ old('current_liabilities') }}" class="border rounded w-full p-2"></div>
                            <div><label class="block mb-1">Total Liabilities</label><input type="number" step="any" name="total_liabilities" value="{{ old('total_liabilities') }}" class="border rounded w-full p-2"></div>
                            <div><label class="block mb-1">Total Equity</label><input type="number" step="any" name="equity" value="{{ old('equity') }}" class="border rounded w-full p-2"></div>
                        </div>
                    </div>

                    <div id="cash_flow_fields" style="display:none;">
                        <h3 class="font-semibold mt-4 mb-2">Cash Flow Statement Figures</h3>
                        <div class="grid grid-cols-2 gap-4">
                            <div><label class="block mb-1">Net Income</label><input type="number" step="any" name="net_income" value="{{ old('net_income') }}" class="border rounded w-full p-2"></div>
                            <div><label class="block mb-1">Operating Cash Flow</label><input type="number" step="any" name="operating_cash_flow" value="{{ old('operating_cash_flow') }}" class="border rounded w-full p-2"></div>
                            <div><label class="block mb-1">Capital Expenditures <span class="text-gray-400 text-sm">(amount spent)</span></label><input type="number" step="any" name="capital_expenditures" value="{{ old('capital_expenditures') }}" class="border rounded w-full p-2"></div>
                            <div><label class="block mb-1">Cash Flow from Investing</label><input type="number" step="any" name="investing_cash_flow" value="{{ old('investing_cash_flow') }}" class="border rounded w-full p-2"></div>
                            <div><label class="block mb-1">Cash Flow from Financing</label><input type="number" step="any" name="financing_cash_flow" value="{{ old('financing_cash_flow') }}" class="border rounded w-full p-2"></div>
                        </div>
                    </div>

                    <button type="submit" class="mt-6 bg-gray-800 text-white px-4 py-2 rounded">
                        Analyze
                    </button>
                </form>

                <script>
                    const select = document.getElementById('statement_type');
                    const sections = {
                        income_statement: document.getElementById('income_fields'),
                        balance_sheet: document.getElementById('balance_fields'),
                        cash_flow_statement: document.getElementById('cash_flow_fields'),
                    };

                    function showFields() {
                        for (const key in sections) {
                            const visible = (key === select.value);
                            sections[key].style.display = visible ? 'block' : 'none';
                            // Only the visible section's inputs are submitted
                            sections[key].querySelectorAll('input').forEach(function (input) {
                                input.disabled = !visible;
                            });
                        }
                    }

                    select.addEventListener('change', showFields);
                    showFields(); // set the correct section on page load
                </script>
            </div>
        </div>
    </div>
</x-app-layout>