<?php

// Function to take in a string from a given file
function input($argument){
	if ($argument == NULL){
		echo "No text file given as argument";
		exit();
	}
	$file = fopen($argument, "r") or die("Can't open the file to read.");
	$input = fread($file, filesize($argument));
	fclose($file);
	return $input;
}

// Simple function to obtain the data format of the string
function parseFormat($input){
	if (strpos($input, "=>") !== FALSE){
        return "array";
	}
	elseif (strpos($input, "&") !== FALSE){
		return "URI";
	}
    elseif (strpos($input, ":") !== FALSE){
        return "JSON";
    }
    elseif (strpos($input, "xml") !== FALSE){
        return "XML";
    }
	elseif (strpos($input, "/") !== FALSE && strpos($input, "=") !== FALSE){
		return "Jan";
	}
	else{
		echo "Unrecognized format. Exiting.";
		exit();
	}
}

// Set the regex based on the format (as well as the separator and delimiter). 
// Keeping whitespace is important for reconstituting the string later. 
function setRegex($format, &$regex, &$separator, &$delimiter){
	if ($format == "array"){
		$separator = " => ";
		$regex = '/([^=>\n ]+)'.$separator.'(.*)/';
		$delimiter = "\n";
	}
	elseif ($format == "URI"){
		$separator = "=";
		$regex = '/([^=&]+)'.$separator.'([^=&]+)/';
		$delimiter = "&";
	}
	elseif ($format == "JSON"){
		$separator = ": ";
		$regex = '/([^:\n]+)'.$separator.'(.*)/';
		$delimiter = "\n";
	}
	elseif ($format == "XML"){
		$separator=">";
		$regex = '/([^>\n]+)'.$separator.'(.*)/';
		$delimiter = "\n";
	}
	elseif ($format == "Jan"){
		$separator = "=";
		$regex = '/([^=\/]+)'.$separator.'([^=\/]+)/';
		$delimiter = "/";
	}
}

// Parse the string into an associative array
function parseString($input, $regex){
	preg_match_all($regex, $input, $r);
	$result = array_combine($r[1], $r[2]);
	return $result;
}

/*
Functions to mask sensitive data fields
There are two unique properties to the card number to help it get masked:
	1. Each string, there's the phrase "card" and "number" in the key
	2. The number of the digits in the value must be between 12 and 19 (from researching card numbers)
To ensure finding the proper number of digits, all non-digits are temporarily removed while digits are masked
*/
function maskCardNumber(&$dictionary){
	foreach ($dictionary as $key => &$value){
		if (stripos($key, "card") !== FALSE && stripos($key, "number") !== FALSE){
			$tempValue = preg_replace("/[^0-9]/", "", $value );
			if ((strlen((string)$tempValue) >= 12) && (strlen((string)$tempValue) <= 19)){
				$value = preg_replace('/\d/', '*', $value, -1);
			}
		}
	}
}

/*
The unique identifiers for the expiry date are:
	1. The length of the value is 4 digits (usually mm/yy, but not entirely sure)
	2. The phrase "exp" appears in the key.
		The phrase "exp" appears in the 4th sample string, so condition 2 is not good enough to mask without condition 1. 
*/
function maskExpiryDate(&$dictionary){
	foreach ($dictionary as $key => &$value){
		if (stripos($key, "exp") !== FALSE){
			$tempValue = preg_replace("/[^0-9]/", "", $value );
			if ((strlen((string)$tempValue) == 4)){
				$value = preg_replace('/\d/', '*', $value, -1);
			}
		}
	}
}

/*
The unique identifiers for CVV are: 
	1. The phrase "CVV" appears in the key
	2. The value is of length 3. 
*/
function maskCVV(&$dictionary){
	foreach ($dictionary as $key => &$value){
		if (stripos($key, "cvv") !== FALSE){
			$tempValue = preg_replace("/[^0-9]/", "", $value );
			if ((strlen((string)$tempValue) == 3)){
				$value = preg_replace('/\d/', '*', $value, -1);
			}
		}
	}
}

// All mask functions are put together into one function to keep the body cleaner
function mask(&$dictionary){
    maskCardNumber($dictionary);
	maskExpiryDate($dictionary);
	maskCVV($dictionary);
}

// This function reconstitutes the string in the same format that was received. 
// It will both output the string in command line, and output to a new file
function output($format, $dictionary ,$separator, $delimiter, $argument){
	// My regex misses the curly brackets in the JSON string, so they have to be added manually
	if ($format == "JSON")
		$output = $output."{\n";
	foreach ($dictionary as $key => $value){
		$output = $output.$key.$separator.$value.$delimiter;
	}
	if ($format == "JSON")
		$output = $output."}";
	print_r($output);
	$file = fopen("masked".$argument, "w") or die("Can't open the file to write.");
	fwrite($file, $output);
	fclose($file);
}

function returnString($format, $dictionary ,$separator, $delimiter, $argument){
	// My regex misses the curly brackets in the JSON string, so they have to be added manually
	if ($format == "JSON")
		$output = $output."{\n";
	foreach ($dictionary as $key => $value){
		$output = $output.$key.$separator.$value.$delimiter;
	}
	if ($format == "JSON")
		$output = $output."}";
	return $output;
}