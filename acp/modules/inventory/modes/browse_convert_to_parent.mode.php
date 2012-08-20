<?php
/**
 * Module: Inventory
 * Mode: Browse Convert to Parent Item
 * 
 * @copyright 2010-2011 Damien Walsh
 * @package b2bfront
 * @version 1.0
 * @author Damien Walsh
 */

// Context check
if(!defined('BF_CONTEXT_ADMIN') || !defined('BF_CONTEXT_MODULE'))
{
  exit();
}

// Load the item to modify
$itemID = $BF->inInteger('id');

// Empty? Try f_id (Perhaps last submit failed)
if(!$itemID)
{
  $itemID = $BF->inInteger('f_id');
}

// Query for it
$BF->db->select('*', 'bf_items')
       ->where('id = \'{1}\'', $itemID)
       ->limit(1)
       ->execute();
    
// Success?
if($BF->db->count != 1)
{
  // Failed
  header('Location: ./?act=inventory&mode=browse');
  exit();
}

$itemRow = $BF->db->next();

// Is this a child item?
$isChild = ($itemRow->parent_item_id != -1);

// Get parent if required
if($isChild)
{
  $parentItemRow = $BF->db->getRow('bf_parent_items', 
    $itemRow->parent_item_id);
}


?>

<h1>Convert <?php print $itemRow->sku; ?> to a Parent Item</h1>
<br />

<div class="panel">
  <div class="contents">
    
    <h3>About Converting Standard Items to Parent Items</h3>
    
    <p>
    
      In this view, you can convert a standard item to a parent item.<br />
    
    </p>

  </div>
</div>

<br />

<div class="panel" style="border: 1px solid #50954b">
  <div class="contents">
    <p style="padding: 7px 0px 0px 5px; float: left; color: #50954b;">
      <strong>Click the button to the right to confirm these changes now.</strong>
    </p>
    <input class="submit ok" type="submit" style="float: right;" value="Create Items" />
    <br class="clear" />
  </div>
</div>

</form>