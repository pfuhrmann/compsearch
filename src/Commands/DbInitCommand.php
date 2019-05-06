<?php declare(strict_types=1);

namespace Room\Search\Commands;

use PDO;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class DbInitCommand extends Command
{
    /**
     * @var PDO
     */
    private $db;

    public function __construct(PDO $db)
    {
        $this->db = $db;

        parent::__construct();
    }

    protected static $defaultName = 'db:init';

    protected function configure()
    {
        $this->setDescription('Initialize MySQL database')
            ->setHelp('This command allows you to bootstrap initial version of the MySQL database');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln('Reading SQL file');
        $sql = file_get_contents(ROOT_PATH . '/db.sql');

        $output->writeln('Running init query');
        $this->db->query($sql);

        $output->writeln('Database was successfully initialized');
    }
}
