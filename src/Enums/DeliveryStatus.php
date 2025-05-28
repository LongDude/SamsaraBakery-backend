<?php

namespace App\Enum;

enum DeliveryStatus: string{
    case REQUEST_SENT = 'Request sent';
    case IN_PROGRESS = 'In progress';
    case RECEIVED = 'Ingredients received';
    case CLOSED = 'Closed';
}