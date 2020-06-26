<?php


namespace App\Internal\Constants;


class ItemStatus
{
    const AWAIT_DELIVERY = 1;
    const PARTIALLY_IN_STOCK = 2;
    const IN_STOCK = 3;
    const CLAIMS = 4;
    const SHIPPED = 5;
}
