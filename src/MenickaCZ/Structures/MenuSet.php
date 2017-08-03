<?php
namespace MenickaCZ\Structures;

class MenuSet
{
    private $menus;

    public function __construct(array $menus)
    {
        $this->menus = $menus;
    }

    public function getMenus(){
        return $this->menus;
    }

    public function getTodaysMenu(){
        foreach($this->menus as $menu){
            $ts = $menu->getDate()->getTimestamp();
            if($ts > strtotime('yesterday 15:00') && $ts < strtotime('today 15:00'))
                return $menu;
        }

        return null;
    }

    public function __toString()
    {
        $return = '';
        foreach ($this->menus as $menu){
            $return .= PHP_EOL . $menu;
        }
        return $return;
    }
}