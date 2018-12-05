<?php 

use \Hcode\Model\User;
	
function formatPrice($vlprice)
{
	return number_format($vlprice, 2, ",", ".");
	# number_format($variavel, Qtd de casas Decimais, Separador de decimal, separador de milhar)

}

function checkLogin($inadmin = true)
{
	return User::checkLogin($inadmin);
}

function getUserName()
{
	$user = User::getFromSession();
	
	return $user->getdesperson();
}

function getCartNrQtd()
{
	$cart = Cart::getFromSession();
	$totals = $cart->getProductsTotals();
	return $totals['nrqtd'];
}

function getCartVlSubTotal()
{
	$cart = Cart::getFromSession();
	$totals = $cart->getProductsTotals();
	return formatPrice($totals['vlprice']);
}


 ?>