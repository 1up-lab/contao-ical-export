<?php

declare(strict_types=1);

namespace Oneup\Contao\ICalExportBundle\Tests\Calendar;

use Eluceo\iCal\Util\DateUtil;
use Oneup\Contao\ICalExportBundle\Calendar\CalendarCreator;
use PHPUnit\Framework\TestCase;

class CalendarCreatorTest extends TestCase
{
    public function testGeneratesValidObject(): void
    {
        $now = new \DateTime();
        $calendarCreator = new CalendarCreator();

        $calendar = $calendarCreator->createCalendar(
            'https://domain.com',
            'Europe/Zurich'
        );

        $event = $calendarCreator->createEvent(
            'Europe/Zurich',
            false,
            'Fadenstrasse 20, 6020 Emmenbrücke',
            'Büro 1up GmbH',
            1637598600,
            1637598660,
            'Test Event'
        );

        $calendar->addComponent($event);

        $data = $calendar->build();

        $this->assertSame('BEGIN:VCALENDAR', $data[0]);
        $this->assertSame('VERSION:2.0', $data[1]);
        $this->assertSame('PRODID:https://domain.com', $data[2]);
        $this->assertSame('X-WR-TIMEZONE:Europe/Zurich', $data[3]);
        $this->assertSame('BEGIN:VTIMEZONE', $data[4]);
        $this->assertSame('TZID:Europe/Zurich', $data[5]);
        $this->assertSame('X-LIC-LOCATION:Europe/Zurich', $data[6]);
        $this->assertSame('END:VTIMEZONE', $data[7]);
        $this->assertSame('BEGIN:VEVENT', $data[8]);
        $this->assertSame('DTSTART;TZID=Europe/Zurich:20211122T173000', $data[10]);
        $this->assertSame('SEQUENCE:0', $data[11]);
        $this->assertSame('TRANSP:OPAQUE', $data[12]);
        $this->assertSame('DTEND;TZID=Europe/Zurich:20211122T173100', $data[13]);
        $this->assertSame('LOCATION:Fadenstrasse 20\, 6020 Emmenbrücke', $data[14]);
        $this->assertSame('SUMMARY:Test Event', $data[15]);
        $this->assertSame('CLASS:PUBLIC', $data[16]);
        $this->assertSame('DTSTAMP:' . DateUtil::getDateString($now, false,  false, true), $data[17]);
        $this->assertSame('END:VEVENT', $data[18]);
        $this->assertSame('END:VCALENDAR', $data[19]);
    }
}
