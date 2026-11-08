<?php

namespace App\Services;

use RuntimeException;

/** A scan that can't hand the parcel to the rider; the message is shown to the rider. */
class RiderParcelScanException extends RuntimeException
{
    public function __construct(string $message, private int $status = 422)
    {
        parent::__construct($message);
    }

    public function status(): int
    {
        return $this->status;
    }
}
