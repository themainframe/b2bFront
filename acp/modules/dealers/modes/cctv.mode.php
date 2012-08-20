<?php
/**
 * Module: Dealers
 * Mode: CCTV View
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
    getCCTV();
  });
  
  /**
   * Get active users
   */
  function getCCTV()
  {
    // Get the CCTV
    $.getJSON('./ajax/cctv.ajax.php', function(data) {
    
      $('#cctv_logs').children().remove();
      
      
      // Add each
      $.each(data, function(key, object) {
      
        // Decide colour
      
      
        var newRow = $('<tr />');
        var time = $('<td />').html(object.time)
                              .css('width', '30px');
                              
        var dealer = $('<td />').html(object.name)
                              .css('width', '150px');
                              
        var activity = $('<td />').html(object.activity);
        
        var actions = $('<td />').html('')
                                 .css('width', '90px');
                
        var tool_cell = $('<a />').addClass('tool')
                                  .attr('href', '#')
                                  .attr('title', 'View Basket')
                                  .html('View Basket')
                                  .click(function() { showBasket($(this).parent().parent().attr('userid')); });
  
        tool_image = $('<img />').attr('src', '/acp/static/icon/magnifier.png')
                                 .attr('alt', 'View basket');
        
        // Add image to tool button
        $(tool_cell).prepend(tool_image); 
        $(actions).append(tool_cell);
               
        // Basket?
        if(object.basketCount > 0)
        {
          basketItem = $('<span />')
                       .css({
                         'background-image' : 'url(/acp/static/icon/shopping-basket.png)',
                         'padding-left' : '20px',
                         'font-weight' : 'bold',
                         'background-repeat' : 'no-repeat',
                         'float' : 'right'
                       })
                       .html(object.basketCount);
        
          $(activity).append(basketItem);
        }
               
        // Create row
        $(newRow).attr('userid', object.id)
                 .append(time)
                 .append(dealer)
                 .append(activity)
                 .append(actions);
                 
   
        // Insert
        $('#cctv_logs').append(newRow);
        
      });  
    
    });
      
    setTimeout(getCCTV, 800);
  }

  function showBasket(id)
  {
    // Clear
    $('#basket_rows').children().remove();
    
    // Download results
    $.getJSON('/acp/ajax/cctv_basket.ajax.php', {'id' : id },
      function(data)
      {
        count = 0;
      
        // Populate rows
        $(data).each(function(k, i) {
          
          // Create a row
          row = $('<tr />');
          sku_cell = $('<td />').html(i.sku);
          name_cell = $('<td />').html(i.name);
          qty_cell = $('<td />').html(i.quantity);

  
          // Build
          $(row).append(sku_cell)
                .append(name_cell)
                .append(qty_cell);

          // Add to table
          $('#basket_rows').append(row);
          
          count ++;
        });    
      
        if(count == 0)
        {
          // Show "no items"
          $('#no_items').show();
          $('#basket_table').hide();
        }
        else
        {
          $('#basket_table').show();
          $('#no_items').hide();
        }
      }
    );
    
    // Open dialog
    $('#basket').dialog('open');
 
    return true;
  }
  
  $(function() {
    
    $('#basket').dialog({
      'autoOpen' : false,
      'modal' : true,
      'width': 750,
      'height': 350,
      'title' : 'Basket Overview',
      'buttons': {
                   'Ok' : function() {$(this).dialog('close'); }
                },
    });
    
  });

</script>

<h1>Website CCTV</h1>
<br />

<div class="ghost" id="basket" style="padding: 0">
  <table class="data" id="basket_table">
    <thead>
      <tr class="header">
        <td style="width: 90px">SKU</td>
        <td>Name</td>
        <td style="width: 90px">Quantity</td>
      </tr>
    </thead>
    <tbody id="basket_rows">
      
    </tbody>
  </table>
  <div id="no_items" style="text-align: center; font-weight: bold; display: none">
    <br /><br /><br />
    The dealer currently has no items in their basket.
  </div>
</div>


<table id="cctv1" class="data">
  <thead>
    <tr class="header">
      <td>
        &nbsp;
      </td>
      <td>
        Dealer
      </td>
      <td>
        Activity
      </td>
      <td style="text-align: right; padding-right: 20px;">
      	Actions
      </td>
    </tr>
  </thead>
  <tbody id="cctv_logs">

  </tbody>
</table>

<br /><br />

<strong>Key: </strong> &nbsp;

<span class="label" style="background-color: #adbaf7">Bikes</span> &nbsp; 
<span class="label" style="background-color: #92f984">Accessory</span> &nbsp; 
<span class="label" style="background-color: #d4c354">Ordering</span> &nbsp; 

<br /><br />
