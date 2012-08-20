<?php
/**
 * Module: Inventory
 * Mode: Add Classification
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

?>

<h1>Add a Classification</h1>
<br />

<form action="./?act=inventory&mode=classifications_add_do" method="post">

<div class="panel">
  <div class="title">Classification Information</div>
  <div class="message">
    <p>
      <span style="float: left; margin-right: 0.3em;" class="ui-icon ui-icon-info"></span>
      <strong>Required Fields</strong> - You need to complete all of the fields in this panel.
      <br class="clear" />
    </p> 
  </div>
  <div class="contents" style="padding: 0px 20px 0px 20px;">
    <table class="fields">
      <tbody>
        <tr class="last">
          <td class="key">
            <strong>Name</strong><br />
            A unique name for the classification.
          </td>
          <td class="value">
            <input name="f_name" type="text" style="width: 200px;" />
          </td>
        </tr>
      </tbody>
    </table>
  </div>
</div>


<br />

<div class="panel">
  <div class="title">Classification Attributes</div>
  <div class="contents">
    
    <h3>Attributes</h3>
    <p>
      You can create a template of attributes associated with this classification.<br />
      These will automatically appear in on the Add Item screen when adding an item of this classification.<br />
      This saves time and improves item consistency.
    </p>
    
    <br />
    
<?php

  // Create a UI element to handle the creation of default attributes
  $attributeEditor = new FormListBuilder('attributes', array(), $BF);
  $attributeEditor->setOption('valueDescription', 'New Attribute Name:');
  $attributeEditor->setOption('listDescription', 'Classification Attributes');
  $attributeEditor->setOption('emptyList', 'Use the field above to add ' .
                              'attributes to the classification.');
  print $attributeEditor->render();

?>
    
  </div>
</div>


<br />


<div class="panel" style="border: 1px solid #50954b">
  <div class="contents">
    <p style="padding: 7px 0px 0px 5px; float: left; color: #50954b;">
      <strong>Click the button to the right to save this classification.</strong>
    </p>
    <input class="submit ok" type="submit" style="float: right;" value="Save and Exit" />
    <br class="clear" />
  </div>
</div>

</form>