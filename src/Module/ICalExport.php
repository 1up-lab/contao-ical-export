<?php

declare(strict_types=1);

namespace Oneup\Contao\ICalExportBundle\Module;

use Contao\BackendTemplate;
use Contao\CalendarEventsModel;
use Contao\Environment;
use Contao\Events;
use Contao\Input;
use Contao\StringUtil;
use Eluceo\iCal\Component\Calendar;
use Eluceo\iCal\Component\Event;
use Patchwork\Utf8;

class ICalExport extends Events
{
    protected $strTemplate = 'mod_ical_export';

    public function generate(): string
    {
        if (TL_MODE === 'BE') {
            $objTemplate = new BackendTemplate('be_wildcard');

            $objTemplate->wildcard = '### ' . Utf8::strtoupper($GLOBALS['TL_LANG']['FMD']['ical_export'][0]) . ' ###';
            $objTemplate->title = $this->headline;
            $objTemplate->id = $this->id;
            $objTemplate->link = $this->name;
            $objTemplate->href = 'contao/main.php?do=themes&amp;table=tl_module&amp;act=edit&amp;id=' . $this->id;

            return $objTemplate->parse();
        }

        // Set the item from the auto_item parameter
        if (!isset($_GET['events']) && $GLOBALS['TL_CONFIG']['useAutoItem'] && isset($_GET['auto_item'])) {
            Input::setGet('events', Input::get('auto_item'));
        }

        // Do not index or cache the page if no event has been specified
        if (!Input::get('events')) {
            global $objPage;
            $objPage->noSearch = 1;
            $objPage->cache = 0;

            return '';
        }

        $this->cal_calendar = $this->sortOutProtected(deserialize($this->cal_calendar));

        // Do not index or cache the page if there are no calendars
        if (!\is_array($this->cal_calendar) || empty($this->cal_calendar)) {
            global $objPage;
            $objPage->noSearch = 1;
            $objPage->cache = 0;

            return '';
        }

        return parent::generate();
    }

    public function sendIcsFile(CalendarEventsModel $objEvent): void
    {
        $vCalendar = new Calendar(Environment::get('url'));
        $vEvent = new Event();
        $noTime = false;

        if ($objEvent->startTime === $objEvent->startDate && $objEvent->endTime === $objEvent->endDate) {
            $noTime = true;
        }

        $address = $location = strip_tags(StringUtil::decodeEntities(self::replaceInsertTags($objEvent->location)));

        if (null !== $objEvent->address && \strlen($objEvent->address)) {
            $address = strip_tags(StringUtil::decodeEntities(self::replaceInsertTags($objEvent->address)));
        }

        $vEvent
            ->setDtStart(\DateTime::createFromFormat('d.m.Y - H:i:s', date('d.m.Y - H:i:s', (int) $objEvent->startTime)))
            ->setDtEnd(\DateTime::createFromFormat('d.m.Y - H:i:s', date('d.m.Y - H:i:s', (int) $objEvent->endTime)))
            ->setSummary(strip_tags(StringUtil::decodeEntities(self::replaceInsertTags($objEvent->title))))
            ->setUseUtc(false)
            ->setLocation($address, $location)
            ->setNoTime($noTime)
        ;

        // HOOK: modify the vEvent
        if (isset($GLOBALS['TL_HOOKS']['modifyIcsFile']) && \is_array($GLOBALS['TL_HOOKS']['modifyIcsFile'])) {
            foreach ($GLOBALS['TL_HOOKS']['modifyIcsFile'] as $callback) {
                $this->import($callback[0]);
                $this->{$callback[0]}->{$callback[1]}($vEvent, $objEvent, $this);
            }
        }

        $vCalendar->addComponent($vEvent);

        header('Content-Type: text/calendar; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $objEvent->alias . '.ics"');

        echo $vCalendar->render();

        exit;
    }

    protected function compile(): void
    {
        $objEvent = CalendarEventsModel::findPublishedByParentAndIdOrAlias(Input::get('events'), $this->cal_calendar);

        if ('' === Input::get('ics') && null !== $objEvent) {
            $this->sendIcsFile($objEvent);
        }

        $query = parse_url(Environment::get('request'), PHP_URL_QUERY);

        $this->Template->href = Environment::get('request') . (null === $query ? '?ics' : '&ics');
        $this->Template->title = $GLOBALS['TL_LANG']['MSC']['ical_download'];
        $this->Template->link = $GLOBALS['TL_LANG']['MSC']['ical_download'];
        $this->Template->objEvent = $objEvent;
    }
}
