<?php
namespace MenickaCZ;

interface IHttpClient
{
    public function __construct(string $baseUrl = null, ICache $cache = null);

    public function requestGet(string $url): string;
}