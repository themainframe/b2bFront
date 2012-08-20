<?php
/**
 * Multi/Single Item Select Loader
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
$search = $BF->in('term');
$category = $BF->inInteger('category');
$classification = $BF->inInteger('classification');
$label = $BF->inInteger('label');

// Empty?
if($search == '' && $category == -1 && $classification == -1 && $label == -1 && $BF->in('ids') == '')
{
  print json_encode(array());
  exit();
}

usleep(100000);

// Filter using WHERE clause
$whereClauseFilter = '';

//
// Labels
//

$whereClauseFilter = '';
$currentLabelID = $BF->inInteger('label');

if($currentLabelID != -1)
{    
  // Find applications
  $labelApplications = $BF->db->query();
  $labelApplications->select('*', 'bf_item_label_applications')
                    ->where('`item_label_id` = \'{1}\'', $currentLabelID)
                    ->execute();

  // Any items?
  if($labelApplications->count > 0)
  {  
    $labelApplicationsHash = $labelApplications->getInHash('item_id');
    
    // Constrain search to items
    $whereClauseFilter .= ' AND (`id` IN (' . $labelApplicationsHash . '))';
  }
  else
  {
    $whereClauseFilter .= ' AND 1=0';
  }
}

//
// Term
//

$whereClause = '1 = 1';
$whereClauseValue = '-1';

// If term is defined
if($search != '')
{
  $whereClause = '(`sku` LIKE \'' . $BF->in('term') . '%\' OR `name` LIKE \'%' . $BF->in('term') . '%\')'; 
}

// Category?
$categoryID = '-1';
if($BF->in('category') && $BF->inInteger('category') != -1)
{
  $categoryID = $BF->inInteger('category');
  $whereClause .= ' AND category_id =  ' . $categoryID;

}

// Classification?
$classificationID = '-1';
if($BF->in('classification') && $BF->inInteger('classification') != -1)
{
  $classificationID = $BF->inInteger('classification');
  $whereClause .= ' AND classification_id = ' . $classificationID;
}

//
// Create a query
//

$query = $BF->db->query();

// IDs override?
if($BF->in('ids') != '')
{
  $query->select('id, sku, name, trade_price, pro_net_price, pro_net_qty,' . 
                 'rrp_price, cost_price, stock_free, visible, parent_item_id', 'bf_items')
        ->where('`id` IN ({1})', urldecode($BF->in('ids')));
}
else
{
  $query->select('id, sku, name, trade_price, pro_net_price, pro_net_qty,' . 
                 'rrp_price, cost_price, stock_free, visible, parent_item_id', 'bf_items')
        ->where($whereClause . $whereClauseFilter);
}    

// Execute
$query->execute();

// Build output
$itemCollection = array();

while($item = $query->next())
{
  $itemCollection[] = array(
    'id' => $item->id,
    'sku' => $item->sku,
    'name' => $item->name
  );
}

print json_encode($itemCollection);

?>