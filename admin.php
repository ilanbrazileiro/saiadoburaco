<?php 

#############################################################
#															#
#	ARQUIVO DE ROTAS PARA AS PEGINAS DA ADMINISTRAÇÃO SITE 	#
#															#
#############################################################


use \Hcode\PageAdmin;//usando a classe PageAdmin para carregar as páginas do lado do Administrador
use \Hcode\Model\User;
use \Hcode\Model\Category;//Classe categoria de produtos
use \Hcode\Model\Products;


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

$app->get('/admin/users', function() {//configurando a rota e dentro vai a página
    
    User::verifyLogin();//Verificar se usuário logado

    $user = User::listAll();

	$page = new PageAdmin();

	$page->setTpl("users", array(
		'users' => $user
	));

});

$app->get('/admin/users/create', function() {//configurando a rota e dentro vai a página
    
    User::verifyLogin();//Verificar se usuário logado

	$page = new PageAdmin();

	$page->setTpl("users-create");

});

$app->get('/admin/users/:iduser/delete', function($iduser) {//configurando a rota e dentro vai a página
    
    User::verifyLogin();//Verificar se usuário logado

    $user = new User();

    $user->get((int)$iduser);//carrega o usuário a ser deletado

    $user->delete();//Deleta o usuário

    //retorna a página de usuários
    header("Location: /admin/users");
    exit;

});

$app->get('/admin/users/:iduser', function($iduser) {//configurando a rota e dentro vai a página
    
    User::verifyLogin();//Verificar se usuário logado

    $user = new User();

    $user->get((int)$iduser);//busca o usuário pelo id

	$page = new PageAdmin();

	$page->setTpl("users-update", array(
		"user"=>$user->getValues()//Busca os valores setados
	));

});

$app->post('/admin/users/create', function() {//configurando a rota e dentro vai a página
    
    User::verifyLogin();//Verificar se usuário logado

    $user = new User();

    $_POST["inadmin"] = (isset($_POST["inadmin"]))?1:0;//Verifica se inadmin vazio, se vazio

    $_POST['despassword'] = password_hash($_POST["despassword"], PASSWORD_DEFAULT, [
     "cost"=>12
]);

    $user->setData($_POST);

    $user->save();

    header("Location: /admin/users");
    exit;
});

$app->post('/admin/users/:iduser', function($iduser) {//configurando a rota e dentro vai a página
    
    User::verifyLogin();//Verificar se usuário logado

    $user = new User;

    $_POST["inadmin"] = (isset($_POST["inadmin"]))?1:0;//Verifica se inadmin vazio, se vazio

    $user->get((int)$iduser);//Carrega os dados do usuário

    $user->setData($_POST); //Seta dos dados enviados através do POST

    $user->update();//Faz a atualização dos dados no banco

    header("Location: /admin/users");//Redireciona para a página que lista os usuários
    exit;

});

$app->get('/admin/forgot', function(){

	 //os parâmetros cancelam o Footer e Header
	$page = new PageAdmin([
		"header" => false,	
		"footer" => false
	]);

	$page->setTpl("forgot");

});

$app->post('/admin/forgot', function(){

	 $user = User::getForgot($_POST["email"]);

	 header("Location: /admin/forgot/sent");
	 exit();

});

$app->get("/admin/forgot/sent", function(){

	  //os parâmetros cancelam o Footer e Header
	$page = new PageAdmin([
		"header" => false,	
		"footer" => false
	]);

	$page->setTpl("forgot-sent");

});

$app->get("/admin/forgot/reset", function(){

	$user = User::validForgotDecrypt($_GET["code"]);
	  //os parâmetros cancelam o Footer e Header
	$page = new PageAdmin([
		"header" => false,	
		"footer" => false
	]);

	$page->setTpl("forgot-reset", array(
		"name"=>$user["desperson"],
		"code"=>$_GET["code"]
	));

});

$app->post("/admin/forgot/reset", function(){

	$forgot = User::validForgotDecrypt($_POST["code"]);

	User::setForgotUsed($forgot["idrecovery"]);

	$user = new User();

	$user->get((int)$forgot["iduser"]);

	$password = password_hash($_POST["password"], PASSWORD_DEFAULT, [
		"cost"=>12
	]);

	$user->setPassword($password);

	$page = new PageAdmin([
		"header" => false,	
		"footer" => false
	]);

	$page->setTpl("forgot-reset-success");


});

$app->get("/admin/categories", function(){

	User::verifyLogin();//Verificar se usuário logado

	$categories = Category::ListAll();

	$page = new PageAdmin();

	$page->setTpl("categories", [
		'categories'=>$categories
	]);

});

$app->get("/admin/categories/create", function(){

	User::verifyLogin();//Verificar se usuário logado

	$page = new PageAdmin();

	$page->setTpl("categories-create");

});

$app->post("/admin/categories/create", function(){

	User::verifyLogin();//Verificar se usuário logado

	$category = new Category();

	$category->setData($_POST);

	$category->save();

	header("Location: /admin/categories");
	exit;

});

$app->get("/admin/categories/:idcategory/delete", function($idcategory){

	User::verifyLogin();//Verificar se usuário logado

	$category = new Category();

	$category->get((int)$idcategory);

	$category->delete();

	header("Location: /admin/categories");
	exit;
	
});

$app->get("/admin/categories/:idcategory", function($idcategory){

	User::verifyLogin();//Verificar se usuário logado

	$category = new Category();

	$category->get((int)$idcategory);

	$page = new PageAdmin();

	$page->setTpl("categories-update",[
		"category"=>$category->getValues()
	]);

});

$app->post("/admin/categories/:idcategory", function($idcategory){

 	User::verifyLogin();//Verificar se usuário logado
	
	$category = new Category();

	$category->get((int)$idcategory);

	$category->setData($_POST);

	$category->save();

	header("Location: /admin/categories");
	exit;

});

$app->get("/admin/products", function(){

	User::verifyLogin();//Verificar se usuário logado

	$products = Products::ListAll();

	$page = new PageAdmin();

	$page->setTpl("products", [
		'products'=>$products
	]);

});

$app->get("/admin/products/create", function(){

	User::verifyLogin();//Verificar se usuário logado

	$page = new PageAdmin();

	$page->setTpl("products-create");

});

$app->post("/admin/products/create", function(){

	User::verifyLogin();//Verificar se usuário logado

	$product = new Products();

	$product->setData($_POST);

	$product->save();

	header("Location: /admin/products");
	exit;

});


$app->get("/admin/products/:idproduct/delete", function($idproduct){

	User::verifyLogin();//Verificar se usuário logado

	$product = new Products();

	$product->get((int)$idproduct);

	$product->delete();

	header("Location: /admin/products");
	exit;
	
});

$app->get("/admin/products/:idproduct", function($idproduct){

	User::verifyLogin();//Verificar se usuário logado

	$product = new products();

	$product->get((int)$idproduct);

	$page = new PageAdmin();

	$page->setTpl("products-update",[
		"product"=>$product->getValues()
	]);

});

$app->post("/admin/products/:idproduct", function($idproduct){

 	User::verifyLogin();//Verificar se usuário logado
	
	$product = new Products();

	$product->get((int)$idproduct);

	$product->setData($_POST);

	$product->save();

	$product->setPhoto($_FILES["file"]);

	header("Location: /admin/products");
	exit;

});

?>