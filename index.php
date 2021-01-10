<?php
	require "funzioni_comuni.php";

	# Genera intestazioni della pagina
	inizio_pagina("Normativa 231 - Home");
	
	# Inizia la pagina
	echo "<Body>\n";

	# Genera il menu di navigazione
	barra_navigazione(0);
	
	# Primo riquadro centrale con il titolo pagina e apertura del riquadro centrale
	echo "    <div class=\"center\">\n    <br>\n    <br>
	<table>
	<tbody>
		<tr class=\"h\">
			<td align=\"center\">Normativa 231</td>
		</tr>
	</tbody>
	</table>
	<hr>
	<h1>Stato del database</h1>
	<table>
	<tbody>\n";

	# Esegue la query informativa sulla versione del database
	$query = "SELECT version();";
	$risultato = esegui_query($query);

	# Riquadro centrale con la presentazione dei risultati della query test
	if ($risultato[0]=="ERRORE") {
		echo "    <tr><td class=\"e\">ATTENZIONE !!! </td><td class=\"v\">$risultato[1]</td></tr>\n";
	} else {
		# Stampa la riga di tabella con il valore ottenuto
		echo "    <tr><td class=\"e\">Versione del database </td><td class=\"v\">{$risultato[0]['version']}</td></tr>\n";

		# Esegue la query per estrarre i nomi delle tabelle del database
		$query_tabelle = "SELECT table_name FROM information_schema.tables WHERE table_schema = 'public' ORDER BY table_name;";
		$risultato_tabelle = esegui_query($query_tabelle);

		# Controlla che la query sia andata a buon fine
		if ($risultato_tabelle[0]=="ERRORE") {
			echo "    <tr><td class=\"e\">ATTENZIONE !!! </td><td class=\"v\">$risultato_tabelle[1]</td></tr>\n";
		} else {
			foreach ($risultato_tabelle as $tabella) {
				# Conta il numero di elementi di ogni tabella
				$query_tabella = "SELECT count(*) FROM {$tabella['table_name']};";
				$conta_record = esegui_query($query_tabella);
				if ($conta_record[0]=="ERRORE"){
					echo "    <tr><td class=\"e\">ATTENZIONE !!! </td><td class=\"v\">{$conta_record[1]}</td></tr>\n";	
				} else {
					echo "	  <tr><td class=\"e\">Tabella {$tabella['table_name']} </td><td class=\"v\">{$conta_record[0]['count']} elementi </td></tr>\n";
				}
			}
		}
	}

	# Chiude la pagina
	echo "    </tbody>\n    </table>\n</Body>\n</Html>\n";
?>
