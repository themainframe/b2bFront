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

<h1 style="float: left">Classification Attributes for <?php print $row->name; ?></h1>
<h1 style="float: right; color: #afafaf;">
  Attributes for
  <a href="./?act=inventory&mode=classifications_modify&id=<?php print $row->id; ?>" style="color: #afafaf;">
    <?php print $row->name; ?>
  </a>
</h1>

<br class="clear" />

<br />

<form action="./?act=inventory&mode=classifications_modify&id=<?php print $row->id; ?>" method="post">
<input type="hidden" name="f_id" value="<?php print $row->id; ?>" />

<div class="panel">
  <div class="title">Classification Attributes</div>
  <div class="contents">
    
    <h3>Modify Attributes</h3>
    <p>
      If you remove an attribute here, all data associated with that attribute will be lost.<br />
      You can rename attributes by clicking their names.
    </p>
    
    <br />
    <span class="button">
      <a href="./?act=inventory&mode=classifications_modify_attributes_add&id=<?php print $row->id; ?>">
        <span class="img" style="background-image:url(/acp/static/icon/price-tag--plus.png)">&nbsp;</span>
        New Attribute...
      </a>
    </span>
  
    <br /><br />
  
  </div>
</div>
    
<br />
    
<?php

  // Find existing attributes
  $attributes = $BF->db->query();
  $attributes->select('*', 'bf_classification_attributes')
             ->where('`classification_id` = \'{1}\'', $row->id);

  // Render a DataTable
  // Define a tool set HTML
  $confirmationJS = 'confirmation(\'Really remove this attribute?<br />' . 
                    'All associated data will be lost.\', function() { window.location=\'' .
                    Tools::getModifiedURL(array('mode' => 'classifications_modify_attributes_remove_do')) . 
                    '&clid=' . $row->id . '&id={id}\'; })';
  
  // Buttons
  $toolSet  = "\n";
  $toolSet .= '<a onclick="' . $confirmationJS . '" class="tool" title="Remove">' . "\n";
  $toolSet .= '  <img src="/acp/static/icon/cross-circle.png" alt="Remove" />' . "\n";
  $toolSet .= 'Remove</a>' . "\n";
  
  // Create a data table view to show the classifications
  $classifications = new DataTable('ca1', $BF, $attributes);
  $classifications->setOption('alternateRows');
  $classifications->setOption('noDataText', 'This classification does not have any attributes.');
  $classifications->addColumns(array(
                          array(
                            'dataName' => 'name',
                            'niceName' => 'Attribute Name',
                            'options' => array(
                                           'editable' => true,
                                           'editableTable' => 'bf_classification_attributes',
                                           'editableEmptyAction' => 'revert'
                                         )
                          ),
                          array(
                            'dataName' => '',
                            'niceName' => 'Actions',
                            'options' => array('fixedOrder' => true),
                            'content' => $toolSet,
                            'css' => array(
                                       'width' => '75px'
                                     )
                          )
                        ));
  
  // Render & output content
  print $classifications->render();
?>

<br />

<div class="panel" style="border: 1px solid #50954b">
  <div class="contents">
    <p style="padding: 7px 0px 0px 5px; float: left; color: #50954b;">
      <strong>Click the button to the right to save changes to the attributes.</strong>
    </p>
    <input class="submit ok" type="submit" style="float: right;" value="Save and Exit" />
    <br class="clear" />
  </div>
</div>

</form>