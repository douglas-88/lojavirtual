<?php

namespace Model\Model;

use App\Models\Model\Mailer;
use Model\DB\Sql;
use Model\Model;
use mysql_xdevapi\Exception;
use Zend\InputFilter\InputFilter;
use Zend\InputFilter\Input;
use Zend\Filter;
use Zend\Validator;
use Zend\InputFilter\Factory as InputFilterFactory;
use Zend\I18n\Filter\Alpha;

class Category extends Model {

    protected $mensagens;

    public static function listAll(){
        $sql = new Sql();
        $result = $sql->select("SELECT * FROM tb_categories ORDER BY descategory");

        return $result;
    }

    public function save(){

        $sql = new Sql();

        $result = $sql->query("CALL sp_categories_save(:idcategory, :descategory)",[
            ":idcategory"  => $this->getidcategory(),
            ":descategory" => $this->getdescategory()
        ]);

        $this->setData($result[0]);
        Category::updateFile();
    }

    protected function createInputFilter() {

        $category = [
            "name" => "descategory",
            "required" => true,
            "validators" =>
                [
                    [
                        "name" => "StringLength",
                        "options" => [
                            "min" => 3,
                            "max" => 20,
                            "message" => "A categoria deve ter no mínimo 3, e no máximo 20 letras"
                        ]
                    ]
                ],
            "filters" =>
                [
                    [
                        "name" => "striptags"
                    ]
                ]
        ];


        $factory = new InputFilterFactory();
        $inputFilterNewUser = $factory->createInputFilter([$category]);

        return $inputFilterNewUser;
    }

    public function validateCategory($data) {
        $inputFilter = $this->createInputFilter();
        $inputFilter->setData($data);

        if ($inputFilter->isValid()) {
            $this->setdescategory($inputFilter->getValue("descategory"));

            return true;
        }else{
            $this->setdescategory($inputFilter->getValue("descategory"));
            $messages["erros"] = $inputFilter->getMessages();
            $this->mensagens = $messages;

            return false;
        }
    }

    public function getMensagens(){
        return $this->mensagens;
    }

    public function get(int $idcategory) {
        $sql = new Sql();
        $result = $sql->select("SELECT * FROM tb_categories WHERE idcategory = :ID", array(
            ":ID" => $idcategory
        ));

        if(count($result) < 0){
            return false;
        }else{
            $this->setData($result[0]);
            return $this->getValues();
        }

    }

    public function update(){
        $sql = new Sql();

        $result = $sql->query("CALL sp_categories_save(:idcategory, :descategory)",[
            ":idcategory"  => $this->getidcategory(),
            ":descategory" => $this->getdescategory()
        ]);

        $this->setData($result[0]);
        Category::updateFile();
    }

    public function delete() {

        $sql = new Sql();
        $sql->query("DELETE FROM tb_categories WHERE idcategory =:idcategory",[
            "idcategory" => $this->getidcategory()
        ]);
        Category::updateFile();

    }
    public static function formatPrice($value){
        return number_format($value,2,",",".");
    }
    public static function updateFile(){
        $sql = new Sql();
        $categories = $sql->select("SELECT * FROM tb_categories");
        $html = [];
        $file = $_SERVER["DOCUMENT_ROOT"].DIRECTORY_SEPARATOR."views".DIRECTORY_SEPARATOR."categories-menu.html";
        foreach ($categories as $row) {
            array_push($html, '<li><a href="/categories/'.$row["idcategory"].'">' . trim($row["descategory"]). '</a></li>'."\n");
        }
        file_put_contents($file,implode("",$html));

    }

    public function getProducts($related = true){


        $sql = new Sql();
        if($related){
           $products = $sql->select("
              SELECT * FROM tb_products WHERE idproduct IN(
                   SELECT a.idproduct
                      FROM tb_products a 
                         INNER JOIN
                           tb_categoriesproducts b ON(a.idproduct = b.idproduct) 
                              WHERE b.idcategory = :idcategory
              )
           ",[":idcategory" => $this->getValues()["idcategory"]]);

        }else{
            $products = $sql->select("
              SELECT * FROM tb_products WHERE idproduct NOT IN(
                   SELECT a.idproduct
                      FROM tb_products a 
                         INNER JOIN
                           tb_categoriesproducts b ON(a.idproduct = b.idproduct) 
                              WHERE b.idcategory = :idcategory
              )
           ",[":idcategory" => $this->getValues()["idcategory"]]);
        }
        foreach ($products as &$res):
            $res["vlprice"] = self::formatPrice($res["vlprice"]);
        endforeach;
        return $products;
    }

    public function getProductsPage($page = 1,$itensPerPage = 1){

        $start = ($page -1) * $itensPerPage;

        $sql = new Sql();
        $products = $sql->select("SELECT * FROM tb_products a 
            INNER JOIN tb_categoriesproducts b ON(a.idproduct = b.idproduct)
            INNER JOIN tb_categories c ON(b.idcategory = c.idcategory)
            WHERE c.idcategory = :idcategory LIMIT $start,$itensPerPage"  ,[":idcategory" => $this->getValues()["idcategory"]]);

         $qtdProducts = $sql->select("SELECT count(*) as qtd FROM tb_products a 
            INNER JOIN tb_categoriesproducts b ON(a.idproduct = b.idproduct)
            INNER JOIN tb_categories c ON(b.idcategory = c.idcategory)
            WHERE c.idcategory = :idcategory",[":idcategory" => $this->getValues()["idcategory"]]);
             
         return [
                    "data"  => $products,
                    "total" => intval($qtdProducts[0]["qtd"]),
                    "pages" => ceil( $qtdProducts[0]["qtd"] / intval($itensPerPage))
                ];

    }

}

?>