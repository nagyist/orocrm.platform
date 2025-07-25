<?php

namespace Oro\Bundle\TranslationBundle\Tests\Unit\Translation\KeySource;

use Oro\Bundle\TranslationBundle\Translation\KeySource\DynamicTranslationKeySource;
use Oro\Bundle\TranslationBundle\Translation\TranslationKeyTemplateInterface;
use PHPUnit\Framework\TestCase;

class DynamicTranslationKeySourceTest extends TestCase
{
    public function testNonConfiguredCalls(): void
    {
        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage(
            'Can\'t build source without template. Please configure source by ->configure($template) method.'
        );

        $dynamicSource = new DynamicTranslationKeySource();
        $dynamicSource->getTemplate();
    }

    public function testMergedConfiguredData(): void
    {
        $template = $this->createMock(TranslationKeyTemplateInterface::class);
        $template->expects($this->once())
            ->method('getRequiredKeys')
            ->willReturn([]);

        $dynamicSource = new DynamicTranslationKeySource(['a' => 1, 'c' => 42]);
        $dynamicSource->configure($template, ['b' => 2, 'a' => 3]);

        $this->assertEquals(
            [
                'c' => 42,
                'b' => 2,
                'a' => 3
            ],
            $dynamicSource->getData()
        );
    }

    public function testConstructorDataValidationFailure(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage(
            'Expected not empty value for key "some_key" in data, null given for template '
        );

        $templateMock = $this->createMock(TranslationKeyTemplateInterface::class);
        $templateMock->expects($this->once())
            ->method('getRequiredKeys')
            ->willReturn(['some_key']);

        $dynamicSource = new DynamicTranslationKeySource();
        $dynamicSource->configure($templateMock, ['some_other_key' => 'someValue']);
    }

    public function testGetTemplate(): void
    {
        $template = $this->createMock(TranslationKeyTemplateInterface::class);
        $template->expects($this->once())
            ->method('getRequiredKeys')
            ->willReturn([]);
        $template->expects($this->once())
            ->method('getTemplate')
            ->willReturn('template string');

        $dynamicSource = new DynamicTranslationKeySource();
        $dynamicSource->configure($template);

        $this->assertEquals('template string', $dynamicSource->getTemplate());
    }
}
