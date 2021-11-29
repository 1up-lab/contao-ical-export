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
    public function testGeneratesValidObject(): void
    {
        $now = new \DateTime();
        $calendarCreator = new CalendarCreator();

        $calendar = $calendarCreator->createCalendar(
            'Europe/Zurich'
        );

        $event = $calendarCreator->createEvent(
            'Europe/Zurich',
            'https://domain.com',
            'Fadenstrasse 20, 6020 Emmenbrücke',
            'Büro 1up GmbH',
            1637598600,
            1637598660,
            'Test Event',
            'Test Event Description'
        );

        $calendar->addEvent($event);

        $ical = new ICal((string) $calendarCreator->createCalendarComponent($calendar));

        /** @var Event $event */
        $event = $ical->events()[0];

        $start = $event->dtstart_array;
        $end = $event->dtend_array;

        $this->assertSame('Test Event', $event->summary);
        $this->assertSame('20211122T173000', $event->dtstart);
        $this->assertSame('20211122T173100', $event->dtend);
        $this->assertSame((string) new DateTimeValue(new Timestamp($now)), $event->dtstamp);
        $this->assertSame('20211122T173000', $event->dtstart_tz);
        $this->assertSame('20211122T173100', $event->dtend_tz);
        $this->assertSame('Test Event Description', $event->description);
        $this->assertSame('Fadenstrasse 20, 6020 Emmenbrücke', $event->location);
        $this->assertSame('https://domain.com', $event->url);
        $this->assertSame('Europe/Zurich', $start[0]['TZID']);
        $this->assertSame('TZID=Europe/Zurich:20211122T173000', $start[3]);
        $this->assertSame('Europe/Zurich', $end[0]['TZID']);
        $this->assertSame('TZID=Europe/Zurich:20211122T173100', $end[3]);
    }
}
