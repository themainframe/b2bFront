<?php
/**
 * Module: Inventory
 * Mode: Browse Multi Organise
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

<script type="text/javascript">

  $(function() {
  
    // Start the file tree object
    $('#category_tree').fileTree({ selectionChanged: function(r, cat, subcat) {
        $('#f_category').val(cat);
        $('#f_subcategory').val(subcat);
			}, root: '0', script: '/acp/ajax/categories.ajax.php', selectable: true }, function(el, ob, name) { });
  
  });

</script>

<h1>Multiple Item Categorisation</h1>
<br />

<div class="panel">
  <div class="contents">
  
    <p>
      <strong>About Multiple Item Categorisation</strong>
      
      <br /><br />
    
      Please choose a new category and/or subcategory below.<br />
      Once you have selected a new category and/or subcategory for the items,
      click Save and Exit to apply the change.
    </p>

  </div>
</div>

<br />

<form method="post" action="./?act=inventory&mode=browse_multi_organise_do">
<input type="hidden" name="dv_inventory" value="<?php print Tools::CSV($itemsArray); ?>" />

<div class="panel">
  <div class="title">Category</div>
  <div class="contents fieldset">
  
    <table class="fields">
      <tbody>
    
        <tr class="last">
          <td class="key">
            <strong>Category</strong><br />
            The category/subcategory into which the items should be placed.
          </td>
          <td class="value">
            <div id="category_tree"></div>
            <input type="hidden" name="f_category" id="f_category" value="-1" />
            <input type="hidden" name="f_subcategory" id="f_subcategory" value="-1" />
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