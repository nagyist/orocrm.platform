<?php

namespace Oro\Bundle\ImportExportBundle\Tests\Unit\TemplateFixture;

use Oro\Bundle\ImportExportBundle\TemplateFixture\EmptyFixture;
use Oro\Bundle\ImportExportBundle\TemplateFixture\TemplateEntityRegistry;
use Oro\Bundle\ImportExportBundle\TemplateFixture\TemplateManager;
use PHPUnit\Framework\TestCase;

class EmptyFixtureTest extends TestCase
{
    public function testGetEntityClass(): void
    {
        $fixture = new EmptyFixture('\stdClass');
        $this->assertEquals('\stdClass', $fixture->getEntityClass());
    }

    public function testGetData(): void
    {
        $templateManager = new TemplateManager(new TemplateEntityRegistry());
        $templateManager->addEntityRepository(new EmptyFixture('\stdClass'));

        $fixture = $templateManager->getEntityFixture('\stdClass');
        $data = $fixture->getData();
        $this->assertCount(1, $data);
        $this->assertInstanceOf('\stdClass', $data->current());
    }
}
