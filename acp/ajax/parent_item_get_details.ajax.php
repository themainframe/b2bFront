<?php
/**
 * Parent Item Data Loader
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

// Load the specified parent item by ID
$parentItemID = $BF->inInteger('id');

// Find it
$BF->db->select('*', 'bf_parent_items')
       ->where('`id` = \'{1}\'', $parentItemID)
       ->limit(1)
       ->execute();
       
// Got one item?
if($BF->db->count == 0)
{
  // Empty JSON set:
  print json_encode(array());
  exit();
}

// Otherwise, provide details
$parentItem = $BF->db->next();

// Delay
sleep(1);

// Return and exit
print json_encode($parentItem);
exit();

?>