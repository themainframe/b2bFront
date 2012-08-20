<?php
/**
 * Module: Dealers
 * Mode: Do Remove Unapproved Dealer
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
$result = false;

// Valid
if($dealerID)
{
  // Remove dealer
  $result = $BF->admin->api('Dealers')
                          ->remove($dealerID);
}


if($result !== false)
{
  $BF->admin->notifyMe('OK', 'The request was declined.');
  header('Location: ./?act=dealers&mode=unapproved');
}

?>