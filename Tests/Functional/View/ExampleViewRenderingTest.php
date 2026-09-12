<?php

declare(strict_types=1);

namespace Webconsulting\RecordsListExamples\Tests\Functional\View;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Psr\Http\Message\ServerRequestInterface;
use Symfony\Component\Routing\Route;
use TYPO3\CMS\Backend\Context\PageContextFactory;
use TYPO3\CMS\Backend\Module\ModuleData;
use TYPO3\CMS\Backend\Module\ModuleInterface;
use TYPO3\CMS\Backend\Module\ModuleProvider;
use TYPO3\CMS\Backend\Routing\Router;
use TYPO3\CMS\Core\Core\SystemEnvironmentBuilder;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Http\NormalizedParams;
use TYPO3\CMS\Core\Http\ServerRequest;
use TYPO3\CMS\Core\Localization\LanguageServiceFactory;
use TYPO3\CMS\Core\Site\Entity\NullSite;
use TYPO3\TestingFramework\Core\Functional\FunctionalTestCase;
use Webconsulting\RecordsListTypes\Controller\RecordListController;
use Webconsulting\RecordsListTypes\Service\ViewTypeRegistry;

/**
 * Boots records_list_types together with this extension and renders every
 * example view through the Records module controller: the TSconfig presets
 * register, the templates resolve and the labels translate.
 */
final class ExampleViewRenderingTest extends FunctionalTestCase
{
    private const int PAGE_ID = 1;
    private const array BUILTIN_VIEWS = ['list', 'grid', 'compact', 'teaser'];

    protected array $testExtensionsToLoad = [
        'webconsulting/records-list-types',
        'webconsulting/records-list-examples',
    ];

    protected function setUp(): void
    {
        parent::setUp();
        $this->importCSVDataSet(__DIR__ . '/../Fixtures/Pages.csv');
        $this->importCSVDataSet(__DIR__ . '/../Fixtures/BackendUsers.csv');
        $this->setUpBackendUser(1);
        $GLOBALS['LANG'] = $this->get(LanguageServiceFactory::class)->createFromUserPreferences($GLOBALS['BE_USER']);
    }

    /**
     * @return iterable<string, array{string, string, string}> view id, template, marker of the rendered template
     */
    public static function exampleViewProvider(): iterable
    {
        yield 'timeline' => ['timeline', 'TimelineView', 'rle-timeline__item'];
        yield 'catalog' => ['catalog', 'CatalogView', 'rle-catalog-card'];
        yield 'addressbook' => ['addressbook', 'CompactView', 'recordlist-compactview-container'];
        yield 'eventlist' => ['eventlist', 'TeaserView', 'recordlist-teaserview-container'];
        yield 'gallery' => ['gallery', 'GridView', 'recordlist-gridview-container'];
        yield 'dashboard' => ['dashboard', 'GridView', 'recordlist-gridview-container'];
    }

    /**
     * @return iterable<string, array{string, string}> view id, marker of a hidden record
     */
    public static function customViewProvider(): iterable
    {
        yield 'timeline' => ['timeline', 'rle-timeline__item--hidden'];
        yield 'catalog' => ['catalog', 'rle-catalog-card--hidden'];
    }

    /**
     * @return iterable<string, array{string}> view id
     */
    public static function customViewIdProvider(): iterable
    {
        foreach (self::customViewProvider() as $name => [$viewId]) {
            yield $name => [$viewId];
        }
    }

    #[Test]
    public function pageTsconfigRegistersEveryExampleViewNextToTheBuiltinViews(): void
    {
        $registry = $this->get(ViewTypeRegistry::class);
        $types = $registry->getViewTypes(self::PAGE_ID);

        $exampleViews = [];
        foreach (self::exampleViewProvider() as [$viewId, $template]) {
            $exampleViews[] = $viewId;
            self::assertSame($template, $registry->getTemplatePaths($viewId, self::PAGE_ID)['template'], $viewId);
            self::assertSame('records_list_examples.messages:viewMode.' . $viewId, $types[$viewId]['label'] ?? null, $viewId);
            self::assertSame('records_list_examples.messages:viewMode.' . $viewId . '.description', $types[$viewId]['description'] ?? null, $viewId);
        }
        self::assertSame(array_merge(self::BUILTIN_VIEWS, $exampleViews), array_keys($types));
    }

