<?php
/**
 * Module: Inventory
 * Mode: Organisation Add Category
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

// Verify Permissions
if(!$BF->admin->can('categories'))
{
?>
    <h1>Permission Denied</h1>
    <br />
    <p>
      You do not have permission to use this section of the ACP.<br />
      Please ask your supervisor for more information.
    </p>
<?php

exit();

}

?>

<h1>Add Category</h1>
<br />

<script type="text/javascript">
  
  // Create the category tree
  $(function() {
    $('#category_tree').fileTree({ selectionChanged: function(r) {
        $("#f_parent").val(r);
			}, root: '0', script: '/acp/ajax/categories.ajax.php?restrict=1', selectable: true }, function(el, ob, name) {  });
  });

</script>

<form action="./?act=inventory&mode=organise_add_do" method="post">

<div class="panel">
  <div class="title">Category Information</div>
  <div class="message">
    <p>
      <span style="float: left; margin-right: 0.3em;" class="ui-icon ui-icon-info"></span>
      <strong>Required Fields</strong> - You need to complete all of the fields in this panel.
      <br class="clear" />
    </p> 
  </div>
  <div class="contents fieldset">
        
    <table class="fields">
      <tbody>
      
        <tr>
          <td class="key">
            <strong>Category Name</strong><br />
            A name to identify the category.
          </td>
          <td class="value">
            <input type="text" style="width: 250px;" name="f_name" />
          </td>
        </tr>
        
        <tr class="last">
          <td class="key">
            <strong>Parent Item Display Mode</strong><br />
            Choose how order quantities should be entered by dealers for parent items.<br />
            <span class="grey">"Table" is better for categories with items that have 2 or more variation options.</span>
          </td>
          <td class="value">
            <select name="f_parent_child_display_mode">
              <option value="dropdowns">Dropdowns</option>
              <option value="table">Table</option>
            </select>
          </td>
        </tr>
        
      </tbody>
    </table>
    
  </div>
</div>

<br />

<div class="panel">
  <div class="title">Other Information</div>
  <div class="contents fieldset">
        
    <table class="fields">
      <tbody>
        
        <tr class="last">
          <td class="key">
            <strong>Parent Category</strong><br />
            If the new category should be nested inside another, select that category here.<br />
            <span class="grey">Choosing a parent category will create a subcategory.</span>
          </td>
          <td class="value">
            <div id="category_tree"></div>
            <input type="hidden" id="f_parent" name="f_parent" value="-1" />
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
      <strong>Click the button to the right to save this category now.</strong>
    </p>
    <input class="submit ok" type="submit" style="float: right;" value="Save and Exit" />
    <br class="clear" />
  </div>
</div>

</form>