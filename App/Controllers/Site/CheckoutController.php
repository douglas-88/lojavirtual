<?php


namespace App\Controllers\Site;

use Model\DB\Sql;
use Model\Model\User;
use Model\Model\Cart;
use Model\Model\Address;
use Model\Model\Product;
use Model\Page;
use App\Controllers\Controller;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;

class CheckoutController extends Controller
{
    public function index(Request $request,Response $response)
    {
        $options = [
            "data" => [
                "path_loja" => $_ENV["PATH_TEMPLATE_LOJA"],
                "produtos"  => Product::listAll(),
                "urlRoot"   => $this->getRouteByName("Home"),
                "urlCarrinho" => $this->getRouteByName("carrinho")
            ]
        ];
        $cart    = Cart::getFromSession();
        $address = new Address();
        $page    = new Page($options);

        $page->setTpl("checkout",[
            "cart" => $cart->getValues(),
            "address" => $address->getValues()
        ]);
    }

    public function login(Request $request,Response $response)
    {
        $options = [
            "data" => [
                "path_loja" => $_ENV["PATH_TEMPLATE_LOJA"],
                "produtos"  => Product::listAll(),
                "urlRoot"   => $this->getRouteByName("Home"),
                "urlCarrinho" => $this->getRouteByName("carrinho")
            ]
        ];
        $cart    = Cart::getFromSession();
        $address = new Address();
        $page    = new Page($options);

        $page->setTpl("login",[
            "cart" => $cart->getValues(),
            "address" => $address->getValues(),
            "error" => User::getError()
        ]);
    }

    public function loginAction(Request $request,Response $response)
    {
        try {
           User::login(filter_input(INPUT_POST, "login"), filter_input(INPUT_POST, "password"));
        }catch (\Exception $e){
            User::setError($e->getMessage());
        }

        $url = $this->getRouteByName("checkout");
        return $response->withRedirect($url);

    }

    public function logoutAction(Request $request,Response $response){

        User::logout();

        $url = $this->getRouteByName("login");
        return $response->withRedirect($url);

    }
}