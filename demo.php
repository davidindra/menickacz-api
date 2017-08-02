<?php
require 'vendor/autoload.php';

use MenickaCZ\GuzzleHttpClient;
use MenickaCZ\MenickaCZ;

$menicka = new MenickaCZ(new GuzzleHttpClient('http://menicka.cz/'));

//var_dump($menicka->getAvailableCities());

//var_dump($menicka->getCityByName($_GET['city']));

echo
    $menicka->getRestaurantInfo(
        $menicka->getAvailableRestaurants(
            $menicka->getCityByName(
                $_GET['city']
            )
        )[0]
    )
;