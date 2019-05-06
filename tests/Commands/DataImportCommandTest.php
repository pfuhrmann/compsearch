<?php declare(strict_types=1);

namespace Tests\Commands;

use Room\Search\Commands\DataImportCommand;
use Tests\ApplicationTestCase;

class DataImportCommandTest extends ApplicationTestCase
{
    /**
     * @var string
     */
    protected $commandName;

    protected function setUp(): void
    {
        parent::setUp();

        // Provision DB
        $q = file_get_contents(ROOT_PATH . '/db.sql');
        $this->db->query($q);

        $this->commandName = DataImportCommand::getDefaultName();
    }

    public function testExecuteDisplaysSuccess(): void
    {
        $output = $this->execCommand($this->commandName);

        $this->assertStringContainsString('success', $output);
    }

    public function testExecuteAddsDbRecords(): void
    {
        $this->execCommand($this->commandName);
        $sth = $this->db->query("SELECT COUNT(*) as count FROM companies");

        $this->assertEquals(41, $sth->fetchColumn(0));
    }

    public function testExecuteIndexesRecords(): void
    {
        $this->execCommand($this->commandName);
        $results = $this->elastic->count([
            'index' => getenv('COMPANIES_INDEX'),
        ]);

        $this->assertEquals(41, $results['count']);
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        $this->elastic->indices()->delete(['index' => getenv('COMPANIES_INDEX')]);
    }
}
