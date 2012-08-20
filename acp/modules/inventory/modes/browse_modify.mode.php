<?php
/**
 * Module: Inventory
 * Mode: Browse Modify
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

// Load the item to modify
$itemID = $BF->inInteger('id');

// Empty? Try f_id (Perhaps last submit failed)
if(!$itemID)
{
  $itemID = $BF->inInteger('f_id');
}

// Query for it
$BF->db->select('*', 'bf_items')
       ->where('id = \'{1}\'', $itemID)
       ->limit(1)
       ->execute();
    
// Success?
if($BF->db->count != 1)
{
  // Failed
  header('Location: ./?act=inventory&mode=browse');
  exit();
}

$itemRow = $BF->db->next();

// Is this a child item?
$isChild = ($itemRow->parent_item_id != -1);

// Get parent if required
if($isChild)
{
  $parentItemRow = $BF->db->getRow('bf_parent_items', 
    $itemRow->parent_item_id);
}

?>

<!-- Scripts -->
<script type="text/javascript">

  // Attach WYSIWYG Editor
  $(function() {
    CKEDITOR.replace( 'description' );
  });
  
</script>
<script type="text/javascript">
  
  // The list of images
  var imageList = new Array();
  
  // Store a change buffer
  var lastChange = '';
  
  /**
   * Select subcategory callback
   * @return boolean
   */
  function selectSubcategory()
  {
    // Verify PHP value
    if("<?php print $itemRow->subcategory_id; ?>" == "-1")
    {
      return false;
    }
  
    $('a[subcat="<?php print $itemRow->subcategory_id; ?>"]').click();
    
    return true;
  }
  
  /**
   * Select category callback
   * @return boolean
   */
  function selectCategory()
  {
    // Verify PHP value
    if("<?php print $itemRow->category_id; ?>" == "-1")
    {
      return false;
    }
  
    // Select category and subcategory
    $('#category_tree').scrollTo($('a[cat="<?php print $itemRow->category_id; ?>"]'), 300);
    $('a[cat="<?php print $itemRow->category_id; ?>"]').click();
    setTimeout('selectSubcategory()', 500);
    
    return true;
  }
  
  // Create the category tree
  $(function() {
  
<?php
  if($isChild)
  {
?>
    // Hide subcategory/category/classification choices
    $('#selectCategory').hide();
    $('#selectClassification').hide();
    $('#selectBrand').hide();
<?php
  }
?>
    // After loading, show the category and subcategory of this item
    setTimeout('selectCategory()', 500);
  
    // Bind links
    $('#skip_attrs').click(function() {
      $.scrollTo('#panel_attributes', 1000);
    });
    
    // Block activity when the form is submitted
    $('#modifyItemForm').submit(function() {
      loadingScreen();
    });
    
    // Try to download a draft if one exists
    var draftText = 'Draft of description for ' + $('#f_sku').val();
    $.get('/acp/ajax/draft_get.ajax.php?description=' + draftText, function(data) {

    });
    
    // Detect classification changes and load attributes as required
    $('#dd_f_classification').change(function() {
      
      // Load the attributes in to view
      $('#attribute_rows').children().remove();
      
      // Download attributes
      $.getJSON('/acp/ajax/default_attributes.ajax.php', 
        { 'id' : $(this).val(), 'item_id' : <?php print $itemRow->id; ?> }, function(data) {
        
        // Clear attributes
        $('#attr_list').val();
        
        // Count data
        var count = 0;
        
        $.each(data, function(index, row) {
          
          // Add row
          var newRow = $('<tr />');
          var newRowTitleCell = $('<td />');
          var newRowValueCell = $('<td />');
          
          // Add content
          newRowTitleCell.html(row.name);
          var valueBox = $('<input />');
          valueBox.attr('type', 'text')
                  .css('width', '150px')
                  .css('margin', '5px 0px 5px 0px')
                  .attr('name', 'f_attr_' + row.id)
                  .val(row.value)
                  .attr('id', row.id)
                  .addClass('attribute_value');
          newRowValueCell.append(valueBox);
          
          // Add title and value to row
          newRow.append(newRowTitleCell);
          newRow.append(newRowValueCell)
          
          $('#attribute_rows').append(newRow);
          
          // Add attribute to list
          $('#attr_list').val($('#attr_list').val() + row.id + ',');
          
          count ++;
          
        });
        
        // Are there attributes?
        if(count > 0)
        {
          // Show link to skip to attributes
          $('#skip_attrs').show();
          
          // Show the Attribute panel
          $('#panel_attributes').show();
          
          // Set up autocompletes for classification attribute values
          $('.attribute_value').each(function(e, i)
          {
            $(i).autocomplete({
        		  source: function(request, response) {
                  $.ajax({
                      url: "/acp/ajax/autocomplete_classification_value.ajax.php",
                      dataType: "json",
                      data: {
                          term: request.term,
                          id: $(i).attr('id')
                      },
                      success: function(data) {
                          response($.map(data, function(item) {
                              return {
                                  label: item.value,
                                  value: item.value
                              }
                          }))
                      }
                  })
              },
        			minLength: 0,
        			delay: 200,
        			data: {
        			  'id' : $(i).attr('id')
        			},
        			select: function( event, ui ) {
        				$(this).val(ui.item.label);	 
        				return false;
        		  },
        		  search: function( event, ui ) { }
        		});
          }).click(function() {
            $(this).autocomplete('search');
          });

        }
        else
        {
          $('#skip_attrs').hide();
          $('#panel_attributes').hide();
        }
        
      });
      
      // Add title
      $('#attributes_title').html('Item Attributes for ' + $(this).children('option:selected').text());

    });
    
    // Start image uploader (Uploadify)				
	  $('#f_image').uploadify({
      'uploader'  : '/acp/js_libs/jquery_uploadify/uploadify.swf',
      'script'    : '/acp/ajax/uploadify.ajax.php?sku=' + $('#f_sku').val(),
      'cancelImg' : '/acp/static/icon/cross-circle.png',
      'folder'    : '/uploads',
      'removeCompleted' : true,
      'buttonImg' : '/acp/static/image/aui-add-image.png',
      'width'		: '211',
	    'height'	: '32',
	    'auto' : true,
	    'simUploadLimit' : 3,
	    'multi'		: true,
	    'scriptData' : {
	                     'PHPSESSID' : '<?php print session_id(); ?>'
	                   },
	    'onComplete' : function(event, ID, fileObj, response, data)
	                   {
	                     // Hide hint
	                     $('#images-none-yet').hide();
	                     
	                     // Parse text
	                     var jsonReply = eval('(' + response + ')');
	                    
	                     // Show the thumbnail
	                     $('<img rel="' + jsonReply.id + '" src="' + jsonReply.thumbnails.thm + '" />')
	                       .appendTo('#images-added');
	                     
	                     // Add to the list
	                     imageList.push(jsonReply.id);
	                     
	                     // Sync form
                       $('#f_image_list').val(imageList.join(','));
	                   }
    });				
    
      
    // Force reload
    $('#dd_f_classification').change();
      
    
    // Allow removal of images
    $('div.images img').live('click', function() {
      
      // Get the ID
      var id = $(this).attr('rel');
      var selectedItem = this;
      
      confirmation('Are you sure you wish to remove the image?<br />' + 
        'This cannot be undone.', function() {
      
        // Remove image from list
        var newImages = Array();
        $.each(imageList, function(i, v) {
          if(v != id)
          {
            newImages.push(v);
          }
        });
        imageList = newImages;
      
        // Remove from DB
        $.get('./ajax/image_remove.ajax.php', {'id' : $(selectedItem).attr('rel') });
        
        // Hide element
        $(selectedItem).remove();
        
        // Sync form
        $('#f_image_list').val(imageList.join(','));
      
      });
            
    });
    
    // Allow reordering of images
    $('div.images').sortable({
      'stop' : function() {
        // Resync all images positions in the form
        resyncImages();
      },
      revert: true,
      tolerance: 'pointer'
    });
    
    // Start the file tree object
    $('#category_tree').fileTree({ selectionChanged: function(r, cat, subcat) {
      $('#f_category').val(cat);
      $('#f_subcategory').val(subcat);
  	}, root: '0', script: '/acp/ajax/categories.ajax.php', selectable: true }, function(el, ob, name) { });
  
    // Automatically save drafts of the description.
    lastChange = $('#description').val();
    autoSave();
  });
  
  /**
   * Perform a full resync of the image positions
   * @return boolean
   */
  function resyncImages()
  {
    // Clear
    $('#f_image_list').val();
    imageList = new Array();
    
    // Rebuild
    $('div.images img').each(function(i, v) {
      
      // Add to array
      imageList.push($(v).attr('rel'));

    });
    
    // Rebuild form
    $('#f_image_list').val(imageList.join(','));
    
    return true;
  }
  
  /**
   * Automatically save the draft of the description if required
   * @return boolean
   */
  function autoSave()
  { 
    // Schedule another attempt
    setTimeout('autoSave();', 8000);
    
    // Changed?
    if($('#description').val() == lastChange)
    {
      // Do not update
      return false;
    }
    
    // Title valid?
    if($('#f_sku').val() == '')
    {
      // Can't use null title
      return false;
    }
    
    var description = 'Draft of description for ' + $('#f_sku').val();
    
    // POST to ajax draft responder
    $.post('/acp/ajax/draft.ajax.php',
           {
             'description' : description,
             'text' : $('#description').val()
           }
          );
    
    // Store last change
    lastChange = $('#description').val();
    
    return true;
  }

