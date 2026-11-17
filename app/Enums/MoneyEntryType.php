<?php

namespace App\Enums;

enum MoneyEntryType: string
{
    case Expense = 'expense';
    case Revenue = 'revenue';
}
