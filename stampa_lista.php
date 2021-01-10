<?php
	require "funzioni_comuni.php";

    $lista_richiesta = $_POST['elenco'];

    inizio_pagina("Elenco $lista_richiesta");
	echo "<Body>\n";
    barra_navigazione(30);

    # Determina la lista da mostrare sulla base dei valori passati
    $tabella = array();
    $itera = 0;
    switch ($lista_richiesta) {
        case 'Normativa Presente':
            $query = "SELECT a.codleg, b.bretip, a.dtaleg, a.numleg, a.titleg, a.modleg, a.codpub, c.numpub, c.codpug, c.numpug, c.dtapub FROM legislazione AS a JOIN tipologia AS b ON a.codtip=b.codtip JOIN pubblicazione as c ON a.codpub=c.codpub ORDER BY a.dtaleg ASC;";
            $risultato = esegui_query($query);
            if ($risultato[0]=="ERRORE") {
                array_push($tabella[$itera]['prima'], $risultato[0]);
                array_push($tabella[$itera]['seconda'], $risultato[1]);
                array_push($tabella[$itera]['terza'], " ");
            } else {
                foreach ($risultato as $legge) {
                    $prima = $legge['bretip'] . " " . $legge['numleg'] . " del " . date('d/m/Y', strtotime($legge['dtaleg']));
                    $seconda = $legge['titleg'];
                    $terza = "In G.U. " . $legge['numpub'];
                    if ($legge['codpug']==5) {
                        $terza .= " S.O.";
                    } 
                    if (!empty($legge['numpug'])){
                        $terza .= " n. " . $legge['numpug'];
                    }
                    $terza .= " del " . date("d/m/Y", strtotime($legge['dtapub']));
                    # Se esiste un provvedimento di modifica lo cita
                    if (!empty($legge['modleg'])) {
                        $query_modifica = "SELECT b.bretip, a.numleg, a.dtaleg FROM legislazione AS a JOIN tipologia AS b ON a.codtip=b.codtip WHERE a.codleg='{$legge['modleg']}';";
                        $modifica = esegui_query($query_modifica);
                        $terza .= "<br> <i>Provvedimento di modifica: ". $modifica[0]['bretip'] . " n. " . $modifica[0]['numleg'] . " del " . date('d/m/Y', strtotime($modifica[0]['dtaleg'])) . " </i>";
                    }

                    # Carica il nuovo array
                    $tabella[$itera] = array($prima, $seconda, $terza);
                    $itera++;
                }
            }    
            break;
    }
            
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
    <h1>$lista_richiesta</h1>
    <table>
    <tbody>\n";
 
     # Riquadro centrale con la presentazione dei risultati della query test

     # Stampa la riga di tabella con il valore ottenuto
    foreach ($tabella as $riga) {
        echo "    <tr>\n";
        echo "        <td class=\"h\"> {$riga[0]} </td>\n";
        echo "        <td class=\"v\"> {$riga[1]} </td>\n";
        echo "        <td class=\"e\"> {$riga[2]} </td>\n";
        echo "    </tr>\n";
    }
 
     # Chiude la pagina
     echo "    </tbody>\n    </table>\n</Body>\n</Html>\n";
 
?>