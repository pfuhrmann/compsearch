<?php declare(strict_types=1);

namespace Tests\Controllers;

use Psr\Http\Message\ResponseInterface;
use Room\Search\Commands\DataImportCommand;
use Room\Search\Commands\DbInitCommand;
use Room\Search\Controllers\ApiController;
use Slim\Http\Environment;
use Slim\Http\Headers;
use Slim\Http\Request;
use Slim\Http\RequestBody;
use Slim\Http\Response;
use Slim\Http\Uri;
use stdClass;
use Tests\ApplicationTestCase;

class ApiControllerTest extends ApplicationTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->execCommand(DbInitCommand::getDefaultName());
        $this->execCommand(DataImportCommand::getDefaultName());
    }

    public function testHeartbeat(): void
    {
        $response = $this->request('GET', '/', 'index');

        $this->assertEquals($response->getStatusCode(), 200);
    }

    public function testSearchAll(): void
    {
        $response = $this->request('POST', '/companies', 'companies', [
            'query' => ['match_all' => new stdClass()]
        ]);
        $contents = $this->getParsedContents($response);

        $this->assertCount(41, $contents);
        $this->assertArrayHasKey('_id', $contents[0]);
        $this->assertArrayHasKey('_source', $contents[0]);
        $this->assertCount(6, $contents[0]['_source']);
    }

    public function testSearchBoolQuery(): void
    {
        $response = $this->request('POST', '/companies', 'companies', [
            'query' => [
                'bool' => [
                    'must_not' => [
                        'match' => ['tags' => 'delivery']
                    ],
                    'must' => [
                        'match' => ['industry' => 'food']
                    ],
                ]
            ]
        ]);
        $contents = $this->getParsedContents($response);

        $this->assertCount(2, $contents);
    }

    private function getParsedContents(ResponseInterface $response): array
    {
        return json_decode((string)$response->getBody(), true);
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        $this->elastic->indices()->delete(['index' => getenv('COMPANIES_INDEX')]);
    }

    /**
     * Mock the API request
     *
     * @param string $method
     * @param string $uri
     * @param string $action
     * @param array $parsedBody
     *
     * @return ResponseInterface
     */
    private function request(string $method, string $uri, string $action, array $parsedBody = []): ResponseInterface
    {
        $request = $this->makeRequest($method, $uri);
        $body = $request->getBody();
        $body->write(json_encode($parsedBody));
        $response = new Response();

        return (new ApiController($this->elastic))
            ->$action($request->withBody($body), $response, []);
    }

    /**
     * Bootstrap mock Request object
     *
     * @param string $method
     * @param string $uri
     *
     * @return Request
     */
    private function makeRequest(string $method, string $uri): Request
    {
        $env = Environment::mock();
        $uri = Uri::createFromString('http://example.com' . $uri);

        $headers = Headers::createFromEnvironment($env);
        $serverParams = $env->all();
        $body = new RequestBody();

        return new Request($method, $uri, $headers, [], $serverParams, $body);
    }


}
