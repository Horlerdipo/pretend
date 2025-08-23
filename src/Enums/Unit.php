<?php

namespace Horlerdipo\Pretend\Enums;

enum Unit: string
{
    case Microsecond = 'microsecond';
    case Millisecond = 'millisecond';
    case Second = 'second';
    case Minute = 'minute';
    case Hour = 'hour';
    case Day = 'day';
    case Week = 'week';
    case Month = 'month';
    case Quarter = 'quarter';
    case Year = 'year';
    case Decade = 'decade';
    case Century = 'century';
    case Millennium = 'millennium';
}
