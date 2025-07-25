<?php

namespace Oro\Component\Layout\Tests\Unit\Extension\Theme\ResourceProvider;

use Oro\Component\Layout\Extension\Theme\ResourceProvider\LayoutUpdateFileMatcher;
use PHPUnit\Framework\TestCase;

class LayoutUpdateFileMatcherTest extends TestCase
{
    /**
     * @param string $fileName
     *
     * @return \SplFileInfo
     */
    private function getFile($fileName)
    {
        $path = realpath(
            __DIR__
            . '/../../../Fixtures/Bundle/TestBundle/Resources/views/layouts/oro-default/'
            . $fileName
        );
        self::assertFileExists($path);

        return new \SplFileInfo($path);
    }

    public function testIsMatchedWhenNoPatterns(): void
    {
        $matcher = new LayoutUpdateFileMatcher([], []);

        self::assertTrue($matcher->isMatched($this->getFile('resource1.yml')));
    }

    public function testIsMatchedWithoutExcludePatterns(): void
    {
        $matcher = new LayoutUpdateFileMatcher(['/\.xml$/', '/\.yml$/'], []);

        self::assertTrue($matcher->isMatched($this->getFile('resource1.yml')));
        self::assertFalse($matcher->isMatched($this->getFile('resource2.txt')));
        self::assertTrue($matcher->isMatched($this->getFile('theme.yml')));
    }

    public function testIsMatchedWithExcludePatterns(): void
    {
        $matcher = new LayoutUpdateFileMatcher(
            ['/\.xml$/', '/\.yml$/'],
            ['#Resources/views/layouts/[\w\-]+/theme\.yml$#']
        );

        self::assertTrue($matcher->isMatched($this->getFile('resource1.yml')));
        self::assertFalse($matcher->isMatched($this->getFile('resource2.txt')));
        self::assertFalse($matcher->isMatched($this->getFile('theme.yml')));
    }

    public function testSerialization(): void
    {
        $matcher = new LayoutUpdateFileMatcher(
            ['/\.xml$/', '/\.yml$/'],
            ['#Resources/views/layouts/[\w\-]+/theme\.yml$#']
        );

        $unserialized = unserialize(serialize($matcher));
        $this->assertEquals($matcher, $unserialized);
        $this->assertNotSame($matcher, $unserialized);
    }
}
