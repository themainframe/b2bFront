<?php
/**
 * Module: Inventory
 * Mode: Browse Multi Tag 
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


// Load the item to modify
$row = $BF->inInteger('id');

// Query for it
$BF->db->select('*', 'bf_items')
       ->where('id = \'{1}\'', $BF->inInteger('id'))
       ->limit(1)
       ->execute();
    
// Success?
if($BF->db->count != 1)
{
  // Failed
  header('Location: ./?act=inventory&mode=browse');
  exit();
}

$row = $BF->db->next();

?>

<h1>Parentise <?php print $row->sku; ?></h1>
<br />

<div class="panel">
  <div class="contents">
    
    <p>
      
      <strong>About Parentising</strong>
      <br /><br />
      
      Parentising converts a standard item in to a parent item.<br />
      Refer to <a href="./?act=inventory&mode=add" title="Add" class="new" target="_blank">this section</a>
      for information on the differences between parent and standard items.

      <br /><br />
      
      This process involves making the item more generic; then creating a set of variations
      that define how its child items differ from one another.
      
      <br /><br />
      
      After the parent item is created, you will be able to create child items for it.
      
    </p>

  </div>
</div>

<br />

<form method="post" action="./?act=inventory&mode=browse_parentise_do">
<input type="hidden" name="f_id" value="<?php print $row->id; ?>" />

<div class="panel">
  <div class="title">Parent Item Information</div>
  <div class="message">
    <p>
      <span style="float: left; margin-right: 0.3em;" class="ui-icon ui-icon-info"></span>
      <strong>Required Fields</strong> - You need to complete all of the fields in this panel.
      <br class="clear" />
    </p> 
  </div>
  <div class="contents fieldset">
    
    <!-- Basic Information -->
  
    <table class="fields">
      <tbody>
      
        <tr>
          <td class="key">
            <br /><br />
            <strong>Virtual SKU</strong><br />
            A SKU that will represent the parent item.<br />
            This value will <em>never</em> be displayed outside of the <abbr title="Admin Control Panel">ACP</abbr>.
            <br /><br />
            <span class="grey">The SKU should not include any variation hints.</span>
            <br /><br /><br />
          </td>
          <td class="value">
            <span class="grey"><?php print $row->sku; ?></span><br /><br />
            <span style="background: #fff; border: 1px solid #a2a2a2; padding: 3px 3px 3px 0px; margin: 10px 0 0 0;">
              <input type="text" name="f_sku" id="f_sku" style="border: 0; width: 70px;" />
              &nbsp; -PAR
            </span>
          </td>
        </tr>
        
        <tr>
          <td class="key">
            <br /><br />
            <strong>Name</strong><br />
            The name of this parent item.<br />
            <br />
            <span class="grey">The existing name probably isn't generic enough.</span><br />
            <span class="grey">It should not describe any of the variations of the item.</span><br /><br /><br />
          </td>
          <td class="value">
            <span class="grey"><?php print $row->name; ?></span><br /><br />
            <input type="text" name="f_name" style="width: 250px;" />
          </td>
        </tr>

      </tbody>
    </table>
    
  </div>
  
</div>

<br />

<div class="panel">
  <div class="title">Parent Item Variation Options</div>
  <div class="contents">
    
    <h3>Variation Options</h3>
    <p>
      This item will act as a template for its children.<br />
      You can add variation options to declare the ways in which this item's children are different from one another.
      <br /><br />
      
      For example, for a pair of gloves, you might add Size and Colour as variations to show
      that the gloves come in various combinations of size and colour.
    </p>
    
    <br />
    
<?php

  // Create a UI element to handle the creation of default attributes
  $attributeEditor = new FormListBuilder('variations', array(), $BF);
  $attributeEditor->setOption('valueDescription', 'New Variation Option Name:');
  $attributeEditor->setOption('listDescription', 'Parent Item Variation Options');
  $attributeEditor->setOption('emptyList', 'Use the field above to add ' .
                              'variation options to the parentised item.');
  print $attributeEditor->render();

?>
    
  </div>
</div>

<br />  

<div class="panel" style="border: 1px solid #50954b">
  <div class="contents">
    <p style="padding: 7px 0px 0px 5px; float: left; color: #50954b;">
      <strong>Click one of the buttons to the right to proceed.</strong>
    </p>
    <input class="submit ok" type="submit" style="float: right;" value="Continue" />
    <input onclick="history.back()" class="submit bad" type="button" style="float: right; margin-right: 10px;" value="Cancel and Exit" />
    <br class="clear" />
  </div>
</div>

</form>