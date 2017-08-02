<?php
namespace MenickaCZ;

use MenickaCZ\Structures\City;
use MenickaCZ\Structures\Restaurant;
use MenickaCZ\Structures\RestaurantInfo;

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

    public function getRestaurantInfo(Restaurant $restaurant)
    {
        $page = $this->parse($this->http->requestGet($restaurant->getUrl()));

        return new RestaurantInfo(
            $restaurant,
            $page->find('.street-address')->first()->text() . ', ' . $page->find('.postal-code')->first()->text() . ' ' . $page->find('.locality')->first()->text(),
            $page->find('span.tel')->last()->text(),
            $page->find('a.email')->first()->text(),
            $page->find('a.url')->first()->text(),
            $page->find('img.photo')->first()->attr('src'),
            $page->find('div.oteviracidoba')->first()->text()
        );
    }
}