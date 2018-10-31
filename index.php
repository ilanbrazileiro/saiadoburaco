<?php 

require_once("vendor/autoload.php");//incluindo o autoload

use \Slim\Slim;//usando o Slim
use \Hcode\Page;//usando a classe Page para carregar as páginas
use \Hcode\PageAdmin;//usando a classe PageAdmin para carregar as páginas do lado do Administrador

$app = new Slim();

$app->config('debug', true);//Habilitando visualização de erros

$app->get('/', function() {//configurando a rota e dentro vai a página
    
	$page = new Page();

	$page->setTpl("index");

});

$app->get('/admin', function() {//configurando a rota e dentro vai a página
    
	$page = new PageAdmin();

	$page->setTpl("index");

});

$app->run();//rodando a aplicação

 ?>