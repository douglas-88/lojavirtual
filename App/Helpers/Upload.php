<?php

namespace Helpers;

use Model\Model;
use Psr\Http\Message\UploadedFileInterface;

class Upload extends Model{

    protected $file;

    //Para Configuração/Validação:
    protected $max_upload_size;
    protected $extension_supported;
    protected $upload_dir;
    protected $width;
    protected $height;

   public function __construct($file,$width,$height)
   {
       $this->max_upload_size     = getenv("MAX_UPLOAD_SIZE") * 1048576;//Convertendo de MegaBytes para Bytes.
       $this->extension_supported = explode(",",getenv("EXTENSION_ALLOWED"));
       $this->upload_dir          = getenv("UPLOAD_DIR");
       $this->width               = $width;
       $this->height              = $height;

       for($i = 0; $i < count($file);$i++){
           $this->file[$i]["tmp"]         = $file[$i]->file;
           $this->file[$i]["name"]        = pathinfo($file[$i]->getClientFilename(),PATHINFO_FILENAME);
           $this->file[$i]["type"]        = pathinfo($file[$i]->getClientFilename(),PATHINFO_EXTENSION);
           $this->file[$i]["size"]        = $file[$i]->getSize();
           $this->file[$i]["error"]       = $file[$i]->getError();
           $this->file[$i]["status"]      = null;
           $this->file[$i]["message"]     = null;
       }


   }

   public function getStatus(){
       return $this->file;
   }

    public function statusFile(){

       for($i = 0; $i < count($this->file); $i++) {

          if($this->file[$i]["status"] === true OR is_null($this->file[$i]["status"])) {
              switch ($this->file[$i]["error"]) {
                  case 0 :
                      $this->file[$i]["message"] = "Arquivo pronto para ser enviado.";
                      $this->file[$i]["status"] = true;
                      break;
                  case 1:
                      $this->file[$i]["message"] = "O arquivo enviado excede o limite definido na diretiva:upload_max_filesize";
                      $this->file[$i]["status"] = false;
                      break;
                  case 2:
                      $this->file[$i]["message"] = "O arquivo excede o limite definido em MAX_FILE_SIZE no formulário HTML.";
                      $this->file[$i]["status"] = false;
                      break;
                  case 3:
                      $this->file[$i]["message"] = "O upload do arquivo foi feito parcialmente.";
                      $this->file[$i]["status"] = false;
                      break;
                  case 4:
                      $this->file[$i]["message"] = "Nenhum arquivo foi enviado para o Formulário";
                      $this->file[$i]["status"] = false;
                      break;
                  case 5:
                      $this->file[$i]["message"] = "Pasta temporária ausênte.";
                      $this->file[$i]["status"] = false;
                      break;
                  case 6:
                      $this->file[$i]["message"] = "Falha em escrever o arquivo em disco.";
                      $this->file[$i]["status"] = false;
                      break;
                  case 7:
                      $this->file[$i]["message"] = "Uma extensão do PHP interrompeu o upload do arquivo.";
                      $this->file[$i]["status"] = false;
                      break;
                  default:
                      $this->file[$i]["message"] = "Erro não catálogado ao fazer o Upload";
                      $this->file[$i]["status"] = false;
                      break;
              }

          }

       }

    }
    public function checkSizeFile(){

        for($i = 0; $i < count($this->file); $i++) {
            if($this->file[$i]["status"] === true OR is_null($this->file[$i]["status"])) {
                if ($this->file[$i]["size"] > $this->max_upload_size) {
                    $this->file[$i]["message"] = "Favor informe um arquivo menor que {$_ENV["MAX_UPLOAD_SIZE"]} MegaByte";
                    $this->file[$i]["status"] = false;
                } else {
                    $this->file[$i]["status"] = true;
                }
            }
        }

    }
    public function checkExtension(){

        for($i = 0; $i < count($this->file); $i++){

            if($this->file[$i]["status"] === true OR is_null($this->file[$i]["status"])) {
                if (!in_array($this->file[$i]["type"], $this->extension_supported)) {
                    $this->file[$i]["message"] = "Extensão .{$this->file[$i]["type"]} não suportada.";
                    $this->file[$i]["status"] = false;
                } else {
                    $this->file[$i]["status"] = true;
                }
            }

        }

    }
    public function upload($nameFile,$folder_opt = null)
    {
        $ano      = date("Y");
        $mes      = date("m");
        $dia      = date("d");

        for($i = 0;$i < count($this->file);$i++){

            if($this->file[$i]["status"]){
                //Cria os diretórios:
                if (!is_null($folder_opt)) {
                    $pathCustom = $this->upload_dir . DIRECTORY_SEPARATOR . $folder_opt . DIRECTORY_SEPARATOR .$ano.DIRECTORY_SEPARATOR . $mes . DIRECTORY_SEPARATOR .$dia .DIRECTORY_SEPARATOR;
                    if (!file_exists($pathCustom)) {
                        mkdir($pathCustom, 0755,true);
                        chmod($pathCustom,0755);
                        $dir = $pathCustom;
                    }else{
                        $dir = $pathCustom;
                    }
                }else{
                    $pathDefault = $this->upload_dir . DIRECTORY_SEPARATOR .$ano.DIRECTORY_SEPARATOR . $mes . DIRECTORY_SEPARATOR .$dia .DIRECTORY_SEPARATOR;
                    mkdir($pathDefault,0755,true);
                    chmod($pathDefault,0755);
                    $dir = $pathDefault;
                }
                //Se encarrega de os arquivos NÃO irem com nomes repetidos:
                while(file_exists($dir.$nameFile[$i].".".$this->file[$i]["type"])){
                    $nameFile[$i] = $this->checkFileExist($nameFile[$i]);
                }
                //Faz o UPLOAD:
                $fileTemp = $this->file[$i]["tmp"];
                $moveTo = $dir.$nameFile[$i] . "." . $this->file[$i]["type"];
                $this->imageResize($fileTemp,$this->file[$i]["type"],$moveTo,$this->width,$this->height);
                //move_uploaded_file($fileTemp, $moveTo);
                chmod($moveTo,0755);
                if(file_exists($moveTo)){

                    $path = substr($moveTo,strpos($moveTo,"uploads"));

                    $this->file[$i]["message"] = $path;
                }
            }
        }

    }

