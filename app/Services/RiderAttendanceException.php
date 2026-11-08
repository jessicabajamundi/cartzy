<?php

namespace App\Services;

use RuntimeException;

/** A time in / time out scan that was refused. The code is the HTTP status to answer with. */
class RiderAttendanceException extends RuntimeException
{
}
