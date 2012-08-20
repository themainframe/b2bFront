<?php
/**
 * Parent Item Variation Options Loader
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

// Get the Parent Item ID
$parentItemID = $BF->inInteger('id');

// Select default attributes from DB
$BF->db->select('*', 'bf_parent_item_variations')
           ->where('parent_item_id = \'{1}\'', $parentItemID)
           ->order('name', 'asc')
           ->execute();

// Build a collection of variation options for outputting
$variationCollection = array();
           
while($variation = $BF->db->next())
{
  $variationCollection[$variation->id] = array();
  $variationCollection[$variation->id]['id'] = $variation->id;
  $variationCollection[$variation->id]['name'] = $variation->name;                           
}

// Write JSON
print json_encode($variationCollection);

?>