<?php
/*  funzioni_comuni.php -   File che contiene tutte le funzioni usate da
                            tutti i programmi.

*/

    # Elabora il file .ini
    $vettore_ini = parse_ini_file("config.ini");

    # Assegna le variabili 
    $host = "host={$vettore_ini['host']}";
	$porta = "port={$vettore_ini['porta']}";
	$nomedb = "dbname={$vettore_ini['dbname']}";
    $credenziali = "user={$vettore_ini['user']} password={$vettore_ini['passwd']}";
    $base_dir = $vettore_ini['default_dir'];



/* lista_articoli -     genera un array con l'elenco ordinato degli articoli di una legge

        parametri       $codice_legge => il codice (codleg) della legge
        return          un array con l'elenco rodinato degli articoli 
*/
function lista_articoli ($codice_legge) {
    $articoli = [];

    # Estrae l'elenco degli articoli attivi
    $query = "SELECT DISTINCT numart FROM articoli WHERE codleg = '{$codice_legge}' AND codarx IS NULL;";
    $elenco_grezzo = esegui_query($query);
    
    # Assegna l'ordine giusto agli articoli
    foreach ($elenco_grezzo as $elemento) {
        $ordine = assegna_valore($elemento['numart'], 'A');
        $lista_ordinata[$ordine] = $elemento; 
    }
    
    # Mette in ordine la lista generata
    ksort($lista_ordinata);
    
    # Prepara l'array di ritorno con l'elenco ordinato
    foreach ($lista_ordinata as $elemento) {
        array_push($articoli, $elemento['numart']);
    }
    return $articoli;
}


/* ordina_risultato -   ordina il risultato della ricerca di un articolo secondo la dizione legale

        parametri:      $lista => l'array associativo multidimensionale dei risultati della query
        return:         un array associativo con i dati ordinati 
*/  
function ordina_risultato ($lista) {
    $lista_ordinata =[];
    # Trasforma i valori -bis, -tris, ecc. eventualmente presenti in numeri
    foreach ($lista as $riga) {
        $comma = $riga['comart'];
        $lettera = $riga['letart'];

        # Assegna il valore base
        $valore_ordinamento = 0;
        # Aggiunge il valore del comma
        $valore_ordinamento += assegna_valore($comma, 'C');
        # Aggiunge il valore della lettera
        $valore_ordinamento += assegna_valore($lettera, 'L');
        # Aggiungo all'arry il valore con il campo 'ordine'
        $lista_ordinata[$valore_ordinamento] = $riga;
    }
    ksort($lista_ordinata);
    return $lista_ordinata;
}


/* assegna_valore - assegna un valore numerico per lìordinamento di commi e lettere

        parametri:      $elemento => la stringa da convertire
                        $tipo => il tipo di elemento che viene passato: L = lettera, C = comma, A = articolo
        return          il valore numerico da assegnare
*/
function assegna_valore($elemento, $tipo) {
    # Definisce i valori per l'ordinamento alternativo
    $array_ordinamento = [
        "bis" => 20,
        "bis.1" => 21,
        "ter" => 30, 
        "ter.1" => 30, 
        "quater" => 40,
        "quater.1" => 41, 
        "quinquies" => 50,
        "quinquies.1" => 51,
        "quinquies.2" => 52,
        "sexies" => 60, 
        "septies" => 70, 
        "octies" => 80, 
        "novies" => 90, 
        "decies" => 100, 
        "undecies" => 110, 
        "duodecies" => 120, 
        "terdecies" => 130, 
        "quaterdecies" => 140, 
        "quinquiesdecies" => 150, 
        "sexiesdecies" => 160
    ];
    
    # Imposta il valore base
    $valore = 0;
    # Spezza la stringa in due parti se c'è la congiunzione
    if (strpos($elemento, "-")) {
        $pezzi = explode("-", $elemento, 2);
        $valore += $array_ordinamento[$pezzi[1]];
    } else {
        $pezzi[0] = $elemento;
    }
    # Stabilisce il valore sulla base del tipo
    switch ($tipo) {
        case 'L':     # Lettera
            if (ord($pezzi[0]) < 90){
                # Numero
                $valore = $valore + intval($pezzi[0]) *100;
            } else {
                # Lettera
                $valore = $valore + (ord($pezzi[0]) - 96) * 100;
            }
        break;
        case 'C':     # Comma
            $valore = $valore * 10000;
            $valore = $valore + $pezzi[0] * 10000000;
        break;
        case 'A':     # Articolo
            $valore = $valore * 1000000;
            $valore = $valore + $pezzi[0] * 100000000000;
        break;      
    }
    return $valore;
}


