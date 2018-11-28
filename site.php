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
use \Hcode\Model\Cart;

$app->get('/', function() {//configurando a rota e dentro vai a página
    $products = Products::listAll();

	$page = new Page();

	$page->setTpl("index", [
		'products'=>Products::checkList($products)
	]);

});

$app->get("/categories/:idcategory", function($idcategory){

 	$page = (isset($_GET['page'])) ? (int)$_GET['page'] : 1;

 	$category = new Category();

	$category->get((int)$idcategory);

	$pagination = $category->getProductPage($page);

	$pages = [];

	for ($i=1; $i <=$pagination['pages'] ; $i++) { 
		array_push($pages, [
			'link'=>'/categories/'.$category->getidcategory().'?page='.$i,
			'page'=>$i
		]);
	}

	$page = new Page();

	$page->setTpl("category",[
		'category'=>$category->getValues(),
		'products'=>$pagination['data'],
		'pages'=>$pages
	]);
});

$app->get("/products/:desurl", function($desurl){//Caminho para testes do sistema (by Ilan)

 	$product = new Products();

 	$product->getFromURL($desurl);

 	$page = new Page();

 	$page->setTpl("product-detail", [
 		'product'=>$product->getValues(),
 		'categories'=>$product->getCategories()
 	]);

});


$app->get("/cart", function(){//Caminho para testes do sistema (by Ilan)

 	$cart = Cart::getFromSession();

 	$page = new Page();

 	$page->setTpl("cart");

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