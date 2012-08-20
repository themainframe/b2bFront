<?php
/**
 * Dashboard Quick-Go Search
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

// Get the search term
$search = $BF->in('query');

// Empty?
if(trim($search) == '' || strlen($search) < 2)
{
  print json_encode(array());
  exit();
}


// Set up JSON output
$jsonReturn = array(

  'users' => array(
    'name' => 'Dealers',
    'icon' => 'user-business.png',
    'linkroot' => './?act=dealers&mode=browse_modify&id=',
    'objects' => array()
  ),
  
  'items' => array(
    'name' => 'Items',
    'icon' => 'box.png',
    'linkroot' => './?act=inventory&mode=browse_modify&id=',
    'objects' => array()
  ),
  
  'orders' => array(
    'name' => 'Orders',
    'icon' => 'money-coin.png',
    'linkroot' => './?act=orders&mode=auto_choose_display&id=',
    'objects' => array()
  )
  
);

// First look for Items
$items = $BF->db->query();
$items->select('*', 'bf_items')
      ->where('`name` LIKE \'{1}%\' OR `sku` LIKE \'{1}%\'', $search)
      ->limit(10)
      ->execute();
      
if($items->count > 0)
{
  while($item = $items->next())
  {
    $jsonReturn['items']['objects'][] = array(
      'id' => $item->id,
      'short' => $item->sku,
      'long' => $item->sku . ' - ' . $item->name
    );
  }
}
      
// Users
$users = $BF->db->query();
$users->select('*', 'bf_users')
      ->where('`description` LIKE \'{1}%\' OR `name` LIKE \'{1}%\'' . 
              ' OR `account_code` LIKE \'{1}%\' OR `email` LIKE \'%{1}%\'', $search)
      ->limit(10)
      ->execute();

if($users->count > 0)
{
  while($user = $users->next())
  {
    $jsonReturn['users']['objects'][] = array(
      'id' => $user->id,
      'short' => $user->name,
      'long' => ($user->account_code != '' ? $user->account_code . ' - ' : '') . 
        $user->description
    );
  }
}

// Orders
$orderPrefix = $BF->config->get('com.b2bfront.ordering.order-id-prefix', true);
$orders = $BF->db->query();
$orders->select('*', 'bf_orders')
      ->where('`id` LIKE \'{1}%\'', str_replace($orderPrefix, '', strtoupper($search)))
      ->limit(10)
      ->execute();

if($orders->count > 0)
{
  while($order = $orders->next())
  {
    // Get user
    $user = $BF->db->getRow('bf_users', $order->owner_id);
  
    $jsonReturn['orders']['objects'][] = array(
      'id' => $order->id,
      'short' => $orderPrefix . $order->id,
      'long' => $orderPrefix . $order->id . ' - ' . $user->description
    );
  }
}
 
// Set checksum
$jsonReturn['checksum'] = md5(serialize($jsonReturn));
 
// Encode and output
print json_encode($jsonReturn);   

$BF->shutdown();

?>