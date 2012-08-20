<?php
/**
 * Module: Website
 * Mode: Modify Page
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

// Load the page to modify
$pageID = $BF->inInteger('id');

// Query for it
$BF->db->select('*', 'bf_pages')
           ->where('id = \'{1}\'', $pageID)
           ->limit(1)
           ->execute();
    
// Success?
if($BF->db->count != 1)
{
  // Failed
  header('Location: ./?act=website&mode=pages');
  exit();
}

$pageRow = $BF->db->next();

?>

<!-- Scripts -->
<script type="text/javascript" src="js_libs/openwysiwyg/scripts/wysiwyg.js"></script>
<script type="text/javascript" src="js_libs/openwysiwyg/scripts/wysiwyg-settings.js"></script>
<script type="text/javascript">

  // Attach WYSIWYG Editor
  $(function() {
    CKEDITOR.replace( 'content' );
  });
  
</script>

<h1><?php print $pageRow->title; ?></h1>
<br />

<form action="./?act=website&mode=pages_modify_do" enctype="multipart/form-data" method="post">
<input type="hidden" name="f_id" id="f_id" value="<?php print $pageRow->id; ?>" />

<div class="panel">
  <div class="title">Page Contents</div>
  <div class="contents" style="padding: 2px 0 0 0 ;">
    
    <!-- Contents -->

    <textarea id="content" name="content" style="width:100%; height: 300px; border: 0; background:transparent;"><?php print $pageRow->content; ?></textarea>    
        
  </div>
  
</div>

<br />

<div class="panel" style="border: 1px solid #50954b">
  <div class="contents">
    <p style="padding: 7px 0px 0px 5px; float: left; color: #50954b;">
      <strong>Click the button to the right to save the changes this page.</strong>
    </p>
    <input class="submit ok" type="submit" style="float: right;" value="Save and Exit" />
    <input onclick="window.location='./?act=website&mode=pages';" class="submit bad" type="button" style="float: right; margin-right: 10px;" value="Cancel and Exit" />
    <br class="clear" />
  </div>
</div>

</form>