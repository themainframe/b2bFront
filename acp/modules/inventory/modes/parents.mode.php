<?php
/**
 * Module: Inventory
 * Mode: Parent Items
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

<h1>Parent Items</h1>
<br />

<div class="panel">
  <div class="contents">
    
    <h3>About Parent Items</h3>
    
    <p>
  
      Parent Items are used as a template for other items and is invisible to
      non-staff users.  They have associated variables that child items can inherit and assign values to.<br />
      For example, you could create a Parent Item for a pair of boots then create 
      three child items to represent the small, medium and large versions of the boots.
      
    </p>
        
    <br />
    
    <span class="button">
      <a href="./?act=inventory&mode=add_parent">
        <span class="img" style="background-image:url(/acp/static/icon/plus-circle.png)"></span>
          New Parent Item...
      </a>
    </span>
 
    <br /><br />
  </div>
</div>

<br />


<form action="./" method="get" name="browseFilterForm">

<?php

  print Tools::getQueryStringInputs();

?>

<div class="panel">
  <div class="title">Search &amp; Filter</div>
  <div class="contents">
    <div style="height: 100%;">
      <div class="threebox">
        <div class="box">
          <div style="padding: 0px 0px 0px 5px">
            
            <table style="width: 100%;">
              <tr>
                <td style="width: 50%;">            
              
                  <strong>Search for:</strong><br />
                  <input type="text" name="f_term" style="width: 80%;" /> <br /> in &nbsp;  
                  <select name="f_in" style="margin-top: 10px;">
                    <option value="sku">SKU</option>
                    <option value="name">Name</option>
                    <option value="description">Description</option>
                    <option value="barcode">Barcode</option>
                  </select>
                      
                </td>
                <td>
                
                  <strong>In Category:</strong><br />
<?php

  // Query the database for Classifications
  $query = $BF->db->query();
  $query->select('*', 'bf_categories')
        ->order('name', 'ASC')
        ->execute();
        
  // Create a UI element
  $dropDown = new DataDropDown('f_category', $query, 'id', 'name',
                               array('-2' => 'None', '-1' => 'Uncategorised'));
  $dropDown->setOption('maxTextLength', 15);
  $dropDown->setOption('css', array(
    'margin-top' => '10px'
  ));
  print $dropDown->render();

?>

                  <br /><br />
                  <a class="new" target="_blank" href="./?act=inventory&mode=organise" title="Categories">
                    Modify Categories...
                  </a>
                    
                </td>
              </tr>
            </table>
              
          </div>
        </div>
        <div class="box" style="border-left: 1px solid #cfcfcf; border-right: 1px solid #cfcfcf;">
          <div style="padding: 0px 0px 0px 20px;">
            <strong>Filter:</strong><br />
        
<?php

  // Query the database for Classifications
  $query = $BF->db->query();
  $query->select('*', 'bf_admin_inventory_browse_filters')
        ->order('name', 'ASC')
        ->execute();
        
  // Create a UI element
  $dropDown = new DataDropDown('f_filter', $query, 'id', 'name', array('-1' => 'None'));
  $dropDown->setOption('css', array(
    'margin-top' => '10px'
  ));
  print $dropDown->render();

?> 
          
            <br /><br />
            <a href="./?act=inventory&mode=browse_modify_filters" title="Create Filters">Add / Remove filters...</a>
          </div>
        </div>
        <div class="box">
          <div style="padding: 2px 0px 0px 20px;">

            <input type="submit" value="Apply!" class="submit ok">
            
            &nbsp;
            
            <span class="button" style="margin-left: 0px;">
              <a href="./?act=inventory&mode=browse&f_term=&f_in=sku&f_filter=-1">
                <span class="img" style="background-image:url(/acp/static/icon/eye--arrow.png)">&nbsp;</span>
                View All
              </a>
            </span>
            
          </div>
        </div>
        <br style="clear: both;" />
      </div>
      
    </div>
  </div>
</div>

</form>

<br />


<?php

  // Create a new query to retreieve Parent Items
  $query = $BF->db->query();
  $query->select('*', 'bf_parent_items');
        
  // Define a tool set HTML
  $confirmationJS = 'confirmation(\'Really remove this Parent Item?<br /><br />\' + 
                    \'This will detach any children associated with it.\', function() { window.location=\'' .
                    Tools::getModifiedURL(array('mode' => 'parents_remove_do')) . '&id={id}\'; })';
  
  // Buttons
  $toolSet  = "\n";
  $toolSet .= '<a onclick="' . $confirmationJS . '" class="tool" title="Remove">' . "\n";
  $toolSet .= '  <img src="/acp/static/icon/cross-circle.png" alt="Remove" />' . "\n";
  $toolSet .= 'Remove</a>' . "\n";
  $toolSet .= '<a class="tool" title="Modify" href="./?act=inventory&mode=parents_modify&id={id}">' . "\n";
  $toolSet .= '  <img src="/acp/static/icon/zone--pencil.png" alt="Modify" />' . "\n";
  $toolSet .= 'Modify</a>' . "\n";
  $toolSet .= '<a class="tool" title="History" href="./?act=inventory&mode=parents_createchildren&id={id}">' . "\n";
  $toolSet .= '  <img src="/acp/static/icon/node-insert-next.png" alt="Create Children" />' . "\n";
  $toolSet .= 'Create Children</a>' . "\n";
  
  // Create a data table view to show the outlets
  $parents = new DataTable('ou1', $BF, $query);
  $parents->setOption('alternateRows');
  $parents->setOption('showTopPager');
  $parents->addColumns(array(
                        array(
                          'niceName' => '',
                          'dataName' => 'id',
                          'options' => array(
                                        'formatAsCheckbox' => true,
                                        'fixedOrder' => true
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
                                         'linkURL' => './?act=inventory&mode=browse' .
                                                      '&f_term={sku}&remove_par=1&f_in=sku&f_filter=-1&f_category=-2' . 
                                                      '&inventory_pg=1&inventory_lpp=0&inventory_order_d=a&' . 
                                                      'inventory_order=1&f_show_parents=1&no_save_default_view=1'
                                       ),
                        'css' => array('width' => '100px')
                        ),
                        array(
                          'dataName' => 'name',
                          'niceName' => 'Item Name',
                          'options' => array(
                                         'callback' => $nameModifier
                                       ),
                          'css' => array(
                                     'overflow' => 'hidden'
                                   )
                        ),
                        array(
                          'dataName' => 'trade_price',
                          'niceName' => 'Trade',
                          'options' => array(
                                         'formatAsPrice' => true
                                       ),
                          'css' => array('width' => '70px')
                        ),
                        array(
                          'dataName' => 'pro_net_price',
                          'niceName' => 'Pro Net',
                          'options' => array(
                                         'formatAsPrice' => true
                                       ),
                          'css' => array('width' => '70px')
                        ),
                        array(
                          'dataName' => 'cost_price',
                          'niceName' => 'Cost',
                          'options' => array(
                                         'formatAsPrice' => true
                                       ),
                          'css' => array('width' => '70px')
                        ),
                        array(
                          'dataName' => 'pro_net_qty',
                          'niceName' => 'PN Qty',
                          'css' => array('width'=> '60px')
                        ),
                        array(
                          'niceName' => 'Options',
                          'content' => $toolSet,
                          'options' => array(
                                         'fixedOrder' => true
                                       ),
                          'css' => array(
                                     'width' => '235px'
                                   )
                        )
                    ));

  // Render & output content
  print $parents->render();
?>
