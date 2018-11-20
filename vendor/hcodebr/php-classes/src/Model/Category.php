<?php 

namespace Hcode\Model;

use \Hcode\DB\Sql;
use \Hcode\Model;
use \Hcode\Mailer;

class Category extends Model{

	

	public static function listAll()#### Listar todas as categorias
	{
		$sql = new Sql();

		return $results = $sql->select("SELECT * FROM tb_categories ORDER BY descategory");

	}

	public function save()##### Salva os registros no BD
	{
		$sql = new Sql();

		$results = $sql->select("CALL sp_categories_save(:idcategory, :descategory)", array(
			":idcategory"=>$this->getidcategory(),
			":descategory"=>$this->getdescategory(),
			
		));

		$this->setData($results[0]);
	}

	public function get($idcategory)##### Retorna a categoria pelo id
	{
		$sql = new Sql();

		$results = $sql->select("SELECT * FROM tb_categories WHERE idcategory = :idcategory",array(
			':idcategory' => $idcategory 
		));

		$this->setData($results[0]);//Seta os valores em suas chaves
	}

	public function delete()
	{
		$sql = new Sql();

		$sql->query("DELETE FROM tb_categories WHERE idcategory = :idcategory", array(
			":idcategory"=>$this->getidcategory()
		));
	}


 }//Fim da Classe Category

?>