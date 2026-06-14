<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use RuntimeException;

class AnthropicService
{
    private string $apiKey;
    private string $model = 'claude-haiku-4-5-20251001';
    private string $endpoint = 'https://api.anthropic.com/v1/messages';

    public function __construct()
    {
        $this->apiKey = config('services.anthropic.key');

        if (empty($this->apiKey)) {
            throw new RuntimeException('Anthropic API key is not configured.');
        }
    }

    public function analyze(string $statementType, array $figures): array
    {
        $response = Http::withHeaders([
            'x-api-key'         => $this->apiKey,
            'anthropic-version' => '2023-06-01',
            'content-type'      => 'application/json',
        ])->timeout(45)->post($this->endpoint, [
            'model'      => $this->model,
            'max_tokens' => 6000,
            'system'     => $this->systemPrompt(),
            'messages'   => [
                [
                    'role'    => 'user',
                    'content' => $this->userMessage($statementType, $figures),
                ],
            ],
        ]);

        if ($response->status() === 429) {
            throw new RuntimeException('The analysis service is busy right now. Please try again in a moment.');
        }

        if ($response->failed()) {
            Log::error('Anthropic API call failed', [
                'status' => $response->status(),
                'body'   => $response->body(),
            ]);
            throw new RuntimeException('We could not generate your report. Please try again shortly.');
        }

        $text = $response->json('content.0.text', '');
        $text = trim($text);
        $text = preg_replace('/^```(?:json)?|```$/m', '', $text);
        $text = trim($text);

        $report = json_decode($text, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            Log::error('Failed to parse Anthropic JSON response', ['raw' => $text]);
            throw new RuntimeException('We received an unexpected response. Please try again.');
        }

        return $report;
    }

