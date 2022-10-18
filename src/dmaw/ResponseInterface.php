<?php

namespace Dmaw;

use Psr\Http\Message\ResponseInterface as HttpResponseInterface;

/**
 * Simple extension of the HTTP response interface to ensure that the contents of the body (stream) can be returned as an array
 */
interface ResponseInterface extends HttpResponseInterface
{
    /**
     * Get the contents of the body as an array - unmarshalled by DMAW if required
     *
     * @param bool $unmarshal
     * @return array
     */
    public function getBodyContentsAsArray(bool $unmarshal = false);
}
