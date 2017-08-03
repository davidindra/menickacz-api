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

        $opening = new RestaurantOpening([
            $dayGenerator(1, $lines->eq(0)->text()),
            $dayGenerator(2, $lines->eq(1)->text()),
            $dayGenerator(3, $lines->eq(2)->text()),
            $dayGenerator(4, $lines->eq(3)->text()),
            $dayGenerator(5, $lines->eq(4)->text()),
            $dayGenerator(6, $lines->eq(5)->text()),
            $dayGenerator(7, $lines->eq(6)->text())
        ]);

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

        $menusArray = [];
        $foodsBuffer = [];
        $soup = null;
        $food = null;
        $currentDate = null;
        foreach($page->find('div.menicka div') as $div){
            if($div->hasClass('datum')){
                if($currentDate != null && count($foodsBuffer)){
                    //echo $currentDate->format('j.n.Y');
                    //var_dump($foodsBuffer);
                    $menusArray[] = new Menu($currentDate, $foodsBuffer);
                    $foodsBuffer = [];
                }

                $currentDate = \DateTime::createFromFormat('j.n.Y', trim(explode(' ', $div->text())[1]));
                continue;
            }

            if($div->hasClass('nabidka_1')){
                $soup = trim($div->text());
                continue;
            }

            if($div->hasClass('nabidka_2')){
                $food = trim($div->text());
                continue;
            }

            if($div->hasClass('cena')){
                if($soup != null){
                    $foodsBuffer[] = new Food($soup, trim($div->text()));
                }else{
                    $foodsBuffer[] = new Food($food, trim($div->text()));
                }
                $food = null;
                $soup = null;
                continue;
            }
        }

        return new MenuSet($menusArray);
    }
}