<?php
/**
 * Autocomplete SKU/Item
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

// Search for items with the current prefix
$sku = $BF->in('term');

$BF->db->select('*', 'bf_items')
           ->where('name LIKE \'{1}%\' OR sku LIKE \'{1}%\'', $sku)
           ->limit(15)
           ->execute();

           
// Buffer all
$items = array();
while($item = $BF->db->next())
{
  $items[] = array('id' => $item->id, 
                   'label' => $item->sku,
                   'value' => $item->id);
}

// Output as JSON
print json_encode($items);

?>