#!/usr/bin/php

<?php
require "functions.php";

// Defining variables
$separator;
$regex;
$delimiter;
$filename = $argv[1];

$input = input($filename);
$format = parseFormat($input);
setRegex($format, $regex, $separator, $delimiter);
$dictionary = parseString($input, $regex);
mask($dictionary);
output($format, $dictionary, $separator, $delimiter, $filename);