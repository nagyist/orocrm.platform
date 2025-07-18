<?php

namespace Oro\Bundle\TranslationBundle\Tests\Unit\Translation\KeySource;

use Oro\Bundle\TranslationBundle\Translation\KeySource\TranslationKeySource;
use Oro\Bundle\TranslationBundle\Translation\TranslationKeyTemplateInterface;
use PHPUnit\Framework\TestCase;

class TranslationKeySourceTest extends TestCase
{
    public function testConstructorDataValidation(): void
    {
        $data = ['some_key' => 'someValue'];
        $templateMock = $this->createMock(TranslationKeyTemplateInterface::class);
        $templateMock->expects($this->once())
            ->method('getRequiredKeys')
            ->willReturn(['some_key']);

        $source = new TranslationKeySource($templateMock, $data);
        $this->assertEquals($data, $source->getData());
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

        new TranslationKeySource($templateMock, ['some_other_key' => 'someValue']);
    }

    public function testGetTemplate(): void
    {
        $templateMock = $this->createMock(TranslationKeyTemplateInterface::class);
        $templateMock->expects($this->once())
            ->method('getRequiredKeys')
            ->willReturn([]);

        $templateMock->expects($this->once())
            ->method('getTemplate')
            ->willReturn('templateString');

        $source = new TranslationKeySource($templateMock);
        $this->assertEquals('templateString', $source->getTemplate());
    }

    public function testGetData(): void
    {
        $data = ['some_key' => 'someValue'];
        $templateMock = $this->createMock(TranslationKeyTemplateInterface::class);
        $templateMock->expects($this->once())
            ->method('getRequiredKeys')
            ->willReturn([]);

        $source = new TranslationKeySource($templateMock, $data);
        $this->assertEquals($data, $source->getData());
    }
}
