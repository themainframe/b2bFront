<?php
/**
 * Show relevant dealers - Inventory/Arrivals
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
$BF = new BFClass();
$BF->admin = new Admin(& $BF);

if(!$BF->admin->isAdmin)
{
  exit();
}

// Dealers
$dealers = array();

// Get the ID of the item to look for
$itemID = $BF->inInteger('id');

// Get the timeout period from config
$timeout = $BF->config->get(
  'com.b2bfront.crm.purchase-history-length', true) * 86400;

// Look up how many dealers have ordered this recently
// Find orders
$orders = $BF->db->query();
$orders->select('*', 'bf_orders')
       ->where('(UNIX_TIMESTAMP() - `timestamp`) < {1}', $timeout)
       ->execute();
  
$dealerIDs = array();
       
while($order = $orders->next())
{
  // Find lines
  $lines = $BF->db->query();
  $lines->select('*', 'bf_order_lines')
        ->where('`order_id` = \'{1}\' AND `item_id` = \'{2}\'', 
                $order->id, $itemID)
        ->execute();
      
  while($line = $lines->next())
  {
    // Add this dealer
    $dealer = $BF->db->getRow('bf_users', $order->owner_id);
    
    if(in_array($dealer->id, $dealerIDs))
    {
      continue;
    }
    
    $dealerIDs[] = $dealer->id;
    
    $dealers[] = array(
      'name' => $dealer->name,
      'description' => $dealer->description
    );
  }     
}


print json_encode($dealers);
$BF->shutdown();

?>