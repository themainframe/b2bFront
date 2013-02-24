<?php
/**
 * Module: Inventory
 * Mode: Browse
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

// Find my admin row to modify my default view.
$myAdminRow = $BF->db->getRow('bf_admins', $BF->admin->AID, 'id', true);

// Check if a default is set
if($BF->in('f_term') == '' && $BF->in('f_in') == '' && $BF->in('f_filter') == '' && $BF->in('f_tag') == '')
{    
  if($myAdminRow->inventory_default_view != '')
  {
    // Navigate
    header('Location: ./?act=inventory&mode=browse&' . $myAdminRow->inventory_default_view);
  }
}

// Override label display
if($BF->in('f_label') == '')
{
  $BF->setIn('f_label', '-1');
}

// Override tag display
if($BF->in('f_tag') == '')
{
  $BF->setIn('f_tag', '-1');
}

// Force parent visibility
$BF->setIn('x_show_parents', '1');

/**
 * Retrieve the current default view query string
 * @return string
 */
function getDefaultViewQS()
{
  global $BF;
  
  // A string representative of the current viewport
  return 'f_term=' . $BF->in('f_term') . '&f_in=' . $BF->in('f_in') . 
         '&f_filter=' . $BF->inInteger('f_filter') . '&f_category=' . 
         $BF->inInteger('f_category') . '&inventory_pg=' .
         $BF->inInteger('inventory_pg') . '&inventory_lpp=' .
         $BF->inInteger('inventory_lpp') . '&inventory_order_d=' . 
         $BF->in('inventory_order_d') . '&inventory_order=' . 
         $BF->inInteger('inventory_order') . '&f_tag=' . 
         $BF->inInteger('f_tag') . '&x_show_parents=' . 
         $BF->in('x_show_parents') . '&f_classification=' . 
         $BF->in('f_classification') . '&f_label=' . 
         $BF->in('f_label') . '&f_brand=' . 
         $BF->in('f_brand');
}

// Make this the default view
$queryString = getDefaultViewQS();
               
// Save the default view unless requested otherwise
if($BF->in('no_save_default_view') != '1')
{
  $BF->db->update('bf_admins', array(
                        'inventory_default_view' => $queryString
                     ))
             ->where('id = \'{1}\'', $BF->admin->AID)
             ->limit(1)
             ->execute();
}

// Load label colours
$labelColourParser = new PropertyList();
$labelColours = $labelColourParser->parseFile(
  BF_ROOT . '/acp/definitions/inventory_label_colours.plist');
$GLOBALS['labelColours'] = $labelColours;

// Failure?
if(!$labelColours)
{
  $BF->log('Unable to load /acp/definitions/inventory_label_colours.plist');
}
               
?>

<script type="text/javascript">
  
  $(function() {
    
    //
    // Set up menus
    //
    
    $('.more').each(function(i, r) {
      $(r).createMenu({
        content : $('#menu').html(),
        flyOut : true,
        'id': $(this).attr('row')
      });
    });

    $('.pmore').each(function(i, r) {
      $(r).createMenu({
        content : $('#pmenu').html(),
        flyOut : true,
        'id': $(this).attr('row')
      });
    });
    
  });
  
  /**
   * Confirm removal of an item
   * @param integer itemID The ID of the Item to remove
   * @return boolean
   */
  function removeItem(itemID)
  {
    confirmation('Really remove this item?',
       function() { 
         window.location = 
           './?act=inventory&mode=browse_remove_do&id=' + itemID;
       }
    );
  }

  /**
   * Confirm removal of a parent item
   * @param integer parentItemID The ID of the Parent Item to remove
   * @return boolean
   */
  function removeParentItem(itemID)
  {
    confirmation('Really remove this parent item?',
       function() { 
         window.location = 
           './?act=inventory&mode=browse_remove_parent_do&id=' + itemID;
       }
    );
    
    return true;
  }
  
</script>

