<?php
namespace MenickaCZ;

interface ICache
{
    public function cache(string $key, callable $valueCallback, array $callbackParameters, \DateInterval $validity);
}