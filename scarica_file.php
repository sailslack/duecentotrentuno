<?php
	require "funzioni_comuni.php";
	
	# Nome del file da scaricare
    $nome_file_csv = $_POST['nome_file'];
    $pagina = $_SERVER['HTTP_REFERER'];

    header('Content-Type: application/octet-stream');
	header("Content-Transfer-Encoding: Binary"); 
	header("Content-disposition: attachment; filename=\"" . basename($nome_file_csv) . "\""); 
	readfile($nome_file_csv); 

    header("Location: $pagina");
    die();

?>