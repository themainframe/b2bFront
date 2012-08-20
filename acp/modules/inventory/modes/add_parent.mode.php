<?php
/**
 * Module: Inventory
 * Mode: Add Parent Item
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
    CKEDITOR.replace( 'description' );
  });
  
</script>
<script type="text/javascript">
  
  // Store a change buffer
  var lastChange = '';
  
  // Create the category tree
  $(function() {
      
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

    
    // Block activity when the form is submitted
    $('#addItemForm').submit(function() {
      loadingScreen();
    });
    
    // Try to download a draft if one exists
    var draftText = 'Draft of description for Parent ' + $('#f_sku').val();
    $.get('/acp/ajax/draft_get.ajax.php?description=' + draftText, function(data) {
      $('#description').val(data);
    });

    // Force reload
    $('#dd_f_classification').change();
    
    // Allow removal of images
    $('div.images img').live('click', function() {
      
      // Remove the temp name from the list
      $('#f_image_list').val($('#f_image_list').val().replace(new RegExp($(this).attr('rel') + '\n','m'), ''));
      
      // Hide element
      $(this).remove();
      
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
    
    var description = 'Draft of description for Parent ' + $('#f_sku').val();
    
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

<h1>Add a Parent Item</h1>
<br />

<form action="./?act=inventory&mode=add_parent_do" method="post" id="addItemForm">

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
            <strong>Virtual SKU</strong><br />
            A SKU that will represent this parent item.<br />
            This value will <em>never</em> be displayed outside of the <abbr title="Admin Control Panel">ACP</abbr>.
          </td>
          <td class="value">
            <span style="background: #fff; border: 1px solid #a2a2a2; padding: 3px 3px 3px 0px; margin: 10px 0 0 0;">
              <input type="text" name="f_sku" id="f_sku" style="border: 0; width: 70px;" />
              &nbsp; -PAR
            </span>
          </td>
        </tr>
        
        <tr>
          <td class="key">
            <strong>Name</strong><br />
            The name of this parent item.
          </td>
          <td class="value">
            <input type="text" name="f_name" style="width: 250px;" />
          </td>
        </tr>
        
        <tr>
          <td class="key">
            <strong>Classification</strong><br />
            The classification of this parent item.
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
  print $dropDown->render();

?>

            &nbsp; <a id="skip_attrs" style="display:none;" href="#">Modify Attributes...</a>

          </td>
        </tr>
        
        <tr>
          <td class="key">
            <strong>Category</strong><br />
            The category/subcategory into which this parent item should be placed.<br />
            It's children will also be placed in this location.
          </td>
          <td class="value">
            <div id="category_tree"></div>
            <input type="hidden" name="f_category" id="f_category" value="-1" />
            <input type="hidden" name="f_subcategory" id="f_subcategory" value="-1" />
          </td>
        </tr>
        
        <tr>
          <td class="key">
            <strong>Default Trade Price</strong><br />
            The basic trade price of this item's children.<br />
            This will be displayed to dealers with appropriate permissions.
          </td>
          <td class="value">
            GBP <input type="text" name="f_trade_price" value="0.00" style="width: 50px;" class="autoselect" />
          </td>
        </tr>
        
        <tr>
          <td class="key">
            <strong>Default Pro-Net Price</strong><br />
            The Default Pro-Net price of this items's children.<br />
            This will be displayed to dealers with appropriate permissions.
          </td>
          <td class="value">
            GBP <input type="text" name="f_pro_net_price" value="0.00" style="width: 50px;" class="autoselect" />
          </td>
        </tr>
        
        <tr>
          <td class="key">
            <strong>Default Pro-Net Quantity</strong><br />
            The number of units a dealer must buy to pay the Pro-Net price.<br />
            This will be displayed to dealers with appropriate permissions.
          </td>
          <td class="value">
            <input type="text" name="f_pro_net_qty" value="0" style="width: 50px;" class="autoselect" />
          </td>
        </tr>
        
        <tr>
          <td class="key">
            <strong>Default Wholesale Price</strong><br />
            The price that dealers marked as wholesale will pay for this item's children.
          </td>
          <td class="value">
            GBP <input type="text" name="f_wholesale_price" value="0.00" style="width: 50px;" class="autoselect" />
          </td>
        </tr>  
              
        <tr class="last">
          <td class="key">
            <strong>Default RRP / MSRP</strong><br />
            The default displayed RRP of this item's children.
          </td>
          <td class="value">
            GBP <input type="text" name="f_rrp_price" value="0.00" style="width: 50px;" class="autoselect" />
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
      <strong>You may continue and add more information to the parent item, or save the parent item now and exit.</strong>
    </p>
    <input class="submit ok" type="submit" style="float: right;" value="Save and Exit" />
    <br class="clear" />
  </div>
</div>


<br />

<div class="panel">
  <div class="title">Parent Item Variation Options</div>
  <div class="contents">
    
    <h3>Variation Options</h3>
    <p>
      This item will act as a template for its children.<br />
      You can add variation options to declare the ways in which this item's children are different from one another.
      <br /><br />
      
      For example, for a pair of gloves, you might add Size and Colour as variations to show
      that the gloves come in various combinations of size and colour.
    </p>
    
    <br />
    
<?php

  // Create a UI element to handle the creation of default attributes
  $attributeEditor = new FormListBuilder('variations', array(), $BF);
  $attributeEditor->setOption('valueDescription', 'New Variation Option Name:');
  $attributeEditor->setOption('listDescription', 'Parent Item Variation Options');
  $attributeEditor->setOption('emptyList', 'Use the field above to add ' .
                              'variation options to the parent item.');
  print $attributeEditor->render();

?>
    
  </div>
</div>

<br />


<div id="panel_attributes" style="display: none; margin-bottom: 15px;">
  
  <div class="panel">
    <div class="title" id="attributes_title">Item Attributes</div>
    <div class="contents">
      
      <!-- Attributes -->
  
      <p>
        
        <strong>Item attributes are custom information fields relevant to this type of item.</strong><br />
        Attribute fields are automatically shown upon selecting a classification for a new item.<br /><br />
        
        All child items will inherit these values.
        
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
      <strong>Click the button to the right to save this parent item now.</strong>
    </p>
    <input class="submit ok" type="submit" style="float: right;" value="Save and Exit" />
    <br class="clear" />
  </div>
</div>

</form>