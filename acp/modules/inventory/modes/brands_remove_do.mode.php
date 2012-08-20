<?php
/**
 * Module: Inventory
 * Mode: Do Remove Brand
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

// Obtain the brand ID
$brandID = $BF->inInteger('id');
$name = '';
$result = false;

// Valid
if($brandID)
{
  // Get information
  $BF->db->select('*', 'bf_brands')
             ->where("`id` = '{1}'", $brandID)
             ->limit(1)
             ->execute();
             
  // Get name
  $brandRow = $BF->db->next();
  $name = $brandRow->name;
  $image = $brandRow->image_path;
  
  // Add restore point
  if($BF->config->get('com.b2bfront.restorepoints.auto', true))
  {
    $BF->admin->api('RestorePoints')
                  ->create('The brand \'' . $name . '\' was removed.');
  }
  
  // Remove brand
  $result = $BF->admin->api('Brands')
                          ->remove($brandID);
}


if($result !== false)
{
  $BF->admin->notifyMe('OK', 'The brand \'' . $name . '\' was removed.');
  header('Location: ./?act=inventory&mode=brands');
}

?>
