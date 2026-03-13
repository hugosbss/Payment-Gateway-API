<?php

namespace App;

enum TransactionStatus
{
    case Pending;
    case Paid;
    case Failed;
    case Refunded;
}
