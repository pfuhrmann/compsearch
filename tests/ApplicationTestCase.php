<?php declare(strict_types=1);

namespace Tests;

use Elasticsearch\Client as ElasticClient;
use PDO;
use PHPUnit\Framework\TestCase;
use Room\Search\SearchApp;
use Room\Search\Commands\DataImportCommand;
use Room\Search\Commands\DbInitCommand;
use Symfony\Component\Console\Application as ConsoleApplication;
use Symfony\Component\Console\Tester\CommandTester;

abstract class ApplicationTestCase extends TestCase
{
    /**
     * @var ConsoleApplication
     */
    protected $console;

    /**
     * @var PDO
     */
    protected $db;

    /**
     * @var ElasticClient
     */
    protected $elastic;

    protected function setUp(): void
    {
        parent::setUp();

        $app = new SearchApp();
        $container = $app->getContainer();
        $this->db = $container->get(PDO::class);
        $this->elastic = $container->get(ElasticClient::class);
        $this->console = new ConsoleApplication();
        $this->console->add(new DbInitCommand($this->db));
        $this->console->add(new DataImportCommand($this->db, $this->elastic));
    }

    /**
     * Execute console command based on the name
     *
     * @param string $commandName
     *
     * @return string Output from the command execution
     */
    protected function execCommand(string $commandName): string
    {
        $command = $this->console->find($commandName);
        $commandTester = new CommandTester($command);
        $commandTester->execute(['command' => $commandName]);

        return $commandTester->getDisplay();
    }
}
