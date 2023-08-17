<?php

namespace App\Command;

use App\Service\Utility\ImportUtility;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'manual:data-import',
    description: 'Initializes database tables for the application',
)]
class ManualDataImportCommand extends Command
{
    /**
     * @var ImportUtility
     */
    private ImportUtility $importUtility;

    protected function configure(): void{}

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        try {
            $this->importUtility->importRoles();
            $this->importUtility->createUserAccounts();

            $io->success('Importing data completed successfully.');
        }
        catch (\Exception $exception) {
            dd($exception);
            $io->error($exception->getTraceAsString());
        }

        return Command::SUCCESS;
    }

    /**
     * DataImportCommand constructor.
     * @param ImportUtility $importUtility
     */
    public function __construct(ImportUtility $importUtility)
    {
        $this->importUtility = $importUtility;

        parent::__construct();
    }
}
