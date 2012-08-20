<?php
/**
 * Module: Inventory
 * Mode: Parent Items - Create Bulk Children
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

// Load parent data
$parentItemID = $BF->inInteger('id');
$BF->db->select('*', 'bf_parent_items')
       ->where('`id` = \'{1}\'', $parentItemID)
       ->limit(1)
       ->execute();
       
// Redirect if this cannot be found
if($BF->db->count == 0)
{
  $BF->go('./?act=inventory&mode=browse');
}

// Get parent
$parentItem = $BF->db->next();

// Load variation options for the parent too
$variationOptions = $BF->db->query();
$variationOptions->select('*', 'bf_parent_item_variations')
                 ->where('`parent_item_id` = \'{1}\'', $parentItemID)
                 ->order('name', 'asc')
                 ->execute();

?>

<script type="text/javascript">

  var rows = 1;

  $(function() {
  
    // Bind events
    $('#newChildRowButton').click(function() {
      addNewChildRow(rows);
      rows ++;
    });
    
    addNewChildRow(rows);
    rows ++;
  
  });

  function addNewChildRow(rowCount)
  {
    // Clone the template row
    var newRow = $('#template_row').clone()
                                   .css('display', 'table-row');
                      
    $.each(newRow.find('input'), function(i, e) {
      // Rename each
      var currentName = $(e).attr('name');
      
      // Get last digit
      var currentNameParts = currentName.split('_');
      var lastDigit = currentNameParts.pop();
      
      // New Value      
      currentNameParts.push(rowCount);
      
      // Set new name
      $(e).attr('name', currentNameParts.join('_'));
      
    });
    
    // Set ID
    $(newRow).attr('id', 'row' + rowCount);
    
    // Add a remove button too, if this isn't row #1
    if(rowCount != 1)
    {
      $('<img />').attr('src', '/acp/static/icon/cross-circle.png')
                  .css('margin-left', '8px')
                  .css('position', 'relative')
                  .css('top', '2px')
                  .css('cursor', 'pointer')
                  .click(function() {
                    // Remove this row
                    $('#row' + rowCount).remove();
                  })
                  .insertAfter($(newRow).find('input').last());
    }
        
    // Add the new row
    newRow.insertBefore('#new_row');
    
    // Focus SKU box
    $('input[name=sku_' + (rowCount) + ']')
      .animate({'background-color': '#a8ffa8'}, 1000)
      .animate({'background-color': '#fff'}, 1000)
      .focus()
      .val($('input[name=sku_' + (rowCount) + ']').val());

    // Update global row count
    $('#rowCount').val(rowCount);
  }

</script>

<h1>Create Child Items for <?php print $parentItem->sku; ?></h1>
<br />

<div class="panel">
  <div class="contents">
    
    <h3>About Creating Child Items</h3>
    
    <p>
    
      In this view, you can create multiple items instantly using a parent item as a template.<br />
      You need to add a new SKU and the variations associated with it for each child item.<br /><br />
      
      If other properties vary too, you can update those in the relevant fields too.
    
    </p>

  </div>
</div>

<br />

<form action="./?act=inventory&mode=browse_createchildren_do" method="post" id="createChildrenForm">

<input type="hidden" id="rowCount" name="f_row_count" value="1" />
<input type="hidden" id="parentItemID" name="f_parent_item_id" value="<?php print $BF->inInteger('id'); ?>" />

<table class="data">
  <thead>
    <tr class="header">
      <td style="width: 80px;">Child SKU</td>
      <td style="width: 100px;">Name</td>
      
<?php

  // Show each of the variation options
  while($variationOption = $variationOptions->next())
  {
  
?>
      <td style="width: 100px;">
        <?php print $variationOption->name; ?>
      </td>
<?php

  }
  
  // Reset Variation Options listing
  $variationOptions->rewind();
  
?>

      <!-- Additional Options -->
      <td style="width: 100px;">Trade Price</td>
      <td style="width: 100px;">Pro Net Price</td>
      <td style="width: 100px;">RRP/MSRP</td>
    
    </tr>
  </thead>
  <tbody id="variation_rows">
  
  
    <!-- Template Row First -->
    
    <tr id="template_row" style="display:none;">
      <td>  
        <input name="sku_0" type="text" style="width: 70px;" value="<?php print Tools::removeParentTag($parentItem->sku); ?>" />
      </td>
      <td style="width: 100px;">
        <input name="name_0" value="<?php print $parentItem->name; ?>" type="text" />
      </td>
      
      
<?php

  // Show each of the variation options
  while($variationOption = $variationOptions->next())
  {
  
?>
      <td style="width: 100px">
        <input name="var<?php print $variationOption->id; ?>_0" type="text" />
      </td>
<?php

  }
  
  // Reset Variation Options listing
  $variationOptions->rewind();
  
?>
      <td style="width: 100px;">
        <input name="trade_price_0" value="<?php print $parentItem->trade_price; ?>" type="text" style="width: 50px;" />
      </td>
      <td style="width: 100px;">
        <input name="pro_net_price_0" value="<?php print $parentItem->pro_net_price; ?>" type="text" style="width: 50px;" />
      </td>
      <td style="width: 100px;">
        <input name="rrp_price_0" value="<?php print $parentItem->rrp_price; ?>" type="text" style="width: 50px;" />
      </td>

    </tr>

    <!-- Existing children -->
    
<?php

  // Find existing child items
  $childItems = $BF->db->query();
  $childItems->select('*', 'bf_items')
             ->where('`parent_item_id` = \'{1}\'', $parentItemID)
             ->execute();
                       
  // Get hash
  $childItemsHash = $childItems->getInHash();
                       
  // Precache variation data
  $childItemsVariationData = $BF->db->query();
  $childItemsVariationData->select('*', 'bf_parent_item_variation_data')
                          ->whereInHash($childItemsHash, 'item_id')
                          ->execute();
                          
  while($variationDataItem = $childItemsVariationData->next())
  {
    $variations[$variationDataItem->item_id]
      [$variationDataItem->parent_item_variation_id] = $variationDataItem->value;
  }
                       
  while($childItem = $childItems->next())
  {
  
?>
    <tr style="height: 30px;">
      <td class="existing_child">  
        <?php print $childItem->sku; ?>
      </td>
      <td class="existing_child">
        <?php print $childItem->name; ?>
      </td>

<?php

  // Show each of the variation options
  while($variationOption = $variationOptions->next())
  {
  
?>
      <td class="existing_child" style="width: 100px;">
        <?php print $variations[$childItem->id][$variationOption->id]; ?>
      </td>
<?php

  }
  
  // Reset Variation Options listing
  $variationOptions->rewind();
  
?>

      <td class="existing_child" style="width: 100px;">
        <?php print $childItem->trade_price; ?>
      </td>
      <td class="existing_child" style="width: 100px;">
        <?php print $childItem->pro_net_price; ?>
      </td>
      <td class="existing_child" style="width: 100px;">
        <?php print $childItem->rrp_price; ?>
      </td>

    </tr>

<?php
  
  }

?>
    
    <tr id="new_row">
      <td class="newrow" colspan="<?php print $variationOptions->count + 5;?>">  
        <span class="button" id="newChildRowButton">
          <a href="#">
            <span class="img" style="background-image:url(/acp/static/icon/plus-circle.png)"></span>
              Add Another Child Item...
          </a>
        </span>
      </td>
    </tr>
    
    
  </tbody>
</table>

<br />


<div class="panel" style="border: 1px solid #50954b">
  <div class="contents">
    <p style="padding: 7px 0px 0px 5px; float: left; color: #50954b;">
      <strong>Click the button to the right to create these items now.</strong>
    </p>
    <input class="submit ok" type="submit" style="float: right;" value="Create Items" />
    <br class="clear" />
  </div>
</div>

</form>