/* esegui_query -   esegue una query e restituisce il risultato o l'errore della query

        parametri:  $query => il testo della query SQL
        return:     un array associativo con i dati estratti dal db 
*/  
function esegui_query ($query) {
    # Prende le variabili globali
    global $host;
    global $porta;
    global $nomedb;
    global $credenziali;

    # Esegue la connessione al database
    $connessione = pg_connect("$host $porta $nomedb $credenziali");

    # Verifica che la connessione sia andata a buon fine, altrimenti restituisce un errore
    if (!$connessione) {
        $risultato_query = ["ERRORE", "Non è stato possibile stabilire una connessione con il database <b>$nomedb</b>"];
        return $risultato_query;
    } else {
        # Esegue la query 
        $esito_query = pg_query($connessione, $query); #or die ("Impossibile eseguire query :$query\n");
    
        if (!$esito_query) {
            $risultato_query = ["ERRORE", "Non è stato possibile eseguire la query <b>$query</b>"];
        } else {
            if (pg_affected_rows($esito_query) < 1) {
                $risultato_query = ["ERRORE", "La query <b>$query</b> non ha prodotto risultati"];
            } else {
                # Recupera i valori della query (unico array per il tipo di query)
                $risultato_query = pg_fetch_all($esito_query);
            }
        }
    }
    # Restituisce l'array dei risultati
    return $risultato_query;
} 
        



/*  inizio_pagina   -   produce l'inizio della pagina HTML

        parametri:      $titolo_pagina => titolo della pagina HTML, default senza titolo
                        $file_css => file delel definizioni css da usare, default standard.css
                        $sorgente_script => eventuale script js, defualt assente
*/
function inizio_pagina ($titolo_pagina="", $file_css="standard.css", $sorgente_script=""){
    echo "<!DOCTYPE html>\n<html xmlns=\"http://www.w3.org/1999/xhtml\" lang=\"it\">\n<Head>
	<Meta http-equiv=\"Content-Type\" content=\"text/html; charset=utf-8\"> 
    <Title>$titolo_pagina</Title>
    <link rel=\"stylesheet\" href=\"$file_css\">
    <script>$sorgente_script</script>\n</Head>\n";
}



