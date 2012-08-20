<?php
/**
 * Fast Search v3
 * AJAX Responder
 *
 * @copyright 2010-2011 Damien Walsh
 * @package b2bfront
 * @version 1.2
 * @author Damien Walsh
 */

// Set context
define('BF_CONTEXT_INDEX', true);

// Relative path for this - no BF_ROOT yet.
require_once('../startup.php');
require_once(BF_ROOT . 'tools.php');

// New BFClass & Admin class
$BF = new BFClass(true);

// Verify that I am logged in
if(!$BF->security->loggedIn())
{
  // Not authenticated
  $BF->shutdown();
  exit();
}

// Set content type of output
header('Content-Type: text/json');

// Find items
$term = trim($BF->in('term'));

// Valid term?
if(!$term)
{
  // Invalid search term
  print json_encode(array());
  
  $BF->shutdown();
  exit();
}

usleep(100000);

$search = $BF->db->query();
$search->select('*', 'bf_items')
       ->where('`visible` = \'1\' AND (`sku` LIKE \'{1}%\' OR `name` LIKE \'%{1}%\')', $term)
       ->order('sku', 'desc')
       ->limit(50)
       ->execute();
      
// Create a pricer
$pricer = new Pricer($BF);

// Preload cart values
$BF->cart->prefetch();

// Build a collection of items
$itemCollection = array();
while($item = $search->next())
{
  $itemCollection[$item->id] = array(
    'id' => $item->id,
    'sku' => $item->sku,
    'name' => $item->name,
    'stock_free' => 
      ($item->stock_free > 100 ? '100' : ($item->stock_free < 1 ? 0 : $item->stock_free)),
    'trade_price' => ($BF->security->hasPermission('can_see_prices') ? $item->trade_price : ''),
    'pro_net_price' => ($BF->security->hasPermission('can_see_prices') ? $pricer->each($item) : ''),
    'pro_net_quantity' => ($BF->security->hasPermission('can_see_prices') ? $item->pro_net_qty : ''),
    'basket_quantity' => $BF->cart->get($item->id, true)
  );
}

// Convert to JSON and output
print json_encode($itemCollection);

// Exit
$BF->shutdown();

?>