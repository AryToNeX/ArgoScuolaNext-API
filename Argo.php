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

class Argo{

	// COSTANTI --------------------------------------------------------------------------------------------------------

	/** L'url delle REST API di Argo ScuolaNext */
	const ARGO_URL = "https://www.portaleargo.it/famiglia/api/rest/";

	/** Chiave delle REST API di Argo ScuolaNext */
	const ARGO_KEY = "ax6542sdru3217t4eesd9";

	/** Versione delle REST API di Argo ScuolaNext */
	const ARGO_VERSION = "2.0.2";

	// VARIABILI -------------------------------------------------------------------------------------------------------

	/**
	 * @var string $schoolCode  Codice scuola
	 * @var string $accountType Tipo utente
	 * @var string $authToken   Token temporaneo di autenticazione
	 * @var array  $userInfo    Informazioni sull'alunno
	 * @var array  $classInfo   Informazioni sulla classe di appartenenza
	 * @var array  $schoolInfo  Informazioni sulla scuola di appartenenza
	 * @var array  $yearInfo    Informazioni sull'anno scolastico
	 * @var array  $features    Funzioni abilitate sulla piattaforma ScuolaNext
	 * @var array  $argoData    Dati utili alle API ScuolaNext
	 */
	private $schoolCode, $accountType, $authToken, $userInfo, $classInfo, $schoolInfo, $yearInfo, $features, $argoData;

	// METODI API ------------------------------------------------------------------------------------------------------

	/**
	 * Costruttore delle API, volutamente vuoto
	 */
	public function __construct(){
		// ops non ci sta niente
	}

	/**
	 * Metodo di login ad Argo ScuolaNext con username e password
	 *
	 * @param string $schoolCode    Codice Argo ScuolaNext della scuola
	 * @param string $username      Nome utente
	 * @param string $password      Password dell'utente
	 * @param string $loginDataFile Directory relativa al file dei dati di sessione che si intende creare
	 *
	 * @return bool
	 */
	public function passwordLogin(string $schoolCode, string $username, string $password, string $loginDataFile = null): bool{
		$this->schoolCode = $schoolCode;
		try{
			$this->argo_getToken($username, $password);
			$this->argo_populateInfo();
		}catch(Exception $e){
			return false;
		}
		
		if(isset($loginDataFile)){
			if($this->saveSession($loginDataFile) === false)
				echo "Login effettuato, ma è stato impossibile salvare il token in un file.\n";
		}
		
		return true;
	}

	/**
	 * Metodo per salvare la sessione su file
	 *
	 * @param string $loginDataFile Directory relativa al file dei dati di sessione che si intende creare
	 */
	public function saveSession(string $loginDataFile): bool{
		return (file_put_contents($loginDataFile, json_encode(array(
			"schoolCode"  => $this->schoolCode,
			"accountType" => $this->accountType,
			"authToken"   => $this->authToken
		))) !== false ? true : false);
	}

	/**
	 * Metodo di login ad Argo ScuolaNext con sessione salvata su file
	 *
	 * @param string $loginDataFile Directory relativa al file dei dati di sessione
	 *
	 * @return bool
	 */
	public function sessionLogin(string $loginDataFile): bool{
		$data = json_decode(file_get_contents($loginDataFile), true);
		$this->schoolCode = $data["schoolCode"];
		$this->accountType = $data["accountType"];
		$this->authToken = $data["authToken"];
		try{
			$this->argo_populateInfo();
		}catch(Exception $e){
			return false;
		}
		
		return true;
	}

	/**
	 * Metodo getter per avere le informazioni sul tipo di account usato per il login
	 *
	 * @return string
	 */
	public function getAccountType(): string{
		return $this->accountType;
	}

	/**
	 * Metodo getter per avere le informazioni sull'alunno
	 *
	 * @return array
	 */
	public function getUserInfo(): array{
		return $this->userInfo;
	}

	/**
	 * Metodo getter per avere le informazioni sulla classe di appartenenza dell'alunno
	 *
	 * @return array
	 */
	public function getClassInfo(): array{
		return $this->classInfo;
	}

	/**
	 * Metodo getter per avere le informazioni sulla scuola di appartenenza dell'alunno
	 *
	 * @return array
	 */
	public function getSchoolInfo(): array{
		return $this->schoolInfo;
	}

