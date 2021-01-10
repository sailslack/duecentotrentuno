<?php
	require "funzioni_comuni.php";

	inizio_pagina("Elenchi");
	
	echo "<Body>\n";

    barra_navigazione(3);

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
	
	echo "    <form  action=\"stampa_lista.php\" method=\"post\">
	<fieldset style=\"width:1000px; margin:0 auto\">
	<legend align=\"center\"> Elenchi Disponibili </legend>\n";
    #    <input type=\"hidden\" name=\"codice_legge\" value=\"1\">";
    echo "<input type=\"submit\" style=\"margin:4px\" name=\"elenco\" value=\"Normativa Presente\">\n";
    echo "	  </fieldset>
	</form>
	\n";

	# Chiude la pagina
	echo "    </tbody>\n    </table>\n</Body>\n</Html>\n"; 

?>