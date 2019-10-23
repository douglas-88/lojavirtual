<?php
/**
 * Product Class.
 *
 * @author Douglas Fernando Ferreira Braga
 * @author Email <<dcdouglas64@gmail.com>>
 * @author GitHub <https://github.com/DouglasFFBraga/lojavirtual>
 * @version 0.1
 */
namespace Model\Model;


use Model\DB\Sql;
use Model\Model;
use Psr\Http\Message\UploadedFileInterface;
use Slim\Http\UploadedFile;
use Valitron\Validator;
use Helpers\Upload;
use Helpers\DataValidator;

class Product extends Model {

    protected $mensagens;

    public static function listAll(){
        $sql = new Sql();
        $result = $sql->select("SELECT * FROM tb_products ORDER BY desproduct");
        foreach ($result as &$res):
            $res["vlprice"] = self::formatPrice($res["vlprice"]);
        endforeach;
        return $result;
    }

    public function save(){

        $sql = new Sql();

        $sql->query("CALL sp_products_save(:pidproduct, :pdesproduct,:pvlprice,:pvlwidth,:pvlheight,:pvllength,:pvlweight,:pdesurl,:pathphoto)",[
            ":pidproduct"  => $this->getidproduct(),
            ":pdesproduct" => $this->getdesproduct(),
            ":pvlprice" => $this->getvlprice(),
            ":pvlwidth" => $this->getvlwidth(),
            ":pvlheight" => $this->getvlheight(),
            ":pvllength"  => $this->getvllength(),
            ":pvlweight"  => $this->getvlweight(),
            ":pdesurl"  => $this->slug($this->getdesproduct()),
            ":pathphoto" => $this->getpathphoto()
        ]);

    }

    public static function formatPrice($value){
        return number_format($value,2,",",".");
    }

    public function validateProduct($data,$file = null) {

        $upload = new Upload($file,200,200);
        $upload->validImage();

        $data["image"] = $upload->file;

        $v = new DataValidator();
        $v->define_pattern('erro_');
        $v->set("nome", $data["desproduct"])->is_required()->max_length(100);
        $v->set("preco", $data["vlprice"])->is_required();
        $v->set("largura", $data["vlwidth"])->is_required();
        $v->set("altura", $data["vlheight"])->is_required();
        $v->set("comprimento", $data["vllength"])->is_required();
        $v->set("peso", $data["vlweight"])->is_required();
        if(!is_null($file)):
            $v->set("imagem1",$data["image"][0]["status"])->checkImage($data["image"][0]["message"]);
        endif;

        if ($v->validate()) {
            $this->setdesproduct($data["desproduct"]);
            $this->setvlprice(str_replace(",",".",str_replace(".","",$data["vlprice"])));
            $this->setvlwidth($data["vlwidth"]);
            $this->setvlheight($data["vlheight"]);
            $this->setvllength($data["vllength"]);
            $this->setvlweight($data["vlweight"]);
            if(!is_null($file)):
                $upload->upload([$this->slug($this->getdesproduct())]);
                $this->setpathphoto($upload->file[0]["message"]);
            endif;

            return true;
        }else{
            $this->setdesproduct($data["desproduct"]);
            $this->setvlprice($data["vlprice"]);
            $this->setvlwidth($data["vlwidth"]);
            $this->setvlheight($data["vlheight"]);
            $this->setvllength($data["vllength"]);
            $this->setvlweight($data["vlweight"]);
            $this->setpathphoto("deu errado");
            $messages["erros"] = $v->get_errors();
            $this->mensagens = $messages;

            return false;
        }
    }

    public function getMensagens(){
        return $this->mensagens;
    }

    public function get(int $idproduct) {
        $sql = new Sql();
        $result = $sql->select("SELECT * FROM  tb_products WHERE 	idproduct = :ID", array(
            ":ID" => $idproduct
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

        $result = $sql->query("CALL sp_products_save(:pidproduct, :pdesproduct,:pvlprice,:pvlwidth,:pvlheight,:pvllength,:pvlweight,:pdesurl,:ppathphoto)",[
            ":pidproduct"   => $this->getidproduct(),
            ":pdesproduct"  => $this->getdesproduct(),
            ":pvlprice"     => $this->getvlprice(),
            ":pvlwidth"     => $this->getvlwidth(),
            ":pvlheight"    => $this->getvlheight(),
            ":pvllength"    => $this->getvllength(),
            ":pvlweight"    => $this->getvlweight(),
            ":pdesurl"      => $this->slug($this->getdesproduct()),
            ":ppathphoto"   => $this->getpathphoto()
        ]);

        $this->setData($result[0]);

    }

    public function delete() {

        $sql = new Sql();
        $sql->query("DELETE FROM  tb_products WHERE idproduct =:ID",[
            ":ID" => $this->getidproduct()
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

    public function getFromUrl($url){
       $sql = new Sql();
       $query = "SELECT * FROM tb_products WHERE desurl LIKE :url";
       $p = $sql->select($query,[":url" => "%".$url."%"])[0];
       $p["vlprice"] = self::formatPrice($p["vlprice"]);
       $this->setData($p);

    }

    public function getCategories(){

        $query = 'SELECT a.idcategory, a.descategory FROM tb_categories a INNER JOIN tb_categoriesproducts b ON a.idcategory = b.idcategory WHERE b.idproduct = :id';
        $sql = new Sql();
        $results = $sql->select($query,[":id" => $this->getidproduct()]);
        /*
        $categories = [];
        foreach ($results as $item) {
            array_push($categories,$item["descategory"]);
        }
        return implode($categories,", ");
        */
        return $results;

    }

}

?>