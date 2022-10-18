<?php

namespace Dmaw;

use Psr\Http\Message\MessageInterface as HttpMessageInterface;
use Psr\Http\Message\ResponseInterface as HttpResponseInterface;
use Psr\Http\Message\StreamInterface;

/**
 * DMAW response
 */
class Response implements ResponseInterface
{
    /**
     * Original HTTP response
     *
     * @var HttpResponseInterface
     */
    private $response;

    /**
     * @param HttpResponseInterface $response
     */
    public function __construct(HttpResponseInterface $response)
    {
        //  Confirm that the content of the response is JSON
        $contentType = $response->getHeaderLine('Content-Type');

        if (!is_string($contentType) || stripos($contentType, 'application/json') === false) {
            throw new Exception('HTTP response must contain JSON content to be used by DMAW');
        }

        $this->response = $response;
    }

    /**
     * Get the contents of the body as an array - unmarshalled by DMAW if required
     *
     * @param bool $unmarshal
     * @return array
     */
    public function getBodyContentsAsArray(bool $unmarshal = false): array
    {
        $body = $this->response->getBody();

        if ($body instanceof StreamInterface) {
            $contentsArr = json_decode($body->getContents(), JSON_OBJECT_AS_ARRAY);

            //  Check for JSON parsing errors
            $jsonError = json_last_error();

            if ($jsonError !== JSON_ERROR_NONE) {
                throw new Exception(sprintf('Error %s parsing response body as JSON', $jsonError));
            }

            if ($unmarshal) {
                $contentsArr = Client::unmarshal($contentsArr);
            }

            return $contentsArr;
        }

        return [];
    }

    /**
     * Proxy to HTTP response
     *
     * @return int
     */
    public function getStatusCode(): int
    {
        return $this->response->getStatusCode();
    }

    /**
     * Proxy to HTTP response
     *
     * @param $code
     * @param $reasonPhrase
     * @return Response|HttpResponseInterface
     */
    public function withStatus($code, $reasonPhrase = ''): HttpResponseInterface
    {
        return $this->response->withStatus($code, $reasonPhrase);
    }

    /**
     * Proxy to HTTP response
     *
     * @return string
     */
    public function getReasonPhrase(): string
    {
        return $this->response->getReasonPhrase();
    }

    /**
     * Proxy to HTTP response
     *
     * @return string
     */
    public function getProtocolVersion(): string
    {
        return $this->response->getProtocolVersion();
    }

    /**
     * Proxy to HTTP response
     *
     * @param $version
     * @return HttpMessageInterface
     */
    public function withProtocolVersion($version): HttpMessageInterface
    {
        return $this->response->withProtocolVersion($version);
    }

    /**
     * Proxy to HTTP response
     *
     * @return array
     */
    public function getHeaders(): array
    {
        return $this->response->getHeaders();
    }

    /**
     * Proxy to HTTP response
     *
     * @param $name
     * @return bool
     */
    public function hasHeader($name): bool
    {
        return $this->response->hasHeader($name);
    }

    /**
     * Proxy to HTTP response
     *
     * @param $name
     * @return array
     */
    public function getHeader($name): array
    {
        return $this->response->getHeader($name);
    }

    /**
     * Proxy to HTTP response
     *
     * @param $name
     * @return string
     */
    public function getHeaderLine($name): string
    {
        return $this->response->getHeaderLine($name);
    }

    /**
     * Proxy to HTTP response
     *
     * @param $name
     * @param $value
     * @return HttpMessageInterface
     */
    public function withHeader($name, $value): HttpMessageInterface
    {
        return $this->response->withHeader($name, $value);
    }

    /**
     * Proxy to HTTP response
     *
     * @param $name
     * @param $value
     * @return HttpMessageInterface
     */
    public function withAddedHeader($name, $value): HttpMessageInterface
    {
        return $this->response->withAddedHeader($name, $value);
    }

    /**
     * Proxy to HTTP response
     *
     * @param $name
     * @return HttpMessageInterface
     */
    public function withoutHeader($name): HttpMessageInterface
    {
        return $this->response->withoutHeader($name);
    }

    /**
     * Proxy to HTTP response
     *
     * @return StreamInterface
     */
    public function getBody(): StreamInterface
    {
        return $this->response->getBody();
    }

    /**
     * Proxy to HTTP response
     *
     * @param StreamInterface $body
     * @return HttpMessageInterface
     */
    public function withBody(StreamInterface $body): HttpMessageInterface
    {
        return $this->response->withBody($body);
    }
}
