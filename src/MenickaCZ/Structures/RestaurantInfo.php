<?php
namespace MenickaCZ\Structures;

class RestaurantInfo
{
    private $restaurant;
    private $address, $phone, $email, $webpage, $photoUrl, $opening;

    public function __construct(Restaurant $restaurant, $address, $phone, $email, $webpage, $photoUrl, RestaurantOpening $opening)
    {
        $this->restaurant = $restaurant;
        $this->address = $address;
        $this->phone = $phone;
        $this->email = $email;
        $this->webpage= $webpage;
        $this->photoUrl = $photoUrl;
        $this->opening = $opening;
    }

    public function getAddress(){
        return $this->address;
    }

    public function getPhone(){
        return $this->phone;
    }

    public function getEmail(){
        return $this->email;
    }

    public function getWebpage(){
        return $this->webpage;
    }

    public function getPhotoUrl(){
        return $this->photoUrl;
    }

    public function getOpening(){
        return $this->opening;
    }

    public function __toString()
    {
        return
            $this->restaurant->getName() . ': ' .
            'adresa: ' . $this->address . '; ' .
            'telefon: ' . $this->phone . '; ' .
            'e-mail: ' . $this->email . '; ' .
            'web: ' . $this->webpage . '; ' .
            'fotka: ' . $this->photoUrl . '; ' .
            'otevírací doba: ' . $this->opening . '; ';
    }
}