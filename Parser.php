<?php





use DiDom\Element;
use Clue\React\Buzz\Browser;



use \React\EventLoop\LoopInterface;


Class Parser{

    private $client;
    public $data = [];



    public function __construct(Browser $client){
        $this->client = $client;
    }

    public function parse()//array $urls
    {

        foreach ($this->parseUrl() as $url) {

            $this->client->get($url)->then(
                function (\Psr\Http\Message\ResponseInterface $response) {
                    $this->data[] = $this->parseDOM((string)$response->getBody());

                },
                function (Exception $error) {
                    var_dump('There was an error', $error->getMessage());
                });

        }
    }

    public function categoryLink(){
        $document = new Document('https://topbiser.ru/', true);
        $catalog = $document->first('div#sidebar');
        $catalog_href = $catalog->find('a::attr(href)');
        foreach($catalog_href as $href){
    
        $array_category_href[] = $href;
        
        }
    
        return $array_category_href;
    
    }


    public function parsePagination(){

        foreach ($this->categoryLink() as $url) {

            $document = new \DiDom\Document($url,true);
            

            $pagination_nav = $document->first('nav.mb-3')->first('a::attr(href)');
            $link = $document->first('nav.mb-3')->find('a::attr(href)');


            $removed_link = array_pop($link);

            $last_link = end($link);

            $count_pagination = substr($last_link, -2);

            for ($i=1; $i <= $count_pagination; $i++) { 
                $nav = str_replace(2,"", $pagination_nav);
                $full_nav = $nav . $i;
                $array_link_nav[] = $full_nav;
            }
        }
        return $array_link_nav;
    }



    public function parseUrl()
    {
        foreach($this->parsePagination() as $url){
            $doc = new \DiDom\Document($url, true);
            
            $table_cart = $doc->first('div#category-content');
            
            $cnt = $table_cart->count('div.goods');
            
            for ($i=0; $i < $cnt; $i++){
            $cart = $table_cart->find('div.goods')[$i]; // Все товары на странице в пределах пагинации

            $href_cart = $cart->first('div.gallery__item__desc')->first('a::attr(href)'); // Ссылки на товары
            $href[] = $href_cart;
            }
        }
        return $href;
        
    }


    public function parseDOM($html){ //
        $document = new \DiDom\Document($html);
        $cart = $document->first('div.content');

        $title_cart = $cart->first('h1')->text();

        $cnt = $cart->first('div.properties')->first('ul')->count('li');

        for($i=0;$i < $cnt; $i++){
            $full_atr = $cart->first('div.properties')->first('ul')->find('li')[$i];
            $atr1 = $full_atr->find('span')[0]->text();
            $atr2 = $full_atr->find('span')[1]->text();
            $stat["{$atr1}"] = $atr2; // Характеристики товара
        }

        
        $description_product = $cart->first('div.description')->first('span')->text(); // Описание товара
        $price_product = $cart->first('div.price')->first('span')->text(); // Цена товара
        $currency = $cart->first('div.price')->first('meta::attr(content)'); // Валюта товара



        if (!empty($cart->first('div.carousel-inner'))){

            $cnt_carousel = $cart->first('div.carousel-inner')->count('div.carousel-item');
            for($i=0;$i < $cnt_carousel; $i++){
                $img = $cart->find('div.carousel-item')[$i]->first('img::attr(src)'); // Получение картинки
                $img_full[] = $img;
        } 
        }else {
            $img = $cart_img = $cart->first('div.col-lg-4.col-sm-12')->first('a::attr(href)');
            $img_full[] = $img;
        }


        return [
            'title'                  => $title_cart,
            'atr'                    => $stat,
            'description_product'    => $description_product,
            'price_product'          => $price_product,
            'currency'               => $currency,
            'img'                    => $img_full,
        ];


    }

    public function getData(){
        return $this->data;
    }


}

$loop = React\EventLoop\Factory::create();
$client = new Browser($loop);

$parser = new Parser($client);

$parser->parse();

$loop->run();

var_dump($parser->data);