<?php

namespace App\Support;

use App\Models\Products\Product;
use App\Models\Products\ProductSerial;
use App\Models\Purchases\PurchaseInvoice;
use App\Models\Sales\SalesInvoice;
use RuntimeException;

class InvoiceSerials
{
    public static function normalize(mixed $rows): array
    {
        $clean = [];
        foreach ((array) $rows as $row) {
            $code = trim((string) (is_array($row) ? ($row['code'] ?? '') : $row));
            if ($code === '') {
                continue;
            }
            $clean[] = [
                'code' => $code,
                'kind' => is_array($row) && ($row['kind'] ?? '') === 'box' ? 'box' : 'piece',
            ];
        }

        return $clean;
    }

    public static function receive(PurchaseInvoice $invoice): void
    {
        $invoice->loadMissing('items.product');
        foreach ($invoice->items as $item) {
            foreach (self::normalize($item->serials) as $row) {
                $exists = ProductSerial::query()->where('serial', $row['code'])->lockForUpdate()->first();
                if ($exists) {
                    throw new RuntimeException('السيريال مسجّل من قبل: '.$row['code']);
                }
                $pieces = $row['kind'] === 'box' ? max(1, (int) $item->product?->pieces_per_box) : 1;
                if ($row['kind'] === 'box' && $pieces < 2) {
                    throw new RuntimeException('حدد عدد القطع داخل العلبة للصنف '.$item->product?->name.'.');
                }
                ProductSerial::query()->create([
                    'product_id' => $item->product_id,
                    'serial' => $row['code'],
                    'kind' => $row['kind'],
                    'status' => 'in_stock',
                    'purchase_invoice_id' => $invoice->id,
                ]);
            }
        }
    }

    public static function sell(SalesInvoice $invoice): void
    {
        $invoice->loadMissing('items');
        foreach ($invoice->items as $item) {
            foreach (self::normalize($item->serials) as $row) {
                $serial = ProductSerial::query()
                    ->where('product_id', $item->product_id)
                    ->where('serial', $row['code'])
                    ->lockForUpdate()
                    ->first();
                if (! $serial || $serial->status !== 'in_stock') {
                    throw new RuntimeException('السيريال غير متاح: '.$row['code']);
                }
                $serial->update(['status' => 'sold', 'sales_invoice_id' => $invoice->id]);
            }
        }
    }

    public static function pieces(Product $product, string $kind): int
    {
        if ($kind === 'box') {
            return max(1, (int) $product->pieces_per_box);
        }

        return 1;
    }
}
