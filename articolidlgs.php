<?php
	require "funzioni_comuni.php";

	inizio_pagina("Ricerca articoli");
	
	echo "<Body>\n";

    barra_navigazione(1);

    $elenco_articoli = lista_articoli('1');

	# Primo riquadro centrale con il titolo pagina e apertura del riquadro centrale
	echo "    <div class=\"center\">\n    <br>\n    <br>
	<table>
	<tbody>
		<tr class=\"h\">
			<td align=\"center\">Normativa 231</td>
		</tr>
	</tbody>
	</table>
	<hr>\n";
	

	echo "    <form  action=\"cerca_articolo.php\" method=\"post\">
	  <fieldset style=\"width:1000px; margin:0 auto\">
	    <legend align=\"center\">Articoli disponibili del D.lgs. 231/2001</legend>
          <input type=\"hidden\" name=\"codice_legge\" value=\"1\">";
    foreach ($elenco_articoli as $articolo) {
        echo "          <input type=\"submit\" style=\"margin:4px\" name=\"articolo_richiesto\" value=\"$articolo\">\n";
    }
    echo "	    </fieldset>\n	  </form>\n";

	# Chiude la pagina
	echo "</Body>\n</Html>\n";

?>