</script>

<h1 <?php print ($isChild ? 'style="float: left;"' : ''); ?>>
  <?php print $itemRow->name; ?>
</h1>

<?php 
  if($isChild)
  {
 
	  // Define confirmation JS for Parent Skip link
	  $parentSkipConfirmationJS = 'confirmation(\'Are you sure you want to move away from this page?<br />' . 
	  						                'You will lose any unsaved changes.\', function() { window.location=\'' .
	                              Tools::getModifiedURL(
	                                array('mode' => 'browse_modify_parent', 'id' => $parentItemRow->id)
	                              ) . '\'; })';
  
?>
<h1 style="float: right; color: #afafaf;">
  Child of 
  <a href="#" onclick="<?php print $parentSkipConfirmationJS; ?>"
    style="color: #afafaf;">
    <?php print $parentItemRow->sku; ?>
  </a>
</h1>
<br class="clear" />
<?php
  }
?>

<br />

<form action="./?act=inventory&mode=browse_modify_do" method="post" id="modifyItemForm">
<input type="hidden" name="f_id" value="<?php print $itemRow->id; ?>" />
<input type="hidden" name="f_old_sku" value="<?php print $itemRow->sku; ?>" />

<div class="panel">
  <div class="title"><?php print ($isChild ? 'Child Item' : 'Basic'); ?> Information</div>
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
            <strong>SKU</strong><br />
            A unique identifier for this item.
          </td>
          <td class="value">
            <input value="<?php print $itemRow->sku; ?>" type="text" name="f_sku" id="f_sku" style="width: 100px;" />
          </td>
        </tr>
        
        <tr>
          <td class="key">
            <strong>Name</strong><br />
            The name of this item.
          </td>
          <td class="value">
            <input value="<?php print htmlentities($itemRow->name); ?>" type="text" name="f_name" style="width: 250px;" />
          </td>
        </tr>
        
        <tr id="selectClassification">
          <td class="key">
            <strong>Classification</strong><br />
            The classification of this item.
          </td>
          <td class="value">
