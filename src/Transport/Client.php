<?php

namespace Smalot\Cups\Transport;

use GuzzleHttp\Psr7\HttpFactory;
use GuzzleHttp\Psr7\Uri;
use Http\Client\Common\Plugin\AddHostPlugin;
use Http\Client\Common\Plugin\ContentLengthPlugin;
use Http\Client\Common\Plugin\DecoderPlugin;
use Http\Client\Common\Plugin\ErrorPlugin;
use Http\Client\Common\PluginClient;
use Http\Client\Socket\Client as SocketHttpClient;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Smalot\Cups\CupsException;

/**
 * Class Client
 *
 * @package Smalot\Cups\Transport
 */
class Client implements ClientInterface
{

    const SOCKET_URL = 'unix:///var/run/cups/cups.sock';

    const AUTHTYPE_BASIC = 'basic';

    const AUTHTYPE_DIGEST = 'digest';

    protected ClientInterface $httpClient;

    protected string $authType;

    protected ?string $username;

    protected ?string $password;

    /**
     * Client constructor.
     *
     * @param string|null $username
     * @param string|null $password
     * @param array $socketClientOptions
     */
    public function __construct(string $username = null, string $password = null, array $socketClientOptions = [])
    {
        $this->username = $username;
        $this->password = $password;

        if (empty($socketClientOptions['remote_socket'])) {
            $socketClientOptions['remote_socket'] = self::SOCKET_URL;
        }

        $messageFactory = new HttpFactory();
        $socketClient = new SocketHttpClient($messageFactory, $socketClientOptions);
        $host = preg_match(
          '/unix:\/\//',
          $socketClientOptions['remote_socket']
        ) ? 'http://localhost' : $socketClientOptions['remote_socket'];
        $this->httpClient = new PluginClient(
          $socketClient, [
            new ErrorPlugin(),
            new ContentLengthPlugin(),
            new DecoderPlugin(),
            new AddHostPlugin(new Uri($host)),
          ]
        );

        $this->authType = self::AUTHTYPE_BASIC;
    }

    public function setAuthentication(string $username, string $password): static
    {
        $this->username = $username;
        $this->password = $password;

        return $this;
    }

    public function setAuthType(string $authType): static
    {
        $this->authType = $authType;

        return $this;
    }

    /**
     * (@inheritdoc}
     */
    public function sendRequest(RequestInterface $request): ResponseInterface
    {
        if ($this->username || $this->password) {
            switch ($this->authType) {
                case self::AUTHTYPE_BASIC:
                    $pass = base64_encode($this->username.':'.$this->password);
                    $authentication = 'Basic '.$pass;
                    break;

                case self::AUTHTYPE_DIGEST:
                    throw new CupsException('Auth type not supported');

                default:
                    throw new CupsException('Unknown auth type');
            }

            $request = $request->withHeader('Authorization', $authentication);
        }

        return $this->httpClient->sendRequest($request);
    }
}
