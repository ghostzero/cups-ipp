<?php

namespace Smalot\Cups\Manager;

use Http\Client\HttpClient;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\ResponseInterface;
use Smalot\Cups\Builder\Builder;
use Smalot\Cups\CupsException;
use Smalot\Cups\Transport\Response;
use Smalot\Cups\Transport\ResponseParser;

/**
 * Class ManagerAbstract
 *
 * @package Smalot\Cups\Manager
 */
class ManagerAbstract
{

    use Traits\CharsetAware;
    use Traits\LanguageAware;
    use Traits\OperationIdAware;
    use Traits\UsernameAware;

    protected ClientInterface $client;

    protected Builder $builder;

    protected ResponseParser $responseParser;

    protected string $version;

    /**
     * ManagerAbstract constructor.
     */
    public function __construct(Builder $builder, ClientInterface $client, ResponseParser $responseParser)
    {
        $this->client = $client;
        $this->builder = $builder;
        $this->responseParser = $responseParser;
        $this->version = chr(0x01).chr(0x01);

        $this->setCharset('us-ascii');
        $this->setLanguage('en-us');
        $this->setOperationId(0);
        $this->setUsername('');
    }

    /**
     * @throws CupsException
     */
    public function buildProperty(string $name, mixed $value, bool $emptyIfMissing = false): string
    {
        return $this->builder->buildProperty($name, $value, $emptyIfMissing);
    }

    public function buildProperties(array $properties = []): string
    {
        return $this->builder->buildProperties($properties);
    }

    public function parseResponse(ResponseInterface $response): Response
    {
        return $this->responseParser->parse($response);
    }

    public function getVersion(): string
    {
        return $this->version;
    }
}