<?php

  // Query the database for Classifications
  $query = $BF->db->query();
  $query->select('*', 'bf_classifications')
        ->order('name', 'ASC')
        ->execute();
        
  // Create a UI element
  $dropDown = new DataDropDown('f_classification', $query, 'id', 'name', array('-1' => 'Default'));
  $dropDown->setOption('defaultSelection', $itemRow->classification_id);
  print $dropDown->render();

?>

  &nbsp; <a id="skip_attrs" style="display:none;" href="#">Modify Attributes...</a>

          </td>
        </tr>
        
        <tr id="selectCategory">
          <td class="key">
            <strong>Category</strong><br />
            The category into which this item should be placed.
          </td>
          <td class="value">
            <br />
<?php

  if($itemRow->category_id == '-1')
  {
    print '<span class="grey">This item is uncategorised.</span>';
  }
  else
  {
    // Retrieve category row
    $category = $BF->db->getRow('bf_categories', $itemRow->category_id);
    print 'Current Location: ' . $category->name;
    
    if($itemRow->subcategory_id != '-1')
    {
      $subcategory = $BF->db->getRow('bf_subcategories', $itemRow->subcategory_id);
      print '&nbsp;&rang;&nbsp;' . $subcategory->name;
    }
  }

?>
            <br />
            <div id="category_tree"></div>
            <input value="<?php print $itemRow->category_id; ?>" type="hidden" name="f_category" id="f_category" value="-1" />
            <input value="<?php print $itemRow->subcategory_id; ?>" type="hidden" name="f_subcategory" id="f_subcategory" value="-1" />
          </td>
        </tr>
        
        <tr>
          <td class="key">
            <strong>Trade Price</strong><br />
            The basic trade price of this item.<br />
            This will be displayed to dealers with appropriate permissions.
          </td>
          <td class="value">
            GBP <input value="<?php print $itemRow->trade_price; ?>" type="text" name="f_trade_price" value="0.00" style="width: 50px;" class="autoselect" />
          </td>
        </tr>
        
        <tr>
          <td class="key">
            <strong>Pro-Net Price</strong><br />
            The Pro-Net price of this item.<br />
            This will be displayed to dealers with appropriate permissions.
          </td>
          <td class="value">
            GBP <input value="<?php print $itemRow->pro_net_price; ?>" type="text" name="f_pro_net_price" value="0.00" style="width: 50px;" class="autoselect" />
          </td>
        </tr>
        
        <tr>
          <td class="key">
            <strong>Pro-Net Quantity</strong><br />
            The number of units a dealer must buy to pay the Pro-Net price.<br />
            This will be displayed to dealers with appropriate permissions.
          </td>
          <td class="value">
            <input value="<?php print $itemRow->pro_net_qty; ?>" type="text" name="f_pro_net_qty" value="0" style="width: 50px;" class="autoselect" />
          </td>
        </tr>
        
        <tr>
          <td class="key">
            <strong>Wholesale Price</strong><br />
            The price that dealers marked as wholesale will pay for this item.
          </td>
          <td class="value">
            GBP <input value="<?php print $itemRow->wholesale_price; ?>" type="text" name="f_wholesale_price" value="0.00" style="width: 50px;" class="autoselect" />
          </td>
        </tr>  
              
        <tr>
          <td class="key">
            <strong>RRP / MSRP</strong><br />
            The displayed RRP of this item.
          </td>
          <td class="value">
            GBP <input value="<?php print $itemRow->rrp_price; ?>" type="text" name="f_rrp_price" value="0.00" style="width: 50px;" class="autoselect" />
          </td>
        </tr>

        <tr class="last">
          <td class="key">
            <strong>Stock Levels</strong><br />
            Your stock levels for this item.<br />
            Display of this option may be <a target="_blank" href="./?" class="new" />configured</a>
          </td>
          <td class="value">
            Free: <input value="<?php print $itemRow->stock_free; ?>" type="text" name="f_stock_free" value="0" style="width: 50px;" class="autoselect" /> &nbsp; &nbsp; &nbsp;
            Held: <input value="<?php print $itemRow->stock_held; ?>" type="text" name="f_stock_held" value="0" style="width: 50px;" class="autoselect" />
          </td>
        </tr>
                
      </tbody>
    </table>
    
  </div>
  
