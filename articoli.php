<?php
	require "funzioni_comuni.php";

	inizio_pagina("Ricerca articoli");
	
	echo "<Body>\n";

    barra_navigazione(2);

	# Estrae l'elenco delle leggi
    $query = "SELECT a.codleg, b.nomtip, a.dtaleg, a.numleg FROM legislazione AS a JOIN tipologia AS b ON a.codtip=b.codtip ORDER BY a.dtaleg ASC;";
    $elenco_normativa = esegui_query($query);
	
	# Estrae l'elenco delle tipologie di normativa
	$query = "SELECT DISTINCT a.codtip, b.nomtip FROM legislazione AS a JOIN tipologia AS b ON a.codtip=b.codtip ORDER BY a.codtip ASC;";
	$elenco_tipologie = esegui_query($query);

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
	<fieldset style=\"width:900px; margin:0 auto\">
	<legend align=\"center\">Ricerca Articoli </legend>
	Tipologia <select name=\"tipo_legge\">\n
		<option value=\"\" selected> &nbsp; </option>\n";
	foreach ($elenco_tipologie as $tipologia) {
		echo"		<option value=\"{$tipologia['codtip']}\"> {$tipologia['nomtip']} </option>";

	}
	echo "    </select>
	Numero: <input type=\"text\" name=\"numero_legge\" value=\"\" placeholder=\"231\" size=\"10\" maxlength=\"10\"> &nbsp;
	Anno: <input type=\"text\" name=\"anno_legge\" value=\"\" placeholder=\"2001\" size=\"4\" maxlength=\"4\">
	<br><br>

	Oppure seleziona la norma dall'elenco &nbsp; 
	<select name=\"codice_legge\">";
	echo "    <option value=\"\" selected> &nbsp; </option>\n";
	foreach ($elenco_normativa as $legge) {
		echo "    <option value=\"{$legge['codleg']}\"> " . $legge['nomtip'] . " " . $legge['numleg'] . "/" . date('Y', strtotime($legge['dtaleg'])) . "</option>\n";
	}
	echo "    </select><br><br>

	Articolo: <input type=\"text\" name=\"articolo_richiesto\" value=\"\" placeholder=\"1\" size=\"15\" maxlength=\"20\"><br>
	<br><br>
	<input type=\"submit\" value=\"Cerca\">&nbsp;&nbsp;&nbsp;<input type=\"reset\" value=\"Cancella\">
	</fieldset>
	</form>
	\n";

	# Chiude la pagina
	echo "    </tbody>\n    </table>\n</Body>\n</Html>\n"; 

?>