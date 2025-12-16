<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Expense;
use App\Models\Income;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use Dompdf\Dompdf;

class ReportController extends Controller
{
    /**
     * JSON report with filters.
     */
    public function index(Request $request)
    {
        [$type, $from, $to, $categoryId] = $this->resolveFilters($request);

        $rows = $this->buildQuery($request->user()->id, $type, $from, $to, $categoryId)
            ->get();

        return response()->json([
            'type' => $type,
            'from' => $from,
            'to' => $to,
            'category_id' => $categoryId,
            'data' => $rows,
        ]);
    }

    /**
     * CSV export.
     */
    public function exportCsv(Request $request)
    {
        [$type, $from, $to, $categoryId] = $this->resolveFilters($request);

        $rows = $this->buildQuery($request->user()->id, $type, $from, $to, $categoryId)
            ->get();

        $filename = "report_{$type}_{$from}_{$to}.csv";

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function () use ($rows, $type) {
            $handle = fopen('php://output', 'w');

            fputcsv($handle, ['Date', 'Type', 'Category/Source', 'Amount', 'Note']);

            foreach ($rows as $row) {
                fputcsv($handle, [
                    $row->date,
                    ucfirst($type),
                    $row->category_name ?? $row->source ?? '',
                    $row->amount,
                    $row->note ?? '',
                ]);
            }

            fclose($handle);
        };

        return Response::stream($callback, 200, $headers);
    }

    /**
     * PDF export.
     */
    public function exportPdf(Request $request)
    {
        [$type, $from, $to, $categoryId] = $this->resolveFilters($request);

        $rows = $this->buildQuery($request->user()->id, $type, $from, $to, $categoryId)
            ->get();

        $html = view('reports.pdf', [
            'type' => $type,
            'from' => $from,
            'to' => $to,
            'rows' => $rows,
        ])->render();

        $dompdf = new Dompdf();
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        $filename = "report_{$type}_{$from}_{$to}.pdf";

        return response($dompdf->output(), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ]);
    }

    /**
     * Resolve common filter inputs.
     */
    protected function resolveFilters(Request $request): array
    {
        $type = $request->query('type', 'expense');
        if (! in_array($type, ['income', 'expense'], true)) {
            $type = 'expense';
        }

        $from = $request->query('from') ?: now()->startOfMonth()->toDateString();
        $to = $request->query('to') ?: now()->endOfMonth()->toDateString();
        $categoryId = $request->query('category_id');

        return [$type, $from, $to, $categoryId];
    }

    /**
     * Build base query for income or expense.
     */
    protected function buildQuery(int $userId, string $type, string $from, string $to, $categoryId)
    {
        if ($type === 'income') {
            $query = Income::query()
                ->selectRaw('date, amount, source, note, null as category_name')
                ->where('user_id', $userId)
                ->whereBetween('date', [$from, $to])
                ->orderBy('date');

            // category filter is ignored for incomes
            return $query;
        }

        // Expense
        $query = Expense::query()
            ->leftJoin('categories', 'expenses.category_id', '=', 'categories.id')
            ->selectRaw('expenses.date, expenses.amount, expenses.note, categories.name as category_name')
            ->where('expenses.user_id', $userId)
            ->whereBetween('expenses.date', [$from, $to])
            ->orderBy('expenses.date');

        if ($categoryId) {
            $query->where('expenses.category_id', $categoryId);
        }

        return $query;
    }
}


