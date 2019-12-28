<?php


namespace App\Controllers\Site;

use Model\DB\Sql;
use Model\Model\User;
use Model\Model\Cart;
use Model\Model\Address;
use Model\Model\Product;
use Model\Page;
use App\Controllers\Controller;
use Helpers\DataValidator;
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


        $mensagens = $this->values["container"]->flash->getMessage("mensagem")[0];
        $dados     = $this->values["container"]->flash->getMessage("dados")[0];

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
            "error" => User::getError(),
            "mensagens" => ($mensagens) ?? false,
            "dados" => ($dados) ?? false
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

    public function register(Request $request,Response $response){

        $mensagens = $this->values["container"]->flash->getMessage("mensagem")[0];
        $dados     = $this->values["container"]->flash->getMessage("dados")[0];


        $options = [
            "data" => [
                "path_loja" => $_ENV["PATH_TEMPLATE_LOJA"],
                "urlRoot"   => $this->getRouteByName("Home"),
                "urlCarrinho" => $this->getRouteByName("carrinho")
            ]
        ];


        $page    = new Page($options);
        $page->setTpl("login",[
            "mensagens" => ($mensagens) ?? false,
            "dados" => ($dados) ?? false,
            "error" => ""
        ]);

    }

    public function registerAction(Request $request,Response $response){

        $post = $request->getParsedBody();

        $validator = new DataValidator();
        $validator->set("Nome",$post["name"])->is_required()->min_length(3);
        $validator->set("Email",$post["email"])->is_required()->is_email();
        $validator->set("Senha",$post["password"])->is_required()->min_length(4);
        $validator->set("Login",$post["login"])->is_required();

        if(!$validator->validate()){


            $this->values["container"]->flash->addMessage("mensagem",$validator->get_errors());
            $this->values["container"]->flash->addMessage("dados",$post);

            $url = $this->getRouteByName("register");
            return $response->withRedirect($url);


        }else {

            if(User::checkLoginExist($post["email"])){
                $mensagens = $this->values["container"]->flash->addMessage("mensagem",["user_exist" => "Já existe um usuário com este e-mail: ".$post["email"].", favor escolha outro."]);
                $dados     = $this->values["container"]->flash->addMessage("dados",$post);

                $url = $this->getRouteByName("register");
                return $response->withRedirect($url);
            }

            $user = new User();
            $user->setData([
                "desperson"    => $post["name"],
                "deslogin"     => $post["email"],
                "despassword"  => $post["password"],
                "desemail"     => $post["email"],
                "nrphone"      => $post["phone"],
                "inadmin"      => 0
            ]);

            if($user->save()){

                User::login($post["email"],$post["password"]);

                $url = $this->getRouteByName("checkout");
                return $response->withRedirect($url);
            }



        }
    }
}