<?php

namespace App\Enum;

enum TransactionStatusEnum: string
{
    case PUBLISHED = "published";
    case REVIEW = "review";
}
