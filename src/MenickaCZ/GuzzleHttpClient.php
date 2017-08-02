<?php
namespace MenickaCZ;

use GuzzleHttp\Client;
use GuzzleHttp\Cookie\CookieJar;

class GuzzleHttpClient implements IHttpClient
{
    private $guzzle;

    private $jar;

    public function __construct(string $baseUrl = null)
    {
        $this->jar = new CookieJar();

        $this->guzzle = new Client([
            'base_uri' => $baseUrl,
            'cookies' => $this->jar,
            'allow_redirects' => true
        ]);
    }

    public function requestGet(string $url): string
    {
        $response = $this->guzzle->get($url);

        if($response->getStatusCode() != 200)
            throw new GuzzleHttpClientException(
                "An error occurred while making GET request with " .
                $response->getStatusCode() . " " . $response->getReasonPhrase()
            );

        return $response->getBody()->getContents();
    }
}

class GuzzleHttpClientException extends \Exception { }