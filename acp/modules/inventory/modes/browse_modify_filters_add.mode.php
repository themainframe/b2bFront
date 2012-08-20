<?php
/**
 * Module: Inventory
 * Mode: Browse - Add Filter
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

<script type="text/javascript">

  $(function() {
  
    $('.source').draggable({
      'helper' : 'clone',
      'revert' : 'invalid'
    });
    
    $('#new_value').keypress(function(event) {
      if(event.keyCode == 13)
      {
        // Close box
        $('#value_chooser').dialog('close');
        
        // Add the value
        $('#designer').append(
          $('<span />').
          addClass('value').
          addClass('block').
          html($('#new_value').val()).
          attr('rel', $('#new_value').val()).
          clone().
          bind('dblclick', function() {
            $(this).remove();
            checkQuery();
          })
        );
        
        // Reset value
        $(this).val('');
        
        // Check
        checkQuery();
      }
    });
    
    $('#designer').sortable({
    
      'connectWith' : '#designer',
      'placeholder': 'placehold',
      'distance': 20,
      'forcePlaceholderSize': true,
      'tolerance': 'pointer',
      'revert': 'true',
      'cursorAt': {'top' : 0, 'left' : 0},
      'stop' : function() {
        // Check
        checkQuery();
      }
    
    }).droppable({
    
      'drop' : function(event, ui) {
      
        if($(ui.draggable).hasClass('source'))
        {
        
          // Check if it is a value
          if($(ui.draggable).hasClass('value'))
          {
            // Ask for a value
            $('#value_chooser').dialog('open');
            return false;
          }
        
          $('#designer').append(
            ui.draggable.
            clone().
            removeClass('source').
            bind('dblclick', function() {
              $(this).remove();
              checkQuery();
            })
          );
          
          // Examine query
          checkQuery();
          
        }        
      }
    
    });
    
    $('#value_chooser').dialog({
      'autoOpen' : false,
      'modal' : true,
      'resizable' : false,
      'width' : 300,
      'height' : 140,
      'title' : 'Enter a value',
      'draggable' : false,
      'buttons' : {
        'OK' : function() {
          
          $(this).dialog('close');
        
          // Add the value
          $('#designer').append(
            $('<span />').
            addClass('value').
            addClass('block').
            html($('#new_value').val()).
            attr('rel', $('#new_value').val()).
            clone().
            bind('dblclick', function() {
              $(this).remove();
              checkQuery()
            })
          );
          
          $('#new_value').val('');

          // Check query
          checkQuery();
        }
      }
    });
  
  });
  
  /**
   * Check the current query
   * @return boolean
   */
  function checkQuery()
  {
    // Build JSON
    var result = '';
    $('#designer').children().each(function(index, element) {
      result += ($(element).attr('rel')) + ' ';
    });
    
    // Make a request
    $.get('/acp/ajax/query_builder_test.ajax.php', { 'query' : result },
    function(data) {
      if(data.length > 6)
      {
        // Invalid!
        $('#valid').hide();
        $('#invalid').show();
      }
      else
      {
        $('#valid').show();
        $('#valid_info').html('This filter is valid and matches ' + data + ' items.');
        $('#invalid').hide();
        
        // Copy into form field
        $('#f_query').val(result);
      }
    });
    
    return true;
  }

</script>

<div class="ghost" id="value_chooser" title="Type a value...">
  <p style="margin: 13px 0px 0px 13px;">
    Please enter a value: &nbsp; <input type="text" id="new_value" style="width: 100px;" />
  </p>
</div>

<h1>Add an Inventory Filter</h1>
<br />

<form action="./?act=inventory&mode=browse_modify_filters_add_do" method="post">

<div class="panel">
  <div class="title">Filter Information</div>
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
            A short name for the filter.<br />
            <span class="grey">20 Characters Max</span>
          </td>
          <td class="value">
            <input type="text" name="f_name" id="f_name" style="width: 150px;" />
          </td>
        </tr>
      </tbody>
    </table>
  
  </div>
</div>

<br />

