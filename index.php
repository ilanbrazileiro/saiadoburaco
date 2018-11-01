<?php 
session_start();
require_once("vendor/autoload.php");//incluindo o autoload

use \Slim\Slim;//usando o Slim
use \Hcode\Page;//usando a classe Page para carregar as páginas
use \Hcode\PageAdmin;//usando a classe PageAdmin para carregar as páginas do lado do Administrador
use \Hcode\Model\User;

$app = new Slim();

$app->config('debug', true);//Habilitando visualização de erros

$app->get('/', function() {//configurando a rota e dentro vai a página
    
	$page = new Page();

	$page->setTpl("index");

});

$app->get('/admin', function() {//configurando a rota e dentro vai a página

	User::verifyLogin();//Verificar se usuário logado
    
	$page = new PageAdmin();

	$page->setTpl("index");

});

$app->get('/admin/login', function() {//configurando a rota e dentro vai a página
    
    //os parâmetros cancelam o Footer e Header
	$page = new PageAdmin([
		"header" => false,	
		"footer" => false
	]);

	$page->setTpl("login");

});

$app->post('/admin/login', function() {//configurando a rota de login do admin
	
	User::login($_POST["login"], $_POST["password"]);

	header("Location: /admin");
	exit;


});

$app->get('/admin/logout', function(){

	User::logout();

	header("Location: /admin/login");
	exit;
});


$app->run();//rodando a aplicação

 ?>