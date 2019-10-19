<?php

namespace Controllers\Site;

use Model\DB\Sql;
use Model\Model\User;
use Model\Model\Category;
use Model\Page;
use App\Controllers\Controller;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;

class SiteCategoryController extends Controller {


    public function index(Request $request,Response $response) {
        $route = $request->getAttribute('route');
        $idcategory = $route->getArgument("id");
        $routeHome     = $this->getRouteByName("Home");


        $category = new Category();

        if(!$category->get($idcategory)){
            $url = $this->getRouteByName("Home");
            return $response->withRedirect($url);
        }else{
            $value = $category->get($idcategory);
        }
        $page = (isset($_GET["page"])) ? intval($_GET["page"]) : 1;
        $pagination = $category->getProductsPage($page);

        $pages = [];
        for ($i = 1; $i < $pagination["total"] ; $i++){
            array_push($pages,[
                "link" => "/categories/{$idcategory}?page=".$i."#produtos",
                "page" => $i
            ]);
        }
        $options = [
                     "data" => [
                                   "path_loja" => $_ENV["PATH_TEMPLATE_LOJA"],
                                    "category" => $value,
                                    "productsRelated" => $pagination["data"],
                                    "pages" => $pages,
                                    "qtd_pages" => $pagination["pages"],
                                    "current_page" => $page,
                                    "urlHome" => $routeHome,
                                    "prev" => "/categories/{$idcategory}?page=".($page - 1)."#produtos",
                                    "next" => "/categories/{$idcategory}?page=".($page + 1)."#produtos"
                               ]
                   ];
        $template = new Page($options);
        $template->setTpl("category");
    }

}
