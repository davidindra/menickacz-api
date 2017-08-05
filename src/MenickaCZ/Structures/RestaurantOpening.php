<?php
namespace MenickaCZ\Structures;

class RestaurantOpening
{
    private $days;

    private $lunchTime;

    public function __construct(array $days, RestaurantOpeningDay $lunchTime = null)
    {
        $this->days = $days;
        $this->lunchTime = $lunchTime;
    }

    public function getDays(){
        return $this->days;
    }

    public function hasLunchTime(){
        return !is_null($this->lunchTime);
    }

    public function getLunchTime(){
        return $this->lunchTime;
    }

    public function __toString()
    {
        $return = '';
        foreach ($this->hasLunchTime() ? array_merge($this->days, [$this->lunchTime]) : $this->days as $day){
            $return .= '| ' . $day . ' |';
        }
        return $return;
    }
}