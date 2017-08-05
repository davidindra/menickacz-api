<?php
namespace MenickaCZ\Structures;

class Menu
{
    private $date;
    private $foods;

    public function __construct(\DateTime $date, array $foods)
    {
        $this->date = $date;
        $this->foods = $foods;
    }

    public function getDate(){
        return $this->date;
    }

    public function getFoods(){
        return $this->foods;
    }

    public function __toString()
    {
        $return = 'Jídelní lístek na ' . $this->date->format('j.n.Y') . ': <br>';
        foreach ($this->foods as $food){
            $return .= '&nbsp;- ' . $food . '<br>';
        }
        return $return;
    }
}