<?php
/**
 * Module: Statistics
 * Mode: Add Custom Statistic
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
				return false;
		  },
		  search: function( event, ui ) {
		    $('#f_item_ok').removeClass('yes');
		  }
			
		});
  
  });

</script>

<h1>Add Custom Statistic</h1>
<br />

<form action="./?act=statistics&mode=custom_add_do" method="post">

<div class="panel">
  <div class="title">Custom Statistic Information</div>
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
            Select the item to monitor.<br />
            <span class="grey">Start typing a SKU or Item Name and select your choice.</span>
          </td>
          <td class="value">
            <input type="hidden" name="f_item_id" id="f_item_id" />
            <input type="text" style="width: 100px;" name="f_item_sku" id="f_item_sku" />
            &nbsp; <span id="f_item_ok" class="checkmark">&nbsp;</span>
          </td>
        </tr>
      
        <tr class="last">
          <td class="key">
            <strong>Aspect</strong><br />
            Choose the aspect of the item to track.
          </td>
          <td class="value">
            <select name="f_aspect">
              <option value="item-views">Views</option>
              <option value="item-searched">Clicked in Search</option>
            </select>
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
      <strong>Click the button to the right to save this custom statistic now.</strong>
    </p>
    <input class="submit ok" type="submit" style="float: right;" value="Save and Exit" />
    <br class="clear" />
  </div>
</div>

</form>