</div>

<br />

<div class="panel">
  <div class="title">More Information</div>
  <div class="contents" style="padding: 0px 20px 0px 20px;">
    
    <!-- More Information -->

    <table class="fields">
      <tbody>

        <tr id="selectBrand">
          <td class="key">
            <strong>Brand</strong><br />
            Select the brand of this item.
          </td>
          <td class="value">
<?php

  // Query the database for Brands
  $query = $BF->db->query();
  $query->select('*', 'bf_brands')
        ->order('name', 'ASC')
        ->execute();
        
  // Create a UI element
  $dropDown = new DataDropDown('f_brand', $query, 'id', 'name', array('-1' => 'Default'));
  $dropDown->setOption('defaultSelection', $itemRow->brand_id);
  print $dropDown->render();

?>
          </td>
        </tr>

        <tr>
          <td class="key">
            <strong>Availability Date</strong><br />
            If this item is out of stock, this date will be displayed.<br />
            <span class="grey">Leave blank to maintain current value, set to past date to hide.</span>
          </td>
          <td class="value">
            <?php 
              print ($itemRow->stock_date > 0 ? '<span>Currently due: ' . 
                    date('d M Y', $itemRow->stock_date) . '</span><br /><br />' : '');
            ?>
            <input id="f_stock_date" type="text" name="f_stock_date" class="date" style="width: 100px;" />
          </td>
        </tr>

        <tr>
          <td class="key">
            <strong>Cost</strong><br />
            The cost of this item from the supplier.<br />
            This value will <em>never</em> be displayed outside of the <abbr title="Admin Control Panel">ACP</abbr>.
          </td>
          <td class="value">
            GBP <input value="<?php print $itemRow->cost_price; ?>" type="text" name="f_cost_price" style="width: 50px;" class="autoselect" />
          </td>
        </tr>
      
        <tr class="last">
          <td class="key">
            <strong>Barcode</strong><br />
            The unique UPC/EAN barcode for this item.<br />
            You can use a Barcode Scanner to enter text into this field.
          </td>
          <td class="value">
            <input value="<?php print $itemRow->barcode; ?>" type="text" name="f_barcode" style="width: 150px;" />
          </td>
        </tr>
       
      </tbody>
    </table>
    
  </div>
  
