<?php
/**
 * Module: Inventory
 * Mode: Do Remove Classification
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

// Obtain the classification ID
$classificationID = $BF->inInteger('clid');
$name = '';
$result = false;

// Valid
if($classificationID)
{
  // Get information
  $BF->db->select('*', 'bf_classifications')
             ->where("`id` = '{1}'", $classificationID)
             ->limit(1)
             ->execute();
             
  // Get name
  $classificationRow = $BF->db->next();
  $name = $classificationRow->name;
  
  // Add restore point
  if($BF->config->get('com.b2bfront.restorepoints.auto', true))
  {
    $BF->admin->api('RestorePoints')
                  ->create('The classification \'' . $name . '\' was removed.');
  }
  
  // Remove classification
  $result = $BF->admin->api('Classifications')
                          ->remove($classificationID);
}


if($result !== false)
{
  $BF->admin->notifyMe('OK', 'The classification \'' . $name . '\' was removed.');
  header('Location: ./?act=inventory&mode=classifications');
}

?>