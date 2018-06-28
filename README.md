# Transaction Masker
Transaction masker is a program made for a coding question based on the following: 
Please send a .php file containing a function that achieves the following:
```
A string parameter is passed to the function – attached are various samples of possible input data
The function receives this data as a string, not an array, json or other data formats
The function should parse the string and mask sensitive data.
Sensitive data should be masked (replaced) with an Asterix (*) character.
Sensitive data includes the fields below, but new sensitive fields should be easily added to the function as needed:
	The credit card number
	The credit card expiry date
	The credit card CVV value
The function returns the parsed string in the same format that it was provided, but with the sensitive data now masked.
```
## Table of Contents
- [Getting Started](#gettingstarted)
- [Problems](#problems)
- [Requirements](#requirements)
	- [Input Requirements](#inputreq)
	- [Masking Requirements](#maskingreq)
- [Approach](#approach)
	- [input($argument)](#input)
	- [parseFormat($input)](#parseformat)
		- [Why Format Over String Parsing?](#why)
	- [setRegex($format, &$regex, &$separator, &$delimiter)](#setregex)
	- [parseString($input, $regex)](#parsestring)
	- [mask()](#mask)
		- [maskCardNumber($dictionary)](#masknumber) 
		- [maskExpiryDate($dictionary)](#maskexpiry) 
		- [maskCVV($dictionary)](#maskcvv) 
		- [Other options](#other) 
	- [output($format, $dictionary, $separator, $delimiter, $filename)](#output)
- [Supported Formats](#supported)
- [Dependencies](#howdoispellthis)


## Getting Started
You'll only need to download 'mask.php' as well as some sample files to input into the program. To run, move to the directory where you've put mask.php in cmd and enter:
```
C:\path\to\php.exe mask.php input.txt
```
The output will be echoed in cmd, as well as written into masked+'input.txt'

## Problems
After reading the guidelines, some obstacles became evident. 
1. String formatting is non-constant
2. Variable names are non-constant
3. Strings might be incorrectly formed
4. Parsing by looking for specific phrases might catch unwanted data
5. Must be in same format as input string
6. Must be modular to changes (cannot hardcode just for CVV, CardNumber, and expiry date)
7. Could be more formats than the following given

## Requirements
Requirements were created based on the information given in the problem. 

Note: Four sample strings were given to us, but the content is in the following data formats:
	- JSON
	- XML
	- URI Queries
	- Array

### Input Requirements
1. The function will be able to identify the format of the input string based on unique identifiers for the sample formats
2. The function will parse transaction data from the input string
3. The function will detect if the string is not in a known format
4. The function will support the addition of new formats

### Masking Requirements
5. The function will identify the card number, expiry date, and CVV from the parsed data
6. The function will mask the sensitive data by replacing the data with asterisks
7. The function will output in the same format as the input
8. The function will support the masking of additional sensitive data fields

## Approach
Each function in the program required a different approach depending on functionality. In this section, I will go over my thought process for each function. 

### input($argument)
This function is very basic. It simply looks that the file exists, and reads the string in. 

### parseFormat($input)
There were two approaches for parsing the input. I could try to parse the data based on the format that the string represented (format parsing), or ignore that and try to parse the string without worrying about the representation (string parsing). I decided to go with format parsing. This leads to the question of...

#### Why Format Over String Parsing?
1. Formats are (assumedly) standard for credit card transactions
	
    The input strings aren't just random key-value pairs. They represent actual credit card transactions. Though they might not be real (and most certainly aren't), the fact that they represent a transaction means  the data will most likely be represented by a standard data format. 
    
2. It's easier to separate the key and value with a regular expression

	Data formats usually make things easy with having the key, a separator, the value, and a delimiter. The use of a regular expression simplifies things greatly. 
    
3. Formats are easy to add if needed

	If one wishes to parse another data format, they can simply add another if statement checking for a unique identifier of that format. With a string parser, a lot of work might have to be done to accomodate that kind of format. 
4. It's easier to reconstruct the output string

	With format parsing, all that is needed is the key, the value, the separator, and the delimiter, and the string can be easily reconstituted in the same format as the input. 
5. A string parser just can't get every string

	Frankly, this is the most important reason. Trying to get every format with string parsing is a fool's game. There will exists formats that break a string parser. Rather than trying to catch every fish in the ocean with a large net, why not just get different nets for different fish?

### setRegex($format, &$regex, &$separator, &$delimiter)
Most of the regular expressions follow the same pattern:
```
$regex = '/([^=>\n ]+)'.$separator.'(.*)/'
```
The first bracket gets the key by going from the start of the line to the separator, then the value goes from the separator to the end of the line. One interesting fact to take away from this function comes from the XML regex: 
```
$regex = '/([^>\n]+)'.$separator.'(.*)/';
```
Given the following example string: 
```
1. <NewOrder>
2. 		<IndustryType>MO</IndustryType>
3. </NewOrder>
```
The key of line 1 will be "\<NewOrder", but the value will be "". The key of line 2 will be "\<IndustryType" but the value will be "MO\</IndustryType>. For the purpose of the function, this is absolutely okay. The end result of the program isn't to pull the key and value, but to mask specific values. The mask will not care about the "\</IndustryType>" part of the value, it just needs the "MO" part. Trying to remove the last part is doing work that doesn't have to be done. 

### parseString($input, $regex)
This function uses the regex to parse the string into an associative array. This array lets us have easy access to each key-value pair. We can walk along the array, and look for specific characteristics which lets us know what to mask. 

### mask()
The mask() function contains 3 subfunctions: maskCardNumber($dictionary), maskExpiryDate($dictionary), and maskCVV($dictionary). All of these replace the desired value with asterisks to mask the original value. 

#### maskCardNumber($dictionary)
One thing that is in common in the sample input strings given are that the key for a credit card number all contain "card" and "number". This seems obvious in finding the credit card number, but what if there was a field that was called "cardPhoneNumber"? Clearly another quantifier is required. Remembering that these string represent actual transactions, we can use the number of digits in the value to determine what is and isn't a credit card number. Looking [here](http://www.dirigodev.com/blog/ecommerce/anatomy-of-a-credit-card-number/), we see that the length of a credit card number is between 12 and 19 digits. This is the second value we can use to find the credit card number. 

#### maskExpiryDate($dictionary)
Similarly to the first function, the phrase "exp" can be found in all expiry dates. However, we have an example where "exp" shows up in a non expiry date: currencyExponent. Therefore, we must use the length of the value (in this case, 4) to determine what is an expiry date. 

#### maskCVV($dictionary)
This looks for "CVV" in the key and a length of 3 in the value. Simple as that. 

#### Other options
If we want to mask other values, we just need to find what is unique about each key-value pair. For example, if we want to mask the card holder's name, we can look for "name" in the key, and perhaps no digits in the value. This is a quick example, but adding new masks shouldn't be too difficult. 

### output($format, $dictionary, $separator, $delimiter, $filename)
This is about as cut and dry as input(). Basically it runs through the $dictionary, and arranges each key-value pair with separators and delimiters as such: 
```
$output = $output.$key.$separator.$value.$delimiter;
```
$format is required because of the JSON string. Regrettably, the regex for JSON removes the braces at the start and end of the string, so those must be manually entered back. 

## Supported Formats
Currently, strings formatted as PHP associative arrays, JSON, URI queries, and XML are supported, but more can easily be added. 

## Dependencies
The only dependency is having php on your machine. 