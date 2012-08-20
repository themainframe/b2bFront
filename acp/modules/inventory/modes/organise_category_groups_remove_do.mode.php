<?php
/**
 * Module: Inventory
 * Mode: Do Remove Category Group
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

// Obtain the ID
$categoryGroupID = $BF->inInteger('id');
$result = false;

// Valid
if($categoryGroupID)
{
  $BF->db->delete('bf_category_groups')
         ->where("`id` = '{1}'", $categoryGroupID)
         ->limit(1)
         ->execute();
         
  $result = true;
}

if($result !== false)
{
  $BF->admin->notifyMe('OK', 'The category group was removed.');
  header('Location: ./?act=inventory&mode=organise_category_groups');
}

?>