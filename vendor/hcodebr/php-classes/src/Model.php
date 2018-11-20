<?php 
	
namespace Hcode;

class Model {

	private $values = [];

	public function __call($name, $args)//Diferencia o Metodo chamado entre o GET e o POST
	{

		$method = substr($name, 0, 3);
		$fieldName = substr($name, 3,strlen($name));

		switch ($method)
		{
			case 'get':
				return (isset($this->values[$fieldName])) ? $this->values[$fieldName] :NULL;
				break;
			
			default:
				$this->values[$fieldName] = $args[0];
				break;
		}
	}

	public function setData($data = array())//Seta os valores com as chaves correspondentes
	{
		foreach ($data as $key => $value) {
			$this->{"set".$key}($value);
		}

	}

	public function getValues()//Busca os valores setados em atributos
	{
		return $this->values;
	}
}
	
?>