<?php

namespace Aropixel\MenuBundle\Tests\Integration\Source;

use Aropixel\MenuBundle\Entity\Menu;
use Aropixel\MenuBundle\Source\PageMenuSource;
use Aropixel\PageBundle\Entity\Page;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Routing\Exception\RouteNotFoundException;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class PageMenuSourceTest extends TestCase
{
    private function makeSource(
        ?EntityManagerInterface $em = null,
        ?ParameterBagInterface $params = null,
        ?UrlGeneratorInterface $router = null,
    ): PageMenuSource {
        $em ??= $this->createMock(EntityManagerInterface::class);
        $translator = $this->createMock(TranslatorInterface::class);
        $translator->method('trans')->willReturnArgument(0);
        $params ??= $this->makeParams();
        $router ??= $this->createMock(UrlGeneratorInterface::class);

        return new PageMenuSource($em, $params, $translator, $router, 'app_page_show');
    }

    private function makeParams(array $staticPages = []): ParameterBagInterface
    {
        $params = $this->createMock(ParameterBagInterface::class);
        $params->method('get')->willReturnCallback(function (string $key) use ($staticPages) {
            return match ($key) {
                'aropixel_menu.static_pages' => $staticPages,
                'kernel.bundles' => [],
                default => null,
            };
        });
        return $params;
    }

    // --- resolveUrl ---

    public function testResolveUrlReturnsHashWhenNoPageNorStaticPage(): void
    {
        $item = $this->createMock(Menu::class);
        $item->method('getStaticPage')->willReturn(null);
        $item->method('getPage')->willReturn(null);

        $source = $this->makeSource();
        $this->assertSame('#', $source->resolveUrl($item));
    }

    public function testResolveUrlReturnsHashOnRouteNotFoundException(): void
    {
        $page = $this->createMock(Page::class);
        $page->method('getSlug')->willReturn('services');

        $item = $this->createMock(Menu::class);
        $item->method('getStaticPage')->willReturn(null);
        $item->method('getPage')->willReturn($page);

        $router = $this->createMock(UrlGeneratorInterface::class);
        $router->method('generate')->willThrowException(new RouteNotFoundException());

        $source = $this->makeSource(router: $router);
        $this->assertSame('#', $source->resolveUrl($item));
    }

    // --- mapToEntity ---

    public function testMapToEntityStaticType(): void
    {
        $item = $this->createMock(Menu::class);
        $item->expects($this->once())->method('setStaticPage')->with('homepage');
        $item->expects($this->once())->method('setPage')->with(null);

        $params = $this->makeParams(['homepage' => 'app_homepage']);
        $source = $this->makeSource(params: $params);
        $source->mapToEntity(['type' => 'static', 'value' => 'homepage'], $item);
    }

    public function testMapToEntityPageType(): void
    {
        $page = $this->createMock(Page::class);
        $page->method('getTitle')->willReturn('Services');

        $repo = $this->createMock(EntityRepository::class);
        $repo->method('find')->with(42)->willReturn($page);

        $em = $this->createMock(EntityManagerInterface::class);
        $em->method('getRepository')->willReturn($repo);

        $item = $this->createMock(Menu::class);
        $item->expects($this->once())->method('setPage')->with($page);
        $item->expects($this->once())->method('setStaticPage')->with(null);

        $source = $this->makeSource(em: $em);
        $source->mapToEntity(['type' => 'page', 'value' => 42], $item);
    }

    // --- getPayload ---

    public function testGetPayloadWithPage(): void
    {
        $page = $this->createMock(Page::class);
        $page->method('getId')->willReturn(5);

        $item = $this->createMock(Menu::class);
        $item->method('getStaticPage')->willReturn(null);
        $item->method('getPage')->willReturn($page);

        $source = $this->makeSource();
        $this->assertSame(['type' => 'page', 'value' => 5], $source->getPayload($item));
    }

    public function testGetPayloadWithStaticPage(): void
    {
        $item = $this->createMock(Menu::class);
        $item->method('getStaticPage')->willReturn('homepage');
        $item->method('getPage')->willReturn(null);

        $source = $this->makeSource();
        $this->assertSame(['type' => 'static', 'value' => 'homepage'], $source->getPayload($item));
    }
}
