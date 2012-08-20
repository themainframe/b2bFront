<?php
/**
 * Module: Inventory
 * Mode: Browse Modify Parent Item
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

// Load the parent item to modify
$parentItemID = $BF->inInteger('id');

// Empty? Try f_id (Perhaps last submit failed)
if(!$parentItemID)
{
  $parentItemID = $BF->inInteger('f_id');
}

// Query for it
$BF->db->select('*', 'bf_parent_items')
       ->where('id = \'{1}\'', $parentItemID)
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

// Find child update policy config
$updateChildren = $BF->config->get('com.b2bfront.acp.update-children-default', true);
$defaultCUP = Tools::booleanToCheckState($updateChildren);

?>

<!-- Scripts -->
<script type="text/javascript">

  // Attach WYSIWYG Editor
  $(function() {
    CKEDITOR.replace( 'description' );
  });
  
</script>
<script type="text/javascript">

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
  
  // The list of images
  var imageList = new Array();
  
  // Store a change buffer
  var lastChange = '';
  
  // Create the category tree
  $(function() {
  
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
    
    // Detect classification changes and load attributes as required
    $('#dd_f_classification').change(function() {
      
      // Load the attributes in to view
      $('#attribute_rows').children().remove();
      
      // Download attributes
      $.getJSON('/acp/ajax/default_attributes.ajax.php', 
        { 'id' : $(this).val(), 'parent' : '1', 'item_id' : '<?php print $itemRow->id; ?>' }, function(data) {
        
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

<h1>
  <?php print $itemRow->name; ?>
  &nbsp;<img style="position: relative; top: -2px; left: -2px;" 
  class="middle" src="./static/image/aui-parent-white.gif" alt="Parent" />
</h1>
<br />

<form action="./?act=inventory&mode=browse_modify_parent_do" method="post" id="modifyItemForm">
<input type="hidden" name="f_id" value="<?php print $itemRow->id; ?>" />
<input type="hidden" name="f_old_sku" value="<?php print $itemRow->sku; ?>" />

<div class="panel">
  <div class="title">Basic Parent Item Information</div>
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
            <br />
            <strong>Virtual SKU</strong><br />
            A SKU that represents this parent item.<br />
            This value will <em>never</em> be displayed outside of the
            <abbr title="Admin Control Panel">ACP</abbr>
            
            <br /><br />
            <span class="grey">Child items are unaffected by this value.</span><br /><br />
          </td>
          <td class="value">
            <input value="<?php print $itemRow->sku; ?>" type="text" name="f_sku" id="f_sku" style="width: 100px;" />
          </td>
        </tr>
        
        <tr>
          <td class="key">
            <strong>Name</strong><br />
            The name of this parent item.
            
            <br /><br />
            <span class="grey">Child items are unaffected by this value.</span>
          </td>
          <td class="value">
            <input value="<?php print htmlentities($itemRow->name); ?>" type="text" name="f_name" style="width: 250px;" />
          </td>
        </tr>
        
        <tr>
          <td class="key">
            <strong>Classification</strong><br />
            The classification of this parent item and its children.
            
            <br /><br />
            <span class="grey">All children will be updated to reflect this value.</span>
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
        
        <tr>
          <td class="key">
            <strong>Category</strong><br />
            The category into which this parent item and its children should be placed.
            
            <br /><br />
            <span class="grey">All children will be updated to reflect this value.</span>
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
      print '&nbsp;&gt;&nbsp;' . $subcategory->name;
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
            <br />
            <strong>Trade Price</strong><br />
            The basic trade price of this item.<br />
            This will be displayed to dealers with appropriate permissions.
            
            <br /><br />
            <input <?php print $defaultCUP; ?> type="checkbox" value="1" name="f_trade_price_ud" />
            <span class="grey">Update all children with this value.</span><br /><br />
          </td>
          <td class="value">
            GBP <input value="<?php print $itemRow->trade_price; ?>" type="text" name="f_trade_price" value="0.00" style="width: 50px;" class="autoselect" />
          </td>
        </tr>
        
        <tr>
          <td class="key">
            <br />
            <strong>Pro-Net Price</strong><br />
            The Pro-Net price of this item.<br />
            This will be displayed to dealers with appropriate permissions.
            
            <br /><br />
            <input <?php print $defaultCUP; ?> type="checkbox" value="1" name="f_pro_net_price_ud" />
            <span class="grey">Update all children with this value.</span><br /><br />
          </td>
          <td class="value">
            GBP <input value="<?php print $itemRow->pro_net_price; ?>" type="text" name="f_pro_net_price" value="0.00" style="width: 50px;" class="autoselect" />
          </td>
        </tr>
        
        <tr>
          <td class="key">
            <br />
            <strong>Pro-Net Quantity</strong><br />
            The number of units a dealer must buy to pay the Pro-Net price.<br />
            This will be displayed to dealers with appropriate permissions.
            
            <br /><br />
            <input <?php print $defaultCUP; ?> type="checkbox" value="1" name="f_pro_net_qty_ud" />
            <span class="grey">Update all children with this value.</span><br /><br />
          </td>
          <td class="value">
            <input value="<?php print $itemRow->pro_net_qty; ?>" type="text" name="f_pro_net_qty" value="0" style="width: 50px;" class="autoselect" />
          </td>
        </tr>
        
        <tr>
          <td class="key">
            <br />
            <strong>Wholesale Price</strong><br />
            The price that dealers marked as wholesale will pay for this item.
            
            <br /><br />
            <input <?php print $defaultCUP; ?> type="checkbox" value="1" name="f_wholesale_price_ud" />
            <span class="grey">Update all children with this value.</span><br /><br />
          </td>
          <td class="value">
            GBP <input value="<?php print $itemRow->wholesale_price; ?>" type="text" name="f_wholesale_price" value="0.00" style="width: 50px;" class="autoselect" />
          </td>
        </tr>  
              
        <tr class="last">
          <td class="key">
            <br />
            <strong>RRP / MSRP</strong><br />
            The displayed RRP of this item.
            
            <br /><br />
            <input <?php print $defaultCUP; ?> type="checkbox" value="1" name="f_rrp_price_ud" />
            <span class="grey">Update all children with this value.</span><br /><br />
          </td>
          <td class="value">
            GBP <input value="<?php print $itemRow->rrp_price; ?>" type="text" name="f_rrp_price" value="0.00" style="width: 50px;" class="autoselect" />
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

        <tr>
          <td class="key">
            <strong>Brand</strong><br />
            Select the brand of this item.
            
            <br /><br />
            <span class="grey">All children will be updated to reflect this value.</span>
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

        <tr class="last">
          <td class="key">  
            <br />
            <strong>Cost</strong><br />
            The cost of this item from the supplier.<br />
            This value will <em>never</em> be displayed outside of the <abbr title="Admin Control Panel">ACP</abbr>.
            
            <br /><br />
            <input <?php print $defaultCUP; ?> type="checkbox" value="1" name="f_cost_price_ud" />
            <span class="grey">Update all children with this value.</span><br /><br />
          </td>
          <td class="value">
            GBP <input value="<?php print $itemRow->cost_price; ?>" type="text" name="f_cost_price" style="width: 50px;" class="autoselect" />
          </td>
        </tr>
       
      </tbody>
    </table>
    
  </div>
  
</div>

<br />

<div id="panel_attributes" style="display: none; margin-bottom: 14px;">
  
  <div class="panel">
    <div class="title" id="attributes_title">Item Attributes</div>
    <div class="contents">
      
      <!-- Attributes -->
  
      <p>
        
        <strong>Item attributes are custom information fields relevant to this type of item.</strong><br />
        Attribute fields are automatically shown upon selecting a classification for a new item.
        
        <br /><br />
        
        All children will inherit these values.
        
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


<div class="panel">
  <div class="title">Parent Item Description</div>
  <div class="contents" style="padding: 2px 0 0 0 ;">
    
    <!-- Description -->

    <textarea id="description" name="description" style="width:100%; height: 200px; border: 0; background:transparent;"><?php print $itemRow->description; ?></textarea>    
        
  </div>
  
</div>


<!-- Save point -->

<br />

<div class="panel" style="border: 1px solid #50954b">
  <div class="contents">
    <p style="padding: 7px 0px 0px 5px; float: left; color: #50954b;">
      <strong>Click the button to the right to save changes now.</strong>
    </p>
    <input class="submit ok" type="submit" style="float: right;" value="Save and Exit" />
    <br class="clear" />
  </div>
</div>

</form>