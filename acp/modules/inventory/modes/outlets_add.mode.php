<?php
/**
 * Module: Inventory
 * Mode: Add Outlet
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

// Item prefilled?
if($BF->inInteger('id'))
{
  $item = $BF->db->getRow('bf_items', $BF->inInteger('id'));
  
  if($item)
  {
    $SKU = $item->sku;
    $itemID = $item->id;
    ?>
      
      <script type="text/javascript">
      
        $(function() {
          $('#f_item_ok').addClass('yes');
          $('#f_dealer_name').focus();
        });
      
      </script>
      
    <?php
  }
}

?>

<script type="text/javascript">

  // The current URL
  var url = '';
  
  // Automatically search for changes to the URL
  function checkURL()
  {
    // Reschedule self
    setTimeout('checkURL()', 1000);
  }

  $(function() {
  
    $('#f_url').change(function() {
      
      // Show loader
      $('#loader').show();
      
      // Clear
      $('#f_actual_price').children().remove();
      
      // Obtain result set
      var results = $.getJSON('/acp/ajax/outlet_check_url.ajax.php', { 'url' : $('#f_url').val() }, function(data) {
        
        $.each(data, function(index, priceOption) {
        
          $('#f_actual_price').append('<option class="priceOption" value="' + index + '">' + priceOption + '</option>');
          
        });
        
        setValue();
       
        // Does the box need to be visible
        if($('#f_actual_price').children().length > 0)
        { 
          $('#actual_price_choice').show();
        }
        else
        {
          $('#actual_price_choice').hide();
        }
        
        // Hide loader
        $('#loader').hide();
        
      });
      
    });
    
    $('#f_actual_price').change(function() {
      
      // Set value
      setValue();
      
    });
    
    $('#f_dealer_name').autocomplete({
    
		  source: '/acp/ajax/autocomplete_dealer.ajax.php',
			minLength: 1,
			select: function( event, ui ) {
				$(this).val(ui.item.label);
				$('#f_dealer_id').val(ui.item.id);
				$('#f_dealer_ok').addClass('yes');
				return false;
		  },
		  search: function( event, ui ) {
		    $('#f_dealer_ok').removeClass('yes');
		  }
			
		});

    $('#f_item_sku').autocomplete({
    
		  source: '/acp/ajax/autocomplete_sku.ajax.php',
			minLength: 1,
			select: function( event, ui ) {
				$(this).val(ui.item.label);
				$('#f_item_id').val(ui.item.id);
				$('#f_item_ok').addClass('yes');
				return false;
		  },
		  search: function( event, ui ) {
		    $('#f_item_ok').removeClass('yes');
		  }
			
		});
  
  });

  function setValue()
  {
    $('#f_actual_price_value').val($('#f_actual_price option:selected').text());
    $('#f_actual_price_node').val($('#f_actual_price').val());
  }
  
</script>

<h1>Add Outlet</h1>
<br />

<form action="./?act=inventory&mode=outlets_add_do" method="post">

<div class="panel">
  <div class="title">Basic Outlet Information</div>
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

        <tr>
          <td class="key">
            <strong>Item SKU</strong><br />
            Select the item of which the sale price you would like to track.<br />
            <span class="grey">Start typing a SKU or Item Name and select your choice.</span>
          </td>
          <td class="value">
            <input type="hidden" name="f_item_id" id="f_item_id" value="<?php print $itemID; ?>" />
            <input type="text" style="width: 100px;" name="f_item_sku" id="f_item_sku" value="<?php print $SKU; ?>" />
            &nbsp; <span id="f_item_ok" class="checkmark">&nbsp;</span>
          </td>
        </tr>
      
        <tr class="last">
          <td class="key">
            <strong>Dealer</strong><br />
            Select a dealer to associate with this outlet.<br />
            <span class="grey">Start typing a Username or Dealer Name and select your choice.</span>
          </td>
          <td class="value">
            <input type="hidden" name="f_dealer_id" id="f_dealer_id" />
            <input type="text" style="width: 150px;" name="f_dealer_name" id="f_dealer_name" />
            &nbsp; <span id="f_dealer_ok" class="checkmark">&nbsp;</span>
          </td>
        </tr>
        
      </tbody>
    </table>
    
  </div>
</div>

<br />

<div class="panel">
  <div class="title">Outlet Data Source</div>
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

        <tr>
          <td class="key">
            <strong>Outlet URL</strong><br />
            Type the exact <abbr title="Website Address">URL</abbr> of the <em>item level</em> page on the Retail website.<br />
          </td>
          <td class="value">
            <input type="text" style="width: 250px;" name="f_url" id="f_url" />
            &nbsp; 
            
            <span class="button">
              <a href="#">
                <span class="img" style="background-image:url(/acp/static/icon/arrow-circle-double.png)">&nbsp;</span>
                Scan...
              </a>
            </span>
            
            &nbsp;
            <img class="middle" id="loader" src="/acp/static/image/aui-loader.gif" style="display: none;" />
            
                
            
          </td>
        </tr>
      
        <tr class="last" id="actual_price_choice" style="display:none">
          <td class="key">
            <strong>Actual Current Price</strong><br />
            Select the text that best represents the current price for this Outlet.<br />
            <span class="grey">Select the option that contains the price you want to track.</span>
          </td>
          <td class="value">
            <input type="hidden" name="f_actual_price_value" id="f_actual_price_value" />
            <input type="hidden" name="f_actual_price_node" id="f_actual_price_node" />
            <select name="f_actual_price" id="f_actual_price">
              
            </select>
          </td>
        </tr>
        
      </tbody>
    </table>
    
  </div>
</div>
<!--
<br />

<div class="panel">
  <div class="title">Outlet Notifications</div>
  <div class="contents fieldset">
        
    <table class="fields">
      <tbody>

        <tr class="last">
          <td class="key">
            <strong>Notify me when...</strong><br />
            Choose when you would like to receive an alert for this Outlet.<br />
            <span class="grey">You can change <em>how</em> you are notified in your Staff Account settings.</span>
          </td>
          <td class="value">
            <table style="width: 100%;">
              <tr>
                <td style="width: 240px;">
                  <input checked="checked" type="radio" value="never" name="f_notify" /> &nbsp; Never<br />
                  <input type="radio" value="percentage" name="f_notify" /> &nbsp; Percentage of RRP drops below...<br />
                  <input type="radio" value="delta" name="f_notify" /> &nbsp; Difference from RRP drops below...<br />
                </td>
                <td style="vertical-align: middle;">
                  <input type="text" style="width: 60px;" value="50" />
                </td>
              </tr>
            </table>

          </td>
        </tr>
    
      </tbody>
    </table>
    
  </div>
</div>-->

<br />

<div class="panel" style="border: 1px solid #50954b">
  <div class="contents">
    <p style="padding: 7px 0px 0px 5px; float: left; color: #50954b;">
      <strong>Click the button to the right to save this outlet now.</strong>
    </p>
    <input class="submit ok" type="submit" style="float: right;" value="Save and Exit" onclick="loadingScreen();" />
    <br class="clear" />
  </div>
</div>

</form>