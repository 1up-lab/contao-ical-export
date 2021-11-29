<?php

declare(strict_types=1);

namespace Oneup\Contao\ICalExportBundle\Calendar;

use DateTimeZone as PhpDateTimeZone;
use Eluceo\iCal\Domain\Entity\Calendar;
use Eluceo\iCal\Domain\Entity\Event;
use Eluceo\iCal\Domain\Entity\TimeZone;
use Eluceo\iCal\Domain\ValueObject\DateTime;
use Eluceo\iCal\Domain\ValueObject\Location;
use Eluceo\iCal\Domain\ValueObject\TimeSpan;
use Eluceo\iCal\Domain\ValueObject\Uri;
use Eluceo\iCal\Presentation\Component;
use Eluceo\iCal\Presentation\Factory\CalendarFactory;

class CalendarCreator
{
    public function createCalendar(string $timezone): Calendar
    {
        return (new Calendar())
            ->addTimeZone(TimeZone::createFromPhpDateTimeZone(new PhpDateTimeZone($timezone)))
        ;
    }

    public function createEvent(string $timezone, string $url, string $address, string $location, int $start, int $end, string $title, string $description = ''): Event
    {
        $dateTimeZone = new \DateTimeZone($timezone);

        $occurenceStart = new DateTime(\DateTime::createFromFormat('d.m.Y - H:i:s', date('d.m.Y - H:i:s', $start), $dateTimeZone), true);
        $occurenceEnd = new DateTime(\DateTime::createFromFormat('d.m.Y - H:i:s', date('d.m.Y - H:i:s', $end), $dateTimeZone), true);
        $occurrence = new TimeSpan($occurenceStart, $occurenceEnd);

        return (new Event())
            ->setSummary($title)
            ->setDescription($description)
            ->setUrl(new Uri($url))
            ->setOccurrence($occurrence)
            ->setLocation(new Location($address, $location))
        ;
    }

    public function createComponent(Calendar $calendar): Component
    {
        $componentFactory = new CalendarFactory();

        return $componentFactory->createCalendar($calendar);
    }
}
