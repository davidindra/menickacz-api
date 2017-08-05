<?php
namespace MenickaCZ;

use GuzzleHttp\Client;
use GuzzleHttp\Cookie\CookieJar;

class GuzzleHttpClient implements IHttpClient
{
    private $guzzle;

    private $jar;

    private $cache;

    public function __construct(string $baseUrl = null, ICache $cache = null)
    {
        $this->jar = new CookieJar();

        $this->guzzle = new Client([
            'base_uri' => $baseUrl,
            'cookies' => $this->jar,
            'allow_redirects' => true
        ]);

        $this->cache = $cache;
    }

    public function requestGet(string $url): string
    {
        if(is_null($this->cache)) {
            return $this->makeRequestGet($url);
        }else{
            return $this->cache->cache(
                'get-' . strval($this->guzzle->getConfig('base_uri')) . $url,
                function($url){
                    return $this->makeRequestGet($url);
                },
                [$url],
                new \DateInterval('PT30M')
            );
        }
    }

    private function makeRequestGet(string $url): string
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