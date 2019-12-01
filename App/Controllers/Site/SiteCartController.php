<?php


namespace App\Controllers\Site;

use Model\DB\Sql;
use Model\Model\User;
use Model\Model\Cart;
use Model\Model\Product;
use Model\Page;
use App\Controllers\Controller;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;

class SiteCartController extends Controller
{
   public function index(Request $request,Response $response){
       $routeCarrinho        = $this->getRouteByName("carrinho");

       $cart = Cart::getFromSession();

       $options = [
           "data" => [
                       "path_loja"      => $_ENV["PATH_TEMPLATE_LOJA"],
                       "urlRoot"        => $this->getRouteByName("Home"),
                       "urlCarrinho"    => $routeCarrinho,
                       "erro"           => $cart::getMsgErro(),
                       "cart"           => $cart->getValues(),
                       "products"       => $cart->getProducts()
                     ]
       ];
       $template = new Page($options);
       $template->setTpl("cart");
   }

   public function add(Request $request,Response $response){
       $route = $request->getAttribute('route');
       $idproduct = $route->getArgument("idproduct");

       $product = new Product();
       $product->get($idproduct);

       $cart = Cart::getFromSession();
       $cart->addProduct($product);

       $options = [
           "data" => [
               "path_loja" => $_ENV["PATH_TEMPLATE_LOJA"],
               "urlRoot"   => $this->getRouteByName("Home"),
               "urlCarrinho" => $this->getRouteByName("carrinho"),
           ]
       ];
       $url = $this->getRouteByName("carrinho");
       return $response->withRedirect($url);

   }

    public function remove(Request $request,Response $response){

        $route = $request->getAttribute('route');
        $idproduct = $route->getArgument("idproduct");

        $product = new Product();
        $product->get($idproduct);

        $cart = Cart::getFromSession();
        $cart->removeProduct($product);

        $options = [
            "data" => [
                "path_loja" => $_ENV["PATH_TEMPLATE_LOJA"],
                "urlRoot"   => $this->getRouteByName("Home"),
                "urlCarrinho" => $this->getRouteByName("carrinho"),

                "cart"           => $cart->getValues(),
                "products"       => $cart->getProducts()
            ]
        ];
        $url = $this->getRouteByName("carrinho");
        return $response->withRedirect($url);

    }

    public function removeAll(Request $request,Response $response){

        $route = $request->getAttribute('route');
        $idproduct = $route->getArgument("idproduct");

        $product = new Product();
        $product->get($idproduct);

        $cart = Cart::getFromSession();
        $cart->removeProduct($product,true);

        $options = [
            "data" => [
                "path_loja" => $_ENV["PATH_TEMPLATE_LOJA"],
                "urlRoot"   => $this->getRouteByName("Home"),
                "urlCarrinho" => $this->getRouteByName("carrinho")
            ]
        ];
        $url = $this->getRouteByName("carrinho");
        return $response->withRedirect($url);

    }

    public function calculateFrete(Request $request,Response $response){
        $post = $request->getParsedBody();
        $zipcode = $post["zipcode"];

        $cart = Cart::getFromSession();
        $cart->setFrete($zipcode);

        $options = [
            "data" => [
                "path_loja" => $_ENV["PATH_TEMPLATE_LOJA"],
                "urlRoot"   => $this->getRouteByName("Home"),
                "urlCarrinho" => $this->getRouteByName("carrinho")
            ]
        ];
        $url = $this->getRouteByName("carrinho");
        return $response->withRedirect($url);

    }
}