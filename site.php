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
use \Hcode\Model\Address;

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


$app->get("/cart", function(){

 	$cart = Cart::getFromSession();

 	$page = new Page();

 	$page->setTpl("cart",[
 		'cart'=>$cart->getValues(),
 		'products'=>$cart->getProducts(),
 		'error'=>Cart::getMsgError()
 	]);

});

$app->get("/cart/:idproduct/add", function($idproduct){

 	$product = new Products();

 	$product->get((int)$idproduct);

 	$cart = Cart::getFromSession();

 	$qtd = (isset($_GET['qtd'])) ? (int)$_GET['qtd'] : 1;

 	for ($i=0; $i < $qtd; $i++) { 

 		$cart->addProduct($product);

 	}

 	header("Location: /cart");
 	exit;

});

$app->get("/cart/:idproduct/minus", function($idproduct){

 	$product = new Products();

 	$product->get((int)$idproduct);

 	$cart = Cart::getFromSession();

 	$cart->removeProduct($product);

 	header("Location: /cart");
 	exit;

});

$app->get("/cart/:idproduct/remove", function($idproduct){

 	$product = new Products();

 	$product->get((int)$idproduct);

 	$cart = Cart::getFromSession();

 	$cart->removeProduct($product, true);

 	header("Location: /cart");
 	exit;

});

$app->post("/cart/freight", function(){

	$cart = Cart::getFromSession();//Pegando o carrinho da sessão

	$cart->setFreight($_POST['zipcode']);//Passando o CEP para a definir frete

	header("Location: /cart");
 	exit;
});

$app->get("/checkout", function(){

 	User::verifyLogin(false);

 	$cart = Cart::getFromSession();

 	$address = new Address();

 	$page = new Page();

 	$page->setTpl("checkout",[
 		'cart'=>$cart->getValues(),
 		'address'=>$address->getValues() 		
 	]);

});

$app->get("/login", function(){

 	$page = new Page();

 	$page->setTpl("login",[
 		'error'=>User::getError()
 	]);

});

$app->post("/login", function(){

 	try {
 		User::login($_POST['login'],$_POST['password']);

 	} catch(Exception $e){
 		User::setError($e->getMessage());
 	}
 	header("Location: /checkout");
 	exit;

});

$app->get("/logout", function(){

 	User::logout();

 	header("Location: /login");
 	exit;

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