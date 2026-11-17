<?php

namespace App\Models\Purchases;

use App\Models\Purchases\PurchaseInvoiceItem;
use App\Models\Treasury\MoneyTransaction;
use App\Models\Suppliers\Supplier;
use App\Models\AllUsers\User;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class PurchaseInvoice extends Model
{
    protected $fillable = [
        'number', 'supplier_id', 'invoice_date', 'due_date', 'status',
        'subtotal', 'discount', 'tax', 'total', 'paid', 'remaining',
        'notes', 'attachment', 'journal_id', 'created_by',
    ];

    protected function casts(): array
    {
        return [
            'invoice_date' => 'date',
            'due_date' => 'date',
            'subtotal' => 'decimal:2',
            'discount' => 'decimal:2',
            'tax' => 'decimal:2',
            'total' => 'decimal:2',
            'paid' => 'decimal:2',
            'remaining' => 'decimal:2',
        ];
    }

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(PurchaseInvoiceItem::class);
    }

    public function payments(): MorphMany
    {
        return $this->morphMany(MoneyTransaction::class, 'invoice');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function isEditable(): bool
    {
        return $this->status === 'draft';
    }

    public function attachmentUrl(): ?string
    {
        $path = (string) $this->attachment;
        if ($path === '' || str_contains($path, '..') || ! str_starts_with($path, 'uploads/purchases/')) {
            return null;
        }
        $full = public_path($path);
        if (! is_file($full)) {
            return null;
        }

        return asset($path).'?v='.filemtime($full);
    }

    public function attachmentIsImage(): bool
    {
        return (bool) preg_match('/\.(jpe?g|png|webp)$/i', (string) $this->attachment);
    }
}
