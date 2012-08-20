<?php
/**
 * Google Charts: Category Blocks
 * b2bFront Statistics Visualisation Plugin Program File 
 *
 * @copyright 2010-2011 Damien Walsh
 * @package b2bfront-plugins
 * @version 1.0
 * @author Damien Walsh
 */
 
// Plugin context check - Admin & Plugin Environment
if(!defined("BF_CONTEXT_ADMIN") || !defined("BF_CONTEXT_PLUGIN_ENV"))
{
  exit();
}

// Access to BFClass
global $BF;

$categorys = $BF->db->query();
$categorys->select('*', 'bf_categories')
                ->execute();
                
$orderLines = $BF->db->query();
$orderLines->select('*', 'bf_order_lines')
           ->execute();
  
// Collect categorys
$classCollection = array();         
while($class = $categorys->next())
{
  $classCollection[$class->id] = array(
    'name' => $class->name,
    'value' => 0
  );
}

// Mode
$mode = $BF->in('block_mode');

if($mode != 'value' && $mode != 'units')
{
  // Default
  $mode = 'units';
}

// Accumulate sales
while($orderLine = $orderLines->next())
{
  $item = $BF->db->getRow('bf_items', $orderLine->item_id);
  $classCollection[$item->category_id]['value'] += 
    ($mode == 'value' ? $orderLine->invoice_price_each * $orderLine->quantity : $orderLine->quantity);
}


?>

<div class="panel">
  <div class="contents">    
    <h3>About Category Blocks</h3>
    <p>
      The blocks below represent the breakdown in category for your inventory.<br />
      <br />
      You are currently viewing breakdown by <?php print ucwords($mode); ?>.<br />
      <a href="<?php print Tools::getModifiedURL(array('block_mode' => ($mode == 'value' ? 'units' : 'value'))); ?>">Click here</a> to change to 
      <?php print ucwords(($mode == 'value' ? 'units' : 'value')); ?> mode.
    </p>
  </div>
</div>

<br />

    <script type="text/javascript" src="https://www.google.com/jsapi"></script>
    <script type="text/javascript">
      google.load("visualization", "1", {packages:["treemap"]});
      google.setOnLoadCallback(drawChart);
      function drawChart() {
          // Create and populate the data table.
          var data = new google.visualization.DataTable();
          data.addColumn('string', 'Category');
          data.addColumn('string', 'Parent');
          data.addColumn('number', 'Value');

          data.addRows([
            
            ['Categories', null, 0],
          
            <?php
              
              foreach($classCollection as $category)
              {
                ?>
                  ['<?php print addslashes($category['name']); ?>', 
                   'Categories',
                    <?php print $category['value']; ?>]
                    
                  <?php
                  if($category != end($classCollection))
                  {
                    ?>
                      ,
                    <?php
                  }
                
              }
            
            ?>
          ]);

          // Create and draw the visualization.
          var tree = new google.visualization.TreeMap(document.getElementById('visualization'));
          tree.draw(data, {
            minColor: '#bd3e31',
            midColor: '#bdbb31',
            maxColor: '#79bd31',
            fontFamily : 'tahoma',
            headerHeight: 30,
            fontColor: 'black' });
      }
    </script>


<div id="visualization" style="width: 100%; height: 600px;"></div>