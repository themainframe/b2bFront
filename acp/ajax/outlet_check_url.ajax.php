<?php
/**
 * Outlet Check URL
 * AJAX Responder
 *
 * @copyright 2010-2011 Damien Walsh
 * @package b2bfront
 * @version 1.0
 * @author Damien Walsh
 */
 
// Set context
define('BF_CONTEXT_ADMIN', true);

// Relative path for this - no BF_ROOT yet.
require_once('../admin_startup.php');
require_once(BF_ROOT . 'tools.php');

// New BFClass & Admin class
$BF = new BFClass(true);
$BF->admin = new Admin(& $BF);

if(!$BF->admin->isAdmin)
{
  exit();
}

/**
 * Remove non-standard characters
 * @param string $text The string to filter
 * @return string
 */
function cleanNonStandard($text)
{
  $text = preg_replace('/[^0-9A-Za-z\s&£.]/', '', $text);
  return $text;
}

// Show JSON
//header('Content-type: text/json');

// Get the URL to check
$url = $BF->in('url');

// Download the page text
$curlObject = curl_init();

// set URL and other appropriate options
curl_setopt($curlObject, CURLOPT_URL, $url);
curl_setopt($curlObject, CURLOPT_HEADER, 0);
curl_setopt($curlObject, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($curlObject, CURLOPT_FOLLOWLOCATION, 1);

// Get the data
$pageText = curl_exec($curlObject);

// close cURL resource, and free up system resources
curl_close($curlObject);

// Try to parse for prices
$document = new DOMDocument();
$document->loadHTML($pageText);
$documentXPath = new DOMXpath($document);
$textNodes = $documentXPath->query('//text()');
$index = 0;
$results = array();

foreach($textNodes as $domElement)
{
  // Appear as a price?
  $textNode = cleanNonStandard(trim($domElement->wholeText));
  
  if(preg_match('/([A-Za-z\s:\-]+)?(£|&pound;)?(\s+)?(&nbsp;)?(\s+)?[0-9]+\.[0-9]{2}/', $textNode, $matches) == 1)
  {
    $results[$index] = substr(str_replace('£', '&pound;', $textNode), 0, 20);
  }

  $index ++;
}

// Produce JSON results
print json_encode($results);

?>