<div class="ghost" id="menu">
  <ul>
    <li>
      <a style="background-image:url(/acp/static/icon/documents.png);"
       class="menu_a" href="./?act=inventory&mode=add_standard&id={id}">
        Copy...
      </a>
    </li>
    <li>
      <a style="background-image:url(/acp/static/icon/money-coin.png);"
       class="menu_a" href="./?act=inventory&mode=browse_orders&id={id}">
        Show Orders...
      </a>
    </li>
    <li>
      <a style="background-image:url(/acp/static/icon/store--plus.png);"
       class="menu_a" href="./?act=inventory&mode=outlets_add&id={id}">
        Add Outlet...
      </a>
    </li>
  <!--
    <li>
      <a style="background-image:url(/acp/static/icon/box-document.png);"
       class="menu_a" href="./?act=inventory&mode=browse_stock_audit&id={id};">
        Stock Audit...
      </a>
    </li>
  -->
    <li>
      <a style="background-image:url(/acp/static/icon/cross-circle.png);"
       class="menu_a" href="javascript:void(removeItem({id}));">
        Remove...
      </a>
    </li>
  </ul>
</div>

<div class="ghost" id="pmenu">
  <ul>
    <li>
      <a style="background-image:url(/acp/static/icon/node-insert-next.png);"
       class="menu_a" href="./?act=inventory&mode=browse_createchildren&id={id}">
        Create Children...
      </a>
    </li>
    <li>
      <a style="background-image:url(/acp/static/icon/cross-circle.png);"
       class="menu_a" href="javascript:void(removeParentItem({id}));">
        Remove...
      </a>
    </li>
  </ul>
</div>

<h1>Browse Inventory</h1>
<br />

<?php
  if($BF->config->get('tips'))
  {
?>

<div class="panel">
  <div class="contents">
    
    <h3>About the Inventory Browse View</h3>
    
    <p>
      This is the main view for locating items in the inventory.  It provides several powerful search and filtering features to make it quick and easy to find the items you are looking for.<br />
      
      Clicking on values in the table below allows you to change them on the fly.  You can also drag values down the columns in a similar style to Spreadsheet applications to fill cells quickly.<br /><br />
      
      <?php print $BF->admin->turnOffTipsHint(); ?>
    </p>

  </div>
</div>

<br />

<?php
  }
?>


<form action="./" method="get" name="browseFilterForm" id="browseFilterForm">

<?php

  print Tools::getQueryStringInputs();

?>

<div class="panel">
  <div class="title">Search &amp; Filter</div>
  <div class="contents">
    <div style="height: 100%;">
      <div class="threebox">
        <div class="box" style=" width: 270px">
          <div style="padding: 0px 0px 0px 5px">
            
            <table style="width: 100%;">
              <tr>
                <td style="width: 50%;">            
              
                  <strong>Search for:</strong><br />
                  <input type="text" name="f_term" style="width: 80%;"  id="term" /> <br /> in &nbsp;  
                  <select name="f_in" style="margin-top: 10px;">
                    <option value="sku">SKU</option>
                    <option value="name">Name</option>
                    <option value="description">Description</option>
                  </select>
                      
                </td>
                <td>
                
                  <strong>In Category:</strong><br />
<?php

  // Query the database for Categories
  $query = $BF->db->query();
  $query->select('*', 'bf_categories')
        ->order('name', 'ASC')
        ->execute();
        
  // Create a UI element
  $dropDown = new DataDropDown('f_category', $query, 'id', 'name',
                               array('-2' => 'Any', '-1' => 'Uncategorised'));
  $dropDown->setOption('maxTextLength', 12);
  $dropDown->setOption('css', array(
    'margin-top' => '10px'
  ));
  print $dropDown->render();

?>

                  <br /><br />
                  <a class="new" target="_blank" href="./?act=inventory&mode=organise" title="Categories">
                    Categories...
                  </a>
                    
                </td>
              </tr>
            </table>
              
          </div>
        </div>
        <div class="box" style="border-left: 1px solid #cfcfcf; border-right: 1px solid #cfcfcf; padding-left: 15px; width: 280px">



            <table style="width: 100%;">
              <tr>
                <td style="width: 50%;">            

 
                  <strong>Classified As:</strong><br />