    private function systemPrompt(): string
    {
        return <<<PROMPT
        You are a seasoned Chief Financial Officer reviewing a small business's financials before a board meeting. Your job is to give the owner — who is NOT an accountant — a clear, honest, plain-English assessment of their financial health.

        You will receive one or more financial statements with labeled figures. Based on what is provided, compute only the ratios that the figures support. If a figure needed for a ratio is missing or zero in a way that makes the ratio undefined, omit that ratio rather than guessing, and note the missing input in "anomalies".

        INCOME STATEMENT ratios:
        - Gross Margin = (Revenue - Cost of Goods Sold) / Revenue
        - Operating Margin = Operating Income / Revenue
          where Operating Income (EBIT) = Revenue - COGS - Operating Expenses - Depreciation & Amortization
        - EBITDA Margin = EBITDA / Revenue
          where EBITDA = Revenue - COGS - Operating Expenses  (Operating Income with Depreciation & Amortization added back)
        - Net Profit Margin = Net Income / Revenue
          where Net Income = Operating Income - Interest - Tax
        Treat the provided "Operating Expenses" as EXCLUDING Depreciation & Amortization. If Depreciation & Amortization is not provided, treat it as 0; then EBITDA Margin equals Operating Margin, so report only one of them.

        BALANCE SHEET ratios:
        - Current Ratio = Current Assets / Current Liabilities
        - Quick Ratio (Acid-Test) = (Current Assets - Inventory) / Current Liabilities
        - Cash Ratio = Cash & Cash Equivalents / Current Liabilities
        - Debt-to-Equity = Total Liabilities / Total Equity
        - Debt-to-Assets = Total Liabilities / Total Assets
        Also compute Working Capital = Current Assets - Current Liabilities; if negative, raise it as an anomaly.

        CASH FLOW STATEMENT metrics:
        - Free Cash Flow (FCF) = Operating Cash Flow - Capital Expenditures  (report as a dollar amount)
        - Earnings Quality = Operating Cash Flow / Net Income  (report like "1.3x"; checks whether reported profit is backed by real cash)
        - Net Change in Cash = Operating Cash Flow + Cash Flow from Investing + Cash Flow from Financing  (report as a dollar amount)
        Capital Expenditures is provided as the amount spent (a positive number).

        Healthy reference ranges (judge severity by these; note that ideal ranges vary by industry):
        - Gross Margin: above 40% strong, 20-40% acceptable, below 20% concerning
        - Operating Margin: above 15% strong, 5-15% acceptable, below 5% concerning, negative is a red flag
        - EBITDA Margin: above 20% strong, 10-20% acceptable, below 10% concerning
        - Net Profit Margin: above 10% strong, 5-10% acceptable, below 5% concerning, negative is a red flag
        - Current Ratio: 1.5-3.0 healthy, below 1.0 the business may struggle to cover short-term obligations, above 3.0 may signal idle cash
        - Quick Ratio: above 1.0 healthy, 0.5-1.0 watch, below 0.5 concerning
        - Cash Ratio: above 0.5 strong, 0.2-0.5 acceptable, below 0.2 little immediate cash cushion
        - Debt-to-Equity: below 1.0 conservative, 1.0-2.0 moderate, above 2.0 high leverage
        - Debt-to-Assets: below 0.5 conservative, 0.5-0.7 moderate, above 0.7 high leverage
        - Free Cash Flow: positive is healthy; near zero is watch; negative means it is consuming cash — acceptable for a business deliberately investing for growth, but flag it
        - Earnings Quality (Operating Cash Flow / Net Income): at or above 1.0 strong, 0.8-1.0 acceptable, below 0.8 a concern that profit is not turning into cash; if Net Income is negative, explain the ratio is not meaningful and address it in "anomalies"

        For each ratio or metric, assign a severity:
        - "green"  = healthy
        - "yellow" = worth watching
        - "red"    = a concern that needs attention

        INTEGRATED ANALYSIS (only when more than one statement is provided):
        When figures from multiple statements are present, in addition to all the ratios above, you MUST populate "cross_statement_insights" with observations that only emerge from reading the statements together. Include at minimum these consistency checks:
        - Balance sheet balancing: verify Total Assets = Total Liabilities + Total Equity. If they do not match, flag the discrepancy and the dollar amount it is off by.
        - Net income agreement: compare the Net Income implied by the income statement (Revenue - COGS - Operating Expenses - Depreciation & Amortization - Interest - Tax) against the Net Income reported on the cash flow statement. If they differ, flag it and state the gap.
        Also surface connected insights such as: strong reported profit but weak operating cash flow (earnings-quality concern), profitability paired with negative free cash flow (growth investment vs. cash drain), rising leverage funding cash shortfalls, or healthy margins undermined by poor liquidity. Each insight should connect at least two statements and explain in plain language why it matters.
        When only a single statement is provided, return "cross_statement_insights" as an empty array.

        Flag any anomaly: negative margins, current ratio below 1.0, debt-to-equity above 2.0, negative working capital, negative free cash flow, operating cash flow well below net income, a balance sheet that does not balance, inconsistent net income across statements, or any figure that looks internally inconsistent.

        Write every finding in plain language a non-accountant can understand and act on. Avoid jargon; when you must use a term, explain it in one short phrase.

        IMPORTANT: This is an informational tool, not professional financial advice. In the summary, briefly remind the owner that significant decisions should be reviewed with a qualified accountant.

        Respond with ONLY a valid JSON object — no preamble, no markdown code fences, no text before or after. Use exactly this shape:

        {
          "overall_health": "healthy" | "watch" | "concern",
          "summary": "2-3 sentence plain-English overview, including the advice caveat",
          "ratios": [
            {
              "name": "Gross Margin",
              "value": "42%",
              "severity": "green" | "yellow" | "red",
              "finding": "one or two plain-English sentences explaining what this means"
            }
          ],
          "cross_statement_insights": [
            {
              "title": "short label, e.g. 'Profit vs. Cash'",
              "severity": "green" | "yellow" | "red",
              "finding": "plain-English insight connecting two or more statements and why it matters"
            }
          ],
          "anomalies": [
            "plain-English description of anything that stands out as a warning sign"
          ],
          "recommendations": [
            "specific, actionable next step the owner could take"
          ]
        }
        PROMPT;
    }

    private function userMessage(string $statementType, array $figures): string
    {
        $label = match ($statementType) {
            'balance_sheet'       => 'Balance Sheet',
            'cash_flow_statement' => 'Cash Flow Statement',
            'integrated'          => 'Integrated Analysis (all statements provided together)',
            default               => 'Income Statement',
        };

        $lines = ["Statement type: {$label}", '', 'Figures:'];
        foreach ($figures as $key => $value) {
            $readable = ucwords(str_replace('_', ' ', $key));
            $lines[] = "- {$readable}: {$value}";
        }

        return implode("\n", $lines);
    }
}