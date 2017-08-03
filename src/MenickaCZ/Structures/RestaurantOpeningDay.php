<?php
namespace MenickaCZ\Structures;

class RestaurantOpeningDay
{
    private $weekDay;

    private $from;

    private $to;

    public function __construct(int $weekDay, int $from, int $to)
    {
        $this->weekDay = $weekDay;
        $this->from = $from;
        $this->to = $to;
    }

    public function getWeekDay(){
        return $this->weekDay;
    }

    public function getFrom(){
        return $this::numberToTimeString($this->from);
    }

    public function getTo(){
        return $this::numberToTimeString($this->to);
    }

    private function getWeekDayString(bool $toLower = true){
        $switch = function($weekDay){
            switch($weekDay){
                case 1: return 'Pondělí';
                case 2: return 'Úterý';
                case 3: return 'Středa';
                case 4: return 'Čtvrtek';
                case 5: return 'Pátek';
                case 6: return 'Sobota';
                case 7: return 'Neděle';
            }
        };

        return $toLower ? mb_strtolower($switch($this->weekDay)) : $switch($this->weekDay);
    }

    public function isClosed(){
        return $this->from == 0 && $this->to == 0;
    }

    public function isNonstop(){
        return $this->from == 0 && $this->to == 24 * 60;
    }

    public static function numberToTimeString(int $number){
        return ($number - ($number % 60)) / 60 . ':' .
            str_pad($number % 60, 2, '0', STR_PAD_LEFT);
    }

    public static function timeStringToNumber(string $time){
        list($hours, $minutes) = explode('.', trim($time));
        return $hours * 60 + $minutes;
    }

    public function __toString()
    {
        if($this->isClosed()) return $this->getWeekDayString() . ': zavřeno';
        if($this->isNonstop()) return $this->getWeekDayString() . ': nonstop';
        return $this->getWeekDayString() . ': ' .
            $this::numberToTimeString($this->from) . ' - ' .
            $this::numberToTimeString($this->to);
    }
}