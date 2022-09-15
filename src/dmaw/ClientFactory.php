<?php

namespace Dmaw;

use GuzzleHttp\Client as GuzzleClient;

/**
 * DMAW client factory
 */
class ClientFactory
{
    /**
     * Create a DMAW client
     *
     * @return Client
     */
    public static function create(): Client
    {
        $httpClient = new GuzzleClient();

        return new Client($httpClient);
    }
}