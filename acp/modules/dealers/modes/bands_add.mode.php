<?php
/**
 * Module: Dealers
 * Mode: Add Discount Band
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

<h1>Add a Discount Band</h1>
<br />

<form action="./?act=dealers&mode=bands_add_do" method="post">

<div class="panel">
  <div class="title">Discount Band Information</div>
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
            <strong>Band Code</strong><br />
            A unique code for the discount band.<br />
            <span class="grey">Uppercase A-Z, 0-9</span>
          </td>
          <td class="value">
            <input name="f_code" id="f_code" type="text" style="width: 70px; text-transform: uppercase;" />
          </td>
        </tr>
                
        <tr class="last">
          <td class="key">
            <strong>Name</strong><br />
            A short name for the discount band.<br />
          </td>
          <td class="value">
            <input name="f_name" id="f_name" type="text" style="width: 200px;" />
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
      <strong>Click the button to the right to save this discount band.</strong>
    </p>
    <input class="submit ok" type="submit" style="float: right;" value="Save and Exit" />
    <br class="clear" />
  </div>
</div>

</form>