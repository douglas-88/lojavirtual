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
    }

    public function delete() {

        $sql = new Sql();
        $sql->query("DELETE FROM tb_categories WHERE idcategory =:idcategory",[
            "idcategory" => $this->getidcategory()
        ]);
    }
}

?>