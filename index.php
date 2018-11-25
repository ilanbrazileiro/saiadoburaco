<?php 

#################################################
#												#
#	ARQUIVO COM AS CONFIGURAÇÕES INICIAIS!	 	#
#												#
#################################################

session_start();
require_once("vendor/autoload.php");//incluindo o autoload

use \Slim\Slim;//usando o Slim


$app = new Slim();

$app->config('debug', true);//Habilitando visualização de erros

require_once("functions.php");
require_once("site.php");
require_once("admin.php");




$app->run();//rodando a aplicação

 ?>