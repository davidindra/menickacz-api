<?php
namespace MenickaCZ\Structures;

class Food
{
    private $order;
    private $name;
    private $price;

    public function __construct(int $order, string $name, float $price)
    {
        $this->order = $order == 0 ? null : $order;
        $this->name = $name;
        $this->price = $price == 0 ? null : $price;
    }

    public function hasOrder(){
        return !is_null($this->order);
    }

    public function getOrder(){
        return $this->order;
    }

    public function getName(){
        return $this->name;
    }

    public function hasPrice(){
        return !is_null($this->price);
    }

    public function getPrice(){
        return $this->price;
    }

    public function __toString()
    {
        return
            ($this->hasOrder() ? $this->order . '. ' : '') .
            $this->name .
            ($this->hasPrice() ? ' (' . $this->price . ',-)' : '');
    }
}