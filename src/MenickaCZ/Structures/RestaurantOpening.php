<?php
namespace MenickaCZ\Structures;

class RestaurantOpening
{
    private $days;

    public function __construct(array $days)
    {
        $this->days = $days;
    }

    public function getDays(){
        return $this->days;
    }

    public function __toString()
    {
        $return = '';
        foreach ($this->days as $day){
            $return .= '| ' . $day . ' |';
        }
        return $return;
    }
}