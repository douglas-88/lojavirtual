<?php

namespace Controllers\Site;

use App\Controllers\Controller;
use Model\DB\Sql;
use Model\Page;
use Model\Model\Product;

class HomeController extends Controller {

    public function index() {

        $options = [
                      "data" => [
                                    "path_loja" => $_ENV["PATH_TEMPLATE_LOJA"],
                                    "produtos"  => Product::listAll(),
                                    "urlRoot"   => $this->getRouteByName("Home"),
                                    "urlCarrinho" => $this->getRouteByName("carrinho")
                                ]
                   ];
        $template = new Page($options);
        $template->setTpl("index");
    }

}
