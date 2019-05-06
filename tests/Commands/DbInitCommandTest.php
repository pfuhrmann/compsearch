<?php declare(strict_types=1);

namespace Tests\Commands;

use Room\Search\Commands\DbInitCommand;
use Tests\ApplicationTestCase;

class DbInitCommandTest extends ApplicationTestCase
{
    /**
     * @var string
     */
    protected $commandName;

    protected function setUp(): void
    {
        parent::setUp();

        $this->commandName = DbInitCommand::getDefaultName();
    }

    public function testExecuteDisplaysSuccess(): void
    {
        $output = $this->execCommand($this->commandName);

        $this->assertStringContainsString('success', $output);
    }

    public function testExecuteCreatesTable(): void
    {
        $this->execCommand($this->commandName);

        $q = "SELECT * FROM companies";
        $sth = $this->db->query($q);

        $this->assertEquals(7, $sth->columnCount());
    }
}
