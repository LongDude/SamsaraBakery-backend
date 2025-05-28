<?php

namespace App\Enum;

enum OrderStatus: string{
    case RECIEVED = 'Order recieved';
    case PROCESSING = 'Order processing';
    case APPROVED = 'Order approved';
    case IN_PRODUCTION = 'Order in production';
    case IN_STORAGE = 'Order is in storage';
    case BEING_DELIVERED = 'Order being delivered';
    case DELIVERED = 'Order delivered';
    case CLOSED = 'Order closed';
}