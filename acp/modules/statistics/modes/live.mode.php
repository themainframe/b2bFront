<?php
/**
 * Module: Statistics
 * Mode: Live Values
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

<h1>Period to Date</h1>
<br />

<?php
  
  // Get statistical domains
  $statDomains = $BF->db->query();
  $statDomains->select('*', 'bf_statistics_domains')
              ->order('title', 'ASC')
              ->execute();

  while($statDomain = $statDomains->next())
  {
?>

<div class="panel" style="border-bottom: 0px;">
  <div class="title">
    <img src="<?php print $statDomain->icon_path; ?>" class="middle" style="top: 2px; float: left;" />
    <span style="position: relative; top: 3px; float: left;">&nbsp; <?php print $statDomain->title; ?></span>
    <span class="grey" style="font-weight: normal; float: right; margin-right: 10px; position: relative; top: 3px;">
      <?php print $statDomain->name; ?>
    </span>
    <br class="clear" />
  </div>
  <div class="contents" style="background: white">
    
    <table>
    
    <?php
    
      // Get this domain data
      $statValues = $BF->db->query();
      $statValues->select('*', 'bf_statistics')
                 ->where('domain_id = \'{1}\'', $statDomain->id)
                 ->order('description', 'ASC')
                 ->execute();
      
      while($value = $statValues->next())
      {
        // Get previous value
        $previousValue = $BF->db->query();
        $previousValue->select('*', 'bf_statistic_snapshot_data')
                      ->where('statistic_id = \'{1}\'', $value->id)
                      ->order('snapshot_id', 'DESC')
                      ->limit(1)
                      ->execute();
                
        $last = false;  
        if($previousValue->count != 0)
        {
          $last = $previousValue->next()->value;
        }
      
        ?>
        
          <tr style="height: 20px;">
          
            <td style="vertical-align: middle; font-weight: bold; width: 250px;">
              <?php print $value->description; ?>
            </td>
            
            <td style="vertical-align: middle; width: 80px;">
              <?php print $value->value; ?>
            </td>
            
            <td style="vertical-align: middle; width: 170px;">
              <?php
                if($last !== false)
                {
                  if($value->value > $last)
                  {
                    print '<img src="/acp/static/image/aui-value-up.png" class="middle" width="13px" />&nbsp; +';
                  }
                  else if($value->value < $last)
                  {
                    print '<img src="/acp/static/image/aui-value-down.png" class="middle" width="13px" />&nbsp; -';
                  }
                  else
                  {
                    print '<img src="/acp/static/image/aui-value-same.png" class="middle" width="13px" />&nbsp; ±';
                  }
                  
                  if($value->value != 0)
                  {
                    $change = abs($last - $value->value) / (abs($last) + abs($value->value) / 2);
                  
                    print str_replace('-', '', $last - $value->value) . '&nbsp;&nbsp;&nbsp;';
                    print '(' . number_format($change * 100, 2) . '%)';
                  }
                }
                else
                {
                  print '<span class="grey">No data</span>';
                }
              ?>
            </td>
          
          </tr>
        
        <?php
      }
      
      if($statValues->count == 0)
      {
        ?>
        
          <div style="text-align: center">
            <span class="grey">No data has been collected for this domain.</span>
          </div>
        
        <?php
      }
                
    ?>
    
    </table>
    
  </div>
</div>

<?php
  
  }
  
?>

<div class="panel" style="border:0; border-top: 1px solid #afafaf; background: white;">
  &nbsp;
</div>