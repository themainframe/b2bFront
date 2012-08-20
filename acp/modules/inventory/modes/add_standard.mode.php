<?php
/**
 * Module: Inventory
 * Mode: Add
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

// Copy mode?
$item = false;

if($BF->in('id'))
{
  $ID = $BF->inInteger('id');
  
  $item = $BF->db->getRow('bf_items', $ID);
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
  
  // Child?
  var IS_CHILD = false;
  
  // Create the category tree
  $(function() {
    
    
    // Parent SKU Autocomplete
    $('#f_item_sku').autocomplete({
    
		  source: '/acp/ajax/autocomplete_parent_sku.ajax.php',
			minLength: 1,
			select: function( event, ui ) {
				$(this).val(ui.item.label);
				$('#f_item_id').val(ui.item.id);
				$('#f_item_ok').hide(); 
				$('#loadParentButton').show();
				return false;
		  },
		  search: function( event, ui ) {
		    $('#f_item_ok').show();
		    $('#loadParentButton').hide();
		  }
			
		});
		
		// Load Parent Item
		$('#loadParentButton').click(function() {
		  
		  // Loading screen up
		  loadingScreen();
		  
		  // Hide button
		  $('#loadParentButton').hide();
		  
		  // Now a child item
		  IS_CHILD = true;
		  
		  // Set parent item ID
		  $('#f_parent_id').val($('#f_item_id').val());
		  
		  // Download parent item data as JSON
		  $.getJSON('/acp/ajax/parent_item_get_details.ajax.php',
    		        {'id': $('#f_item_id').val() },
    		        function(data) {
    		        
    		          // Finished loading
    		          hideLoadingScreen();
    		          
    		          // Hide parent item Selection
    		          $('#parentItemSelection').hide();
                  
                  // Set each value
                  for(var i in data)
                  {
                    // Trim off the -PAR tag on the SKU
                    if(i == 'sku')
                    {
                      data[i] = data[i].substring(0, data[i].length - 4);
                    }
                  
                    $('#f_' + i).val(data[i]);
                  }
                  
                  // Add title
                  $("#title").text("Add a Child Item to " + data.sku + "-PAR");
                  
                  // Dropdowns
                  $('#dd_f_brand').val(data.brand_id).change();
                  $('#dd_f_classification').val(data.classification_id).change();
                  
                  // Set cat/subcategory ID values
                  $('#f_category').val(data.category_id);
                  $('#f_subcategory').val(data.subcategory_id);
                  
                  // Hide subcategory/category/classification choices
                  $('#selectCategory').hide();
                  $('#selectClassification').hide();
                  $('#selectBrand').hide();
                  $('#panel_attributes').hide();
                  
                  // Load the variations in to view
                  $('#variation_rows').children().remove();
                  
                  // Download variation option data
                  $.getJSON('/acp/ajax/parent_item_variations.ajax.php',
                     { 'id' : $('#f_item_id').val() }, function(data) {
                    
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
                              .attr('name', 'f_variation_' + row.id);
                      newRowValueCell.append(valueBox);
                      
                      // Add title and value to row
                      newRow.append(newRowTitleCell);
                      newRow.append(newRowValueCell)
                      
                      $('#variation_rows').append(newRow);
                      
                      // Add attribute to list
                      $('#variation_list').val($('#variation_list').val()
                         + row.id + ',');
                      
                      count ++;
                      
                    });
                    
                    // Are there any variations?
                    if(count > 0)
                    {
                      // Show the Variations panel
                      $('#panel_variations').show();
            
                    }
                    else
                    {
                      $('#panel_variations').hide();
                    }
                    
                  });
                  
                  // Add title
                  $('#variations_title').html('Variations on ' + 
                    $('#f_item_sku').val());

    		        });
    		  
		});
		

    // Prevent form submission by enter key
    $('input').bind("keypress", function(e) {
      if(e.keyCode == 13)
      {
        return false;
      }
    });
    
    // Bind links
    $('#skip_attrs').click(function() {
      $.scrollTo('#panel_attributes', 1000);
    });
    
    // Block activity when the form is submitted
    $('#addItemForm').submit(function() {
      loadingScreen();
    });
    
    // Try to download a draft if one exists
    var draftText = 'Draft of description for ' + $('#f_sku').val();
    $.get('/acp/ajax/draft_get.ajax.php?description=' + draftText, function(data) {
    
      if($('#description').val() == '')
      {
        $('#description').val(data);
      }
      
    });
    
    // Detect classification changes and load attributes as required
    $('#dd_f_classification').change(function() {
      
      // Load the attributes in to view
      $('#attribute_rows').children().remove();
      
      // Download attributes
      $.getJSON('/acp/ajax/default_attributes.ajax.php', 
        { 'id' : $(this).val() }, function(data) {
        
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
        if(count > 0 && !IS_CHILD)
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
	                     $('<img rel="' + jsonReply.id + '" src="' + 
	                       jsonReply.thumbnails.thm + '" />')
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
      $.get('./ajax/image_remove.ajax.php', {'id' : $(this).attr('rel') });
      
      // Hide element
      $(this).remove();
      
      // Sync form
      $('#f_image_list').val(imageList.join(','));
      
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
    
    // Copy?
    <?php
    
      if($item)
      {
      
    ?>
    
    setTimeout(copySelectCategory, 500);
    
    <?php
      }
    ?>
    
  });
  
  /**
   * Automatically save the draft of the description if required
   * @return boolean
   */
  function autoSave()
  { 
    // Schedule another attempt
    setTimeout(autoSave, 8000);
    
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
  
  /**
   * Select subcategory callback
   * @return boolean
   */
  function copySelectSubcategory()
  {
    // Verify PHP value
    if("<?php print $item ? $item->subcategory_id : ''; ?>" == "-1")
    {
      return false;
    }
  
    $('a[subcat="<?php print $item ? $item->subcategory_id : ''; ?>"]').click();
    
    return true;
  }
  
  /**
   * Select category callback
   * @return boolean
   */
  function copySelectCategory()
  {
    // Verify PHP value
    if("<?php print $item ? $item->category_id : ''; ?>" == "-1")
    {
      return false;
    }
  
    // Select category and subcategory
    $('#category_tree').scrollTo($('a[cat="<?php print $item ? $item->category_id : ''; ?>"]'), 300);
    $('a[cat="<?php print $item ? $item->category_id : ''; ?>"]').click();
    setTimeout('copySelectSubcategory()', 500);
    
    return true;
  }
  
  /**
   * Select subcategory callback
   * @return boolean
   */
  function selectSubcategory(subcategoryID)
  {
    $('a[subcat="' + subcategoryID + '"]').click();
    
    return true;
  }

</script>

<h1 id="title"><?php print ($item ? 'Create a Copy of ' . $item->sku : 'Add an Item'); ?></h1>
<br />

<form action="./?act=inventory&mode=add_do" method="post" id="addItemForm">

<input type="hidden" name="f_parent_id" id="f_parent_id" value="-1" />

<?php
  if(!$item)
  {
?>

<div class="panel" id="parentItemSelection" style="margin-bottom: 5px">
  <div class="title">Select Parent Item</div>
  <div class="contents" style="padding: 0px 20px 0px 20px;">
    
    <!-- Load from Child options -->

      <br />

      <p>
        
        <strong>If you would like this item to have a parent, select it here.</strong><br /><br />
        
        Information from the parent item will be automatically loaded in to the fields in this view.<br />
        Any information already filled in will be overwritten.
      
      </p>

    <table class="fields" style="margin-left: 10px;">
      <tbody>

        <tr class="last">
          <td class="key" style="width: 160px;">
            <strong>Parent Item Virtual SKU</strong><br />
          </td>
          <td class="value">
            <input type="hidden" name="f_item_id" id="f_item_id" />
            <input type="text" style="width: 100px;" name="f_item_sku" id="f_item_sku" />
            &nbsp; <span id="f_item_ok" class="checkmark">&nbsp;</span>
                    
            <span id="loadParentButton" class="button" style="display: none;">
              <a href="#">
                <span class="img" style="background-image:url(/acp/static/icon/tick-circle.png)">&nbsp;</span>
                Use As Parent
              </a>
            </span>
            
          </td>
        </tr>
       
      </tbody>
    </table>
    
  </div>
  
</div>

<div id="panel_variations" style="display:none;">
  
  <div class="panel">
    <div class="title" id="variations_title">Variations on Default</div>
    <div class="message">
      <p>
        <span style="float: left; margin-right: 0.3em;" class="ui-icon ui-icon-info"></span>
        <strong>Required Fields</strong> - You need to complete all of the fields in this panel.
        <br class="clear" />
      </p> 
    </div>
    <div class="contents">
      
      <!-- Variations -->
  
      <p>
        
        <strong>Choose values for the variation options associated with this items parent.</strong><br />
        
      </p>
      
      <input type="hidden" name="f_variation_list" value="" id="f_variation_list" />
      
      <div style="padding: 10px">
      
        <table class="data">
          <thead>
            <tr class="header">
              <td>
                Variation Name
              </td>
              <td>
                Set Variation Value
              </td>
            </tr>
          </thead>
          <tbody id="variation_rows">
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

<br />

<?php
  }
  else
  {
?>

<div class="panel">
  <div class="contents">
    
    
    <h3>About Copying Items</h3>
    
    <p>
      This view allows you to create a copy of an existing item.<br />
      Some of the fields below have already been filled in based on <em><?php print $item->sku; ?></em>.
    </p>

  </div>
</div>

<br />

<?php
  }
?>


<div class="panel">
  <div class="title">Basic Information</div>
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
            <input type="text" name="f_sku" id="f_sku" style="width: 100px;" />
          </td>
        </tr>
        
        <tr>
          <td class="key">
            <strong>Name</strong><br />
            The name of this item.
          </td>
          <td class="value">
            <?php if($item) { ?><span class="grey">Copied from <?php print $item->sku; ?></span><br /><br /><?php } ?>
            <input type="text" name="f_name" id="f_name" style="width: 250px;" value="<?php print ($item ? $item->name : '') ?>" />
          </td>
        </tr>
        
        <tr id="selectClassification">
          <td class="key">
            <strong>Classification</strong><br />
            The classification of this item.
          </td>
          <td class="value">
          <?php if($item) { ?><span class="grey">Copied from <?php print $item->sku; ?></span><br /><br /><?php } ?>
<?php

  // Query the database for Classifications
  $query = $BF->db->query();
  $query->select('*', 'bf_classifications')
        ->order('name', 'ASC')
        ->execute();
        
  // Create a UI element
  $dropDown = new DataDropDown('f_classification', $query, 'id', 'name', array('-1' => 'Default'));
  
  // Default?
  if($item)
  {
    $dropDown->setOption('defaultSelection', $item->classification_id);
  }
  
  print $dropDown->render();

?>

  &nbsp; <a id="skip_attrs" style="display:none;" href="#">Modify Attributes...</a>

          </td>
        </tr>
        
        <tr id="selectCategory">
          <td class="key">
            <strong>Category</strong><br />
            The category/subcategory into which this item should be placed.
          </td>
          <td class="value">
            <?php if($item) { ?><br /><span class="grey">Copied from <?php print $item->sku; ?></span><br /><?php } ?>
            <div id="category_tree"></div>
            <input type="hidden" name="f_category" id="f_category" value="-1" />
            <input type="hidden" name="f_subcategory" id="f_subcategory" value="-1" />
          </td>
        </tr>
        
        <tr>
          <td class="key">
            <strong>Trade Price</strong><br />
            The basic trade price of this item.<br />
            This will be displayed to dealers with appropriate permissions.
          </td>
          <td class="value">
            <?php if($item) { ?><span class="grey">Copied from <?php print $item->sku; ?></span><br /><br /><?php } ?>
            GBP <input type="text" id="f_trade_price" name="f_trade_price" style="width: 50px;" class="autoselect"
               value="<?php print ($item ? $item->trade_price : '0.00') ?>" />
          </td>
        </tr>
        
        <tr>
          <td class="key">
            <strong>Pro-Net Price</strong><br />
            The Pro-Net price of this item.<br />
            This will be displayed to dealers with appropriate permissions.
          </td>
          <td class="value">
            <?php if($item) { ?><span class="grey">Copied from <?php print $item->sku; ?></span><br /><br /><?php } ?>
            GBP <input type="text" id="f_pro_net_price" name="f_pro_net_price" style="width: 50px;" class="autoselect"
              value="<?php print ($item ? $item->pro_net_price : '0.00') ?>" />
          </td>
        </tr>
        
        <tr>
          <td class="key">
            <strong>Pro-Net Quantity</strong><br />
            The number of units a dealer must buy to pay the Pro-Net price.<br />
            This will be displayed to dealers with appropriate permissions.
          </td>
          <td class="value">
            <?php if($item) { ?><span class="grey">Copied from <?php print $item->sku; ?></span><br /><br /><?php } ?>
            <input type="text" id="f_pro_net_qty" name="f_pro_net_qty" style="width: 50px;" class="autoselect"
              value="<?php print ($item ? $item->pro_net_qty : '0') ?>" />
          </td>
        </tr>
        
        <tr>
          <td class="key">
            <strong>Wholesale Price</strong><br />
            The price that dealers marked as wholesale will pay for this item.
          </td>
          <td class="value">
            <?php if($item) { ?><span class="grey">Copied from <?php print $item->sku; ?></span><br /><br /><?php } ?>
            GBP <input type="text" id="f_wholesale_price" name="f_wholesale_price" style="width: 50px;" class="autoselect" value="<?php print ($item ? $item->wholesale_price : '0.00') ?>" />
          </td>
        </tr>  
              
        <tr>
          <td class="key">
            <strong>RRP / MSRP</strong><br />
            The displayed RRP of this item.
          </td>
          <td class="value">
            <?php if($item) { ?><span class="grey">Copied from <?php print $item->sku; ?></span><br /><br /><?php } ?>
            GBP <input type="text" id="f_rrp_price" name="f_rrp_price" style="width: 50px;" class="autoselect"
              value="<?php print ($item ? $item->rrp_price : '0.00') ?>" />
          </td>
        </tr>

        <tr class="last">
          <td class="key">
            <strong>Stock Levels</strong><br />
            Your stock levels for this item.<br />
            Display of this option may be <a target="_blank" href="./?" class="new" />configured</a>
          </td>
          <td class="value">
            Free: <input type="text" name="f_stock_free" value="0" style="width: 50px;" class="autoselect" /> &nbsp; &nbsp; &nbsp;
            Held: <input type="text" name="f_stock_held" value="0" style="width: 50px;" class="autoselect" />
          </td>
        </tr>
                
      </tbody>
    </table>
    
  </div>
  
</div>

<!-- Savable at this point -->

<br />

<div class="panel" style="border: 1px solid #50954b">
  <div class="contents">
    <p style="padding: 7px 0px 0px 5px; float: left; color: #50954b;">
      <strong>You may continue and add more information to the item, or save the item now and exit.</strong>
    </p>
    <input class="submit ok" type="submit" style="float: right;" value="Save and Exit" />
    <br class="clear" />
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
            <?php if($item) { ?><span class="grey">Copied from <?php print $item->sku; ?></span><br /><br /><?php } ?>
<?php

  // Query the database for Brands
  $query = $BF->db->query();
  $query->select('*', 'bf_brands')
        ->order('name', 'ASC')
        ->execute();
        
  // Create a UI element
  $dropDown = new DataDropDown('f_brand', $query, 'id', 'name', array('-1' => 'Default'));
  
  // Default?
  if($item)
  {
    $dropDown->setOption('defaultSelection', $item->brand_id);
  }
  
  print $dropDown->render();

?>
          </td>
        </tr>

        <tr>
          <td class="key">
            <strong>Availability Date</strong><br />
            If this item is out of stock, this date will be displayed.
          </td>
          <td class="value">
            <input type="text" name="f_stock_date" class="date" style="width: 100px;" />
          </td>
        </tr>

        <tr>
          <td class="key">
            <strong>Cost</strong><br />
            The cost of this item from the supplier.<br />
            This value will <em>never</em> be displayed outside of the <abbr title="Admin Control Panel">ACP</abbr>.
          </td>
          <td class="value">
            <?php if($item) { ?><span class="grey">Copied from <?php print $item->sku; ?></span><br /><br /><?php } ?>
            GBP <input type="text" id="f_cost_price" name="f_cost_price" style="width: 50px;" class="autoselect"
              value="<?php print ($item ? $item->cost_price : '0.00') ?>"/>
          </td>
        </tr>
      
        <tr class="last">
          <td class="key">
            <strong>Barcode</strong><br />
            The unique UPC/EAN barcode for this item.<br />
            You can use a Barcode Scanner to enter text into this field.
          </td>
          <td class="value">
            <input type="text" name="f_barcode" style="width: 150px;" />
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
    
      <?php if($item) { ?><br /><br /><span class="grey">Copied from <?php print $item->sku; ?></span><?php } ?>

    </p>
    
    <br />
    
<?php

  // Create a UI element to handle the creation of default attributes
  $attributeEditor = new FormListBuilder('keywords', 
    $item ? array_filter(explode(',', $item->keywords), function($item) {return $item;}) : array(), $BF);
  $attributeEditor->setOption('valueDescription', 'New Keyword:');
  $attributeEditor->setOption('listDescription', 'Item Keywords');
  $attributeEditor->setOption('emptyList', 'Use the field above to add ' .
                              'keywords to the item.');
  print $attributeEditor->render();

?>
    
  </div>
</div>

<br />

<div class="panel">
  <div class="title">Images</div>
  <div class="contents">
    
    <!-- Images -->
    <input type="hidden" name="f_image_list" id="f_image_list" value="" />

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

<div id="panel_attributes" style="display: none;">
  
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

<br />

<div class="panel" id="tagsPanel">
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
    
    <?php if($item) { ?>
      <br /><span class="grey" style="padding-left: 20px">Copied from <?php print $item->sku; ?></span><br /><br />
    <?php } ?>

    <textarea id="description" name="description" style="width:100%; height: 200px; border: 0; background:transparent;"><?php
      if($item)
      {
        print $item->description;
      }
    ?></textarea>    
        
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