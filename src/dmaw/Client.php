<?php

namespace Dmaw;

use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\RequestOptions;
use Psr\Http\Message\UriInterface;
use ReflectionClass;
use ReflectionObject;
use ReflectionProperty;

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
        $response = $this->httpClient->get($path, [
            'headers'   => $this->buildHeaders(),
            'json'      => self::marshal($data),
        ]);

        //  Create a DMAW response using the Guzzle response
        return new Response($response);
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
        $response = $this->httpClient->post($path, [
            'headers'   => $this->buildHeaders(),
            'json'      => self::marshal($data),
        ]);

        //  Create a DMAW response using the Guzzle response
        return new Response($response);
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
        $reponse = $this->httpClient->put($path, [
            'headers'   => $this->buildHeaders(),
            'json'      => self::marshal($data),
        ]);

        //  Create a DMAW response using the Guzzle response
        return new Response($response);
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
        $reponse = $this->httpClient->patch($path, [
            'headers'   => $this->buildHeaders(),
            'json'      => self::marshal($data),
        ]);

        //  Create a DMAW response using the Guzzle response
        return new Response($response);
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
        $reponse = $this->httpClient->delete($path, [
            'headers'   => $this->buildHeaders(),
            'json'      => self::marshal($data),
        ]);

        //  Create a DMAW response using the Guzzle response
        return new Response($response);
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

    /**
     * Interrogate the data to look for DMAW annotations that can be used during the marshalling process to convert data models into array representations
     *
     * @param array $data
     * @param bool $includeMode
     * @return array
     */
    public static function marshal(array $data, bool $includeMode = false): array
    {
        //  Loop through the data to try find any objects that might have DMAW anotations
        foreach ($data as $name => $dataItem) {
            if (is_object($dataItem)) {
                $data[$name] = self::marshalObject($dataItem, $includeMode);
            } elseif (is_array($dataItem)) {
                $data[$name] = self::marshal($dataItem, $includeMode);
            }
        }

        return $data;
    }

    /**
     * Use reflection to inspect an object for DMAW annotation and extract the data
     *
     * @param object $dataObject
     * @param bool $includeMode
     * @return array
     */
    private static function marshalObject(object $dataObject, bool $includeMode = false): array
    {
        $dataArr = [];

        $dataObjectReflection = new ReflectionObject($dataObject);

        $dmawClassCommand = self::getDmawCommand($dataObjectReflection);

        //  If the class is marked as exclude then never include the data - even if any parent class marked this data as include
        if ($dmawClassCommand != Commands::EXCLUDE) {
            //  Include the data if flagged to do so, or if we're in include mode (i.e. the parent class had this data to include)
            if ($dmawClassCommand == Commands::INCLUDE || $includeMode) {
                //  Loop through the properties and determine what data to include
                foreach ($dataObjectReflection->getProperties() as $dataObjectPropertyRelfection) {
                    if ($dataObjectPropertyRelfection instanceof ReflectionProperty) {
                        $dataObjectPropertyRelfection->setAccessible(true);

                        //  Get the DMAW command for the property to see if the data should be excluded or not
                        $dmawPropertyCommand = self::getDmawCommand($dataObjectPropertyRelfection);

                        //  Skip excluded properties
                        if ($dmawPropertyCommand == Commands::EXCLUDE) {
                            continue;
                        }

                        $value = $dataObjectPropertyRelfection->getValue($dataObject);

                        if (is_object($value)) {
                            $dataArr[$dataObjectPropertyRelfection->getName()] = self::marshalObject($value, true);
                        } elseif (is_array($value)) {
                            //  Attempt to marshal the array contents
                            $dataArr[$dataObjectPropertyRelfection->getName()] = self::marshal($value, true);
                        } else {
                            //  If the value isn't an object or array then just include the value
                            $dataArr[$dataObjectPropertyRelfection->getName()] = $value;
                        }
                    }
                }

                //  Add DMAW metadata
                $dataArr['_dmaw'] = [
                    'class' => get_class($dataObject),
                ];
            }
        }

        return $dataArr;
    }

    /**
     * Try to return a valid DMAW command from the doc comment annotation
     *
     * @param mixed $reflectionData
     * @return false|string
     * @throws Exception
     */
    private static function getDmawCommand(mixed $reflectionData)
    {
        if (!$reflectionData instanceof ReflectionObject
            && !$reflectionData instanceof ReflectionProperty
        ) {
            throw new Exception('DMAW commands can only be extracted from reflection objects or reflection properies');
        }

        $docComment = $reflectionData->getDocComment();

        if (is_string($docComment) && stripos($docComment, Commands::FLAG) > 0) {
            $parts = explode(Commands::FLAG, $docComment);

            if (count($parts) > 1) {
                $parts2 = explode(' ', trim($parts[1]));

                if (!empty($parts2)) {
                    //  Make sure the command is one of the permitted values
                    $command = trim(array_shift($parts2));

                    if (in_array($command, [
                        Commands::EXCLUDE,
                        Commands::INCLUDE,
                    ])) {
                        return $command;
                    }
                }
            }
        }

        return false;
    }

    /**
     * Interrogate the data to look for DMAW meta data that can be used during the unmarshalling process to convert array representations into data models
     *
     * @param array $data
     * @return array
     * @throws Exception
     */
    public static function unmarshal(array $data): array
    {
        //  Loop through the data to try find any DMAW metadata
        foreach ($data as $name => $dataItem) {
            if (is_array($dataItem)) {
                //  Try to unmarshal the sub data before going any further
                $dataItem = self::unmarshal($dataItem);

                //  If the data item contains DMAW metadata then try to recontruct the object
                if (isset($dataItem['_dmaw'])) {
                    if (!isset($dataItem['_dmaw']['class'])) {
                        throw new Exception('DMAW metadata must contain a class definition');
                    }

                    $class = $dataItem['_dmaw']['class'];

                    //  Unset the DMAW metadata so the remaining data can be set in the object
                    unset($dataItem['_dmaw']);

                    if (class_exists($class)) {
                        $dataObjectReflection = new ReflectionClass($class);
                        $dataObject = $dataObjectReflection->newInstanceWithoutConstructor();

                        foreach ($dataItem as $dataItemName => $dataItemValue) {
                            if ($dataObjectReflection->hasProperty($dataItemName)) {
                                $dataObjectPropertyRelfection = $dataObjectReflection->getProperty($dataItemName);
                                $dataObjectPropertyRelfection->setAccessible(true);
                                $dataObjectPropertyRelfection->setValue($dataObject, $dataItemValue);
                            }
                        }

                        $data[$name] = $dataObject;
                    }
                }
            }
        }

        return $data;
    }
}
