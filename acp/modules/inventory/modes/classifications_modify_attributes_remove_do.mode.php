<?php
/**
 * Module: Inventory
 * Mode: Do Remove Classification Attribute
 *       And redirect back to Modify Classification screen.
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

// Obtain the attribute ID
$attributeID = $BF->inInteger('id');

// Valid
if($attributeID)
{
  // Remove attribute and all data
  $BF->admin->api('Classifications')
            ->removeAttribute($attributeID);
}

// Redirect back to classification modification view
$BF->admin->notifyMe('OK', 'The classification attribute was removed.');
$BF->go('./?act=inventory&mode=classifications_modify_attributes&id=' . $classificationID);

?>