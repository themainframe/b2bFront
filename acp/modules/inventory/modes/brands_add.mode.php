<?php
/**
 * Module: Inventory
 * Mode: Add Brand
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

<h1>Add a Brand</h1>
<br />

<form action="./?act=inventory&mode=brands_add_do" enctype="multipart/form-data" method="post">

<div class="panel">
  <div class="title">Brand Information</div>
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
        <tr>
          <td class="key">
            <strong>Name</strong><br />
            A unique name for the brand.
          </td>
          <td class="value">
            <input name="f_name" id="f_name" type="text" style="width: 200px;" />
          </td>
        </tr>
        
        <tr>
          <td class="key">
            <strong>Primary Classification</strong><br />
            Choose the classification that this brand is associated with, if any.
          </td>
          <td class="value">
<?php

  // Query the database for Classifications
  $query = $BF->db->query();
  $query->select('*', 'bf_classifications')
        ->order('name', 'ASC')
        ->execute();
        
  // Create a UI element
  $dropDown = new DataDropDown('f_classification', $query, 'id', 'name', array('-1' => 'No Primary Classification'));
  print $dropDown->render();

?>
          </td>
        </tr>     
      
        
        <tr class="last">
          <td class="key">
            <strong>Logo</strong><br />
            Select an image to represent the brand. Must either .jpg, .gif or .png<br />
            <span class="grey">Try to choose a <abbr title="As close to a 1:1 ratio as possible.">near-square</abbr> image.  The image will be resized automatically.</span>
          </td>
          <td class="value">
            
            <input type="file" name="f_image" />
            
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
      <strong>Click the button to the right to save this brand.</strong>
    </p>
    <input class="submit ok" type="submit" style="float: right;" value="Save and Exit" />
    <br class="clear" />
  </div>
</div>

</form>