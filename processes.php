<?php
require_once('./incs/functions.inc.php');

function explode_whitespace($str) {  
  # Split the input string into an array
  $parts = preg_split('/\s+/', $str);
  # Get the size of the array of substrings
  $sizeParts = sizeof($parts);
  # Check if the last element of the array is a zero-length string
  if ($sizeParts > 0) {
    $lastPart = $parts[$sizeParts-1];
    if ($lastPart == '') {
      array_pop($parts);
      $sizeParts--;
    }
    # Check if the first element of the array is a zero-length string
    if ($sizeParts > 0) {
      $firstPart = $parts[0];
      if ($firstPart == '') 
        array_shift($parts); 
    }
  }
  return $parts;   
}

$formatted = array();
exec('ps ahxwwo',$output);
print $output . "<BR />";
$formatted = explode_whitespace($output);
dump($formatted);
