<?php 

#################################################
#												#
#	ARQUIVO DE ROTAS PARA AS PEGINAS DO SITE 	#
#												#
#################################################

use \Hcode\Page;//usando a classe Page para carregar as páginas
use \Hcode\Model\User;
use \Hcode\Model\Category;//Classe categoria de produtos

$app->get('/', function() {//configurando a rota e dentro vai a página
    
	$page = new Page();

	$page->setTpl("index");

});

$app->get("/categories/:idcategory", function($idcategory){

 	$category = new Category();

	$category->get((int)$idcategory);

	$page = new Page();

	$page->setTpl("category",[
		'category'=>$category->getValues(),
		'products'=>[]
	]);
});



 ?>