</div>

<br />

<div class="panel">
  <div class="title">Keywords</div>
  <div class="contents">
    
    <h3>Keywords</h3>
    <p>
      Keywords improve the searchability of the website and improve the quality of exported data.<br />
      You should add as many relevant keywords as possible.
    </p>
    
    <br />
    
<?php

  // Create a UI element to handle the creation of default attributes
  $attributeEditor = new FormListBuilder('keywords', 
    Tools::removeEmptyEntries(Tools::unCSV($itemRow->keywords)), $BF);
  $attributeEditor->setOption('valueDescription', 'New Keyword:');
  $attributeEditor->setOption('listDescription', 'Item Keywords');
  $attributeEditor->setOption('emptyList', 'Use the field above to add ' .
                              'keywords to the item.');
  print $attributeEditor->render();

?>
    
  </div>
</div>

<br />

<?php

  // List images
  $images = $BF->db->query();
  $images->select('*', 'bf_item_images')
         ->where('item_id = \'{1}\'', $itemRow->id)
         ->order('priority', 'asc')
         ->execute();
  
  // Build image collection
  $imageCollection = array();
  while($image = $images->next())
  {
    // Find image
    $imageRow = $BF->db->getRow('bf_images', $image->image_id);
    $imageCollection[$imageRow->id] = $imageRow;
  }
  
?>

<div class="panel">
  <div class="title">Images</div>
  <div class="contents">
    
    <!-- Images -->
    <input type="hidden" name="f_image_list" id="f_image_list"
      value="<?php print Tools::CSV(array_keys($imageCollection)); ?>" />

    <p>
        
        <strong>Images are important to show your customers the best of your products.</strong><br />
        You should add as many high quality, relevant images to your inventory items as possible.<br /><br />
        
        Click the button below to choose images (in either GIF, JPEG or PNG format) to upload.<br />
        If you want to remove an image, click on it in the box underneath.
        
        <br /><br />
        
        You can rearrange images by dragging them in to place.
        
        <br /><br />
    
    </p>
  
    
    <table style="width: 100%;">
      
      <tbody>
        
        <tr>
        
          <td style="width: 240px;">    
            <div style="height: 300px; margin-left: 10px; background: #fff;" class="panel">
              <div class="title">Upload Queue</div>
              <div class="contents" id="images-uploading">
              
                <input name="f_image" id="f_image" type="file" />

              </div>
            </div>
          </td>
          <td style="padding-right: 8px;">
            <div style="height: 300px; margin-left: 15px; background: #fff;" class="panel">
              <div class="title">Images</div>
              <div class="message" id="images-none-yet">
                <p style="padding: 0;">
                  <span style="float: left; margin-right: 0.3em;" class="ui-icon ui-icon-circle-triangle-w"></span>
                  Click the button to the left to upload an image from your computer.
                  <br class="clear" />
                </p> 
              </div>
              <div class="contents images" id="images-added">
                
