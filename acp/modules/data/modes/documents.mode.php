<?php
/**
 * Module: Data
 * Mode: Documents
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

<h1>Create Documents</h1>
<br />

<div class="panel">
  <div class="contents" style="">
    <h3>About Creating Documents</h3>
    <p>
      This feature allows you to provide b2bFront with a template document and generate multiple copies for each Inventory item.<br />
      This procedure works in a similar way to <a href="http://en.wikipedia.org/wiki/Mail_merge" class="new" target="_blank">Mail Merge</a>.
      <br /><br />
      
      You should create a document with the desired layout and appropriate space for text and images, using the same names as placeholders
      as used in the <a href="./?act=data&mode=import_help" title="Import Data Help" class="new" target="_blank">Import Data</a> feature.
      <br />
      These names should be enclosed inside curly braces, for example:<br /><br />
      
      <tt>{SKU}</tt> &nbsp; <tt>{Name}</tt> &nbsp; <tt>{Stock Free}</tt>
      
      <br /><br />
      
      In addition, you can also use <tt>{Images}</tt> and <tt>{Image 1}</tt> (and other image numbers) to load in images.
      
      <br /><br />
      A .zip (Compressed Zipped Folder) file containing the generated files will be made available in the 
      <a href="./?act=dashboard&mode=downloads" target="_blank" title="My Downloads" class="new">My Downloads</a> section of the ACP
      once the process has finished.
    </p>
  </div>
</div>

<br />

<div class="panel">
  <div class="title">Select a Template File</div>
  <div class="message">
    <p>
      <span style="float: left; margin-right: 0.3em;" class="ui-icon ui-icon-info"></span>
      <strong>Required Fields</strong> - You need to complete all of the fields in this panel.
      <br class="clear" />
    </p> 
  </div>
  <div class="contents">
  
  </div>
</div>

<br />