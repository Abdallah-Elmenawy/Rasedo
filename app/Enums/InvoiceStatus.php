<?php

namespace App\Enums;

enum InvoiceStatus: string
{
    case Draft = 'draft';
    case Confirmed = 'confirmed';
    case Partial = 'partial';
    case Paid = 'paid';
    case Cancelled = 'cancelled';

    /**
     * Statuses that count as posted documents in reports and balances.
     *
     * @return list<string>
     */
    public static function posted(): array
    {
        return [self::Confirmed->value, self::Partial->value, self::Paid->value];
    }

    /**
     * Statuses that still accept a collection or payment.
     *
     * @return list<string>
     */
    public static function collectable(): array
    {
        return [self::Confirmed->value, self::Partial->value];
    }
}
