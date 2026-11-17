<?php

namespace App\Models\Purchases;

use App\Models\Purchases\PurchaseInvoice;
use App\Models\Products\Product;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PurchaseInvoiceItem extends Model
{
    protected $fillable = [
        'purchase_invoice_id', 'product_id', 'description', 'serials', 'qty',
        'unit_cost', 'discount', 'tax', 'total',
    ];

    protected function casts(): array
    {
        return [
            'serials' => 'array',
            'qty' => 'decimal:3',
            'unit_cost' => 'decimal:2',
            'discount' => 'decimal:2',
            'tax' => 'decimal:2',
            'total' => 'decimal:2',
        ];
    }

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(PurchaseInvoice::class, 'purchase_invoice_id');
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
