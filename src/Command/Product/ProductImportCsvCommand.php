<?php

namespace App\Command\Product;

use App\Entity\Product\Mercurial;
use App\Entity\Product\Supplier;
use App\Message\Product\MercurialImport;
use App\Repository\Product\SupplierRepository;
use App\Service\Product\MercurialImportService;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Exception\ORMException;
use Doctrine\ORM\OptimisticLockException;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Messenger\MessageBusInterface;

#[AsCommand(
    name: 'app:product:import-csv',
    description: 'Add a short description for your command',
)]
class ProductImportCsvCommand extends Command
{
    public function __construct(
        private MessageBusInterface $messageBus,
        private EntityManagerInterface $entityManager,
        private MercurialImportService $mercurialImportService
    )
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument(
                name: 'supplier',
                mode: InputArgument::REQUIRED,
                description: 'Name of the Supplier'
            )
            ->addArgument(
                name: 'file',
                mode: InputArgument::REQUIRED,
                description: 'Path of the file to import'
            )
            ->addOption(
                name: 'force',
                shortcut: 'f',
                mode: InputOption::VALUE_NONE,
                description: 'Forces the creation of a new supplier with the provided name if no supplier matching the provided name were found without asking for user confirmation'
            )
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $supplierName = $input->getArgument(name: 'supplier');
        $filePath = $input->getArgument(name: 'file');
        $forceCreation = $input->getOption(name: 'force');

        if (false === file_exists(filename: $filePath)) {
            $io->error(message: "The specified file does not exists.");
            return Command::FAILURE;
        }

        $fileRawData = file_get_contents(filename: $filePath);

        /** @var SupplierRepository $supplierRepository */
        $supplierRepository = $this->entityManager->getRepository(className: Supplier::class);
        $supplier = $supplierRepository->findOneByName(name: $supplierName);
        if (null === $supplier) {
            /** @var QuestionHelper $helper */
            $helper = $this->getHelper(name: 'question');
            if ($forceCreation === false) {
                $shouldCreateSupplier = new ConfirmationQuestion(
                    question: sprintf('The provided supplier does not exists. Would you like to create a new one with the name `%s` ?', $supplierName),
                    default: true,
                );

                if (false === $helper->ask($input, $output, $shouldCreateSupplier)) {
                    $io->success(message: 'No supplier found.');
                    return Command::SUCCESS;
                }
            }
            $supplier = new Supplier(name: $supplierName);

            try {
                $this->entityManager->persist(object: $supplier);
                $this->entityManager->flush();
            } catch (ORMException|OptimisticLockException $e) {
                $io->error(message: sprintf('An error occurred while trying to register the new supplier : %s', $e->getMessage()));
                return Command::FAILURE;
            }
        }

        $filename = $this->mercurialImportService->storeMercurialFileContents(fileContents: $fileRawData, supplierName: $supplierName);

        $message = new MercurialImport(filename: $filename, supplierId: $supplier->getId());
        $this->messageBus->dispatch($message);

        $io->success("The specified file has been added to the update queue.");
        return Command::SUCCESS;
    }
}
