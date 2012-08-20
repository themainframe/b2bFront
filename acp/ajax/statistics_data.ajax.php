<?php
/**
 * Statistics
 * AJAX Responder
 * Sends AJAX replies containing column and row data for statistics.
 *
 * @copyright 2010-2011 Damien Walsh
 * @package b2bfront
 * @version 1.0
 * @author Damien Walsh
 */
 
// Set context
define('BF_CONTEXT_ADMIN', true);

// Relative path for this - no BF_ROOT yet.
require_once('../admin_startup.php');
require_once(BF_ROOT . 'tools.php');

// New BFClass & Admin class
$BF = new BFClass(true);
$BF->admin = new Admin(& $BF);

if(!$BF->admin->isAdmin)
{
  exit();
}

// Get a collection of IDs
$IDs = $BF->in('statistics');
$statistics = Tools::unCSV($IDs);

// Get maximum count
$count = $BF->inInteger('count');

// Build column collection
$columns = array();
$columnQuery = $BF->db->query();
$columnQuery->select('*', 'bf_statistics')
            ->whereInHash($IDs)
            ->execute();
          
// Add each
while($column = $columnQuery->next())
{
  $columns[$column->id] = array(
    'name' => $column->description,
    'id' => $column->id,
    'value' => $column->value
  );
}

// Build row collection
$rows = array();

// Get a hash of snapshots extending as far back as specified
$snapshotGroup = $BF->db->query();
$snapshotGroup->select('*', 'bf_statistic_snapshots')
              ->order('timestamp', 'desc')
              ->limit($count)
              ->execute();
              
// Build collection
while($snapshot = $snapshotGroup->next())
{
  $rows[$snapshot->id]['time'] = 
    date('d/m/Y', $snapshot->timestamp);
}
              
// Rewind and get hash
$snapshotGroup->rewind();
$snapshotHash = $snapshotGroup->getInHash();

// Find snapshot data
$snapshots = $BF->db->query();
$snapshots->select('*', 'bf_statistic_snapshot_data')
          ->where('`snapshot_id` IN ({1})', $snapshotHash)
          ->order('snapshot_id', 'desc')
          ->execute();
                   
// Create rows
$firstSnapshotID = -1;
while($snapshotData = $snapshots->next())
{
  // Requested?
  if(!in_array($snapshotData->statistic_id, $statistics))
  {
    continue;
  }

  if($firstSnapshotID == -1)
  {
    // Set first snapshot ID
    $firstSnapshotID = $snapshotData->snapshot_id;
  }

  // Still first snapshot?
  if($firstSnapshotID == $snapshotData->snapshot_id)
  {
    // Set last value
    $columns[$snapshotData->statistic_id]['last'] = 
      $snapshotData->value;
  }

  if(!isset($rows[$snapshotData->snapshot_id]['data']))
  {
    $rows[$snapshotData->snapshot_id]['data'] = array();
  }

  $rows[$snapshotData->snapshot_id]['data']
       [$snapshotData->statistic_id] = $snapshotData->value;
}

// Build final value
$finalData = array(
  'rows' => $rows,
  'columns' => $columns
);

// Output
print json_encode($finalData);

// Shutdown
$BF->shutdown();
?>