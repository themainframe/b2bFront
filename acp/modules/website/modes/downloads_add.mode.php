<?php
/**
 * Module: Website
 * Mode: Add Download
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

<h1>Add a Download</h1>
<br />

<form action="./?act=website&mode=downloads_add_do" enctype="multipart/form-data" method="post">
<input type="hidden" name="f_id" id="f_id" value="<?php print $pageRow->id; ?>" />

<div class="panel">
  <div class="title">File Information</div>
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
            <strong>Title</strong><br />
            A unique title for the download.<br />
            <span class="grey">This value is distinct from the filename.</span>
          </td>
          <td class="value">
            <input type="text" name="f_name" id="f_name" style="width: 200px;" />
          </td>
        </tr>
        
        <tr class="last">
          <td class="key">
            <strong>Select File</strong><br />
            Select a file from your computer.
          </td>
          <td class="value">
            <input type="file" name="f_file" id="f_file" />
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
      <strong>Click the button to the right to upload the file and create the download.</strong>
    </p>
    <input class="submit ok" type="submit" style="float: right;" value="Upload, Save and Exit" />
    <input onclick="window.location='./?act=website&mode=pages';" class="submit bad" type="button" style="float: right; margin-right: 10px;" value="Cancel and Exit" />
    <br class="clear" />
  </div>
</div>

</form>