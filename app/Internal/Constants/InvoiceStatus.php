<?php


namespace App\Internal\Constants;


class InvoiceStatus
{
    const AWAIT_DELIVERY = 1;
    const PARTIALLY_IN_STOCK = 2;
    const CLAIMS = 3;
    const IN_STOCK = 4;
    const COMPLAINT_WORK = 5;
    const ARCHIVED = 6;
}
