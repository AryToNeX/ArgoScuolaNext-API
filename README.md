# ArgoScuolaNext-API, build your PHP school projects with ease!

This project was built from scratch, but it was also hugely inspired by those other APIs built over Argo ScuolaNext:

- [ArgoAPI by Cristian Livella](https://github.com/cristianlivella/ArgoAPI) (Those APIs are kind of compatible with these ones)
- [ArgoScuolaNext by hearot](https://github.com/hearot/ArgoScuolaNext) (Those APIs are kind of compatible with these ones too)

So what's different with those APIs, you may ask?

- These APIs are simpler. I mean, really. No JSON tinkering, simple method calls, _it just works_.
- These APIs can store a session and recover it instead of using username and password every time _and that means security_.
- These APIs use PHP 7 and its features, _just because we love typehinting and return types_.

## Usage

Alright, so how do we use these APIs? Just look at these pieces of code, they are self-explanatory!

### Login

```php
<?php

date_default_timezone_set('UTC');
include_once "Argo.php";

const SCHOOL_CODE = "XX12345", USER = "someguy", PASSWORD = "somepass";

$argo = new Argo();

try{
	if(!is_file("session"))
		$argo->passwordLogin(SCHOOL_CODE, USER_NAME, PASSWORD, "session");
	else
		$argo->sessionLogin("session");
}catch(Exception $e){
	echo "Something happened: " . $e->getMessage();
	exit(-1);
}
```

### Example calls

Today's resume

```php
$oggi = $argo->oggi();

var_dump($oggi);
```

Yesterday's resume

```php
$ieri = $argo->oggi(strtotime("yesterday"));

var_dump($ieri);
```

Homework

```php
$compiti = $argo->compiti();

var_dump($compiti);
```

## Possible calls

- schede (Already used when logging in, so please use the getter methods)
- assenze
- notedisciplinari
- votigiornalieri
- votiscrutinio
- compiti
- argomenti
- promemoria
- orario
- docenticlasse

## Apache 2.0 License

```text
Copyright 2018 Anthony Calabretta

Licensed under the Apache License, Version 2.0 (the "License");
you may not use this file except in compliance with the License.
You may obtain a copy of the License at

   http://www.apache.org/licenses/LICENSE-2.0

Unless required by applicable law or agreed to in writing, software
distributed under the License is distributed on an "AS IS" BASIS,
WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
See the License for the specific language governing permissions and
limitations under the License.
```
