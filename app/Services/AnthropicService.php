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
        ])->timeout(30)->post($this->endpoint, [
            'model'      => $this->model,
            'max_tokens' => 1500,
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
        You are a seasoned Chief Financial Officer reviewing a small business's financial statement before a board meeting. Your job is to give the owner — who is NOT an accountant — a clear, honest, plain-English assessment of their financial health.

        You will receive a single financial statement with labeled figures. Based on the statement type, compute only the ratios that the provided figures support:

        INCOME STATEMENT ratios:
        - Gross Margin = (Revenue - Cost of Goods Sold) / Revenue
        - Net Profit Margin = Net Income / Revenue
          (Net Income = Revenue - COGS - Operating Expenses - Interest - Tax)

        BALANCE SHEET ratios:
        - Current Ratio = Current Assets / Current Liabilities
        - Debt-to-Equity = Total Liabilities / Total Equity

        Healthy reference ranges (use these to judge severity):
        - Gross Margin: above 40% is strong, 20-40% is acceptable, below 20% is concerning (varies by industry — note this caveat)
        - Net Profit Margin: above 10% is strong, 5-10% is acceptable, below 5% is concerning, negative is a red flag
        - Current Ratio: 1.5-3.0 is healthy, below 1.0 means the business may struggle to cover short-term obligations, above 3.0 may signal idle cash
        - Debt-to-Equity: below 1.0 is conservative, 1.0-2.0 is moderate, above 2.0 indicates high leverage

        For each ratio you compute, assign a severity:
        - "green"  = healthy, within the strong/acceptable range
        - "yellow" = worth watching, near the edge of acceptable
        - "red"    = a concern that needs attention

        Flag any anomaly: negative margins, a current ratio below 1.0, debt-to-equity above 2.0, or any figure that looks internally inconsistent.

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
          "anomalies": [
            "plain-English description of anything that stands out as a warning sign"
          ],
          "recommendations": [
            "specific, actionable next step the owner could take"
          ]
        }

        If a figure needed for a ratio is missing or zero in a way that makes the ratio undefined, omit that ratio rather than guessing, and note the missing input in "anomalies".
        PROMPT;
    }

    private function userMessage(string $statementType, array $figures): string
    {
        $label = $statementType === 'balance_sheet' ? 'Balance Sheet' : 'Income Statement';

        $lines = ["Statement type: {$label}", '', 'Figures:'];
        foreach ($figures as $key => $value) {
            $readable = ucwords(str_replace('_', ' ', $key));
            $lines[] = "- {$readable}: {$value}";
        }

        return implode("\n", $lines);
    }
}