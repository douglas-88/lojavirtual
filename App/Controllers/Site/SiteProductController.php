<?php

namespace Controllers\Site;

use Model\DB\Sql;
use Model\Model\User;
use Model\Model\Product;
use Model\Page;
use App\Controllers\Controller;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;

class SiteProductController extends Controller {


    public function index(Request $request,Response $response) {
        $route = $request->getAttribute('route');
        $idcategory = $route->getArgument("id");

        $category = new Category();
        if(!$category->get($idcategory)){
            $url = $this->getRouteByName("Home");
            return $response->withRedirect($url);
        }else{
            $value = $category->get($idcategory);
        }


        $options = [
                     "data" => [
                                   "path_loja" => $_ENV["PATH_TEMPLATE_LOJA"],
                                    "category" => $value,
                                     "urlCarrinho" => $this->getRouteByName("carrinho")
                               ]
                   ];
        $template = new Page($options);
        $template->setTpl("category");
    }

    public function show(Request $request,Response $response){
        $slug = $request->getAttribute("url");
        $url = $this->getRouteByName("Home");

        $produto = new Product();
        $produto->getFromUrl($slug);


        $options = [
            "data" => [
                "path_loja" => $_ENV["PATH_TEMPLATE_LOJA"],
                "urlRoot"   => $url,
                "categories" => $produto->getCategories(),
                "urlCarrinho" => $this->getRouteByName("carrinho")
            ]
        ];
        $page = new Page($options);
        $page->setTpl("product-detail",["product" => $produto->getValues()]);

    }

}
