<?php
/**
 * Module: Inventory
 * Mode: Modify Classification Attributes
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

// Get the ID
$ID = $BF->inInteger('id');

// Get the row information
$BF->db->select('*', 'bf_classifications')
           ->where('id = \'{1}\'', $ID)
           ->limit(1)
           ->execute();
           
// Check the ID was valid
if($BF->db->count < 1)
{
  // Return the user to the selection interface
  header('Location: ./?act=inventory&mode=classifications');
  exit();
}

// Retrieve the row
$row = $BF->db->next();

?>

<h1 style="float: left">Add a Classification Attribute to <?php print $row->name; ?></h1>
<h1 style="float: right; color: #afafaf;">
  Attributes for
  <a href="./?act=inventory&mode=classifications_modify&id=<?php print $row->id; ?>" style="color: #afafaf;">
    <?php print $row->name; ?>
  </a>
</h1>

<br class="clear" />

<br />

<form action="./?act=inventory&mode=classifications_modify_attributes_add_do" method="post">
<input type="hidden" name="f_id" value="<?php print $row->id; ?>" />

<div class="panel">
  <div class="title">Classification Attribute Information</div>
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
            A name for the classification attribute.
          </td>
          <td class="value">
            <input name="f_name" id="f_name" value="" type="text" style="width: 200px;" />
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
      <strong>Click the button to the right to save the attribute.</strong>
    </p>
    <input class="submit ok" type="submit" style="float: right;" value="Save and Exit" />
    <input onclick="history.back();" class="submit bad" type="button" style="float: right; margin-right: 10px;" value="Cancel and Exit" />
    <br class="clear" />
  </div>
</div>

</form>