	/**
	 * Metodo getter per avere le informazioni sull'anno scolastico
	 *
	 * @return array
	 */
	public function getYearInfo(): array{
		return $this->yearInfo;
	}

	/**
	 * Metodo getter per avere le informazioni sulle funzioni abilitate sulla piattaforma ScuolaNext
	 *
	 * @return array
	 */
	public function getFeatures(): array{
		return $this->features;
	}

	/**
	 * Metodo per ottenere il riepilogo della giornata
	 * @deprecated poiché redirect a $this->oggi()
	 * Scritto solo per 'facilitare' lo switch a questa API dalla API di Cristian Livella
	 *
	 * @param string|null $data Data in formato 'Y-m-d' del giorno voluto
	 *
	 * @return array|null
	 */
	public function oggiScuola(string $data = null): ?array{
		if(isset($data)) $data = date_create_from_format("!Y-m-d", $data)->getTimestamp();

		return $this->oggi($data);
	}

	/**
	 * Metodo per ottenere il riepilogo della giornata
	 *
	 * @param int|null $data UNIX Timestamp del giorno voluto
	 *
	 * @return array|null
	 */
	public function oggi(int $data = null): ?array{
		try{
			return $this->argo_genericRequest("oggi", array("datGiorno" => date("Y-m-d", $data ?? time())))["dati"];
		}catch(Exception $e){
			return null;
		}
	}
	
	/**
	 * Metodo per ottenere una lista dei docenti della classe
	 * @deprecated poiché redirect a $this->docentiClasse();
	 * Scritto solo per 'facilitare' lo switch a questa API dalla API di Christian Livella
	 *
	 * @return array|null
	 */
	public function docenti(): ?array{
		return $this->docenticlasse();
	}

	/**
	 * Metodo per chiamate non definite alle API ScuolaNext
	 *
	 * Chiamate possibili:
	 * - schede (Già chiamata nel metodo interno argo_populateInfo(), meglio usare i getter)
	 * - assenze
	 * - notedisciplinari
	 * - votigiornalieri
	 * - votiscrutinio
	 * - compiti
	 * - argomenti
	 * - promemoria
	 * - orario
	 * - docenticlasse
	 * - Qualsiasi altra chiamata non listata ma compatibile con le API ScuolaNext
	 *
	 * @param string $name      Nome del metodo API da chiamare
	 * @param array  $arguments Argomenti GET delle API da passare
	 *
	 * @return array|null
	 */
	public function __call(string $name, array $arguments): ?array{
		try{
			if(in_array(strtolower($name), ["votiscrutinio", "docenticlasse"]))
				return $this->argo_genericRequest(strtolower($name), $arguments);
			else
				return $this->argo_genericRequest(strtolower($name), $arguments)["dati"];
		}catch(Exception $e){
			return null;
		}
	}

	// METODI INTERNI --------------------------------------------------------------------------------------------------

