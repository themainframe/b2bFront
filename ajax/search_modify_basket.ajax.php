<?php
/**
 * Fast Search Basket Modification
 * AJAX Responder
 *
 * @copyright 2010-2011 Damien Walsh
 * @package b2bfront
 * @version 1.0
 * @author Damien Walsh
 */

// Set context
define('BF_CONTEXT_INDEX', true);

// Relative path for this - no BF_ROOT yet.
require_once('../startup.php');
require_once(BF_ROOT . 'tools.php');

// New BFClass & Admin class
$BF = new BFClass(true);

// Verify that I am logged in
if(!$BF->security->loggedIn())
{
  // Not authenticated
  $BF->shutdown();
  exit();
}

// Set content type of output
header('Content-Type: text/json');

// Parse the inputs - intval all due to using $_GET here.
$inputs = $_GET;
foreach($inputs as $key => $value)
{
  // Update basket values for each
  $BF->cart->add(intval($key), intval($value));
}


// Output basket count
print json_encode(array(
  'basket_count' => $BF->cart->count()
));

// Shut down
$BF->shutdown();

?>