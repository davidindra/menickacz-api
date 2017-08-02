<?php
namespace MenickaCZ;

interface IHttpClient
{
    public function __construct(string $baseUrl = null);

    public function requestGet(string $url): string;
}