	/**
	 * Metodo interno di login ad Argo ScuolaNext con username e password
	 *
	 * @param string $username Nome utente
	 * @param string $password Password dell'utente
	 *
	 * @throws Exception
	 */
	private function argo_getToken(string $username, string $password){
		/*
		 * Innanzitutto per effettuare il login dobbiamo avere un token di accesso.
		 * Inizializziamo cURL per connettersi alle API (ricordo il link completo: https://www.portaleargo.it/famiglia/api/rest/login )
		 * ed effettuiamo la richiesta.
		 */
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, self::ARGO_URL . "login");
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_HTTPHEADER, array(
			"x-key-app: " . self::ARGO_KEY,
			"x-version: " . self::ARGO_VERSION,
			"user-agent: Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/57.0.2987.133 Safari/537.36",
			"x-cod-min: " . $this->schoolCode,
			"x-user-id: " . $username,
			"x-pwd: " . $password
		));
		$output = json_decode(curl_exec($ch), true);
		if(curl_getinfo($ch, CURLINFO_HTTP_CODE) != 200) throw new Exception("Impossibile ottenere il token di login");
		curl_close($ch);
		unset($ch);

		/*
		 * Una volta che abbiamo effettuato la richiesta, possiamo salvare il tipo di utente (G sarà genitore, non so gli altri)
		 * e il token in variabili dedicate e togliamoci di mezzo eventuali variabili non usate.
		 */
		$this->accountType = $output["tipoUtente"];
		$this->authToken = $output["token"];
		unset($output);
	}

	/**
	 * Metodo interno per la popolazione delle informazioni base
	 *
	 * @throws Exception
	 */
	private function argo_populateInfo(){
		/*
		 * Chiamiamo "schede" per ottenere un po' più di informazioni che eventualmente possono servirci e ce le salviamo in variabili dedicate.
		 * (URL chiamato: https://www.portaleargo.it/famiglia/api/rest/schede )
		 */
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, self::ARGO_URL . "schede");
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_HTTPHEADER, array(
			"x-key-app: " . self::ARGO_KEY,
			"x-version: " . self::ARGO_VERSION,
			"user-agent: Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/57.0.2987.133 Safari/537.36",
			"x-cod-min: " . $this->schoolCode,
			"x-auth-token: " . $this->authToken
		));
		$output = json_decode(curl_exec($ch), true)[0];
		if(curl_getinfo($ch, CURLINFO_HTTP_CODE) != 200) throw new Exception("Impossibile ottenere le informazioni utili.");
		curl_close($ch);
		unset($ch);

		/*
		 * Informazioni sull'alunno
		 * - Nome
		 * - Cognome
		 * - Sesso
		 * - Indirizzo, codice postale
		 * - Codice fiscale
		 * - Numero di telefono e di cellulare
		 * - Data di nascita
		 * - Comune di recapito e di residenza
		 * - Cittadinanza
		 */
		$this->userInfo = $output["alunno"];

		/*
		 * Informazioni sulla classe
		 * - Numero classe (per l'API)
		 * - Grado classe (1, 2, 3, 4, 5)
		 * - Corso (o sezione) classe
		 */
		$this->classInfo = array(
			"prgClasse"        => $output["prgClasse"],
			"desDenominazione" => $output["desDenominazione"],
			"desCorso"         => $output["desCorso"]
		);

		/*
		 * Informazioni sulla scuola
		 * - Nome scuola
		 * - Tipo di sede
		 * - Codice ARGO della scuola (ridondanza di $this->schoolCode)
		 */
		$this->schoolInfo = array(
			"desScuola" => $output["desScuola"],
			"desSede"   => $output["desSede"],
			"codMin"    => $output["codMin"],
		);

		/*
		 * Informazioni sull'anno scolastico
		 * - Inizio
		 * - Fine
		 * NB: Inizio e fine dell'anno scolastico non combaciano con l'inizio e la fine delle lezioni (settembre-giugno)
		 */
		$this->yearInfo = $output["annoScolastico"];

		/*
		 * Funzioni attive nella piattaforma ScuolaNext
		 */
		$this->features = $output["abilitazioni"];

		/*
		 * Dati utili alle API di Argo per chiamare altre funzioni
		 */
		$this->argoData = array(
			"prgAlunno" => $output["prgAlunno"],
			"prgScheda" => $output["prgScheda"],
			"prgScuola" => $output["prgScuola"]
		);
	}

	/**
	 * Metodo interno per richieste generiche ad Argo ScuolaNext
	 *
	 * @param string $request Metodo delle API ScuolaNext da chiamare
	 * @param array  $query   Query GET in forma array da mandare alle API
	 *
	 * @throws Exception
	 * @return array
	 */
	private function argo_genericRequest(string $request, array $query = array()): array{
		$query = array_merge(array("_dc" => round(microtime(true) * 1000)), $query);

		// init curl
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, self::ARGO_URL . $request . "?" . http_build_query($query));
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_HTTPHEADER, array(
			"x-key-app: " . self::ARGO_KEY,
			"x-version: " . self::ARGO_VERSION,
			"user-agent: Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/57.0.2987.133 Safari/537.36",
			"x-auth-token: " . $this->authToken,
			"x-cod-min: " . $this->schoolCode,
			"x-prg-alunno: " . $this->argoData["prgAlunno"],
			"x-prg-scheda: " . $this->argoData["prgScheda"],
			"x-prg-scuola: " . $this->argoData["prgScuola"]
		));
		$output = curl_exec($ch);
		if(curl_getinfo($ch, CURLINFO_HTTP_CODE) != 200) throw new Exception("\nImpossibile soddisfare la richiesta: $request\nOutput: $output\n");
		curl_close($ch);

		return json_decode($output, true);
	}
}
