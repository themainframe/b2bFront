<?php
/**
 * Module: Website
 * Mode: Add Article
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

  $(function() {
    
    $('#f_type').change(function() {
      
      $('.type_options').hide();
      $('.' + $(this).val()).show();
      
    });
  
  });
  
</script>

<h1>Add an Article</h1>
<br />

<form action="./?act=website&mode=articles_add_do" enctype="multipart/form-data" method="post">

<div class="panel">
  <div class="title">Article Information</div>
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
            <strong>Name</strong><br />
            A name for the article.
          </td>
          <td class="value">
            <input type="text" name="f_name" id="f_name" style="width: 200px;" />
          </td>
        </tr>
        
        <tr>
          <td class="key">
            <strong>Article Category</strong><br />
            The category of the article.
          </td>
          <td class="value">
      <?php
        
        $articleCategories = $BF->db->query();
        $articleCategories->select('*', 'bf_article_categories')
                          ->order('name', 'ASC')
                          ->execute();
        
        // Create a UI element
        $dropDown = new DataDropDown('f_article_category', $articleCategories, 'id', 'name');
        $dropDown->setOption('defaultSelection', $BF->in('f_category_id'));
        $dropDown->setOption('css', array(
          'margin-top' => '10px'
        ));
        print $dropDown->render();

      ?>
          </td>
        </tr>

        <tr class="last">
          <td class="key">
            <strong>Type</strong><br />
            The type of article to create.
          </td>
          <td class="value">
            <select name="f_type" id="f_type">
              <option value="ART_TEXT">Text (HTML)</option>
              <option value="ART_IMAGE">Image</option>
              <option value="ART_ITEM">Item Link</option>
              <option value="ART_ITEM_COLLECTION">Item Collection Link</option>
              <option value="ART_CATEGORY">Category Link</option>
            </select>
          </td>
        </tr>
        
      </tbody>
    </table>
    
  </div>
</div>

<br />

<div class="panel">
  <div class="title">Article Metadata</div>
  <div class="contents fieldset">
    
    <!-- Meta Information -->
  
    <table class="fields">
      <tbody>
      
        <tr>
          <td class="key">
            <strong>Metadata</strong><br />
            Optionally, any additional data to supply along with the article.<br />
            <span class="grey">Depending on how the article is displayed, this may or may not be used.</span>
          </td>
          <td class="value">
            <textarea name="f_metadata" style="width: 300px; height: 90px; margin: 10px 0px 10px 0px"></textarea>
          </td>
        </tr>
        
      </tbody>
    </table>
    
  </div>
</div>

<br />

<div class="panel">
  <div class="title">Article Contents</div>
  <div class="contents" style="padding: 2px 0 0 0 ;">
    
    <!-- Contents -->
    <div class="type_options ART_TEXT">
      <textarea id="content" name="content" style="width:100%; height: 300px; border: 0; background:transparent; resize: vertical"></textarea>    
    </div>
    
    <!-- Contents -->
    <div class="type_options ART_IMAGE" style="display:none;">
    <table class="fields">
      <tbody>
      
        <tr class="last">
          <td class="key" style="padding-left: 20px;">
            <strong>Image</strong><br />
            Choose the image that this article will contain.
          </td>
          <td class="value">
            <input type="file" name="f_image" id="f_image" />
          </td>
        </tr>
      </tbody>
    </table>
    </div>
    
    <!-- Contents -->
    <div class="type_options ART_ITEM" style="display:none;">
    <table class="fields">
      <tbody>
      
        <tr class="last">
          <td class="key" style="padding-left: 20px;">
            <strong>Item</strong><br />
            Choose the item for the article.
          </td>
          <td class="value">
            <span class="button">
              <a href="#" 
                onclick="selectItems(false, function(d) { $('#item-id').val(d); $('#selected-item').html('1 item selected.'); })">
                <span class="img" style="background-image:url(/acp/static/icon/magnifier.png)">&nbsp;</span>
                Choose Item...
              </a>
            </span>
            &nbsp;&nbsp; 
            <span id="selected-item">&nbsp;</span>
            <input type="hidden" name="f_item_id" id="item-id" />
          </td>
        </tr>
      </tbody>
    </table>
    </div>
 
    <!-- Contents -->
    <div class="type_options ART_ITEM_COLLECTION" style="display:none;">
    <table class="fields">
      <tbody>
      
        <tr class="last">
          <td class="key" style="padding-left: 20px;">
            <strong>Item</strong><br />
            Choose one or more items for the article.
          </td>
          <td class="value">
            <span class="button">
              <a href="#" 
                onclick="selectItems(true, function(d) { $('#item-ids').val(d.join(',')); $('#selected-items').html(d.length.toString() + ' item(s) selected.'); })">
                <span class="img" style="background-image:url(/acp/static/icon/magnifier.png)">&nbsp;</span>
                Choose Items...
              </a>
            </span>
            &nbsp;&nbsp; 
            <span id="selected-items">&nbsp;</span>
            <input type="hidden" name="f_item_ids" id="item-ids" />
          </td>
        </tr>
      </tbody>
    </table>
    </div>
    
    <!-- Contents -->
    <div class="type_options ART_CATEGORY" style="display:none;">
    <table class="fields">
      <tbody>
      
        <tr class="last">
          <td class="key" style="padding-left: 20px;">
            <strong>Category</strong><br />
            Choose a category for the article.
          </td>
          <td class="value">
            <select id="f_category" name="f_category">
<?php

  $categories = $BF->db->query();
  $categories->select('*', 'bf_categories')
             ->order('name', 'asc')
             ->execute();
  
  while($category = $categories->next())
  {
    print '          <option value="' . $category->id . '">' . 
      $category->name . '</option>' . "\n";
  }
  
?>
  
            </select>
          </td>
        </tr>
      </tbody>
    </table>
    </div>
    
  </div>
  
</div>

<br />

<div class="panel" style="border: 1px solid #50954b">
  <div class="contents">
    <p style="padding: 7px 0px 0px 5px; float: left; color: #50954b;">
      <strong>Click the button to the right to save this article.</strong>
    </p>
    <input class="submit ok" type="submit" style="float: right;" value="Save and Exit" />
    <input onclick="window.location='./?act=website&mode=pages';" class="submit bad" type="button" style="float: right; margin-right: 10px;" value="Cancel and Exit" />
    <br class="clear" />
  </div>
</div>

</form>