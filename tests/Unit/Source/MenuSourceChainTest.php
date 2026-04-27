<?php

namespace Aropixel\MenuBundle\Tests\Unit\Source;

use Aropixel\MenuBundle\Entity\Menu;
use Aropixel\MenuBundle\Entity\MenuInterface;
use Aropixel\MenuBundle\Source\MenuSourceChain;
use Aropixel\MenuBundle\Source\MenuSourceInterface;
use PHPUnit\Framework\TestCase;

class MenuSourceChainTest extends TestCase
{
    private function makeMenuItem(string $type): Menu
    {
        $item = $this->createMock(Menu::class);
        $item->method('getType')->willReturn($type);
        return $item;
    }

    private function makeSource(string $name, string $supports): MenuSourceInterface
    {
        $source = $this->createMock(MenuSourceInterface::class);
        $source->method('getName')->willReturn($name);
        $source->method('supports')->willReturnCallback(fn(string $type) => $type === $supports);
        $source->method('resolveUrl')->willReturn('https://resolved/' . $name);
        return $source;
    }

    public function testRoutesToCorrectSource(): void
    {
        $sourceA = $this->makeSource('page', 'page');
        $sourceB = $this->makeSource('link', 'link');

        $chain = new MenuSourceChain([$sourceA, $sourceB]);
        $item = $this->makeMenuItem('page');

        $this->assertSame('https://resolved/page', $chain->resolveUrl($item));
    }

    public function testReturnsHashWhenNoSourceSupports(): void
    {
        $sourceA = $this->makeSource('page', 'page');

        $chain = new MenuSourceChain([$sourceA]);
        $item = $this->makeMenuItem('unknown');

        $this->assertSame('#', $chain->resolveUrl($item));
    }

    public function testReturnsFirstMatchingSourceWhenMultipleSupport(): void
    {
        $sourceA = $this->makeSource('first', 'page');
        $sourceB = $this->makeSource('second', 'page');

        $chain = new MenuSourceChain([$sourceA, $sourceB]);
        $item = $this->makeMenuItem('page');

        $this->assertSame('https://resolved/first', $chain->resolveUrl($item));
    }

    public function testNullTypeReturnsHash(): void
    {
        $item = $this->createMock(Menu::class);
        $item->method('getType')->willReturn(null);

        $chain = new MenuSourceChain([]);

        $this->assertSame('#', $chain->resolveUrl($item));
    }
}
