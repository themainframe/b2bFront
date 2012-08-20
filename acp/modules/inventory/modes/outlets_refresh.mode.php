<?php
/**
 * Module: Inventory
 * Mode: Do Refresh Outlets Manually
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

// Update Outlets
$BF->admin->api('Outlets')
          ->updateAll();


$BF->admin->notifyMe('OK', 'Outlets have been refreshed.');
header('Location: ./?act=inventory&mode=outlets');

?>