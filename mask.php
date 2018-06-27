#!/usr/bin/php

<?php
// Defining variables
$separator;
$regex;
$delimiter;
$input = 
"{
    \"MsgTypId\": 111231232300,
    \"CardNumber\": \"4242424242424242\",
    \"CardExp\": 1024,
    \"CardCVV\": 240,
    \"TransProcCd\": \"004800\",
    \"TransAmt\": \"57608\",
    \"MerSysTraceAudNbr\": \"456211\",
    \"TransTs\": \"180603162242\",
    \"AcqInstCtryCd\": \"840\",
    \"FuncCd\": \"100\",
    \"MsgRsnCd\": \"1900\",
    \"MerCtgyCd\": \"5013\",
    \"AprvCdLgth\": \"6\",
    \"RtrvRefNbr\": \"1029301923091239\",
}";

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
	else{
		echo "Unrecognized format. Exiting.";
		exit();
	}
}

// Set the regex based on the format (as well as the separator and delimiter
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
		$regex = '/([^:\n]+)'.$separator.'([^,]+)/';
		$delimiter = ",\n";
	}
	elseif ($format == "XML"){
		
	}
}

// Parse the string into an associative array
function parseString($input, $regex){
	preg_match_all($regex, $input, $r);
	$result = array_combine($r[1], $r[2]);
	print_r($r);
	return $result;
}

function maskCardName(&$dictionary){
	foreach ($dictionary as $key => &$value){
		if (stripos($key, "card") !== FALSE && stripos($key, "number") !== FALSE){
			$tempValue = preg_replace("/[^0-9]/", "", $value );
			if ((strlen((string)$tempValue) >= 12) && (strlen((string)$tempValue) <= 19)){
				$value = preg_replace('/\d/', '*', $value, -1);
			}
		}
	}
}

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

function mask(&$dictionary){
    maskCardName($dictionary);
	maskExpiryDate($dictionary);
	maskCVV($dictionary);
}

function outsource($dictionary, $format, $regex ,$separator, $delimiter){
	print_r($input);
		
}

function output($input){
	print_r($input);
}

$format = parseFormat($input);
echo "$format\n";
setRegex($format, $regex, $separator, $delimiter);
$dictionary = parseString($input, $regex);
mask($dictionary);
print_r($dictionary);