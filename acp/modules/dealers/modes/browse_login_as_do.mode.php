<?php
/**
 * Module: Dealers
 * Mode: Log In As Dealer
 * 
 * @copyright 2010-2011 Damien Walsh
 * @package b2bfront
 * @version 1.0
 * @author Damien Walsh
 */

// Context check
if(!defined("BF_CONTEXT_ADMIN") || !defined("BF_CONTEXT_MODULE"))
{
  exit();
}

// Obtain the dealer ID
$dealerID = $BF->inInteger('id');
$letterCallback = $BF->in('letter');
$name = '';
$result = false;

// Valid
if($dealerID)
{
  // Log In
  $BF->security->logOut();
  $BF->security->logInWithID($dealerID);
  
  // Redirect to home screen
  $BF->go($BF->config->get('com.b2bfront.site.url', true));
}


if($result !== false)
{
  $BF->admin->notifyMe('OK', 'The dealer \'' . $name . '\' was removed.');
  header('Location: ./?act=dealers&letter=' . $letterCallback);
}

?>