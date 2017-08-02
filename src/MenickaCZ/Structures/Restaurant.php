<?php
namespace MenickaCZ\Structures;

class Restaurant
{
    private $name;
    private $url;

    public function __construct(string $name, string $url)
    {
        $this->name = $name;
        $this->url = $url;
    }

    public function getName(){
        return $this->name;
    }

    public function getUrl(){
        return $this->url;
    }

    public function __toString()
    {
        return $this->name . '(' . $this->url . ')';
    }
}