<?php

namespace App\Http\Controllers;

use App\Models\Analysis;
use App\Services\AnthropicService;
use Illuminate\Http\Request;

class AnalysisController extends Controller
{
    public function create()
    {
        return view('analyses.create');
    }

    public function index()
    {
        $analyses = Analysis::where('user_id', auth()->id())
            ->latest()
            ->get();

        return view('analyses.index', compact('analyses'));
    }

    public function show(Analysis $analysis)
    {
        if ($analysis->user_id !== auth()->id()) {
            abort(403);
        }

        $report = json_decode($analysis->report_text, true);

        return view('analyses.show', compact('analysis', 'report'));
    }

    public function store(Request $request, AnthropicService $anthropic)
    {
        $validated = $request->validate([
            'statement_type' => 'required|in:income_statement,balance_sheet,cash_flow_statement,integrated',

            // Income statement figures
            'revenue'                   => 'nullable|numeric',
            'cogs'                      => 'nullable|numeric',
            'operating_expenses'        => 'nullable|numeric',
            'depreciation_amortization' => 'nullable|numeric',
            'interest'                  => 'nullable|numeric',
            'tax'                       => 'nullable|numeric',

            // Balance sheet figures
            'cash'                => 'nullable|numeric',
            'inventory'           => 'nullable|numeric',
            'current_assets'      => 'nullable|numeric',
            'total_assets'        => 'nullable|numeric',
            'current_liabilities' => 'nullable|numeric',
            'total_liabilities'   => 'nullable|numeric',
            'equity'              => 'nullable|numeric',

            // Cash flow statement figures
            'net_income'           => 'nullable|numeric',
            'operating_cash_flow'  => 'nullable|numeric',
            'capital_expenditures' => 'nullable|numeric',
            'investing_cash_flow'  => 'nullable|numeric',
            'financing_cash_flow'  => 'nullable|numeric',
        ]);

        $statementType = $validated['statement_type'];
        unset($validated['statement_type']);

        $figures = array_filter($validated, fn ($v) => $v !== null && $v !== '');

        try {
            $report = $anthropic->analyze($statementType, $figures);
        } catch (\RuntimeException $e) {
            return back()
                ->withInput()
                ->withErrors(['analysis' => $e->getMessage()]);
        }

        $analysis = Analysis::create([
            'user_id'        => auth()->id(),
            'statement_type' => $statementType,
            'input_json'     => json_encode($figures),
            'report_text'    => json_encode($report),
        ]);

        return redirect()->route('analyses.show', $analysis);
    }
}