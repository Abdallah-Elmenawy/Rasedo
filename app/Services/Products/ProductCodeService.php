<?php

namespace App\Services\Products;

use App\Models\Products\Product;
use App\Models\Products\ProductSerial;
use App\Support\InvoiceSerials;
use Milon\Barcode\DNS1D;
use Milon\Barcode\DNS2D;
use RuntimeException;

class ProductCodeService
{
    public function assign(Product $product): Product
    {
        $scanned = trim((string) $product->sku);
        $generated = 'PRD-'.str_pad((string) $product->id, 6, '0', STR_PAD_LEFT);
        $code = $generated;
        if ($scanned !== '' && mb_strlen($scanned) <= 32 && ! Product::query()->where('scan_code', $scanned)->whereKeyNot($product->id)->exists()) {
            $code = $scanned;
        }
        $product->forceFill(['scan_code' => $code])->save();
        try {
            $this->png($code, 'barcode');
            $this->png($code, 'qr');
        } catch (RuntimeException) {
            if ($code !== $generated) {
                $product->forceFill(['scan_code' => $generated])->save();
                $this->png($generated, 'barcode');
                $this->png($generated, 'qr');
            }
        }

        return $product;
    }

    public function png(string $code, string $kind): string
    {
        $encoded = $kind === 'qr'
            ? (new DNS2D)->getBarcodePNG($code, 'QRCODE', 6, 6, [30, 78, 155], [255, 255, 255])
            : (new DNS1D)->getBarcodePNG($code, 'C128', 2, 70, [30, 78, 155], false, [255, 255, 255]);
        $binary = base64_decode((string) $encoded, true);
        if ($binary === false || $binary === '') {
            throw new RuntimeException('تعذر توليد كود الصنف.');
        }

        return $binary;
    }

    public function find(string $code): array
    {
        $found = $this->lookup($code, 'sale');

        return ['product' => $found['product_model'], 'serial' => $found['serial_model']];
    }

    public function lookup(string $code, string $context): array
    {
        $code = trim($code);
        if ($code === '') {
            throw new RuntimeException('أدخل الكود أو امسحه.');
        }

        $serial = ProductSerial::query()->where('serial', $code)->with('product')->first();
        if ($serial) {
            if ($context === 'purchase') {
                throw new RuntimeException('هذا السيريال مسجّل من قبل.');
            }
            if ($serial->status !== 'in_stock' || ! $serial->product?->is_active) {
                throw new RuntimeException('هذا السيريال غير متاح للبيع.');
            }
            $kind = $serial->kind === 'box' ? 'box' : 'piece';

            return [
                'mode' => 'serial',
                'product' => $this->payload($serial->product, $serial),
                'kind' => $kind,
                'pieces' => InvoiceSerials::pieces($serial->product, $kind),
                'product_model' => $serial->product,
                'serial_model' => $serial,
            ];
        }

        $product = Product::query()
            ->where(function ($query) use ($code) {
                $query->where('scan_code', $code)->orWhere('sku', $code);
            })
            ->first();
        if ($product && $product->is_active) {
            return [
                'mode' => 'product',
                'product' => $this->payload($product),
                'kind' => 'piece',
                'pieces' => 1,
                'product_model' => $product,
                'serial_model' => null,
            ];
        }

        if ($context === 'purchase') {
            return [
                'mode' => 'new_serial',
                'code' => $code,
                'product' => null,
                'kind' => 'piece',
                'pieces' => 1,
                'product_model' => null,
                'serial_model' => null,
            ];
        }

        throw new RuntimeException('لا يوجد صنف بهذا الكود.');
    }

    public function payload(Product $product, ?ProductSerial $serial = null): array
    {
        return [
            'id' => $product->id,
            'name' => $product->name,
            'sku' => $product->sku,
            'scan_code' => $product->scan_code,
            'unit' => $product->unit,
            'price' => (float) $product->sale_price,
            'tax_rate' => (float) $product->tax_rate,
            'stock' => (float) $product->current_stock,
            'type' => $product->type,
            'category_id' => $product->category_id,
            'track_serial' => (bool) $product->track_serial,
            'pieces_per_box' => (int) ($product->pieces_per_box ?: 0),
            'letter' => mb_substr($product->name, 0, 1),
            'serial' => $serial?->serial,
            'serial_kind' => $serial?->kind === 'box' ? 'box' : 'piece',
            'serial_pieces' => $serial ? InvoiceSerials::pieces($product, $serial->kind === 'box' ? 'box' : 'piece') : 1,
            'batch_no' => $serial?->batch_no,
        ];
    }
}
