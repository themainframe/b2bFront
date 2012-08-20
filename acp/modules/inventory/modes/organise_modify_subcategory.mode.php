<?php
/**
 * Module: Inventory
 * Mode: Organisation Modify Subcategory
 * 
 * @copyright 2010-2011 Damien Walsh
 * @package b2bfront
 * @version 1.0
 * @author Damien Walsh
 */

// Context check
if(!defined('BF_CONTEXT_ADMIN') || !defined('BF_CONTEXT_MODULE'))
{
  exit();
}

// Verify Permissions
if(!$BF->admin->can('categories'))
{
?>
    <h1>Permission Denied</h1>
    <br />
    <p>
      You do not have permission to use this section of the ACP.<br />
      Please ask your supervisor for more information.
    </p>
<?php

exit();

}

// Get the ID of the subcategory
$ID = $BF->inInteger('id');

// Get the row information
$BF->db->select('*', 'bf_subcategories')
           ->where('id = \'{1}\'', $ID)
           ->limit(1)
           ->execute();
           
// Check the ID was valid
if($BF->db->count < 1)
{
  // Return the user to the selection interface
  header('Location: ./?act=inventory&mode=organise');
  exit();
}

// Retrieve the row
$row = $BF->db->next();

?>

<script type="text/javascript">
  
  $(function() {
    $('#f_item_sku').autocomplete({
    
		  source: '/acp/ajax/autocomplete_sku.ajax.php',
			minLength: 1,
			select: function( event, ui ) {
				
				$(this).val(ui.item.label);
				$('#f_item_id').val(ui.item.id);
				$('#f_item_ok').addClass('yes');
				
				// Find the images for this item
				$.getJSON('/acp/ajax/item_get_image_thumbnails.ajax.php',
				          { 'id' : ui.item.id },
				          function(data) {
				            
				            // Remove all images
				            $('div.image').remove();
				            $('#f_image_id').val('');
				            
				            // Hide row
				            $('#images_row').hide();
				            
				            // Add each image
				            $.each(data, function(index, row) {
				            
				              // Row visible
				              $('#images_row').show();
				              
				              // Add an item image
				              $('#category_images').append(
				                $('<div />').addClass('image')
				                            .css('background-image', 'url(' + row.url + ')')
				                            .click(function() {
				                              $('div.image').removeClass('selected_image');
				                              $(this).addClass('selected_image');
				                              $('#f_image_id').val($(this).attr('rel'));
				                            })
				                            .attr('rel', index)
				              );
				            });
                    
                    // Select first image
				            $('div.image:eq(0)').addClass('selected_image');
				            $('#f_image_id').val($('div.image:eq(0)').attr('rel'));
				          });
				
				return false;
		  },
		  search: function( event, ui ) {
		    $('#f_item_ok').removeClass('yes');
		  }
			
		}).keyup(function() {
		  $('#f_item_ok').removeClass('yes');
		});
  });
</script>

<h1>Subcategory: <?php print $row->name; ?></h1>
<br />

<form action="./?act=inventory&mode=organise_modify_subcategory_do&id=<?php print $BF->inInteger('id'); ?>" method="post">

<div class="panel">
  <div class="title">Category Information</div>
  <div class="message">
    <p>
      <span style="float: left; margin-right: 0.3em;" class="ui-icon ui-icon-info"></span>
      <strong>Required Fields</strong> - You need to complete all of the fields in this panel.
      <br class="clear" />
    </p> 
  </div>
  <div class="contents fieldset">
        
    <table class="fields">
      <tbody>
      
        <tr class="last">
          <td class="key">
            <strong>Subcategory Name</strong><br />
            A name to identify the subcategory.
          </td>
          <td class="value">
            <input value="<?php print $row->name; ?>" type="text" style="width: 250px;" name="f_name" />
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
      <strong>Click the button to the right to save this subcategory now.</strong>
    </p>
    <input class="submit ok" type="submit" style="float: right;" value="Save and Exit" />
    <br class="clear" />
  </div>
</div>

</form>