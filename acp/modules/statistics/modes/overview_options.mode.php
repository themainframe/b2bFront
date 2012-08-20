<?php
/**
 * Module: Statistics
 * Mode: Overview Advanced Options
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

<h1>Statistics Options</h1>
<br />

<form action="./?act=website&mode=pages_add_do" enctype="multipart/form-data" method="post">
<input type="hidden" name="f_id" id="f_id" value="<?php print $pageRow->id; ?>" />

<div class="panel">
  <div class="title">Statistics Options</div>
  <div class="contents fieldset">
    
    <!-- Basic Information -->
  
    <table class="fields">
      <tbody>
      
        <tr class="last">
          <td class="key">
            <br />
            
            <strong>Clear Statistics History</strong><br />
            Delete all recorded statistical history from the system.<br />
            This action cannot be undone without a backup.<br /><br />
            
            <span class="grey">Data collected already this period will be preserved.</span>
            
            <br /><br />
          </td>
          <td class="value">
            <span class="button">
              <a href="./?act=statistics&mode=overview_clear_do">
                <span class="img" style="background-image:url(/acp/static/icon/cross-circle.png)"></span>
                &nbsp;Clear Statistics History
              </a>
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
      <strong>Click the button to the right to return to the statistics overview.</strong>
    </p>
    <input onclick="window.location='./?act=statistics';" class="submit" type="button" style="float: right;" value="Go Back" />
    <br class="clear" />
  </div>
</div>

</form>