<?php
         
  // Show all images
  foreach($imageCollection as $image)
  {
    print '<img rel="' . $image->id . '" src="' . Tools::getImageThumbnail($image->url) . '" />' . "\n";
    print '<script type="text/javascript">' . "\n";
    print '  imageList.push(' . $image->id . ');' . "\n";
    print '</script>' . "\n &nbsp;";
  }

?>
                
              </div>
            </div>
          </td>
          
        </tr>
        
      </tbody>
      
    </table>
    
    <p>
      <br />
      <span class="grey">Thumbnails will be generated automatically.</span><br />
      <span class="grey">Images will automatically be given appropriate names when this item is saved.</span>
    </p>
        
  </div>
  
</div>

<br />

<div id="panel_attributes" style="display: none; margin-bottom: 10px;">
  
  <div class="panel">
    <div class="title" id="attributes_title">Item Attributes</div>
    <div class="contents">
      
      <!-- Attributes -->
  
      <p>
        
        <strong>Item attributes are custom information fields relevant to this type of item.</strong><br />
        Attribute fields are automatically shown upon selecting a classification for a new item.
        
        <br />
      
      </p>
      
      <input type="hidden" name="f_attr_list" value="" id="attr_list" />
      
      <div style="padding: 10px">
      
        <table class="data">
          <thead>
            <tr class="header">
              <td>
                Attribute Name
              </td>
              <td>
                Set Attribute Value
              </td>
            </tr>
          </thead>
          <tbody id="attribute_rows">
            <tr>
              <td></td>
              <td></td>
            </tr>
          </tbody>
        </table>
      </div>
      
    </div>
    
  </div>
  
</div>

<?php
  if($isChild)
  {
?>

<br />

<div id="panel_variations">
  
  <div class="panel">
    <div class="title" id="variations_title">Variations on <?php print $parentItemRow->sku; ?></div>
    <div class="contents">
      
      <!-- Attributes -->
  
      <p>
        
        <strong>Choose values for the variation options associated with this items parent.</strong>
        
        <br />
      
      </p>
      
      <input type="hidden" name="f_variation_list" value="" id="variation_list" />
      
      <div style="padding: 10px">
      
        <table class="data">
          <thead>
            <tr class="header">
              <td>
                Variation Name
              </td>
              <td>
                Variation Value
              </td>
            </tr>
          </thead>
          <tbody id="variation_rows">
            
<?php
  
  // Find variation options
  $variationOptions = $BF->db->query();
  $variationOptions->select('*', 'bf_parent_item_variations')
                   ->where('`parent_item_id` = \'{1}\'', $parentItemRow->id)
                   ->execute();

  // Cache in memory
  $variations = array();
  while($variationOption = $variationOptions->next())
  { 
    $variations[$variationOption->id]['name'] = $variationOption->name;
    $variations[$variationOption->id]['id'] = -1;
    $variations[$variationOption->id]['value'] = '';
  }
  
  // Find variation values
  $variationValues = $BF->db->query();
  $variationValues->select('*', 'bf_parent_item_variation_data')
                   ->where('`item_id` = \'{1}\'', $itemRow->id)
                   ->order('parent_item_variation_id', 'ASC')
                   ->execute();  


  // Set values
  while($variationValue = $variationValues->next())
  {
    // Set value
    $variations[$variationValue->parent_item_variation_id]['value'] = 
      $variationValue->value;
      
    // Set ID of data row
    $variations[$variationValue->parent_item_variation_id]['id'] = 
      $variationValue->id;
  }

  // List all
  foreach($variations as $id => $properties)
  {
    
    // Add key to the variation option value list
    $variationDataIDs[] = $properties['id'];
  
?>          
            <tr>
              <td><?php print $properties['name']; ?></td>
              <td>
                <input type="text" name="f_variation_<?php print $properties['id']; ?>"
                  value="<?php print $properties['value']; ?>" />
              </td>
            </tr>
<?php
  }
  
  // Collect keys as CSV
  $variationKeysCSV = Tools::CSV($variationDataIDs);
  
?>        
          </tbody>
        </table>
      </div>
      
    </div>
    
  </div>
  
</div>

<input type="hidden" name="f_variation_list"
  id="variation_list" value="<?php print $variationKeysCSV; ?>" />

<br />

<?php
  }
