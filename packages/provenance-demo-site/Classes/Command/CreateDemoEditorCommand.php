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
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Creates the demo editor: a non-admin account with a published password,
 * so the demo can show its login without revealing the admin password.
 * Editors may change pages, content, provenance records and facts below the
 * demo root and use the Page, List and Provenance modules. Does nothing when
 * the account exists.
 */
#[AsCommand(
    name: 'provenance-demo:editor',
    description: 'Create the demo editor account with a known password.',
)]
final class CreateDemoEditorCommand extends Command
{
    public const USERNAME = 'editor';
    // Published on the login page and in the README; meets TYPO3's default password policy.
    public const PASSWORD = 'Demo-Editor-1';

    private const ROOT_PAGE = 1;
    private const TABLES = ['pages', 'tt_content', 'tx_provenance_document', 'tx_provenance_source', 'tx_provenancefacts_fact'];
    private const MODULES = ['web_layout', 'web_list', 'provenance', 'provenance_pages', 'provenance_facts'];

    public function __construct(
        private readonly ConnectionPool $connectionPool,
        private readonly LanguageServiceFactory $languageServiceFactory,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $existing = $this->connectionPool->getConnectionForTable('be_users')
            ->count('uid', 'be_users', ['username' => self::USERNAME, 'deleted' => 0]);
        if ($existing > 0) {
            $io->note('The demo editor exists already.');
            return Command::SUCCESS;
        }

        Bootstrap::initializeBackendAuthentication();
        $GLOBALS['LANG'] = $this->languageServiceFactory->createFromUserPreferences($GLOBALS['BE_USER']);

        $tables = implode(',', self::TABLES);
        $dataHandler = GeneralUtility::makeInstance(DataHandler::class);
        $dataHandler->start([
            'be_groups' => ['NEWgroup' => [
                'pid' => 0,
                'title' => 'Demo editors',
                'tables_select' => $tables,
                'tables_modify' => $tables,
                'groupMods' => implode(',', self::MODULES),
                'db_mountpoints' => self::ROOT_PAGE,
                'pagetypes_select' => '1,254',
                'non_exclude_fields' => implode(',', $this->excludeFields()),
                'explicit_allowdeny' => implode(',', $this->contentTypes()),
            ]],
            'be_users' => ['NEWeditor' => [
                'pid' => 0,
                'username' => self::USERNAME,
                'password' => self::PASSWORD,
                'realName' => 'Demo editor',
                // TYPO3 creates backend users disabled unless told otherwise.
                'disable' => 0,
                'usergroup' => 'NEWgroup',
                'options' => 3,
            ]],
        ], []);
        $dataHandler->process_datamap();
        if ($dataHandler->errorLog !== []) {
            throw new \RuntimeException(implode("\n", $dataHandler->errorLog), 1794700201);
        }

        // Pages created by the import belong to the admin; open them to the editors' group.
        $this->connectionPool->getConnectionForTable('pages')->update(
            'pages',
            ['perms_groupid' => (int)$dataHandler->substNEWwithIDs['NEWgroup'], 'perms_group' => 31],
            ['deleted' => 0]
        );

        $io->success(sprintf('Created the demo editor "%s".', self::USERNAME));
        return Command::SUCCESS;
    }

    /**
     * Every field of the editable tables that TCA restricts to explicitly allowed users.
     *
     * @return list<string>
     */
    private function excludeFields(): array
    {
        $fields = [];
        foreach (self::TABLES as $table) {
            foreach ($GLOBALS['TCA'][$table]['columns'] ?? [] as $field => $configuration) {
                if (!empty($configuration['exclude'])) {
                    $fields[] = $table . ':' . $field;
                }
            }
        }
        return $fields;
    }

    /**
     * Every content type, as "tt_content:CType:<type>".
     *
     * @return list<string>
     */
    private function contentTypes(): array
    {
        $types = [];
        foreach ($GLOBALS['TCA']['tt_content']['columns']['CType']['config']['items'] ?? [] as $item) {
            $value = (string)($item['value'] ?? '');
            if ($value !== '' && $value !== '--div--') {
                $types[] = 'tt_content:CType:' . $value;
            }
        }
        return $types;
    }
}
