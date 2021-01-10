<?php
	require "funzioni_comuni.php";

	inizio_pagina("Query libera");
	
	echo "<Body>\n";

    barra_navigazione(4);

	# Primo riquadro centrale con il titolo pagina e apertura del riquadro centrale
	echo "    <div class=\"center\">\n    <br>\n    <br>
	<table>
	<tbody>
		<tr class=\"h\">
			<td align=\"center\">Normativa 231</td>
		</tr>
	</tbody>
	</table><br>\n";
	
	# Form di inserimento della query
	echo "    <form  action=\"query_libera.php\" method=\"post\">
	<fieldset style=\"width:934px; margin:0 auto\">
	<legend align=\"center\">Query libera sul database normativa</legend>
	<br><input type=\"text\" name=\"query_libera\" value=\"\" placeholder=\"SELECT * FROM ...\" size=\"100\" maxlength=\"800\"><br>
	<br><br>
	<input type=\"submit\" value=\"Cerca\">&nbsp;&nbsp;&nbsp;<input type=\"reset\" value=\"Cancella\">
	</fieldset>
	</form>\n";
	 
    # Esegue la query se è stata inserita
    if (!empty($_POST['query_libera'])) {
		# Assegna la query sulla base dle testo inserito 
		$query = $_POST['query_libera'];
		# Esegue la query
		$risultato = esegui_query($query);
		
		# Inizia la presentazioen della tabella con i risultati
		echo "    <div class=\"center\">\n    <br>\n    <br>
		<h1>Risultato della query</h1>
		<h4>(<i>$query</i>)</h4>
		<table style=\"width: 1200px\">\n";

		# Verifica che sia andato tutot bene altrimenti restituisce il problema
		if ($risultato[0]=="ERRORE") {
			echo "		<tbody>\n";
			echo "		<tr><td class=\"e\">ERRORE: </td>\n";
            echo "          <td class=\h\"> $risultato[1]</td>\n</tr>\n";
		} else {
			# Determina e stampa le intestazioni di colonna
        	echo "        <thead>\n          <tr>\n";
        	foreach ($risultato[0] as $campo => $valore){
            	echo "            <td class=\"h\">$campo</td>\n";
        	}
			echo "          </tr>\n        </thead>\n";
			echo "        <tbody>\n";
			# Stampa i risultati
			$itera = 0;
        	foreach ($risultato as $riga) {
				$itera++;
            	echo "          <tr>\n";
            	foreach ($riga as $campo) {
					if ($itera%2==0) {
						echo "            <td class=\"v\">$campo</td>\n";
					} else {
						echo "            <td>$campo</td>\n";
					}
            	}
            	echo "          </tr>";
        	}
		}
        echo "    </tbody>\n    </table>\n"; 

    }


	# Chiude la pagina
	echo "</Body>\n</Html>\n"; 

?>