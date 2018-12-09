<?php 

namespace Hcode\DB;

class Sql {
		##### CONFIGURAÇÕES DE ACESSO AO BANCO DE DADOS ####
	const HOSTNAME = "127.0.0.1";//Servidor do Banco
	const USERNAME = "root"; //Nome de Usuário
	const PASSWORD = ""; //Senha do Usuário
	const DBNAME = "db_ecommerce"; //nome do Banco de Dados

	private $conn;

	public function __construct()
	{

		$this->conn = new \PDO(
			"mysql:dbname=".Sql::DBNAME.";host=".Sql::HOSTNAME, 
			Sql::USERNAME,
			Sql::PASSWORD
		);

	}

	private function setParams($statement, $parameters = array())
	{

		foreach ($parameters as $key => $value) {
			
			$this->bindParam($statement, $key, $value);

		}

	}

	private function bindParam($statement, $key, $value)
	{

		$statement->bindParam($key, $value);

	}

	public function query($rawQuery, $params = array())
	{

		$stmt = $this->conn->prepare($rawQuery);

		$this->setParams($stmt, $params);

		$stmt->execute();

	}

	public function select($rawQuery, $params = array()):array
	{

		$stmt = $this->conn->prepare($rawQuery);

		$this->setParams($stmt, $params);

		$stmt->execute();

		/*
		if($stmt->execute() === false){
    		echo "<pre>";
   		 print_r($stmt->errorInfo());
   		 exit;
		}
		*/
		return $stmt->fetchAll(\PDO::FETCH_ASSOC);

	}

}

 ?>