    public function validImage(){
        $this->statusFile();
        $this->checkSizeFile();
        $this->checkExtension();
    }

    public function checkFileExist($FileName){

           if(strpos($FileName,"_copia-")){
               $numberCopi = substr($FileName,strpos($FileName,"_copia-")+7);
               $nameFile = substr($FileName,0,strpos($FileName,"_copia-")) . "_copia-" . ($numberCopi + 1);
           }else{
               $nameFile = $FileName ."_copia-". 1;
           }

        return $nameFile;
    }

    private function imageResize($img_file,$type, $folder,$new_width, $new_height, $proportion = true){

        list($width, $height) =  getimagesize($img_file);

        if ($proportion) {
            if ($width > $height) {
                $new_height = ($new_width  / $width) * $height;

            } elseif ($width < $height) {
                $new_width = ($new_height / $height) * $width;
            }
        }

        $nova_imagem  = imagecreatetruecolor($new_width, $new_height);


        if($type == "jpeg" or $type == "jpg"){
            $img_original = imagecreatefromjpeg($img_file);
            imagecopyresampled($nova_imagem, $img_original, 0, 0, 0, 0, $new_width, $new_height, $width, $height);
            imagejpeg($nova_imagem , $folder,75);
        }if($type == "png"){
            $img_original = imagecreatefrompng($img_file);
            imagecopyresampled($nova_imagem, $img_original, 0, 0, 0, 0, $new_width, $new_height, $width, $height);
            imagepng($nova_imagem , $folder,7);
        }


        imagedestroy($img_original);
        imagedestroy($nova_imagem);
    }

}