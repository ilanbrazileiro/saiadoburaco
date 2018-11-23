<?php 

namespace Hcode\Model;

use \Hcode\DB\Sql;
use \Hcode\Model;
use \Hcode\Mailer;

class Products extends Model{

	

	public static function listAll()#### Listar todas as categorias
	{
		$sql = new Sql();

		return $results = $sql->select("SELECT * FROM tb_products ORDER BY desproduct");

	}

	public function save()##### Salva os registros no BD
	{
		$sql = new Sql();

		$results = $sql->select("CALL sp_products_save(:idproduct, :desproduct, :vlprice, :vlwidth, :vlheight, :vllength, :vlweight, :desurl)", array(
			":idproduct"=>$this->getidproduct(),
			":desproduct"=>$this->getdesproduct(),
			":vlprice"=>$this->getvlprice(),
			":vlwidth"=>$this->getvlwidth(),
			":vlheight"=>$this->getvlheight(),
			":vllength"=>$this->getvllength(),
			":vlweight"=>$this->getvlweight(),
			":desurl"=>$this->getdesurl()		
		));

		$this->setData($results[0]);
	}

	public function get($idproduct)##### Retorna a Produto pelo id
	{
		$sql = new Sql();

		$results = $sql->select("SELECT * FROM tb_products WHERE idproduct = :idproduct",array(
			':idproduct' => $idproduct 
		));

		$this->setData($results[0]);//Seta os valores em suas chaves
	}

	public function delete()
	{
		$sql = new Sql();

		$sql->query("DELETE FROM tb_products WHERE idproduct = :idproduct", array(
			":idproduct"=>$this->getidproduct()
		));
	}

	public function checkPhoto()//Checa se existe foto do produto, Senão preenche com foto padrão
	{

		if (file_exists($_SERVER['DOCUMENT_ROOT'] . DIRECTORY_SEPARATOR . "res" . 
			DIRECTORY_SEPARATOR . "site" .
			DIRECTORY_SEPARATOR . "img" . 
			DIRECTORY_SEPARATOR . "products" . 
			DIRECTORY_SEPARATOR . 
			$this->getidproduct() . ".jpg"
		)){

			$url = "/res/site/img/products/" . $this->getidproduct() . ".jpg";
		} else {

			$url =  "/res/site/img/product.jpg";
		}

		return $this->setdesphoto($url);
	}
	
	public function getValues()//Sobrescreve a funçãoincluindo a foto do produto
	{

		$this->checkPhoto();

		$values = parent::getValues();

		return $values;

	}

	public function setPhoto($file)//Setar foto sempre em jpg
	{

		$extension = explode('.', $file["name"]);
		$extension = end($extension);//detectar a extensão

		switch ($extension) {
			
			case 'jpg':
			case 'jpeg':
				$image = imagecreatefromjpeg($file["tmp_name"]);
				break;

			case 'gif':
				$image = imagecreatefromgif($file["tmp_name"]);
				break;

			case 'png':
				$image = imagecreatefrompng($file["tmp_name"]);
				break;
		}

		$dist = $_SERVER['DOCUMENT_ROOT'] . DIRECTORY_SEPARATOR . "res" . 
			DIRECTORY_SEPARATOR . "site" .
			DIRECTORY_SEPARATOR . "img" . 
			DIRECTORY_SEPARATOR . "products" . 
			DIRECTORY_SEPARATOR . 
			$this->getidproduct() . ".jpg";//destino do arquivo

		imagejpeg($image, $dist);//Salvando a imagem como jpg

		imagedestroy($image);

		$this->checkPhoto();
	}
	

	

 }//Fim da Classe Products

?>