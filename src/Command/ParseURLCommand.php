<?php

namespace App\Command;

use Generator;
use App\Entity\URL;
use App\Repository\URLRepository;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ParseURLCommand extends Command
{

    /**
     * @var ObjectManager
     */
    private $manager;

    /**
     * @var URLRepository
     */
    private $repository;

    protected static $defaultName = 'app:url:parse';
    protected static $defaultDescription = 'Parse URLs from file and import them into database';

    public function __construct(ManagerRegistry $managerRegistry, URLRepository $repository, string $name = null)
    {
        parent::__construct($name);
        $this->repository = $repository;
        $this->manager = $managerRegistry->getManager();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('file', InputArgument::REQUIRED, 'Chosen file')
            ->addOption(
                'max-line',
                'm',
                InputOption::VALUE_REQUIRED,
                'Maximum line read',
                -1)
            ->addOption(
                'dry-run',
                'd',
                InputOption::VALUE_NONE,
                'Run in dry-run mode'
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $maxLine = $input->getOption('max-line');
        $isDryRun = $input->getOption('dry-run');

        if ($isDryRun) {
            $io->note('You run command in dry-run mode');
        }

        $cacheURLs = [];
        foreach ($this->cacheURLs() as $key => $cacheURL) {
            $cacheURLs[$key] = $cacheURL;
        }

        if ($filename = $input->getArgument('file')) {

            if (!file_exists($filename)) {
                $io->error(sprintf('File not found: %s', realpath($filename)));
                return Command::FAILURE;
            }

            $iterators = $this->readFile($filename, $maxLine);

            foreach ($iterators as $iterator) {
                $url = trim($iterator);
                if (!filter_var($url, FILTER_VALIDATE_URL)) {
                    continue;
                }
                if (isset($cacheURLs[$url])) {
                    if ($isDryRun) {
                        $io->info(sprintf('URL {%s} already saved.', $url));
                    }
                    continue;
                }
                if ($isDryRun) {
                    $io->info($url);
                } else {
                    $entity = new URL();
                    $entity->setUrl($url);
                    $this->manager->persist($entity);
                    $cacheURLs[$url] = $entity;
                }

            }
            $this->manager->flush();
        }


        $io->success('Command end successfully');

        return Command::SUCCESS;
    }

    public function cacheURLs(): Generator
    {
        $urls = $this->repository->findAll();
        foreach ($urls as $url) {
            yield $url->getUrl() => $url;
        }
    }

    public function readFile($filename, $maxLine = -1): Generator
    {
        $iterator = 0;
        $resource = fopen($filename, 'r');
        while (!feof($resource)) {
            $iterator++;
            if ($maxLine > 0 && $iterator > $maxLine) {
                return false;
            }
            yield trim(fgets($resource));
        }
        fclose($resource);
    }
}
