<?php declare(strict_types=1);

namespace Room\Search\Commands;

use Elasticsearch\Client as Elastic;
use Exception;
use PDO;
use Ramsey\Uuid\Uuid;
use RuntimeException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class DataImportCommand extends Command
{
    protected static $defaultName = 'data:import';

    /**
     * @var PDO
     */
    private $db;

    /**
     * @var PDO
     */
    private $elastic;

    public function __construct(PDO $db, Elastic $elastic)
    {
        $this->db = $db;
        $this->elastic = $elastic;

        parent::__construct(null);
    }

    protected function configure()
    {
        $this->setDescription('Import data')
            ->setHelp('This command allows you to import data from the CSV file');
    }

    /**
     * {@inheritDoc}
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     *
     * @throws Exception
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln('Started import');
        try {
            $this->importFromCsv();
        } catch (Exception $e) {
            throw new RuntimeException('Error while inserting data: ' . $e->getMessage());
        }

        $output->writeln('Data from the CSV imported successfully');
    }

    private function importFromCsv(): void
    {
        $file = ROOT_PATH . '/Test_Data.csv';
        $csv = array_map('str_getcsv', file($file));

        $titles = array_map(function ($title) {
            return str_replace(' ', '_', strtolower(trim($title)));
        }, $csv[0]);
        array_shift($csv);

        $params = ['body' => []];
        foreach ($csv as $company) {
            $document = $this->indexRecord($company, $titles);
            // Keep IDs between SQL and Elastic same for simplicity
            array_unshift($company, $document[0]['index']['_id']);
            $this->insertDbRecord($company);

            $params['body'] = array_merge($document, $params['body']);
        }

        $params['refresh'] = true;
        $this->elastic->bulk($params);
    }

    private function insertDbRecord(array $company): void
    {
        $q = "INSERT INTO companies VALUES (?, ?, ?, ?, ?, ?, ?)";
        $sth = $this->db->prepare($q);
        $sth->execute(array_values($company));
    }

    private function indexRecord(array $company, array $titles): array
    {
        $param[] = [
            'index' => [
                '_index' => getenv('COMPANIES_INDEX') ?: 'companies',
                '_type' => 'all',
                '_id' => Uuid::uuid4(),
            ]
        ];
        $param[] = array_combine($titles, $company);

        return $param;
    }
}
