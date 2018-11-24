<?php 

#################################################
#												#
#	ARQUIVO DE ROTAS PARA AS PEGINAS DO SITE 	#
#												#
#################################################

use \Hcode\Page;//usando a classe Page para carregar as páginas
use \Hcode\Model\User;
use \Hcode\Model\Category;//Classe categoria de produtos
use \Hcode\Model\Products;

$app->get('/', function() {//configurando a rota e dentro vai a página
    $products = Products::listAll();

	$page = new Page();

	$page->setTpl("index", [
		'products'=>Products::checkList($products)
	]);

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

$app->get("/teste", function(){//Caminho para testes do sistema (by Ilan)

 	$a = [
 		'a'=>1,
 		'b'=>2,
 		'c'=>3
 	];
 	$i = 3;
 	foreach ($a as &$row) {
 		$row = $i--;
 	}

 	var_dump($a);

});



 ?>