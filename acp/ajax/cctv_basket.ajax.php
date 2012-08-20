<?php
/**
 * Notifications
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

$user = $BF->inInteger('id');

if($user == -1 || !$user)
{
  // Empty result
  print json_encode(array());

  // Can't provide data
  $BF->shutdown();
  exit();
}

// Get basket data
$basket = $BF->db->query();
$basket->select('*', 'bf_user_cart_items')
       ->where('`user_id` = \'{1}\'', $user)
       ->order('id', 'asc')
       ->execute();
       
// Find the basket items and load into JSON encodable array
$items = array();

while($basketItem = $basket->next())
{
  $item = $BF->db->getRow('bf_items', $basketItem->item_id);
  $items[] = array(
    'sku' => $item->sku,
    'name' => $item->name,
    'quantity' => $basketItem->quantity
  );
}

// Dump output
print json_encode($items);

$BF->shutdown();

?>