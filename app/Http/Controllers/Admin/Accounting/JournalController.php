<?php

namespace App\Http\Controllers\Admin\Accounting;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Accounting\StoreJournalRequest;
use App\Models\Accounting\Journal;
use App\Support\GridQuery;
use App\Services\Accounting\AccountService;
use App\Services\Accounting\JournalEntryService;
use Illuminate\Http\Request;
use RuntimeException;

class JournalController extends Controller
{
    public function __construct(
        private readonly JournalEntryService $journals,
        private readonly AccountService $accounts,
    ) {}

    public function index(Request $request)
    {
        return view('admin.journals.index', [
            'journals' => $this->journals->paginate($request->q),
            'filters' => [
                'creators' => GridQuery::users(Journal::query()),
            ],
        ]);
    }

    public function create()
    {
        return view('admin.journals.create', [
            'accounts' => $this->accounts->active(),
        ]);
    }

    public function store(StoreJournalRequest $request)
    {
        try {
            $journal = $this->journals->post($request->validated());
        } catch (RuntimeException $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }

        return redirect()->route('journals.show', $journal)->with('success', 'تم ترحيل القيد.');
    }

    public function show(Journal $journal)
    {
        return view('admin.journals.show', [
            'journal' => $this->journals->detail($journal),
        ]);
    }
}
