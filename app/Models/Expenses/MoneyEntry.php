<?php

namespace App\Models\Expenses;

use App\Models\Expenses\MoneyCategory;
use App\Models\Treasury\Treasury;
use App\Models\AllUsers\User;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MoneyEntry extends Model
{
    protected $fillable = [
        'number', 'type', 'money_category_id', 'treasury_id',
        'amount', 'entry_date', 'description', 'journal_id', 'created_by',
    ];

    protected function casts(): array
    {
        return ['amount' => 'decimal:2', 'entry_date' => 'date'];
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(MoneyCategory::class, 'money_category_id');
    }

    public function treasury(): BelongsTo
    {
        return $this->belongsTo(Treasury::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
