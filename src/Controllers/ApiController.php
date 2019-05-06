<?php declare(strict_types=1);

namespace Room\Search\Controllers;

use Elasticsearch\Client;
use Elasticsearch\Common\Exceptions\Missing404Exception;
use Exception;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

class ApiController
{
    /**
     * @var Client
     */
    private $elastic;

    public function __construct(Client $elastic)
    {
        $this->elastic = $elastic;
    }

    public function index(RequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        $body = $response->getBody();
        $body->write('Heartbeat OK');

        return $response->withBody($body);
    }

    public function companies(RequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        $body = $response->getBody();
        $query = json_decode((string) $request->getBody())->query;
        try {
            $results = $this->elastic->search([
                'index' => getenv('COMPANIES_INDEX') ?: 'companies',
                'size' => 100,
                'filter_path' => 'hits.hits._source,hits.hits._id',
                'body' => [
                    'query' => $query,
                ]
            ]);
        } catch (Exception $e) {
            if ($e instanceof Missing404Exception) {
                $body->write(json_encode(['error' => 'Missing data']));
            } else {
                $body->write(json_encode(['error' => 'Server error']));
            }

            return $response->withHeader('Content-Type', 'application/json')
                ->withBody($body)
                ->withStatus(500);
        }

        $content = empty($results) ? $results : $results['hits']['hits'];
        $body->write(json_encode($content));

        return $response->withHeader('Content-Type', 'application/json')
            ->withBody($body);
    }
}
