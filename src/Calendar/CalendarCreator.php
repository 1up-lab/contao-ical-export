<?php

declare(strict_types=1);

namespace Oneup\Contao\ICalExportBundle\Calendar;

use Eluceo\iCal\Component\Calendar;
use Eluceo\iCal\Component\Event;
use Eluceo\iCal\Component\Timezone;

class CalendarCreator
{
    public function createCalendar(string $url, string $timezone): Calendar
    {
        $calendar = new Calendar($url);

        $tz = new Timezone($timezone);
        $calendar->setTimezone($tz);

        return $calendar;
    }

    public function createEvent(string $timezone, bool $noTime, string $address, string $location, int $start, int $end, string $title): Event
    {
        $dateTimeZone = new \DateTimeZone($timezone);

        $event = new Event();
        $event->setDtStart(\DateTime::createFromFormat('d.m.Y - H:i:s', date('d.m.Y - H:i:s', $start), $dateTimeZone));
        $event->setDtEnd(\DateTime::createFromFormat('d.m.Y - H:i:s', date('d.m.Y - H:i:s', $end), $dateTimeZone));
        $event->setSummary($title);
        $event->setLocation($address, $location);
        $event->setNoTime($noTime);
        $event->setUseTimezone(true);

        return $event;
    }
}
