<?php 

namespace Hcode;

use Rain\Tpl;

class Page {

	private $tpl;
	private $options = [];
	private $defaults = [
		"header"=>true,
		"footer"=>true,
		"data"=>[]
	];

	#### Método contrutor, define as configurações e constroi o 'Header'
	public function __construct($opts = array(), $tpl_dir = "/views/")
	{

		$this->options = array_merge($this->defaults, $opts);

		$config = array(//configurações do RainTpl
		    "base_url"      => null,
		    "tpl_dir"       => $_SERVER['DOCUMENT_ROOT'].$tpl_dir, //Caminho dos templates do sistema
		    "cache_dir"     => $_SERVER['DOCUMENT_ROOT']."/views-cache/",//Caminhos dos arquivos em cache do sistema
		    "debug"         => false//
		);

		Tpl::configure( $config );

		$this->tpl = new Tpl();

		if ($this->options['data']) $this->setData($this->options['data']);

		if ($this->options['header'] === true) $this->tpl->draw("header", false);

	}
	#### Constroi o 'footer' (Não está recebendo dados nenhum) ####
	public function __destruct()
	{

		if ($this->options['footer'] === true) $this->tpl->draw("footer", false);

	}

	#### Método para setar os dados antes do merge ####
	private function setData($data = array())
	{

		foreach($data as $key => $val)
		{

			$this->tpl->assign($key, $val);

		}

	}

	#### Constroi o conteudo ####
	public function setTpl($tplname, $data = array(), $returnHTML = false)
	{

		$this->setData($data);

		return $this->tpl->draw($tplname, $returnHTML);

	}

}

 ?>