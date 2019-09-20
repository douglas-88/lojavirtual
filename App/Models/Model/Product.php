<?php

namespace Model\Model;


use Model\DB\Sql;
use Model\Model;
use Psr\Http\Message\UploadedFileInterface;
use Slim\Http\UploadedFile;
use Valitron\Validator;
use Helpers\Upload;

class Product extends Model {

    protected $mensagens;

    public static function listAll(){
        $sql = new Sql();
        $result = $sql->select("SELECT * FROM tb_products ORDER BY desproduct");

        return $result;
    }

    public function save(){

        $sql = new Sql();

       $sql->query("CALL sp_products_save(:pidproduct, :pdesproduct,:pvlprice,:pvlwidth,:pvlheight,:pvllength,:pvlweight,:pdesurl)",[
            ":pidproduct"  => $this->getidproduct(),
            ":pdesproduct" => $this->getdesproduct(),
            ":pvlprice" => $this->getvlprice(),
            ":pvlwidth" => $this->getvlwidth(),
            ":pvlheight" => $this->getvlheight(),
            ":pvllength"  => $this->getvllength(),
            ":pvlweight"  => $this->getvlweight(),
            ":pdesurl"  => $this->slug($this->getdesproduct())
        ]);


    }


    public function validateProduct($data,$file = null) {

        $dir = $_SERVER["DOCUMENT_ROOT"].DIRECTORY_SEPARATOR."vendor".DIRECTORY_SEPARATOR."vlucas".DIRECTORY_SEPARATOR."valitron".DIRECTORY_SEPARATOR."lang".DIRECTORY_SEPARATOR;
        Validator::langDir($dir);
        Validator::lang("pt-br");
        $upload = new Upload($file);
        $upload->upload(["nome1","nome2"],"posts_image");
        echo("<pre>");
        var_dump($upload);
        echo("</pre>");
        exit;

        $validador = new Validator($data);
        $validador->rule('required', ['desproduct', 'vlprice','vlwidth','vlheight','vllength','vlweight']);
        $validador->rule("lengthMax",'desproduct',100);
        $validador->labels(
                            [
                             "desproduct" => "Nome do Produto",
                             "vlprice"    => "o preço do produto",
                             "vlwidth"    => "a largura",
                             "vlheight"   => "a altura",
                             "vllength"   => "o comprimento",
                             "vlweight"   => "o peso"
                            ]
                          );


        if ($validador->validate()) {
            $this->setdesproduct($data["desproduct"]);
            $this->setvlprice($data["vlprice"]);
            $this->setvlwidth($data["vlwidth"]);
            $this->setvlheight($data["vlheight"]);
            $this->setvllength($data["vllength"]);
            $this->setvlweight($data["vlweight"]);

            return true;
        }else{
            $this->setdesproduct($data["desproduct"]);
            $this->setvlprice($data["vlprice"]);
            $this->setvlwidth($data["vlwidth"]);
            $this->setvlheight($data["vlheight"]);
            $this->setvllength($data["vllength"]);
            $this->setvlweight($data["vlweight"]);
            $messages["erros"] = $validador->errors();
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

    public function slug($string){

            $string = preg_replace('/[\t\n]/', ' ', $string);
            $string = preg_replace('/\s{2,}/', ' ', $string);

            $list = array(
                'Š' => 'S',
                'š' => 's',
                'Đ' => 'Dj',
                'đ' => 'dj',
                'Ž' => 'Z',
                'ž' => 'z',
                'Č' => 'C',
                'č' => 'c',
                'Ć' => 'C',
                'ć' => 'c',
                'À' => 'A',
                'Á' => 'A',
                'Â' => 'A',
                'Ã' => 'A',
                'Ä' => 'A',
                'Å' => 'A',
                'Æ' => 'A',
                'Ç' => 'C',
                'È' => 'E',
                'É' => 'E',
                'Ê' => 'E',
                'Ë' => 'E',
                'Ì' => 'I',
                'Í' => 'I',
                'Î' => 'I',
                'Ï' => 'I',
                'Ñ' => 'N',
                'Ò' => 'O',
                'Ó' => 'O',
                'Ô' => 'O',
                'Õ' => 'O',
                'Ö' => 'O',
                'Ø' => 'O',
                'Ù' => 'U',
                'Ú' => 'U',
                'Û' => 'U',
                'Ü' => 'U',
                'Ý' => 'Y',
                'Þ' => 'B',
                'ß' => 'Ss',
                'à' => 'a',
                'á' => 'a',
                'â' => 'a',
                'ã' => 'a',
                'ä' => 'a',
                'å' => 'a',
                'æ' => 'a',
                'ç' => 'c',
                'è' => 'e',
                'é' => 'e',
                'ê' => 'e',
                'ë' => 'e',
                'ì' => 'i',
                'í' => 'i',
                'î' => 'i',
                'ï' => 'i',
                'ð' => 'o',
                'ñ' => 'n',
                'ò' => 'o',
                'ó' => 'o',
                'ô' => 'o',
                'õ' => 'o',
                'ö' => 'o',
                'ø' => 'o',
                'ù' => 'u',
                'ú' => 'u',
                'û' => 'u',
                'ý' => 'y',
                'ý' => 'y',
                'þ' => 'b',
                'ÿ' => 'y',
                'Ŕ' => 'R',
                'ŕ' => 'r',
                '/' => '-',
                "'" => '' ,
                ' ' => '-',
                '.' => '-',
            );

            $string = strtr($string, $list);
            $string = preg_replace('/-{2,}/', '-', $string);
            $string = strtolower($string);
            $string = trim(str_replace('"',"",$string));


            return $string;
        }


        public function checkType($extension){
            $suported = ["png","jpeg","jpg"];
            if(in_array($extension,$suported)){
                return true;
            }else{
                return false;
            }
        }

        public function checkSize($size){
        $limit = 2000000;//bytes ou seja, 2MB
        if($size > $limit){
            return false;
        }else{
            return true;
        }
    }

        public function moveFile(UploadedFile $file){
        $dir = $_SERVER["DOCUMENT_ROOT"].
               DIRECTORY_SEPARATOR.
               "resource".
                DIRECTORY_SEPARATOR.
               "site".DIRECTORY_SEPARATOR.
               "img".DIRECTORY_SEPARATOR.
               "produtos".DIRECTORY_SEPARATOR;

        $extensao = pathinfo($file->getClientFilename(),PATHINFO_EXTENSION);
        return $file->moveTo($dir."minha-imagem".".".$extensao);
    }

}

?>