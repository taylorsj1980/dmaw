<?php

namespace Dmaw;

use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Exception\GuzzleException;
use Psr\Http\Message\ResponseInterface;

/**
 * DMAW client
 */
class Client
{
    /**
     * @var GuzzleClient
     */
    private $httpClient;

    /**
     * @param GuzzleClient $httpClient
     */
    public function __construct(GuzzleClient $httpClient)
    {
        $this->httpClient = $httpClient;
    }

    /**
     * Performs a GET against the API
     *
     * @param   string $path
     * @param   array $data
     * @return  ResponseInterface
     * @throws  GuzzleException
     */
    public function get(string $path, array $data = []): ResponseInterface
    {
        return $this->httpClient->get($path, [
            'headers'   => $this->buildHeaders(),
            'json'      => $data,
        ]);
    }

    /**
     * Performs a POST against the API
     *
     * @param   string $path
     * @param   array $data
     * @return  ResponseInterface
     * @throws  GuzzleException
     */
    public function post(string $path, array $data = []): ResponseInterface
    {
        return $this->httpClient->post($path, [
            'headers'   => $this->buildHeaders(),
            'json'      => $data,
        ]);
    }

    /**
     * Performs a PUT against the API
     *
     * @param   string $path
     * @param   array $data
     * @return  ResponseInterface
     * @throws  GuzzleException
     */
    public function put(string $path, array $data = []): ResponseInterface
    {
        return $this->httpClient->put($path, [
            'headers'   => $this->buildHeaders(),
            'json'      => $data,
        ]);
    }

    /**
     * Performs a PATCH against the API
     *
     * @param   string $path
     * @param   array $data
     * @return  ResponseInterface
     * @throws  GuzzleException
     */
    public function patch(string $path, array $data = []): ResponseInterface
    {
        return $this->httpClient->patch($path, [
            'headers'   => $this->buildHeaders(),
            'json'      => $data,
        ]);
    }

    /**
     * Performs a DELETE against the API
     *
     * @param   string $path
     * @param   array $data
     * @return  ResponseInterface
     * @throws  GuzzleException
     */
    public function delete(string $path, array $data = []): ResponseInterface
    {
        return $this->httpClient->delete($path, [
            'headers'   => $this->buildHeaders(),
            'json'      => $data,
        ]);
    }

    /**
     * Generates the standard set of HTTP headers expected by the API
     *
     * @return array
     */
    private function buildHeaders(): array
    {
        return [
            'Accept'        => 'application/json',
            'Content-Type'  => 'application/json',
        ];
    }
}