<?php

  // Query the database for Classifications
  $query = $BF->db->query();
  $query->select('*', 'bf_classifications')
        ->order('name', 'ASC')
        ->execute();
        
  // Create a UI element
  $dropDown = new DataDropDown('f_classification', $query, 'id', 'name',
                               array('-2' => 'Any', '-1' => 'Unclassified'));
  $dropDown->setOption('maxTextLength', 13);
  $dropDown->setOption('css', array(
    'margin-top' => '10px'
  ));
  print $dropDown->render();

?>

                  <br /><br />
                  <a class="new" target="_blank" href="./?act=inventory&mode=classifications" title="Classifications">
                    Classifications...
                  </a>
                    

              
                </td>
                <td>


                 <div style="padding: 0px 10px 0px 20px;">
                 <strong>Filter:</strong><br />
        
<?php

  // Query the database for Filers
  $query = $BF->db->query();
  $query->select('*', 'bf_admin_inventory_browse_filters')
        ->order('name', 'ASC')
        ->execute();
        
  // Create a UI element
  $dropDown = new DataDropDown('f_filter', $query, 'id', 'name', array('-1' => 'Any'));
  $dropDown->setOption('maxTextLength', 15);
  $dropDown->setOption('css', array(
    'margin-top' => '10px'
  ));
  print $dropDown->render();

?> 
          
                    <br /><br />
                    <a href="./?act=inventory&mode=browse_modify_filters" title="Create Filters">Add / Remove filters...</a>
                  </div>

                
                </td>
              </tr>
            </table>

        </div>
        
        
        
        
        <div class="box" style="width: 150px;">
        
                         <div style="padding: 0px 10px 0px 20px;">
                 <strong>Labelled:</strong><br />
        
<?php

  // Query the database for Labels
  $query = $BF->db->query();
  $query->select('*', 'bf_item_labels')
        ->order('name', 'ASC')
        ->execute();
        
  // Create a UI element
  $dropDown = new DataDropDown('f_label', $query, 'id', 'name', array('-1' => 'Any'));
  $dropDown->setOption('maxTextLength', 15);
  $dropDown->setOption('css', array(
    'margin-top' => '10px'
  ));
  print $dropDown->render();

?> 
          
                    <br /><br />
                    <a href="./?act=inventory&mode=browse_labels" title="Create Labels">Add / Remove labels...</a>
                  </div>
                  
                  
        </div>
        
        
          
        
        <div class="box" style="width: 150px;">
        
                         <div style="padding: 0px 10px 0px 20px;">
                 <strong>Brand:</strong><br />
        
<?php

  // Query the database for Brands
  $query = $BF->db->query();
  $query->select('*', 'bf_brands')
        ->order('name', 'ASC')
        ->execute();
        
  // Create a UI element
  $dropDown = new DataDropDown('f_brand', $query, 'id', 'name',
                                 array('-2' => 'Any', '-1' => 'Unbranded'));
  $dropDown->setOption('maxTextLength', 15);
  $dropDown->setOption('css', array(
    'margin-top' => '10px'
  ));
  print $dropDown->render();

?> 
          
                    <br /><br />
                    <a href="./?act=inventory&mode=brands" title="Create Labels">Add / Remove brands...</a>
                  </div>
   
        </div>      

                  <div style="float: right; padding: 5px 5px 5px 5px; text-align: center; ">


            &nbsp; <input type="submit" value="Apply" class="submit ok">
            
            <br /><br />  
            
              <span class="button bad">                
                <a style="color: #fff;" href="./?act=inventory&mode=browse&f_term=&f_in=sku&f_filter=-1">
                  Clear
                </a>
              </span>
           
          </div>


        <br style="clear: both;" />
      </div>
      
    </div>
  </div>
</div>

</form>

<br />

