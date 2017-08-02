<?php
namespace MenickaCZ\Structures;

class Food
{
    private $name;
    private $price;

    public function __construct(string $name, float $price)
    {
        $this->name = $name;
        $this->price = $price;
    }

    public function getName(){
        return $this->name;
    }

    public function getPrice(){
        return $this->price;
    }

    public function __toString()
    {
        return $this->name . ', ' . $this->url;
    }
}