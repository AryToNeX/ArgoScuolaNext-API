# Note varie nella comprensione dei valori significativi delle API

## ASSENZE:

Valori di "codEvento":
- 'A' per assenze
- 'I' per ritardi
- 'U' per permessi d'uscita

Ottenere la data precisa per i ritardi e per i permessi:

```php
$oraEsatta = date_create_from_format("!Y-m-d", $assenza["datAssenza"])->getTimestamp() + date_create_from_format("!d-m-Y H:i", $assenza["oraAssenza"])->getTimestamp();

$stringaOraEsatta = date("d/m/Y H:i", $oraEsatta);
```

NB: Ovviamente controllare con `isset($assenza["oraAssenza"])` prima di incappare in eccezioni.

## VOTI GIORNALIERI:

Valori di "codVotoPratico":
- 'N' è un voto orale
- 'P' è un voto pratico
- 'S' è un voto scritto