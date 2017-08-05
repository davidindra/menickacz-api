<?php
namespace MenickaCZ;

class NullCache implements ICache
{
    public function cache(string $key, callable $valueCallback, array $callbackParameters, \DateInterval $validity)
    {
        if(count($callbackParameters) > 0){
            return call_user_func_array($valueCallback, $callbackParameters);
        }else{
            return call_user_func($valueCallback);
        }
    }
}