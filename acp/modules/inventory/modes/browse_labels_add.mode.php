<?php
/**
 * Module: Inventory
 * Mode: Add Label
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

$labelColourParser = new PropertyList();
$labels = $labelColourParser->parseFile(
  BF_ROOT . '/acp/definitions/inventory_label_colours.plist');

// Sort
ksort($labels);

// Failure?
if(!$labelColourParser)
{
  $BF->log('Unable to load /acp/definitions/inventory_label_colours.json');
}

?>

<h1>Add Label</h1>
<br />

<form action="./?act=inventory&mode=browse_labels_add_do" method="post">

<div class="panel">
  <div class="title">Label Information</div>
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
            <strong>Label Name</strong><br />
            A name to identify the label.
          </td>
          <td class="value">
            <input type="text" style="width: 250px;" name="f_name" />
          </td>
        </tr>
        
        <tr class="last">
          <td class="key">
            <strong>Colour</strong><br />
            Choose a colour to make the label stand out.
          </td>
          <td class="value">
            <select name="f_colour">
              <?php
                
                foreach($labels as $labelName => $labelColour)
                {
                  print '              <option value="' . $labelName . '" style="background-color: ' . $labelColour['colour'] . ';">' . 
                        $labelColour['name'] . '</option>' . "\n";
                }
                
              ?>
            </select>
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
      <strong>Click the button to the right to save this Label now.</strong>
    </p>
    <input class="submit ok" type="submit" style="float: right;" value="Save and Exit" />
    <br class="clear" />
  </div>
</div>

</form>