    #[Test]
    public function exampleViewsAreSelectableWhereThePageTsconfigAllowsThem(): void
    {
        // The fixture page carries the documented snippet from Documentation/Configuration:
        // mod.web_list.viewMode.allowed = list,grid,compact,teaser,timeline,...
        $allowed = $this->get(ViewTypeRegistry::class)->getAllowedViewTypes(self::PAGE_ID);

        $exampleViews = array_map(static fn(array $set): string => $set[0], iterator_to_array(self::exampleViewProvider()));
        self::assertSame(array_merge(self::BUILTIN_VIEWS, array_values($exampleViews)), array_keys($allowed));
    }

    #[Test]
    #[DataProvider('customViewIdProvider')]
    public function customViewsResolveTheirOwnTemplateAndPartialRootsAfterTheParentOnes(string $viewId): void
    {
        $registry = $this->get(ViewTypeRegistry::class);
        $paths = $registry->getTemplatePaths($viewId, self::PAGE_ID);

        self::assertSame('EXT:records_list_examples/Resources/Private/Backend/Templates/', end($paths['templateRootPaths']));
        self::assertSame('EXT:records_list_examples/Resources/Private/Backend/Partials/', end($paths['partialRootPaths']));
        self::assertSame(
            ['EXT:records_list_types/Resources/Public/Css/base.css', 'EXT:records_list_examples/Resources/Public/Css/' . $viewId . '.css'],
            $registry->getCssFiles($viewId, self::PAGE_ID),
        );
    }

    #[Test]
    #[DataProvider('exampleViewProvider')]
    public function rendersRecordsWithExampleView(string $viewId, string $template, string $marker): void
    {
        $this->insertContentElement('Rendered example record');

        $response = $this->get(RecordListController::class)->mainAction($this->createBackendRequest($viewId));
        $html = (string)$response->getBody();

        self::assertSame(200, $response->getStatusCode());
        self::assertStringContainsString('Rendered example record', $html);
        self::assertStringContainsString($marker, $html, $viewId . ' did not render through ' . $template . '.');
        self::assertStringNotContainsString('records_list_examples.messages:', $html, 'Every own label must resolve through the translation domain.');
        self::assertStringNotContainsString('records_list_types.messages:', $html, 'Every parent label must resolve through the translation domain.');
        self::assertStringNotContainsString('core.core:', $html, 'Every Core label must resolve through the translation domain.');
    }

    #[Test]
    #[DataProvider('customViewProvider')]
    public function customViewsRenderStateAwareActionsForHiddenRecords(string $viewId, string $hiddenMarker): void
    {
        $this->insertContentElement('Hidden example record', true);

        $html = (string)$this->get(RecordListController::class)->mainAction($this->createBackendRequest($viewId))->getBody();

        self::assertStringContainsString('Hidden example record', $html);
        self::assertStringContainsString($hiddenMarker, $html);
        self::assertStringContainsString('aria-label="Unhide record"', $html);
        self::assertStringContainsString('title="Unhide record"', $html);
    }

    private function insertContentElement(string $header, bool $hidden = false): void
    {
        $this->get(ConnectionPool::class)->getConnectionForTable('tt_content')->insert('tt_content', [
            'pid' => self::PAGE_ID,
            'header' => $header,
            'CType' => 'text',
            'bodytext' => 'Example body',
            'hidden' => $hidden ? 1 : 0,
        ]);
    }

    private function createBackendRequest(string $viewId): ServerRequestInterface
    {
        $module = $this->get(ModuleProvider::class)->getModule('records');
        self::assertInstanceOf(ModuleInterface::class, $module, 'The Records module must be registered.');
        $route = $this->get(Router::class)->getRoute('records');
        self::assertInstanceOf(Route::class, $route, 'The Records route must be registered.');
        $route->setOption('_identifier', 'records');
        $request = (new ServerRequest('https://example.test/typo3/module/content/records', 'GET', serverParams: [
            'HTTP_HOST' => 'example.test',
            'SCRIPT_NAME' => '/index.php',
            'SCRIPT_FILENAME' => $this->instancePath . '/public/index.php',
            'HTTPS' => 'on',
        ]))
            ->withQueryParams(['id' => self::PAGE_ID, 'table' => 'tt_content', 'displayMode' => $viewId])
            ->withAttribute('applicationType', SystemEnvironmentBuilder::REQUESTTYPE_BE)
            ->withAttribute('site', new NullSite())
            ->withAttribute('module', $module)
            ->withAttribute('moduleData', ModuleData::createFromModule($module, []))
            ->withAttribute('route', $route);
        $request = $request->withAttribute('normalizedParams', NormalizedParams::createFromRequest($request));
        $request = $request->withAttribute('pageContext', $this->get(PageContextFactory::class)->createFromRequest($request, self::PAGE_ID, $GLOBALS['BE_USER']));
        $GLOBALS['TYPO3_REQUEST'] = $request;

        return $request;
    }
}
