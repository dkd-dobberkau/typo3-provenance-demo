<?php

declare(strict_types=1);

namespace CmsProvenance\ProvenanceDemoSite\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use TYPO3\CMS\Core\Core\Bootstrap;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\DataHandling\DataHandler;
use TYPO3\CMS\Core\Localization\LanguageServiceFactory;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Creates the demo page tree (a short history of collecting) with content
 * elements and facts. Everything goes through DataHandler, so the provenance
 * hooks run exactly as they do for an editor. Does nothing when pages exist.
 */
#[AsCommand(
    name: 'provenance-demo:import',
    description: 'Create the demo page tree about the history of collecting.',
)]
final class ImportDemoContentCommand extends Command
{
    /** The site configuration in config/sites/main expects the root page here. */
    private const EXPECTED_ROOT_PAGE = 1;

    public function __construct(
        private readonly ConnectionPool $connectionPool,
        private readonly LanguageServiceFactory $languageServiceFactory,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        if ($this->connectionPool->getConnectionForTable('pages')->count('uid', 'pages', ['deleted' => 0]) > 0) {
            $io->note('The page tree exists already. Nothing was imported.');
            return Command::SUCCESS;
        }

        Bootstrap::initializeBackendAuthentication();
        $GLOBALS['LANG'] = $this->languageServiceFactory->createFromUserPreferences($GLOBALS['BE_USER']);

        $demo = require ExtensionManagementUtility::extPath('provenance_demo_site')
            . 'Resources/Private/DemoContent/Collecting.php';

        $rootUid = $this->createPage(0, $demo['home']['page'] + ['is_siteroot' => 1]);
        if ($rootUid !== self::EXPECTED_ROOT_PAGE) {
            $io->warning(sprintf(
                'The root page got uid %d; config/sites/main/config.yaml expects %d.',
                $rootUid,
                self::EXPECTED_ROOT_PAGE
            ));
        }

        // A new page with a positive pid lands on top, so chapters are created last to first.
        $pageLinks = [];
        foreach (array_reverse($demo['chapters'], true) as $key => $chapter) {
            $chapterUid = $this->createPage($rootUid, $chapter['page']);
            $this->createContent($chapterUid, $chapter['content']);
            $pageLinks['{' . $key . '}'] = 't3://page?uid=' . $chapterUid;
            $io->writeln(sprintf('Page %d: %s', $chapterUid, $chapter['page']['title']));
        }

        $homeContent = array_map(
            static fn(array $element): array => ['bodytext' => strtr($element['bodytext'], $pageLinks)] + $element,
            $demo['home']['content']
        );
        $this->createContent($rootUid, $homeContent);

        $io->success(sprintf('Created %d pages.', count($demo['chapters']) + 1));
        return Command::SUCCESS;
    }

    private function createPage(int $pid, array $fields): int
    {
        $dataHandler = $this->process(['pages' => ['NEWpage' => ['pid' => $pid, 'doktype' => 1, 'hidden' => 0] + $fields]]);
        return (int)$dataHandler->substNEWwithIDs['NEWpage'];
    }

    /**
     * @param list<array{header: string, bodytext: string, facts?: list<array<string, string>>}> $elements
     */
    private function createContent(int $pageUid, array $elements): void
    {
        foreach (array_reverse($elements) as $element) {
            $dataHandler = $this->process(['tt_content' => ['NEWcontent' => [
                'pid' => $pageUid,
                'CType' => 'text',
                'colPos' => 0,
                'header' => $element['header'],
                'bodytext' => $element['bodytext'],
            ]]]);
            $contentUid = (int)$dataHandler->substNEWwithIDs['NEWcontent'];

            $facts = [];
            foreach ($element['facts'] ?? [] as $index => $fact) {
                if (!str_contains(html_entity_decode(strip_tags($element['bodytext'])), $fact['quote'])) {
                    throw new \RuntimeException('Fact quote not found in its content element: ' . $fact['quote'], 1794600001);
                }
                $facts['NEWfact' . $index] = $fact + [
                    'pid' => $pageUid,
                    'content_uid' => $contentUid,
                    'object_literal' => '',
                    'object_uri' => '',
                    'source_url' => '',
                ];
            }
            if ($facts !== []) {
                $this->process(['tx_provenancefacts_fact' => $facts]);
            }
        }
    }

    private function process(array $data): DataHandler
    {
        $dataHandler = GeneralUtility::makeInstance(DataHandler::class);
        $dataHandler->start($data, []);
        $dataHandler->process_datamap();
        if ($dataHandler->errorLog !== []) {
            throw new \RuntimeException(implode("\n", $dataHandler->errorLog), 1794600002);
        }
        return $dataHandler;
    }
}
