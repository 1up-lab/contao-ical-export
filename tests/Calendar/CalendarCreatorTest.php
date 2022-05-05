<?php

declare(strict_types=1);

namespace Oneup\Contao\ICalExportBundle\Tests\Calendar;

use Eluceo\iCal\Domain\ValueObject\Timestamp;
use Eluceo\iCal\Presentation\Component\Property\Value\DateTimeValue;
use ICal\Event;
use ICal\ICal;
use Oneup\Contao\ICalExportBundle\Calendar\CalendarCreator;
use PHPUnit\Framework\TestCase;

class CalendarCreatorTest extends TestCase
{
    /**
     * @dataProvider getTimeZoneTestData
     */
    public function testGeneratesValidObject(string $timezone, string $startTime, string $endTime): void
    {
        date_default_timezone_set($timezone);

        $now = new \DateTime();
        $calendarCreator = new CalendarCreator();

        $calendar = $calendarCreator->createCalendar($timezone);

        $event = $calendarCreator->createEvent(
            'https://domain.com/foo/bar?page=1',
            'Fadenstrasse 20, 6020 Emmenbrücke',
            'Büro 1up GmbH',
            1637598600,
            1637598660,
            'Test Event',
            'Test Event Description'
        );

        $calendar->addEvent($event);

        $ical = new ICal((string) $calendarCreator->createComponent($calendar, $timezone));

        /** @var Event $event */
        $event = $ical->events()[0];

        $this->assertSame('Test Event', $event->summary);
        $this->assertSame($startTime, $event->dtstart);
        $this->assertSame($endTime, $event->dtend);
        $this->assertSame((string) new DateTimeValue(new Timestamp($now)), $event->dtstamp);
        $this->assertSame($startTime, $event->dtstart_tz);
        $this->assertSame($endTime, $event->dtend_tz);
        $this->assertSame('Test Event Description', $event->description);
        $this->assertSame('Fadenstrasse 20, 6020 Emmenbrücke', $event->location);
        $this->assertSame('https://domain.com/foo/bar?page=1', $event->url);
        $this->assertSame($timezone, $ical->calendarTimeZone());
    }

    public function getTimeZoneTestData(): array
    {
        return [
            ['UTC', '20211122T163000', '20211122T163100'],
            ['Europe/Lisbon', '20211122T163000', '20211122T163100'],
            ['Europe/Zurich', '20211122T173000', '20211122T173100'],
            ['Atlantic/Azores', '20211122T153000', '20211122T153100'],
        ];
    }
}