<?php

  if($BF->config->get('tips'))
  {
  
?>

<div class="panel">
  <div class="contents">
    <p>
      To create a filter, you need to drag the blocks from the left into the Filter Designer to build conditions for showing individual rows.<br />
      You can remove blocks by double clicking them and change their order by dragging them.<br /><br />
      For example, building the phrase:
      
      <br /><br />
      
      <span class="block field">Trade Price</span>
      <span class="block compare">&lt;</span>
      <span class="block value">10.00</span>
      <span class="block boolean">And</span>
      <span class="block field">Free Stock</span>
      <span class="block compare">&gt;</span>
      <span class="block value">5</span>
      
      <br /><br />
      
      Would filter the current Inventory view to items that both have 5 or more free stock and a trade price lower than 10.00<br /><br />
      
      <?php print $BF->admin->turnOffTipsHint(); ?>
      
    </p>
  </div>
</div>

<br />

<?php

  }

?>

<input type="hidden" id="f_query" name="f_query" value="" />

<table style="width:100%;">

  <tbody>
  
    <tr>
  
      <td style="width: 200px;">
      

        
        <div class="panel">
          <div class="title">Literals</div>
          <div class="contents" style="word-wrap: none;">
            <span class="block value source" rel="value">New Value...</span>
          </div>
        </div>
        
        <br />
        
        <div class="panel">
          <div class="title">Fields</div>
          <div class="contents" style="word-wrap: none;">
            <span class="block field source" rel="f_trade_price">Trade Price</span>
            <span class="block field source" rel="f_pro_net_price">Pro Net Price</span>
            <span class="block field source" rel="f_rrp_price">RRP</span>
            <span class="block field source" rel="f_pro_net_qty">Pro Net Qty</span><br />
            <span class="block field source" rel="f_free_stock">Free Stock</span>
            <span class="block field source" rel="f_held_stock">Held Stock</span><br />
            <span class="block field source" rel="f_total_stock">Total Stock</span>
            <span class="block field source" rel="f_name">Name</span>
          </div>
        </div>

      
      </td>
      <td style="padding: 0px 15px 0px 15px;">
      
        <div class="panel" style="height: 295px; background:url(/acp/static/image/aui-grid.png);">
          <div class="title">Filter Designer</div>
          <div id="designer" class="contents" style="height: 100%;">
            
          </div>
        </div>
      
      </td>
      
      <td style="width: 200px;">
    
        <div class="panel">
          <div class="title">Comparisons</div>
          <div class="contents">
            <span class="block compare source" rel="equal">=</span>
            <span class="block compare source" rel="lt">&lt;</span>
            <span class="block compare source" rel="gt">&gt;</span>
            <span class="block compare source" rel="lte">&lt;=</span>
            <span class="block compare source" rel="gte">&gt;=</span><br />
            <span class="block compare source" rel="not_equal">&lt; &gt;</span>
            <span class="block compare source" rel="contains">Contains</span>
          </div>
        </div>
        
        
        <br />
        
        <div class="panel">
          <div class="title">Boolean</div>
          <div class="contents">
            <span class="block boolean source" rel="bool_and">AND</span>
            <span class="block boolean source" rel="bool_or">OR</span>
            <span class="block boolean source" rel="bool_xor">XOR</span>
            <span class="block boolean source" rel="bool_not">NOT</span><br />
            <span class="block boolean source" rel="bracket_l">(</span>
            <span class="block boolean source" rel="bracket_r">)</span>
          </div>
        </div>
      
      </td>
      
    </tr>
    
  </tbody>

</table>


<div id="invalid" class="panel" style="margin-top: 20px; display: none; border: 1px solid #c02626">
  <div class="contents">
    <p style="padding: 0px 0px 0px 5px; float: left; color: #c02626;">
      <strong>This filter is currently invalid. &nbsp; Please check your syntax.</strong>
    </p>
    <br class="clear" />
  </div>
</div>

<div id="valid" class="panel" style="margin-top: 20px; display: none; border: 1px solid #50954b">
  <div class="contents">
    <p style="padding: 7px 0px 0px 5px; float: left; color: #50954b;">
      <strong id="valid_info">This filter is valid and matches items.</strong>
    </p>
    <input class="submit ok" type="submit" style="float: right;" value="Save and Exit" />
    <br class="clear" />
  </div>
</div>

</form>