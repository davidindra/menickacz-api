<?php
namespace MenickaCZ;

use MenickaCZ\Structures\City;
use MenickaCZ\Structures\Food;
use MenickaCZ\Structures\Menu;
use MenickaCZ\Structures\MenuSet;
use MenickaCZ\Structures\Restaurant;
use MenickaCZ\Structures\RestaurantInfo;
use MenickaCZ\Structures\RestaurantOpening;
use MenickaCZ\Structures\RestaurantOpeningDay;

/**
 * Class MenickaCZ
 * @package MenickaCZ
 */
class MenickaCZ
{
    /**
     * @var IHttpClient
     */
    private $http;

    /**
     * MenickaCZ constructor.
     * @param IHttpClient $http
     */
    public function __construct(IHttpClient $http)
    {
        $this->http = $http;
    }

    /**
     * @param string $source
     * @return \QueryPath\DOMQuery
     */
    private function parse(string $source)
    {
        return \QueryPath::withHTML5(iconv('CP1250', 'UTF-8', $source));
    }

    /**
     * @return City[]
     */
    public function getAvailableCities()
    {
        $page = $this->parse($this->http->requestGet('index.php'));

        $cities = [];
        foreach($page->find('ul#changecity a') as $member){
            $cities[] = new City(
                trim(str_replace('»', '', $member->text())),
                $member->attr('href')
            );
        }

        return $cities;
    }

    /**
     * @param string $name
     * @return City|null
     */
    public function getCityByName(string $name)
    {
        foreach($this->getAvailableCities() as $city){
            if(levenshtein(strtolower($city->getName()), strtolower($name), 3, 1, 3) < 5)
                return $city;
        }

        return null;
    }

    /**
     * @param City $city
     * @return Restaurant[]
     */
    public function getAvailableRestaurants(City $city)
    {
        $page = $this->parse($this->http->requestGet($city->getUrl()));

        $restaurants = [];
        foreach($page->find('ul#cityroll a') as $member){
            $restaurants[] = new Restaurant(
                trim(str_replace('»', '', $member->text())),
                $member->attr('href')
            );
        }

        return $restaurants;
    }

    /**
     * @param string $name
     * @param City $city
     * @return Restaurant|null
     */
    public function getRestaurantByNameAndCity(string $name, City $city)
    {
        foreach($this->getAvailableRestaurants($city) as $restaurant){
            if(levenshtein(strtolower($restaurant->getName()), strtolower($name), 3, 1, 3) < 5)
                return $restaurant;
        }

        return null;
    }

    /**
     * @param Restaurant $restaurant
     * @return RestaurantInfo
     */
    public function getRestaurantInfo(Restaurant $restaurant)
    {
        $page = $this->parse($this->http->requestGet($restaurant->getUrl()));

        $address = $page->find('.street-address')->first()->text() . ', ';
        $address .= $page->find('.postal-code')->first()->text() . ' ';
        $address .= $page->find('.locality')->first()->text();

        $phone = '';
        foreach($page->find('span.tel') as $tel)
            if(strlen(trim($tel->text())) > 6 && strlen(trim($tel->text())) > strlen($phone))
                $phone = trim($tel->text());

        $email = $page->find('a.email')->first()->text();
        $webpage = $page->find('a.url')->first()->text();
        $photoUrl = $page->find('div.foto div.hlavni a')->attr('href');

        $lines = $page->find('div.oteviracidoba div.in div.line');

        $dayGenerator = function($weekDay, $data){
            $data = substr(trim($data), 4);
            $data = str_replace('dnes', '', $data);

            if(strpos(strtolower($data), 'nonstop') !== false)
                return new RestaurantOpeningDay($weekDay, 0, 24 * 60);
            elseif(strpos(strtolower($data), 'zavřeno') !== false)
                return new RestaurantOpeningDay($weekDay, 0, 0);
            elseif(strpos($data, '.') === false && strpos($data, ':') === false) // we dunno the state
                return new RestaurantOpeningDay($weekDay, 0, 0);
            else
                return new RestaurantOpeningDay(
                    $weekDay,
                    RestaurantOpeningDay::timeStringToNumber(trim(explode('–', $data)[0])),
                    RestaurantOpeningDay::timeStringToNumber(trim(explode('–', $data)[1]))
                );
        };

        $lunchTimeString = $page->find('div.obedovycas')->first()->text();

        $lunchTimeGenerator = function($data){
            $data = explode('Menu:', $data)[1];

            return strpos($data, '–') === false ? null : new RestaurantOpeningDay(
                8,
                RestaurantOpeningDay::timeStringToNumber(trim(explode('–', $data)[0])),
                RestaurantOpeningDay::timeStringToNumber(trim(explode('–', $data)[1]))
            );
        };

        $opening = new RestaurantOpening([
            $dayGenerator(1, $lines->eq(0)->text()),
            $dayGenerator(2, $lines->eq(1)->text()),
            $dayGenerator(3, $lines->eq(2)->text()),
            $dayGenerator(4, $lines->eq(3)->text()),
            $dayGenerator(5, $lines->eq(4)->text()),
            $dayGenerator(6, $lines->eq(5)->text()),
            $dayGenerator(7, $lines->eq(6)->text())
        ],
            $lunchTimeGenerator($lunchTimeString));

        return new RestaurantInfo(
            $restaurant,
            trim($address) == '' ? null : $address,
            $phone == '' ? null : $phone,
            trim($email) == '' ? null : $email,
            trim($webpage) == '' ? null : $webpage,
            trim($photoUrl) == '' ? null : $photoUrl,
            $opening
        );
    }

    public function getMenuSet(Restaurant $restaurant){
        $page = $this->parse($this->http->requestGet($restaurant->getUrl()));

        $menus = [];

        foreach($page->find('div.menicka') as $menu) {
            $date = \DateTime::createFromFormat(
                'j.n.Y',
                trim(
                    explode(
                        ' ',
                        $menu->find('div.nadpis')->first()->text()
                    )[1]
                )
            );
					
            $foods = [];

            $order = 0;
            $food = null;
            $price = 0;

            foreach ($menu->find('ul li') as $li) {
                $orderEl = $li->find(".polozka span.poradi");
                if ($orderEl) {
                    if (!is_null($food)) {
                        $foods[] = new Food($order, $food, $price);

                        //$order = 0;
                        $food = null;
                        $price = 0;
                    }

                    $order = intval(trim($orderEl->text()));
                }
                $itemEl = $li->find("div.polozka");
                if ($itemEl) {
                    $itemEl->remove("em");
                    $itemEl->remove("span.poradi");
                    $food = trim($itemEl->text());
                }
                $priceEl = $li->find("div.cena");
                if ($priceEl) {
                    $price = intval(trim($priceEl->text()));
                }
            }

            if (!is_null($food)) { // probably always true (last food)
                $foods[] = new Food($order, $food, $price);
            }

            if (count($foods) > 0) $menus[] = new Menu($date, $foods);
        }

        return new MenuSet($menus);
    }
}
