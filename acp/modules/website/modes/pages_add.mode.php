<?php
/**
 * Module: Website
 * Mode: Add Page
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

<!-- Scripts -->
<script type="text/javascript">

  // Attach WYSIWYG Editor
  $(function() {
    CKEDITOR.replace( 'content' );
  });
  
</script>

<h1>Add a Page</h1>
<br />

<form action="./?act=website&mode=pages_add_do" enctype="multipart/form-data" method="post">
<input type="hidden" name="f_id" id="f_id" value="<?php print $pageRow->id; ?>" />

<div class="panel">
  <div class="title">Page Information</div>
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
      
        <tr class="last">
          <td class="key">
            <strong>Title</strong><br />
            A unique title for the page.
          </td>
          <td class="value">
            <input type="text" name="f_title" id="f_title" style="width: 200px;" />
          </td>
        </tr>
      </tbody>
    </table>
    
  </div>
</div>

<br />

<div class="panel">
  <div class="title">Page Contents</div>
  <div class="contents" style="padding: 2px 0 0 0 ;">
    
    <!-- Contents -->

    <textarea id="content" name="content" style="width:100%; height: 300px; border: 0; background:transparent;"></textarea>    
        
  </div>
  
</div>

<br />

<div class="panel" style="border: 1px solid #50954b">
  <div class="contents">
    <p style="padding: 7px 0px 0px 5px; float: left; color: #50954b;">
      <strong>Click the button to the right to save this page.</strong>
    </p>
    <input class="submit ok" type="submit" style="float: right;" value="Save and Exit" />
    <input onclick="window.location='./?act=website&mode=pages';" class="submit bad" type="button" style="float: right; margin-right: 10px;" value="Cancel and Exit" />
    <br class="clear" />
  </div>
</div>

</form>