/*  barra_navigazione - produce la barra di navigazione delle pagine

        parametri:      $tipo_barra => definisce il tipo di barra da mostrare, default barra iniziale
*/
function barra_navigazione(int $tipo_barra=0){
    echo "    <ul>\n";
    switch ($tipo_barra) {
        case 0:     # Menu principale
            echo "      <li> &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; </li>\n";
            echo "      <li><a href=\"articolidlgs.php\">Articoli 231</a></li>\n";
            echo "      <li><a href=\"articoli.php\">Altri Articoli</a></li>\n";
            echo "      <li><a href=\"elenchi.php\">Elenchi</a></li>\n";
            echo "      <li><a href=\"query_libera.php\">Query</a></li>\n";
            echo "      <li><a href=\"checklist.php\">Checklist</a></li>\n";
        break;
        case 1:     # Menu Articoli 231
            echo "      <li><a href=\"index.php\">Home</a></li>\n";
            echo "      <li> &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; </li>\n";
            echo "      <li><a href=\"articoli.php\">Altri Articoli</a></li>\n";
            echo "      <li><a href=\"elenchi.php\">Elenchi</a></li>\n";
            echo "      <li><a href=\"query_libera.php\">Query</a></li>\n";
            echo "      <li><a href=\"checklist.php\">Checklist</a></li>\n";
        break;
        case 2:     # Menu Altri Articoli
            echo "      <li><a href=\"index.php\">Home</a></li>\n";
            echo "      <li><a href=\"articolidlgs.php\">Articoli 231</a></li>\n";
            echo "      <li> &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;</li>\n";
            echo "      <li><a href=\"elenchi.php\">Elenchi</a></li>\n";
            echo "      <li><a href=\"query_libera.php\">Query</a></li>\n";
            echo "      <li><a href=\"checklist.php\">Checklist</a></li>\n";
        break;
        case 3:     # Menu Elenchi
            echo "      <li><a href=\"index.php\">Home</a></li>\n";
            echo "      <li><a href=\"articolidlgs.php\">Articoli 231</a></li>\n";
            echo "      <li><a href=\"articoli.php\">Altri Articoli</a></li>\n";
            echo "      <li> &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;</li>\n";
            echo "      <li><a href=\"query_libera.php\">Query</a></li>\n";
            echo "      <li><a href=\"checklist.php\">Checklist</a></li>\n";
        break;
        case 4:     # Menu Query
            echo "      <li><a href=\"index.php\">Home</a></li>\n";
            echo "      <li><a href=\"articolidlgs.php\">Articoli 231</a></li>\n";
            echo "      <li><a href=\"articoli.php\">Altri Articoli</a></li>\n";
            echo "      <li><a href=\"elenchi.php\">Elenchi</a></li>\n";
            echo "      <li> &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; </li>\n";
            echo "      <li><a href=\"checklist.php\">Checklist</a></li>\n";
        break;
        case 5:     # Menu Checklist
            echo "      <li><a href=\"index.php\">Home</a></li>\n";
            echo "      <li><a href=\"articolidlgs.php\">Articoli 231</a></li>\n";
            echo "      <li><a href=\"articoli.php\">Altri Articoli</a></li>\n";
            echo "      <li><a href=\"elenchi.php\">Elenchi</a></li>\n";
            echo "      <li><a href=\"query_libera.php\">Query</a></li>\n";
            echo "      <li>&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;</li>\n";
        break;
        case 20:     # Menu Navigazione Articoli
            echo "      <li><a href=\"index.php\">Home</a></li>\n";
            echo "      <li><a href=\"articolidlgs.php\">Articoli 231</a></li>\n";
            echo "      <li><a href=\"articoli.php\">Altri Articoli</a></li>\n";
            echo "      <li><a href=\"elenchi.php\">Elenchi</a></li>\n";
            echo "      <li><a href=\"query_libera.php\">Query</a></li>\n";
            echo "      <li><a href=\"checklist.php\">Checklist</a></li>\n";
        break;
        case 30:     # Menu Navigazione Elenchi
            echo "      <li><a href=\"index.php\">Home</a></li>\n";
            echo "      <li><a href=\"articolidlgs.php\">Articoli 231</a></li>\n";
            echo "      <li><a href=\"articoli.php\">Altri Articoli</a></li>\n";
            echo "      <li><a href=\"elenchi.php\">Elenchi</a></li>\n";
            echo "      <li><a href=\"query_libera.php\">Query</a></li>\n";
            echo "      <li><a href=\"checklist.php\">Checklist</a></li>\n";
        break;
        case 99:     # Menu Segnaposto 
            echo "      <li><a href=\"index.php\">Home</a></li>\n";
            echo "      <li>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</li>\n";
            echo "      <li><a href='#' onclick='diminuisci()'>Precedente</a></li>\n";
            echo "      <li><a href='#' onclick='incrementa()'>Successivo</a></li>\n";
            echo "      <li>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</li>\n";
            echo "      <li><a href='efesto.php'>Inserisci</a></li>\n";
        break;
    }
    echo "    </ul>\n";
}

?>