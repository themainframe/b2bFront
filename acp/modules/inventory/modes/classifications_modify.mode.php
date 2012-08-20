<?php
/**
 * Module: Inventory
 * Mode: Modify Classification
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

// Count attributes
$attributeCount = $BF->db->query();
$attributeCount->select('`id`', 'bf_classification_attributes')
               ->where('`classification_id` = \'{1}\'', $row->id)
               ->execute();
               
$attributes = $attributeCount->count;

// Define confirmation JS for Modify Attributes link
$modifyAttributesJS = 'confirmation(\'Are you sure you want to move away from this page?<br />' . 
  		                'You will lose any unsaved changes.\', function() { window.location=\'' .
                      Tools::getModifiedURL(
                        array('mode' => 'classifications_modify_attributes', 'id' => $row->id)
                      ) . '\'; })';
?>

<h1><?php print $row->name; ?></h1>
<br />

<form action="./?act=inventory&mode=classifications_modify_do" method="post">
<input type="hidden" name="f_id" value="<?php print $row->id; ?>" />

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
            <input name="f_name" id="f_name" value="<?php print $row->name; ?>" type="text" style="width: 200px;" />
          </td>
        </tr>
      </tbody>
    </table>
  </div>
</div>

<br />

<div class="panel">
  <div class="title">Classification Attributes</div>
  <div class="contents" style="padding: 0px 20px 0px 20px;">
    <table class="fields">
      <tbody>
        <tr class="last">
          <td class="key">
            <strong>Modify Attributes</strong><br />
            Modify Attributes associated with this Classification.
          </td>
          <td class="value">
            <span class="button">
              <a href="#" onclick="<?php print $modifyAttributesJS; ?>">
                <span class="img" style="background-image:url(/acp/static/icon/price-tag--pencil.png)">&nbsp;</span>
                Modify Attributes...
              </a>
            </span> &nbsp; &nbsp;
            
            <span class="grey">
              <?php
                
                if($attributes > 0)
                {
                  print 'This classification has ' . $attributes . ' attribute' . Tools::plural($attributes) . '.';
                }
                else
                {
                  print 'This classification does not have any attributes.';
                }
                
              ?>
            </span>
            
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
      <strong>Click the button to the right to save changes to this classification.</strong>
    </p>
    <input class="submit ok" type="submit" style="float: right;" value="Save and Exit" />
    <br class="clear" />
  </div>
</div>

</form>