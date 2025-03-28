<?php

namespace Oro\Bundle\ActionBundle\Tests\Functional\Command;

use Oro\Bundle\TestFrameworkBundle\Test\WebTestCase;
use Oro\Component\ConfigExpression\FactoryWithTypesInterface;

abstract class AbstractDebugCommandTestCase extends WebTestCase
{
    /** @var FactoryWithTypesInterface */
    protected $factory;

    #[\Override]
    protected function setUp(): void
    {
        $this->initClient();
        $this->factory = $this->getContainer()->get($this->getFactoryServiceId());
    }

    public function testExecute(): void
    {
        $typeNames = array_keys($this->factory->getTypes());
        $result = $this->runCommand($this->getCommandName());
        self::assertStringContainsString('Short Description', $result);
        foreach ($typeNames as $name) {
            self::assertStringContainsString($name, $result);
        }
    }

    public function testExecuteWithArgument(): void
    {
        $types = $this->factory->getTypes();
        $typeNames = array_keys($types);
        $name = array_shift($typeNames);
        $result = $this->runCommand($this->getCommandName(), [$name]);
        self::assertStringContainsString('Full Description', $result);
        self::assertStringContainsString($name, $result);
        self::assertStringContainsString(array_shift($types), $result);
    }

    public function testExecuteWithNotExistsArgument(): void
    {
        $name = 'some_not_exists_name';
        $result = $this->runCommand($this->getCommandName(), [$name]);

        $this->assertEquals(sprintf('Type "%s" is not found', $name), $result);
    }

    abstract protected function getFactoryServiceId(): string;

    abstract protected function getCommandName(): string;
}