<script type="text/javascript">

  function showUsedLimits()
  {
    $('select.data_dropdown').each(function() {
      if($(this).attr('name') != 'f_in')
      {
        if($(this).children('option:selected').text() != 'Any')
        {
          $(this).addClass('used');
        }
        else
        {
          $(this).removeClass('used');
        }
      }
    });
    
    // Check value box
    if($('#term').val() != '')
    {
      $('#term').addClass('used');
    }
    else
    {
      $('#term').removeClass('used');
    }
  }

  $(function() {
    
    $('a.toolbar').click(function() {
      
      // Stop notifications
      stopNotifications(); 

      // Set the form and submit
      $('#select_do').val($(this).attr('rel'));
      $('#multi_form').submit();
      
    });
    
    // Auto-update all dropdowns
    $('select').change(function() {
      showUsedLimits();
    });
    
    $('#term').keyup(function() {
      showUsedLimits();
    });
   
    showUsedLimits();
    
  });
  

  
</script>

<form id="multi_form" method="post" action="./?act=inventory">
<input type="hidden" id="select_do" name="mode" value="" />

<div class="panel">
  <div class="contents">
  
    <span class="grey" style="font-weight: bold;">With selected:</span>
    
    &nbsp;&nbsp;
    
    <a rel="browse_multi_remove" class="toolbar tool" href="#" title="Remove the selected items.">
      <img src="/acp/static/icon/cross-circle.png" alt="Remove" />
      Remove
    </a>
    
    &nbsp;&nbsp;&nbsp;&nbsp;
    
    <a class="toolbar tool" href="#" rel="browse_multi_classify" title="Classify the selected items.">
      <img src="/acp/static/icon/zones.png" alt="Classify" />
      Classify
    </a>
    <a class="toolbar tool" href="#" rel="browse_multi_organise" title="Categorise the selected items.">
      <img src="/acp/static/icon/folder.png" alt="Categorise" />
      Categorise
    </a>
    <a class="toolbar tool" href="#" rel="browse_multi_tag" title="Apply item tags to the selected items.">
      <img src="/acp/static/icon/tag--plus.png" alt="Tags" />
      Tag
    </a>
    <a class="toolbar tool" href="#" rel="browse_multi_brand" title="Set the brand of the selected items.">
      <img src="/acp/static/icon/reg-trademark.png" alt="Brand" />
      Brand
    </a>

    &nbsp;&nbsp;&nbsp;&nbsp;
    
    <a class="toolbar tool" href="#" rel="browse_multi_stop" title="Stop showing the selected items on all sites.">
      <img src="/acp/static/icon/slash.png" alt="Stop" />
      Stop
    </a>
    <a class="toolbar tool" href="#" rel="browse_multi_start" title="Start showing the selected items on all sites.">
      <img src="/acp/static/icon/tick-circle.png" alt="Start" />
      Start
    </a>
    
    &nbsp;&nbsp;&nbsp;&nbsp;
    
    <a class="toolbar tool" href="#" rel="browse_multi_label" title="Label the selected items.">
      <img src="/acp/static/icon/price-tag-label.png" alt="Label" />
      Label
    </a>
    <a class="toolbar tool" href="#" title="Tweet/Facebook about the selected items.">
      <img src="/acp/static/icon/balloon-twitter-left.png" alt="Tweet" />
      Advertise
    </a>
  </div>
</div>

<br />
        
