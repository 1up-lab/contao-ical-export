<?php

declare(strict_types=1);

namespace Oneup\Contao\ICalExportBundle\Tests;

use Oneup\Contao\ICalExportBundle\OneupContaoICalExportBundle;
use PHPUnit\Framework\TestCase;

class OneupContaoICalExportBundleTest extends TestCase
{
    public function testClassInstantiation(): void
    {
        $bundle = new OneupContaoICalExportBundle();

        $this->assertInstanceOf("Oneup\Contao\IcalExportBundle\OneupContaoICalExportBundle", $bundle);
    }

    public function testGetExpectedContainerExtension(): void
    {
        $bundle = new OneupContaoICalExportBundle();

        $this->assertInstanceOf(
            "Oneup\Contao\IcalExportBundle\DependencyInjection\OneupContaoICalExportExtension",
            $bundle->getContainerExtension()
        );
    }
}