?>



<div class="panel">
  <div class="title">Item Tags</div>
  <div class="contents">
    
    <!-- Tags -->

    <p>
      
      <strong>Item Tags are virtual labels that you can attach to items.</strong><br />
      This creates a way to organise items by tag, as well as making items stand out in search results and list views. 
      
      <br /><br />
      
      <a href="./?act=inventory&mode=tags" title="Tags" class="new" target="_blank">Click Here</a>
      to create Item Tags in a new window. 
      
      <br />
    
    </p>
    
    <div style="padding: 10px">
         
<?php
  
  // Collect an array of the tags that are applied to this item already
  $appliedTags = $BF->db->query();
  $appliedTags->select('*', 'bf_item_tag_applications')
              ->where('`item_id` = \'{1}\'', $BF->inInteger('id'))
              ->execute();
            
  // Load into array in memory
  $currentTags = array();
  while($appliedTag = $appliedTags->next())
  {
    $currentTags[] = $appliedTag->item_tag_id;
  }
  
  // Find all tags
  $query = $BF->db->query();
  $query->select('*', 'bf_item_tags')
       ->order('name', 'asc')
       ->execute();
  
  // Create tools
  $toolSet  = "\n";
  $toolSet .= '<a class="tool" target="_blank" href="./?act=inventory&mode=tags_modify&id={id}" title="Modify">' . "\n";
  $toolSet .= '  <img src="/acp/static/icon/tag--pencil.png" alt="Modify" />' . "\n";
  $toolSet .= 'Modify&nbsp;<img src="/acp/static/image/aui-external.png" class="new" alt="New Window" /></a>' . "\n";
  
  // Create a data view
  $itemTags = new DataTable('f_tag', $BF, $query);
  $itemTags->setOption('alternateRows');
  $itemTags->addColumns(array(
                          array(
                            'dataName' => 'id',
                            'niceName' => '',
                            'options' => array(
                                           'formatAsCheckbox' => true,
                                           'checkIfIn' => $currentTags,
                                           'fixedOrder' => true
                                         ),
                            'css' => array(
                                       'width' => '16px'
                                     )
                          ),
                          array(
                            'dataName' => 'icon_path',
                            'niceName' => '',
                            'options' => array(
                                           'formatAsImage' => true,
                                           'fixedOrder' => true
                                         ),
                            'css' => array(
                                       'width' => '16px'
                                     )
                          ),
                          array(
                            'dataName' => 'name',
                            'niceName' => 'Tag Name',
                            'options' => array(
                                           'fixedOrder' => true
                                         )
                          ),
                          array(
                            'dataName' => '',
                            'niceName' => 'Actions',
                            'options' => array('fixedOrder' => true),
                            'css' => array(
                                       'text-align' => 'right',
                                       'padding-right' => '10px',
                                       'width' => '80px'
                                     ),
                            'content' => $toolSet
                          )
                        ));
  
  // Render & output content
  print $itemTags->render();
  
?>

    </div>
    
        
  </div>
  
</div>


<br />


<div class="panel">
  <div class="title">Description</div>
  <div class="contents" style="padding: 2px 0 0 0 ;">
    
    <!-- Description -->

    <textarea id="description" name="description" style="width:100%; height: 200px; border: 0; background:transparent;">
      <?php print $itemRow->description; ?>
    </textarea>    
        
  </div>
  
</div>


<!-- Save point -->

<br />

<div class="panel" style="border: 1px solid #50954b">
  <div class="contents">
    <p style="padding: 7px 0px 0px 5px; float: left; color: #50954b;">
      <strong>Click the button to the right to save this item now.</strong>
    </p>
    <input class="submit ok" type="submit" style="float: right;" value="Save and Exit" />
    <br class="clear" />
  </div>
</div>

</form>