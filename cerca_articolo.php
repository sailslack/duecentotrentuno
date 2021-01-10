<?php
	require "funzioni_comuni.php";

	inizio_pagina("Ricerca articoli");
	echo "<Body>\n";
    barra_navigazione(20);

    # Determina i parametri di ricerca sulla base dei valori passati
    
    # L'articolo deve sempre essere presente
    if (!empty($_POST['articolo_richiesto'])) {
        $articolo_richiesto = $_POST['articolo_richiesto'];
    }
    # Se è presente il codice_legge lo utilizza
    if (empty($_POST['codice_legge'])) {
        $tipo = $_POST['tipo_legge'];
        $query = "SELECT codleg FROM legislazione WHERE codtip='{$_POST['tipo_legge']}' AND numleg='{$_POST['numero_legge']}' AND extract(year from dtaleg)='{$_POST['anno_legge']}';";
        $cerca_legge = esegui_query($query);
        if ($cerca_legge[0]=="ERRORE"){
            $codice_legge = 0;
        } else {
            $codice_legge = $cerca_legge[0]['codleg'];
        }
    } else {    # altrimenti cerca di trovarlo sulla base dei dati inseriti
        $codice_legge = $_POST['codice_legge'];
    }


	# Primo riquadro centrale con il titolo pagina
	echo "    <div class=\"center\">\n    <br>\n    <br>
	<table>
	<tbody>
        <tr class=\"h\">\n";

    # Verifica che i dati forniti siano sufficienti per la ricerca ed estrae i dati generali della legge
    if ($codice_legge and $articolo_richiesto) {
        # Query con i dati completi della legge
        $query = "SELECT b.nomtip, a.numleg, a.dtaleg, a.titleg, c.numpub, c.codpug, c.numpug, c.dtapub FROM legislazione AS a JOIN tipologia AS b ON a.codtip=b.codtip join pubblicazione as c on a.codpub=c.codpub WHERE a.codleg='{$codice_legge}';";
        $risultato = esegui_query($query);
	    # Riquadro centrale con la presentazione dei risultati della query test
	    if ($risultato[0]=="ERRORE") {
            echo "		<td align=\"center\">Ricerca Articolo</td>\n    </tr>\n</tbody>\n</table>\n<hr>\n";
            echo "    <h1> $risultato[1]</h1>\n        <table>\n        <tbody>\n";
	    } else {
            # Non c'è errore va avanti e stampa l'intestazione della legge
            $legge = $risultato[0]['nomtip'] . " N. " . $risultato[0]['numleg'] . " del " . date('d/m/Y', strtotime($risultato[0]['dtaleg'])) . " <br><i>" . $risultato[0]['titleg'] . "</i><br>\n";
            
            # Dichiara la variabile $aggiornamento per la parte finale = a quella iniziale
            $aggiornamento = $risultato;
            $legge = $legge . " In G.U. del " . date('d/m/Y', strtotime($risultato[0]['dtapub']));
            if ($risultato[0]['codpug']=='5'){
                $legge = $legge . " Supplemento Ordinario";
            }
            if ($risultato[0]['numpug']!='') {
                $legge = $legge . " n. " . $risultato[0]['numpug'];
            }
            echo "		<td align=\"center\"> {$legge} </td>
            </tr>\n    </tbody>\n    </table>\n<hr>\n";

            # Cerca il testo dell'articolo
            $query = "SELECT numart, comart, letart, txtart, codarv FROM articoli WHERE numart='$articolo_richiesto' AND codleg='$codice_legge' AND codarx IS NULL ORDER BY comart ASC, letart ASC;";
            $articolo = esegui_query($query);
            if ($articolo[0]=="ERRORE"){
                echo "    <h1>L'articolo {$articolo_richiesto} </h1>\n    <h3>non è stato trovato nel database.</h3>\n        <table>\n        <tbody>\n";
            } else {
                # Determina l'elenco degli articoli disponibili
                $elenco_articoli = lista_articoli($codice_legge);

                # Determina se esiste la rubrica
                $rubrica = "";
                if ($articolo[0]['numart']==0){
                    $rubrica = $articolo[0]['txtart'];
                    unset($articolo[0]);
                }
                # Prepara i contatori per la navigazione tra gli articoli
                $corrente = array_search($articolo_richiesto, $elenco_articoli);
                $totali = count($elenco_articoli);
                $precedente = $elenco_articoli[$corrente];;
                $prossimo = $elenco_articoli[$corrente];;
                if ($corrente > 0) {
                    $precedente = $elenco_articoli[$corrente-1];
                }
                if ($corrente < $totali-1) {
                    $prossimo = $elenco_articoli[$corrente + 1];
                }

                # Presenta il menu di navigazione
                echo "    <table>\n        <tbody>\n";
                echo "        <tr>";
                echo "            <FORM method=\"POST\" action=\"cerca_articolo.php\">\n";
                echo "            <input type=\"hidden\" name=\"codice_legge\" value=\"$codice_legge\">\n";
                echo "            <input type=\"hidden\" name=\"articolo_richiesto\" value=\"$precedente\">\n";
                echo "            <td class=\"h\" align=\"center\"> 	<input type=\"image\" src=\"Img/prev.png\" width=\"48px\"> </FORM></td>\n";
                echo "            <td align=\"center\" width=\"750\"> <h1>Articolo {$articolo_richiesto}</h1>  <h3>{$rubrica}<h3></td>\n";
                echo "            <FORM method=\"POST\" action=\"cerca_articolo.php\">\n";
                echo "            <input type=\"hidden\" name=\"codice_legge\" value=\"$codice_legge\">\n";
                echo "            <input type=\"hidden\" name=\"articolo_richiesto\" value=\"$prossimo\">\n";
                echo "            <td class=\"h\" align=\"center\"> 	<input type=\"image\" src=\"Img/next.png\" width=\"48px\"> </FORM></td>\n";
                echo "    </tbody>\n        </table>\n    <table>\n        <tbody>\n";

                $num_comma = 1;
                # Ordina i commi e le lettere
                $ordinato  = ordina_risultato($articolo);
                                
                foreach ($ordinato as $riga) {
                    # stampa i dati come si deve
                    $comma = $riga['comart'];
                    $lettera = $riga['letart'];
                    $testo =  $riga['txtart'];
                    echo "    <tr><td class=\"h\" align=\"right\"> {$comma}.</td><td class=\"v\"> {$lettera} </td><td class=\"v\">{$testo}</td></tr>\n";
                }
                # Determina lo stato di aggiornamento dell'articolo, stampando l'ultimo provvedimento inserito.
                foreach ($articolo as $riga) {
                    $flag_aggiornamento = FALSE;
                    $flag_errore = FALSE;
                    $data_aggiornamento = strtotime("2001-01-01");
                    $articolo_aggiornamento = $riga['codarv'];
                    if ($articolo_aggiornamento !='') {
                        $flag_aggiornamento = TRUE;
                        $query = "SELECT b.nomtip, a.numleg, a.dtaleg, a.titleg FROM legislazione AS a JOIN tipologia AS b ON a.codtip=b.codtip WHERE a.codleg=(select codleg from articoli where codart ='{$articolo_aggiornamento}');";
                        $ultimo_aggiornamento = esegui_query($query);
                        if ($ultimo_aggiornamento[0]=="ERRORE") {
                            $flag_errore = TRUE;
                        } else {
                            $data_aggiornamento = strtotime($ultimo_aggiornamento[0]['dtaleg']);
                            $aggiornamento = $ultimo_aggiornamento;
                        }
                    }
                }
                #Presenta il risultato
                echo "    <table>\n    <tbody>\n        <tr class=\"v\">\n";
                if ($flag_errore) {
                    echo "		<td align=\"center\">Ricerca Aggiornamento Fallita</td>\n    </tr>\n";    
                }
                if ($flag_aggiornamento) {
                    $risultato = $aggiornamento;
                }
                # Non c'è errore va avanti e stampa il riferimento alla legge
                $legge = $risultato[0]['nomtip'] . " N. " . $risultato[0]['numleg'] . " del " . date('d/m/Y', strtotime($risultato[0]['dtaleg'])) . " <br><i>" . $risultato[0]['titleg'] . "</i><br>\n";
                echo "		<td align=\"center\"> Ultimo provvedimento inserito: <br>&nbsp;<br> {$legge} </td></tr>\n";
                echo "    </tbody>\n    </table>\n";
            }
        }
    } else {
        echo "    <h1>Ricerca Articolo </h1>\n        <table>\n        <tbody>\n";
        echo "    <tr><td class=\"e\">ATTENZIONE !!! </td><td class=\"v\">Non sono stati immessi tutti i parametri corretti. Riprovare</td></tr>\n";
    }

	# Chiude la pagina
    echo "    </tbody>\n    </table>\n</Body>\n</Html>\n";
?>