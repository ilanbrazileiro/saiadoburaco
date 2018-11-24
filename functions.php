<?php 
	
function formatPrice(float $vlprice)
{
	return number_format($vlprice, 2, ",", ".");
	# number_format($variavel, Qtd de casas Decimais, Separador de decimal, separador de milhar)

}


 ?>