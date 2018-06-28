#!/usr/bin/php

<?php
require "functions.php";
function maskSensitiveData($input){
	// Defining variables
	$separator;
	$regex;
	$delimiter;
	
	$format = parseFormat($input);
	setRegex($format, $regex, $separator, $delimiter);
	$dictionary = parseString($input, $regex);
	mask($dictionary);
	return returnString($format, $dictionary ,$separator, $delimiter, $argument);
}
$filename = $argv[1];
$inputString = input($filename);
$outputString = maskSensitiveData($inputString);
echo $outputString;