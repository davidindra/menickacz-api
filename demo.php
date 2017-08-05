<?php
require 'vendor/autoload.php';

use MenickaCZ\GuzzleHttpClient;
use MenickaCZ\MenickaCZ;
use MenickaCZ\SqliteCache;

ini_set('display_errors', 1);

echo '<h1>Meníčka.cz</h1>';

$cache = new SqliteCache(__DIR__ . '/cache.db');
//$cache = new \MenickaCZ\NullCache();

$menicka = new MenickaCZ(new GuzzleHttpClient('http://menicka.cz/', $cache));

if(!isset($_GET['city'])){
    echo 'Města k dispozici: ';
    foreach ($menicka->getAvailableCities() as $city){
        echo '<a href="?city=' . $city->getName() . '">' . $city->getName() . '</a>, ';
    }
}else{
    $city = $menicka->getCityByName($_GET['city']);
    if($city == null){
        echo 'Zadané město nebylo nalezeno.';
    }else{
        echo 'Vybrané město: <b>' . $city->getName() . '</b> (<a href="?">zpět</a> na výběr měst)<br>';

        if(!isset($_GET['restaurant'])) {
            echo 'Restaurace k dispozici: ';

            foreach ($menicka->getAvailableRestaurants($city) as $restaurant) {
                echo '<a href="?city=' . $city->getName() . '&restaurant=' . $restaurant->getName() . '">' . $restaurant->getName() . '</a>, ';
            }
        }else{
            $restaurant = $menicka->getRestaurantByNameAndCity($_GET['restaurant'], $city);
            if($restaurant == null){
                echo 'Zadaná restaurace nebyla nalezena.';
            }else{
                echo 'Vybraná restaurace: <b>' . $restaurant->getName() . '</b> (<a href="?city=' . $city->getName() . '">zpět</a> na výběr restaurací)<br><br>';

                $restaurantInfo = $menicka->getRestaurantInfo($restaurant);
                echo '<img style="width: auto; height: 10em;" src="' . $restaurantInfo->getPhotoUrl() . '"><br><br>';
                echo 'Adresa: ' . $restaurantInfo->getAddress() . '<br>';
                if($restaurantInfo->getEmail()) echo 'E-mail: ' . $restaurantInfo->getEmail() . '<br>';
                if($restaurantInfo->getPhone()) echo 'Telefon: ' . $restaurantInfo->getPhone() . '<br>';
                if($restaurantInfo->getWebpage()) echo 'Web: <a href="http://' . $restaurantInfo->getWebpage() . '">' . $restaurantInfo->getWebpage() . '</a><br>';
                echo 'Otevírací doba: ' . $restaurantInfo->getOpening() . '<br><br>';

                echo '<h3>Jídelní lístek</h3>';

                $menuSet = $menicka->getMenuSet($restaurant);
                foreach ($menuSet->getMenus() as $menu) {
                    echo '<br><b>Lístek ' . $menu->getDate()->format('j.n.Y') . '</b><br>';

                    foreach ($menu->getFoods() as $food){
                        //echo '&nbsp;- ' . $food->getName() . ' (' . $food->getPrice() . ',-)<br>';
                        echo $food . '<br>';
                    }
                }
            }
        }
    }
}
