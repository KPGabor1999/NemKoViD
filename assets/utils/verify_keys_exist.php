<?php

function array_all_keys_exist($array, $inputs) {
	foreach ($inputs as $input) {
		if (!isset($array[$input])) {
			return FALSE;
		}
	}
	return TRUE;
}

function verify_get(...$inputs) {	                //A megkapott argumentumokat tömbként dolgozza fel. A kulcsokat reprezentáló stringeket add át neki.
	return array_all_keys_exist($_GET, $inputs);
}

function verify_post(...$inputs) {                  //Ugyanaz, mint az előbb, csak most a $_POST tömbbre.
	return array_all_keys_exist($_POST, $inputs);
}
