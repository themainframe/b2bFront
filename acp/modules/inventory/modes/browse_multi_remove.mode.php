<?php
/**
 * Module: Inventory
 * Mode: Browse Multi Remove Prompt
 * 
 * @copyright 2010-2011 Damien Walsh
 * @package b2bfront
 * @version 1.0
 * @author Damien Walsh
 */

// Context check
if(!defined("BF_CONTEXT_ADMIN") || !defined("BF_CONTEXT_MODULE"))
{
  exit();
}

// Count all items
$items = $BF->in('dv_inventory');
if(!$items)
{
  // Return to inventory
  header('Location: ./?act=inventory');
  exit();
}

// Split
$itemsArray = explode(',', $items);

// Get Checkboxes
foreach($itemsArray as $key => $item)
{
  if($BF->inInteger('inventory_' . $item) != 1)
  {
    // Not Selected
    unset($itemsArray[$key]);
  }
}

// Count 
$itemsCount = count($itemsArray);

// Empty set?
if($itemsCount == 0)
{
  // Return to inventory
  $BF->admin->notifyMe('Instruction', 'Select one or more items first.', 'property.png');
  header('Location: ./?act=inventory');
  exit();
}

?>

<h1>Multiple Item Removal</h1>
<br />

<div class="panel">
  <div class="title">Important Information</div>
  <div class="warning">
    <span style="float: left; margin-right: 0.3em;" class="ui-icon ui-icon-alert"></span>
    <strong>Important</strong> - You are about to make critical changes to the Inventory.
    <br class="clear">
  </div>
  <div class="contents">
    
    <h3>Are you sure you want to remove <?php print $itemsCount; ?> item<?php Tools::plural($itemsCount); ?>?</h3>
    
    <br />

    <br />
    <span class="button">
      <a href="./?act=inventory&mode=browse_multi_remove_do&dv_inventory=<?php print Tools::CSV($itemsArray); ?>">
        <span class="img" style="background-image:url(/acp/static/icon/tick-circle.png)">&nbsp;</span>
        Yes
      </a>
    </span>
    
    <span class="button">
      <a href="javascript: history.back()">
        <span class="img" style="background-image:url(/acp/static/icon/cross-circle.png)">&nbsp;</span>
        Cancel
      </a>
    </span>
    
    <br /><br />
  </div>
</div>

<br />