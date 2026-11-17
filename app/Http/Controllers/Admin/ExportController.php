<?php

namespace App\Http\Controllers\Admin;

use App\Exports\RowsExport;
use App\Http\Controllers\Controller;
use App\Services\Exports\TableExportService;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class ExportController extends Controller
{
    public function __construct(private readonly TableExportService $tables) {}

    public function __invoke(string $sheet): BinaryFileResponse
    {
        abort_unless($this->tables->allowed($sheet), 403);

        [$title, $headings, $rows] = $this->tables->build($sheet);

        return Excel::download(new RowsExport($headings, $rows, $title), $title.'.xlsx');
    }
}
