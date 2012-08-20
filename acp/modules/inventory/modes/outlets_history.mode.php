<?php
/**
 * Module: Inventory
 * Mode: Outlet History
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

// Find the outlet
$outlet = $BF->db->getRow('bf_outlets', $BF->inInteger('id'));

// Valid?
if(!$outlet)
{
  $BF->go('./?act=inventory&mode=outlets');
}

// Get the user
$user = $BF->db->getRow('bf_users', $outlet->user_id);

// Get the item
$item = $BF->db->getRow('bf_items', $outlet->item_id);

// Find outlet data
$outletData = $BF->db->query();
$outletData->select('*', 'bf_outlet_snapshots')
           ->where('`outlet_id` = \'{1}\'', $outlet->id)
           ->order('timestamp', 'asc')
           ->execute();
       /*    
       
// Also plot orders by this dealer
$firstSnapshot = $outletData->next();
$outletData->rewind();

// Find orders
$orders = $BF->db->query();
$orders->select('*', 'bf_orders') 
       ->where('`owner_id` = \'{1}\' AND `timestamp` >= \'{2}\'', 
         $user->id, $firstSnapshot->timestamp)
       ->order('timestamp', 'asc')
       ->execute();
       
       */
       
?>

<!--Load the AJAX API-->
<style type="text/css">
  .annotationsdiv {
    float: left;
  }
</style>
<script type="text/javascript" src="http://www.google.com/jsapi"></script>
<script type="text/javascript">
google.load('visualization', '1', {'packages':['annotatedtimeline']});
      google.setOnLoadCallback(drawChart);
      function drawChart() {
        var data = new google.visualization.DataTable();
        
        data.addColumn('datetime', 'Date');
        data.addColumn('number', '<?php print addslashes($user->name) ?> Price');
   
        //data.addColumn('string', 'title1');
        //data.addColumn('string', 'text1');
        
        
        data.addRows([
        
          <?php
          
            while($snapshot = $outletData->next())
            {
              print '[new Date(' . date('Y', $snapshot->timestamp) . ', ' . 
                    date('n', $snapshot->timestamp) . ' ,' .
                    date('j', $snapshot->timestamp) . ',' . date('H', $snapshot->timestamp) . ',' . date('i', $snapshot->timestamp) . ',' . date('s', $snapshot->timestamp) . '), ' . $snapshot->price .
                    ']' . ($outletData->last() ? '' : ',');
            }
          
          ?>
                    ]);

        var chart = new google.visualization.AnnotatedTimeLine(document.getElementById('chart_div'));
        chart.draw(data, {displayAnnotations: true, annotationsWidth: '15'});
      }
</script>


<h1><?php print $user->description; ?>'s Price History for <?php print $item->sku; ?></h1>
<br />

  <div id="chart_div" class="contents" style="width:100%; height: 300px; background: #ffffff ; text-align: center;">

  </div>

<br />

<?php

  // Create a new query to retreieve Outlet History
  $query = $BF->db->query();
  $query->select('*', 'bf_outlet_snapshots')
        ->where('`outlet_id` = \'{1}\'', $outlet->id);

  // Create a data table view to show the outlets
  $outlets = new DataTable('ou2', $BF, $query);
  $outlets->setOption('alternateRows');
  $outlets->setOption('showTopPager');
  $outlets->setOption('defaultOrder', array('timestamp', 'desc'));
  $outlets->addColumns(array(
                        array(
                          'dataName' => 'rise',
                          'niceName' => '',
                          'options' => array(
                                         'formatAsToggleImage' => true,
                                         'toggleImageTrue' => '/acp/static/icon/arrow-green.png',
                                         'toggleImageFalse' => '/acp/static/icon/arrow-red.png',
                                         'toggleImageTrueTitle' => 'The dealer\'s price increased.',
                                         'toggleImageFalseTitle' => 'The dealer\'s price decreased.',
                                         'fixedOrder' => true

                                       ),
                          'css' => array(
                                     'width' => '16px'
                                   )
                        ),
                        array(
                          'dataName' => 'price',
                          'niceName' => 'Dealer\'s Price',
                          'css' => array('width' => '100px')
                        ),
                        array(
                          'dataName' => 'timestamp',
                          'niceName' => 'Date',
                          'options' => array(
                            'formatAsDate' => true
                          )
                        )
                      ));
  
  // Render & output content
  print $outlets->render();
?>