<?php
/**
 * Show relevant dealers - Inventory/Requests
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

sleep(1);

// Dealers
$dealers = array();

// Get the ID of the item to look for
$itemID = $BF->inInteger('id');

// Find requesters
$getDealers = $BF->db->query();
$getDealers->text('SELECT * FROM `bf_users`, `bf_user_stock_notifications` WHERE `bf_user_stock_notifications`.`user_id` = `bf_users`.`id` AND `bf_user_stock_notifications`.`item_id` = \'' . $itemID . '\'');
$getDealers->execute();

$dealers = array();
while($dealer = $getDealers->next())
{
  $dealers[] = array(
    'name' => $dealer->name,
    'description' => $dealer->description,
    'type' => ($dealer->type == 'sms' ? 'SMS' : 'Email')
  );
}

print json_encode($dealers);
$BF->shutdown();

?>