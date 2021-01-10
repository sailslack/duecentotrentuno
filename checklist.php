<?php
	require "funzioni_comuni.php";

	# Nome del file di output, poi verrà preso dal file config.ini
	$nome_file_csv = "/tmp/reati_231.csv";

	# Prepara la pagina
	inizio_pagina("Checklist");
	echo "<Body>\n";
    barra_navigazione(5);

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

	# Lista degli articoli 231 con i reati presupposto 
	$articoli_231 = ["24", "24-bis", "24-ter", "25", "25-bis", "25-bis.1", "25-ter", "25-quater", "25-quater.1", 
					"25-quinquies", "25-sexies", "25-septies", "25-octies", "25-novies", "25-decies", "25-undecies", 
					"25-duodecies", "25-terdecies", "25-quaterdecies", "25-quinquiesdecies", "25-sexiesdecies"];

	# Inizia a presentare il riquadro di selezione degli articoli
	echo "    <form  action=\"checklist.php\" method=\"post\">
	<fieldset style=\"width:1000px; margin:0 auto\">
	<legend align=\"center\"> Scelta Articoli da Inserire nella Checklist </legend>\n";
	
	# Predispone la tabella interna e determian i parametri di visualizzazione del modello
	echo "    <table>\n    <tbody>\n      <tr>\n";
	
	# Valori di deafult  
	$checked = "";
	$bottone = "    <button name=\"lista\" type=\"submit\" value=\"tutti\">Seleziona Tutti</button>";
	$elementi = 0;
	if (!empty($_POST['lista'])) {
		if ($_POST['lista']=="nessuno") {
			# Bottone "deseleziona tutti"
			$checked = "";
			$bottone = "    <button name=\"lista\" type=\"submit\" value=\"tutti\">Seleziona Tutti</button>";
		} elseif ($_POST['lista']=="tutti") {
			# Bottone "seleziona tutti"
			$checked = "checked";
			$bottone = "    <button name=\"lista\" type=\"submit\" value=\"nessuno\">Deseleziona Tutti</button>";
		} else {
			# Bottone "Esegui"
			# Elimino l'elemento dell'array $_POST x le elaborazioni successive
			unset ($_POST['lista']);
			# Poi conto gli elementi presenti
			$elementi = count($_POST);
		}
	}

	# Produce l'output della parte alta della schermata
	$itera = -1;
	foreach ($articoli_231 as $articolo) {
		$itera++;
		echo "        <td style=\"border:0 solid #fff;\"><input type=\"checkbox\" $checked id=\"$itera\" name=\"$itera\" value=\"$articolo\">\n";
		echo "        <label for=\"$itera\"> $articolo </label></td>\n";
		if (($itera+1)%4 == 0) {
			echo "      </tr>\n      <tr>\n";
		}
	}
	if (($itera+1)%4 != 0) {
		echo "        <td style=\"border:0;\" colspan=\"" . (4-($itera+1)%4) . "\"></td>\n";
	}
	echo "      </tr>\n    </tbody>\n    </table>\n";
	echo $bottone;
	echo "    <button name=\"lista\" type=\"submit\" value=\"esegui\">Esegui</button>";
    echo "	  </fieldset>\n 	</form>\n";

	# Se è stato premuto il tasto "Esegui" con qualche articolo flaggato allora crea la checklist
	if ($elementi) {
		# Apre il file csv
		$file_csv = fopen($nome_file_csv, 'w') or die ("Impossibile aprire il file!!!!!");
		
		# Riga di intestazione del file CSV
		$riga_csv = ["Numero", "Articolo D.lgs. 231", "Articolo reato", "Legge", "Testo"];
		fputcsv($file_csv, $riga_csv);

		# Prepara il contatore delle righe del file csv
		$itera = 1;
		# Eseguo per tutti gli articoli 231 selezionati
		foreach ($_POST as $articolo) {
			# Crea ed esegue la query per l'intestazione dell'articolo dlgs 231
			$query = "SELECT txtart FROM articoli WHERE numart='$articolo' AND comart='0' AND codleg='1' AND codarx IS NULL;"; 
			$rubrica_articolo = esegui_query($query);

			# Intestazione articolo 231
			$articolo_231 = "Articolo {$articolo} - {$rubrica_articolo[0]['txtart']}";

			# Crea ed esegue la query per estrarre i reati presupposto di quell'articolo
			$query = "SELECT DISTINCT ON (r.artleg, r.comleg, r.letleg, r.codleg) r.artdec, r.comdec, r.letdec, r.artleg, r.comleg, r.letleg, r.codleg, l.dtaleg FROM rpresupposto AS r JOIN legislazione AS l ON r.codleg=l.codleg WHERE artdec='$articolo' and r.recatt='t' ORDER BY r.codleg;";
			$elenco_reati = esegui_query($query);

			# Creo una query per estrarre gli articoli di reato elencati nel risultato della query precedente
			foreach ($elenco_reati as $reato_p) {
				#Prepara la query per estrarre il testo dell'articolo del reato presupposto
				$query = "SELECT a.txtart, a.numart, a.comart, a.letart, t.bretip, n.numleg, n.dtaleg FROM articoli AS a JOIN legislazione AS n ON a.codleg=n.codleg JOIN tipologia as t ON n.codtip=t.codtip WHERE a.codleg='{$reato_p['codleg']}' AND a.numart='{$reato_p['artleg']}' AND a.codarx IS NULL;";
				
				# Estrae il testo dei vari reati presupposto
				$reato_presupposto = esegui_query($query);

				# Compone il nome della legge sulla base del primo risultato (è uguale per tutti gli altri)
				switch ($reato_p['codleg']) {
					case '6':
						$legge = "c.p.";
						break;
					case '54':
						$legge = "c.p.p.";
						break;
					case '69':
						$legge = "c.c.";
						break;
					default:
					$legge = $reato_presupposto[0]['bretip'] . " " . $reato_presupposto[0]['numleg'] . " del " . date("d/m/Y", strtotime($reato_presupposto[0]['dtaleg']));
				}
				
				# Compone il numero di articolo di reato
				$articolo_reato = $reato_p['artleg'];

				# Ordina l'articolo x comma e lettera
				$reato_presupposto = ordina_risultato($reato_presupposto);
				
				# Compone il testo dell'articolo
				if (array_key_exists('0', $reato_presupposto)) {
					$testo = $reato_presupposto[0]['txtart'] . "\n";
					unset($reato_presupposto[0]);	
				} else {
					$testo = "";
				}

				foreach ($reato_presupposto as $riga) {
					if (empty($riga['letart'])) {
						$testo .= $riga['comart'] . ". " . $riga['txtart'] . "\n";
					} else {
						$testo .= "          " . $riga['letart'] . ") " . $riga['txtart'] . "\n";
					}
					
				}
				
				# Produce la riga per il file csv
				$riga_csv = [$itera, $articolo_231, $articolo_reato, $legge, $testo];
				fputcsv($file_csv, $riga_csv);
				$itera++;

			}
		}
		fclose($file_csv);

		# Presenta la possibilità di scaricare il file
		echo "    <form  action=\"scarica_file.php\" method=\"post\">\n";
    	echo "        <input type=\"hidden\" name=\"nome_file\" value=\"$nome_file_csv\">";
    	echo "        <button name=\"scarica\" type=\"submit\" value=\"scarica\">Scarica il file</button>";
    	echo "    </form>\n";
	}
	
	# Chiude la pagina
	echo "</Body>\n</Html>\n";

?>