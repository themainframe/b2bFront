<?php
/**
 * Module: Inventory
 * Mode: Browse Multi Brand 
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

<h1>Multiple Item Branding</h1>
<br />

<div class="panel">
  <div class="contents">
    
    <p>
      <strong>About Multiple Item Branding</strong>
      
      <br /><br />
    
      Select a brand for the selected items and then click Save and Exit to apply the changes.
    </p>

  </div>
</div>

<br />

<form method="post" action="./?act=inventory&mode=browse_multi_brand_do">
<input type="hidden" name="dv_inventory" value="<?php print Tools::CSV($itemsArray); ?>" />

<div class="panel">
  <div class="title">Brand</div>
  <div class="contents fieldset">
  
    <table class="fields">
      <tbody>
    
        <tr class="last">
          <td class="key">
            <strong>Brand</strong><br />
            The new brand of the selected items.
          </td>
          <td class="value">
<?php

  // Query the database for Brands
  $query = $BF->db->query();
  $query->select('*', 'bf_brands')
        ->order('name', 'ASC')
        ->execute();
        
  // Create a UI element
  $dropDown = new DataDropDown('f_brand', $query, 'id', 'name', array('-1' => 'Default'));
  print $dropDown->render();

?>

          </td>
        </tr>
        
      </tbody>
    </table>
  </div>
</div>

<br />

<div class="panel" style="border: 1px solid #50954b">
  <div class="contents">
    <p style="padding: 7px 0px 0px 5px; float: left; color: #50954b;">
      <strong>Click one of the buttons to the right to proceed.</strong>
    </p>
    <input class="submit ok" type="submit" style="float: right;" value="Save and Exit" />
    <input onclick="history.back()" class="submit bad" type="button" style="float: right; margin-right: 10px;" value="Cancel and Exit" />
    <br class="clear" />
  </div>
</div>

</form>
