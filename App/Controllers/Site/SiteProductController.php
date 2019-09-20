<?php

namespace Controllers\Site;

use Model\DB\Sql;
use Model\Model\User;
use Model\Model\Product;
use Model\Page;
use App\Controllers\Controller;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;

class SiteCategoryController extends Controller {


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
                                    "category" => $value
                               ]
                   ];
        $template = new Page($options);
        $template->setTpl("category");
    }

}