<?php
  
  // This can be slow so both profile the operation if profiling is enabled
  // and cache results if it at all possible:
  
  $inventoryViewProfiler = new Profiler();
  $inventoryViewProfiler->start('RENDERING_FILTERS');
  
  // Filter using WHERE clause
  $whereClauseFilter = '';

  // Find a filter ID
  $filterID = $BF->inInteger('f_filter');
  if($filterID != -1)
  {
    // Load the filter setting
    $filter = $BF->db->query();
    $filter->select('*', 'bf_admin_inventory_browse_filters')
           ->where('id = \'{1}\'', $filterID)
           ->limit(1)
           ->execute();
           
    if($filter->count == 1)
    {
      $filterRow = $filter->next();
           
      // Get the filter SQL
      $whereClauseFilter = (trim($filterRow->sql_where) == '' ? '' : 'AND ' . $filterRow->sql_where);
    }
  }
  
  $inventoryViewProfiler->stop('RENDERING_FILTERS');
  $inventoryViewProfiler->start('RENDERING_LABELS');

  // Get all labels applications of current selection
  $currentLabelID = $BF->inInteger('f_label');
  
  if($currentLabelID != -1)
  {    
    // Find applications
    $labelApplications = $BF->db->query();
    $labelApplications->select('*', 'bf_item_label_applications')
                      ->where('`item_label_id` = \'{1}\'', $currentLabelID)
                      ->execute();
    
    // Any items?
    if($labelApplications->count > 0)
    {  
      $labelApplicationsHash = $labelApplications->getInHash('item_id');
      
      // Constrain search to items
      $whereClauseFilter .= ' AND (`id` IN (' . $labelApplicationsHash . '))';
    }
    else
    {
      $whereClauseFilter .= ' AND 1=0';
    }
  }
     
  $inventoryViewProfiler->stop('RENDERING_LABELS');
  
  $inventoryViewProfiler->start('RENDERING_TAGS');

  // Get all tag applications of current selection
  $currentTagID = $BF->inInteger('f_tag');
  
  if($currentTagID != -1)
  {    
    // Find applications
    $tagApplications = $BF->db->query();
    $tagApplications->select('*', 'bf_item_tag_applications')
                      ->where('`item_tag_id` = \'{1}\'', $currentTagID)
                      ->execute();
    
    // Any items?
    if($tagApplications->count > 0)
    {  
      $tagApplicationsHash = $tagApplications->getInHash('item_id');
      
      // Constrain search to items
      $whereClauseFilter .= ' AND (`id` IN (' . $tagApplicationsHash . '))';
    }
    else
    {
      $whereClauseFilter .= ' AND 1=0';
    }
  }
     
  $inventoryViewProfiler->stop('RENDERING_TAGS');
  
  // List safe fields to include
  $safeFields = array(
    'sku',
    'name',
    'description'
  ); 
  
  $whereClause = '1 = 1';
  $whereClauseValue = '-1';
  
  if(in_array($BF->in('f_in'), $safeFields))
  {
    // If SKU - do not allow any substrings, only strings starting with
    $whereClause = '`' . $BF->in('f_in') . '` LIKE \'' . 
      ($BF->in('f_in') == 'sku' ? '' : '%') . '{1}%\'';
    
    // Search for...
    $searchTerm = $BF->in('f_term');
    
    // Remove -PAR tags from search term?
    if($BF->in('remove_par') == '1')
    {
      $searchTerm = Tools::removeParentTag($searchTerm);
    }
    
    // Append search term
    $whereClauseValue = $searchTerm;
  }
  
  // Category?
  $categoryID = '-1';
  if($BF->in('f_category') && $BF->inInteger('f_category') != -2)
  {
    $whereClause .= ' AND category_id = {2} ';
    $categoryID = $BF->inInteger('f_category');
  }
  

  // Classification?
  $classificationID = '-1';
  if($BF->in('f_classification') && $BF->inInteger('f_classification') != -2)
  {
    $whereClause .= ' AND classification_id = {3} ';
    $classificationID = $BF->inInteger('f_classification');
  }
  
  // Brand?
  $brandID = '-1';
  if($BF->in('f_brand') && $BF->inInteger('f_brand') != -2)
  {
    $whereClause .= ' AND brand_id = {4} ';
    $brandID = $BF->inInteger('f_brand');
  }

  
  $inventoryViewProfiler->start('PRELOADING_TAGS');
  
  // Pull linker table for item tag applications
  $BF->db->select('*', 'bf_item_tag_applications')
             ->execute();
           
  $tagLinks = array();  
  while($itemTagApplication = $BF->db->next())
  {
    // Create a tag link record
    if(array_key_exists($itemTagApplication->item_id, $tagLinks))
    {
      // Add this tag
      $tagLinks[$itemTagApplication->item_id][] = 
        $itemTagApplication->item_tag_id;
    }
    else
    {
      $tagLinks[$itemTagApplication->item_id] = 
        array($itemTagApplication->item_tag_id);
    }
  }
  
  // Collect actual item tags
  $BF->db->select('*', 'bf_item_tags')
         ->execute();
    
  $itemTags = array();           
  while($itemTag = $BF->db->next())
  {
    $itemTags[$itemTag->id] = $itemTag;
  }
  
  // Make global so they can be accessed inside a closure
  $GLOBALS['itemTags'] = $itemTags;
  $GLOBALS['tagLinks'] = $tagLinks;
  
  $inventoryViewProfiler->stop('PRELOADING_TAGS');
  $inventoryViewProfiler->start('PRELOADING_LABELS');

  
  // Pull linker table for item label applications
  $BF->db->select('*', 'bf_item_label_applications')
             ->execute();
           
  $labelLinks = array();  
  while($itemLabelApplication = $BF->db->next())
  {
    // Create a label link record
    if(array_key_exists($itemLabelApplication->item_id, $labelLinks))
    {
      // Add this label
      $labelLinks[$itemLabelApplication->item_id][] = 
        $itemLabelApplication->item_label_id;
    }
    else
    {
      $labelLinks[$itemLabelApplication->item_id] = 
        array($itemLabelApplication->item_label_id);
    }
  }
  
  // Collect actual item labels
  $BF->db->select('*', 'bf_item_labels')
         ->execute();
    
  $itemLabels = array();           
  while($itemLabel = $BF->db->next())
  {
    $itemLabels[$itemLabel->id] = $itemLabel;
  }
  
  // Make global so they can be accessed inside a closure
  $GLOBALS['itemLabels'] = $itemLabels;
  $GLOBALS['labelLinks'] = $labelLinks;
  
  $inventoryViewProfiler->stop('PRELOADING_LABELS');
  $inventoryViewProfiler->start('BUILDING_QUERY');
  
  // Create a query
  $query = $BF->db->query();
  $query->select('0 AS is_parent, id, sku, name, barcode, trade_price, pro_net_price, pro_net_qty,' . 
                 'rrp_price, cost_price ,wholesale_price, stock_free, visible, parent_item_id', 'bf_items')
        ->where($whereClause . $whereClauseFilter, $whereClauseValue, $categoryID, $classificationID, $brandID);
        
  // Show parents?
  if($BF->in('x_show_parents') && $BF->in('f_filter') == '-1')
  {
    $query->text('UNION SELECT 1 AS is_parent, id, sku, name, "" AS barcode, trade_price, pro_net_price,' .
               'pro_net_qty, rrp_price, cost_price, wholesale_price, 0, 1, -1 FROM `bf_parent_items`')
          ->where($whereClause . $whereClauseFilter, $whereClauseValue, $categoryID, $classificationID, $brandID);
  }
  

  $toolSet .= '<a class="tool" href="./?act=inventory&mode=browse_modify&id={id}" title="Modify">' . "\n";
  $toolSet .= '  <img src="/acp/static/icon/pencil.png" alt="Modify" />' . "\n";
  $toolSet .= 'Modify</a>' . "\n";
  $toolSet .= '<a class="tool notext more" row="{id}" href="#" title="More">' . "\n";
  $toolSet .= '  <img src="/acp/static/icon/control-270-g.png" class="notext" alt="More" />' . "\n";
  $toolSet .= '</a>' . "\n<br />"; 

  // Confirm Deletion (Parent)
  $confirmationParentJS = 'confirmation(\'Really remove this parent item?' . 
                          '<br />Variation data will be deleted.\', function() { window.location=\'' .
                          Tools::getModifiedURL(array('mode' => 'browse_remove_parent_do')) . '&id={id}\'; })';
  
  // Buttons for parent items
  $pToolSet  = "\n";
  $pToolSet .= '<a class="tool" title="Modify" href="./?act=inventory&mode=browse_modify_parent&id={id}">' . "\n";
  $pToolSet .= '  <img src="/acp/static/icon/pencil.png" alt="Modify" />' . "\n";
  $pToolSet .= 'Modify</a>' . "\n";
  $pToolSet .= "\n";  
  $pToolSet .= '<a class="tool notext pmore" row="{id}" href="#" title="More">' . "\n";
  $pToolSet .= '  <img src="/acp/static/icon/control-270-g.png" class="notext" alt="More" />' . "\n";
  $pToolSet .= '</a>' . "\n<br />"; 
  
  $inventoryViewProfiler->stop('BUILDING_QUERY');
  
  // Callback for adding icons to the name of items
  $nameModifier = function($row, $parent) 
  {    
    // Access to tags
    $tagLinks = $GLOBALS['tagLinks'];
    $tags = $GLOBALS['itemTags'];

    // Access to labels
    $labelLinks = $GLOBALS['labelLinks'];
    $labels = $GLOBALS['itemLabels'];
    $labelColours = $GLOBALS['labelColours'];

    // Normally empty
    $namePrefix = '';
    $nameSuffix = '';
    
    // Is the item stopped?
    if($row->visible == 0)
    {
      $namePrefix = ' <img title="Stopped" class="middle" src="/acp/s' . 
                    'tatic/icon/slash-small.png" /><span class="grey">';
      $nameSuffix = '</span>';
    }
   
    // Add tag icons
    $showTagIcons = $parent->config->get('com.b2bfront.acp.inventory-tags-visible', true);
    if($showTagIcons)
    {
      if(array_key_exists($row->id, $tagLinks))
      {
        foreach($tagLinks[$row->id] as $index => $value)
        {
          $nameSuffix .= '<img title="' . $tags[$value]->name . '" class="middle" style="float:' . 
                         ' right; margin-left: 10px;" src="' . 
                         $tags[$value]->icon_path . '" alt="Icon" />' . "\n";
        }
      }
    }
        
    // Add label blobs
    $showLabelColours = $parent->config->get('com.b2bfront.acp.inventory-labels-visible', true);
    if($showLabelColours)
    {
      if(array_key_exists($row->id, $labelLinks))
      {
        foreach($labelLinks[$row->id] as $index => $value)
        {
          $namePrefix .= '<span title="' . $labels[$value]->name . 
          				 '" class="label_small" style="background-color: ' . 
                         $labelColours[$labels[$value]->colour]['colour'] .  ';">&nbsp;' . "\n";
          $nameSuffix .= '&nbsp;&nbsp;</span>';
        }
      }
    }
    
    return $namePrefix . $row->name . $nameSuffix;
  };
  
  $inventoryViewProfiler->start('RENDERING_VIEW');
  
  // Create a data table view
  $inventory = new DataTable('inventory', $BF, $query);
  $inventory->setOption('alternateRows');
  $inventory->setOption('showTopPager');
  $inventory->setOption('showDownloadOption');
  $inventory->setOption('rowStyles', array(
                          array(                
                            'if_equal' => array('{is_parent}', '1'),
                            'css' => array(
                                       'background' => '#dfdfdf',
                                       'border-top' => '1px solid #afafaf',
                                       'border-bottom' => '1px solid #afafaf',
                                       'color' => '#7f7f7f'
                                     )
                          )
                       ));
  
  // Profiling of the render of the DataTable:
  $inventory->setOption('profileRendering');
  
  $inventory->setOption('noDataText', 'There are no items matching your search/filter settings.');
  $inventory->addColumns(array(
                          array(
                            'niceName' => '',
                            'dataName' => 'id',
                            'options' => array(
                                          'formatAsCheckbox' => true,
                                          'fixedOrder' => true,
                                          'clearField' => 'is_parent'
                                        ),
                            'css' => array(
                                       'width' => '16px'
                                     )
                          ),
                          array(
                            'dataName' => 'sku',
                            'niceName' => 'SKU',
                            'options' => array(
                                           'formatAsLink' => true,
                                           'linkNewWindow' => true,
                                           'linkURL' => $BF->config->get('com.b2bfront.site.url', true)
                                                         . '?option=item&id={id}',
                                            'replaceFieldIf' => 'is_parent',
                                            'replaceFieldWith' => 'sku'
                                         ),
                          'css' => array('width' => '80px')
                          ),
                          array(
                            'dataName' => 'name',
                            'niceName' => 'Item Name',
                            'options' => array(
                                           'callback' => $nameModifier,
                                           'replaceFieldIf' => 'is_parent',
                                           'replaceFieldWith' => '{name} <img style="float: right; position: relative; top: 2px" src="./static/image/aui-parent.gif" />'
                                         ),
                            'css' => array(
                                       'overflow' => 'hidden'
                                     )
                          ),
                          array(
                            'dataName' => 'barcode',
                            'niceName' => 'Barcode',
                            'options' => array(
                                           'editable' => true,
                                           'editableTable' => 'bf_items'
                                         ),
                            'css' => array('width' => '85px')
                          ),
                          array(
                            'dataName' => 'trade_price',
                            'niceName' => 'Trade',
                            'options' => array(
                                           'formatAsPrice' => true,
                                           'editable' => true,
                                           'editableTable' => 'bf_items',
                                           'downloadableFormatting' => '###0.00',
                                           'replaceFieldIf' => 'is_parent',
                                           'replaceFieldWith' => 'trade_price'
                                         ),
                            'css' => array('width' => '75px')
                          ),
                          array(
                            'dataName' => 'pro_net_price',
                            'niceName' => 'Pro Net',
                            'options' => array(
                                           'formatAsPrice' => true,
                                           'editable' => true,
                                           'editableTable' => 'bf_items',
                                           'downloadableFormatting' => '###0.00',
                                           'replaceFieldIf' => 'is_parent',
                                           'replaceFieldWith' => 'pro_net_price'
                                         ),
                            'css' => array('width' => '75px')
                          ),
                          array(
                            'dataName' => 'wholesale_price',
                            'niceName' => 'WS',
                            'options' => array(
                                           'formatAsPrice' => true,
                                           'editable' => true,
                                           'editableTable' => 'bf_items',
                                           'downloadableFormatting' => '###0.00',
                                           'replaceFieldIf' => 'is_parent',
                                           'replaceFieldWith' => 'wholesale_price'
                                         ),
                            'css' => array('width' => '75px')
                          ),
                          array(
                            'dataName' => 'rrp_price',
                            'niceName' => 'RRP',
                            'css' => array('width'=> '75px'),
                            'options' => array(
                                           'editable' => true,
                                           'editableTable' => 'bf_items',
                                           'replaceFieldIf' => 'is_parent',
                                           'replaceFieldWith' => 'rrp_price'
                                         ),
                          ),
                          array(
                            'dataName' => 'pro_net_qty',
                            'niceName' => 'PN Qty',
                            'css' => array('width'=> '75px'),
                            'options' => array(
                                           'editable' => true,
                                           'editableTable' => 'bf_items',
                                           'replaceFieldIf' => 'is_parent',
                                           'replaceFieldWith' => 'pro_net_qty'
                                         ),
                          ),
                          array(
                            'dataName' => 'stock_free',
                            'niceName' => 'Stock',
                            'css' => array('width'=> '65px'),
                            'options' => array(
                                           'editable' => true,
                                           'editableTable' => 'bf_items',
                                           'clearField' => 'is_parent'
                                         ),
                          ),
                          array(
                            'niceName' => 'Options',
                            'content' => $toolSet,
                            'options' => array(
                                           'fixedOrder' => true,
                                           'replaceFieldIf' => 'is_parent',
                                           'replaceFieldWith' => $pToolSet
                                         ),
                            'css' => array(
                                       'width' => '92px'
                                     )
                          )
                        ));
  
  // Render & output content
  print $inventory->render();
  $inventoryViewProfiler->stop('RENDERING_VIEW');

?>
</form>
