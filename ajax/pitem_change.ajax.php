<?php
/**
 * Parent Item Child ID Loader
 *
 * Given a hash of variation application values; provides details
 * of the appropriate parent item.
 *
 * AJAX Responder
 *
 * @copyright 2010-2011 Damien Walsh
 * @package b2bfront
 * @version 1.0
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
}

// Set content type of output
header('Content-Type: text/json');

// Unpack the IDs
$IDList = Tools::removeEmptyEntries(Tools::unCSV(urldecode($BF->in('hash'))));

// Find all child item variations
$childItemVariations = $BF->db->query();
$childItemVariations->select('*', 'bf_parent_item_variation_data')
                    ->where('`parent_item_id` = \'{1}\'', $BF->inInteger('id'))
                    ->execute();
            
// Examine each and find the represented item
$itemCollection = array();
while($childItemVariation = $childItemVariations->next())
{
  $itemCollection[$childItemVariation->item_id][] = $childItemVariation->value;
}

// Find the item that matches the requested one
foreach($itemCollection as $itemID => $item)
{
  foreach($IDList as $variationValue)
  {
    if(!in_array($variationValue, $item))
    {
      continue 2;
    }
  }
  
  // Found the item
  $childItemID = $itemID;
}

// Obtain the Item to load images
$item = new BOMItem($childItemID, $BF);

// Grab images
$imageIDs = array();
foreach($item->images as $image)
{
  $imageIDs[] = $image->id;
}

// Build the data set to provide
$output = array(
  'images' => $imageIDs,
  'id' => $childItemID,
  'sku' => $item->sku,
  'stock_free' => ($item->stock_free > 100 ? 100 : 
                  ($item->stock_free < 1 ? 0 : $item->stock_free)),
  'barcode' => $item->barcode,
  'trade_price' => $item->trade_price,
  'my_price' => $item->myPrice,
  'rrp' => $item->rrp_price,
  'pro_net_qty' => $item->pro_net_qty,
  'basket' => $BF->cart->get($childItemID),
  'visible' => $item->visible,
  'stock_date' => ($item->stock_date ? date('d/m/Y', $item->stock_date) : '')
);

// Output as JSON
print json_encode($output);

// Exit
$BF->shutdown();

?>