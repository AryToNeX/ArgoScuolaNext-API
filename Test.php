#!/usr/bin/env php

<?php

/**
 * Copyright 2018 Anthony Calabretta
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 * http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

/**
 * Test delle API Argo (coprono tutte le chiamate)
 */

date_default_timezone_set('UTC'); // ci vuole per forza #incolpailfusoorario

$loginCredentials = json_decode(file_get_contents("login_credentials.json"), true);

/**
 * CODICE
 */
include_once "Argo.php";

// Instanziamento
$argo = new Argo();

// Login
try{
	if(!is_file("session"))
		$argo->passwordLogin($loginCredentials["schoolcode"], $loginCredentials["username"], $loginCredentials["password"], "session");
	else
		$argo->sessionLogin("session");
}catch(Exception $e){
	echo "Qualcosa è andato a fuoco: " . $e->getMessage();
	exit(-1);
}

// Tests
echo "Oggi: \n";
foreach($argo->oggi() as $ordinedelgiorno){
	echo $ordinedelgiorno["dati"]["desMateria"] . ": " . $ordinedelgiorno["dati"]["desArgomento"] . " " . $ordinedelgiorno["dati"]["docente"] . "\n";
}

echo "\nAssenze, ritardi e permessi:\n";
foreach($argo->assenze() as $assenza){
	switch($assenza["codEvento"]){
		case "A": // assenza
			echo "- Assenza il " .
				date("d/m/Y", date_create_from_format("Y-m-d", $assenza["datAssenza"])->getTimestamp()) .
				($assenza["flgDaGiustificare"] ? " (da giustificare)" : " (giustificata)") .
				($assenza["desAssenza"] == "" ? "" : " con descrizione '" . $assenza["desAssenza"] . "'") .
				" registrata da " . substr($assenza["registrataDa"], 1, -1) . "\n";
			break;
		case "I": // ritardo
			if(isset($assenza["oraAssenza"])){
				$oraEsatta = date("d/m/Y H:i",
					date_create_from_format("!Y-m-d", $assenza["datAssenza"])->getTimestamp() +
					date_create_from_format("!d-m-Y H:i", $assenza["oraAssenza"])->getTimestamp()
				);
			}else
				$oraEsatta = date("d/m/Y", date_create_from_format("Y-m-d", $assenza["datAssenza"])->getTimestamp());

			echo "- Ritardo il " . $oraEsatta .
				" in " . $assenza["numOra"] . "° ora" .
				($assenza["flgDaGiustificare"] ? " (da giustificare)" : " (giustificata)") .
				($assenza["desAssenza"] == "" ? "" : " con descrizione '" . $assenza["desAssenza"] . "'") .
				" registrato da " . substr($assenza["registrataDa"], 1, -1) . "\n";
			break;
		case "U": // permesso
			if(isset($assenza["oraAssenza"]))
				$oraEsatta = date("d/m/Y H:i",
					date_create_from_format("!Y-m-d", $assenza["datAssenza"])->getTimestamp()
					+ date_create_from_format("!d-m-Y H:i", $assenza["oraAssenza"])->getTimestamp()
				);
			else
				$oraEsatta = date("d/m/Y", date_create_from_format("Y-m-d", $assenza["datAssenza"])->getTimestamp());

			echo "- Permesso il " . $oraEsatta .
				" in " . $assenza["numOra"] . "° ora" .
				($assenza["flgDaGiustificare"] ? " (da giustificare)" : " (giustificata)") .
				($assenza["desAssenza"] == "" ? "" : " con descrizione '" . $assenza["desAssenza"] . "'") .
				" registrato da " . substr($assenza["registrataDa"], 1, -1) . "\n";
			break;
	}
}

echo "\nNote disciplinari:\n";
foreach($argo->notedisciplinari() as $nota){
	var_dump($nota); // non ho mai avuto note per cui non posso testare
}

echo "\nVoti giornalieri:\n";
foreach($argo->votigiornalieri() as $voto){
	switch($voto["codVotoPratico"]){
		case "P":
			$tipoVoto = "(Voto pratico)";
			break;
		case "S":
			$tipoVoto = "(Voto scritto)";
			break;
		case "N":
			$tipoVoto = "(Voto orale)";
			break;
		default:
			$tipoVoto = "(Tipo di voto sconosciuto: " . $voto["codVotoPratico"] . ")";
			break;
	}
	echo "- " . date("d/m/Y", date_create_from_format("!Y-m-d", $voto["datGiorno"])->getTimestamp()) . " " .
		$voto["desMateria"] . " " . $tipoVoto . ": " . $voto["desProva"] . ($voto["desCommento"] == "" ? "" : " / " . $voto["desCommento"]) .
		"\n\t" . $voto["codVoto"] . " (" . $voto["decValore"] . ") " . $voto["docente"] . "\n";
}

echo "\nCompiti:\n";
foreach($argo->compiti() as $compito){
	echo "- " . date("d/m/Y", date_create_from_format("!Y-m-d", $compito["datGiorno"])->getTimestamp()) . " " .
		$compito["desMateria"] . ": " . $compito["desCompiti"] . " " . $compito["docente"] . "\n";
}

echo "\nArgomenti:\n";
foreach($argo->argomenti() as $argomento){
	echo "- " . date("d/m/Y", date_create_from_format("!Y-m-d", $argomento["datGiorno"])->getTimestamp()) . " " .
		$argomento["desMateria"] . ": " . $argomento["desArgomento"] . " " . $argomento["docente"] . "\n";
}

echo "\nPromemoria:\n";
foreach($argo->promemoria() as $promemoria){
	echo "- " . date("d/m/Y", date_create_from_format("!Y-m-d", $promemoria["datGiorno"])->getTimestamp()) . " " .
		$promemoria["desAnnotazioni"] . " (" . $promemoria["desMittente"] . ")\n";
}

echo "\nOrario:\n";
foreach($argo->orario() as $orario){
	if(!isset($orario["lezioni"])) continue;
	$lezioni = "";
	foreach($orario["lezioni"] as $lezione){
		$lezioni .= $lezione["materia"] . " " . $lezione["docente"] . ", ";
	}
	$lezioni = substr($lezioni, 0, -2);
	echo "- " . $orario["giorno"] . " " . $orario["numOra"] . "° ora: " . $lezioni . "\n";
}

echo "\nDocenti:\n";
foreach($argo->docenticlasse() as $docente){
	echo "- " . $docente["docente"]["nome"] . " " . $docente["docente"]["cognome"] . " " . $docente["materie"] . "\n";
}
