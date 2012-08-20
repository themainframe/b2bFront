<?php
/**
 * Module: System
 * Mode: Information
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

// Calculate the Zone text of a resource
function zoneText($resourceName, $resourceValue)
{
  
  $resourceBounds = array(

    'local storage' => array(
      
      'yellow_zone' => 70,
      'red_zone' => 90
    
    ),
    
    'cache' => array(
      
      'yellow_zone' => 50,
      'red_zone' => 70
    
    ),
    
    'last 1 minute workload' => array(
      
      'yellow_zone' => 2,
      'red_zone' => 4
    
    ),

    'last 5 minutes workload' => array(
      
      'yellow_zone' => 2,
      'red_zone' => 4
    
    ),
    
    'last 15 minutes workload' => array(
      
      'yellow_zone' => 2,
      'red_zone' => 4
    
    )
    
  );

  // Resource exists?
  if(array_key_exists($resourceName, $resourceBounds))
  {
    if($resourceBounds[$resourceName]['red_zone'] < $resourceValue)
    {
      return '<span style="color:#e7211a">' . ucwords($resourceName) . ' is in the Red Zone!</span>';
    }
    
    if($resourceBounds[$resourceName]['yellow_zone'] < $resourceValue)
    {
      return '<span style="color:#d6b11d">' . ucwords($resourceName) . ' is in the Yellow Zone.</span>';
    }
    
    return '<span style="color:#00a651">' . ucwords($resourceName) . ' is in the Green Zone.</span>';
  }
  
  return '';
}

?>

<h1>System Information</h1>
<br />

  <div style="width:33%; float:left;">
    <div class="panel">
      <div class="title">Software Version Information</div>
      <div class="content">
        <p style="margin: 15px;">
          <strong>b2bFront</strong><br /><br />
          &copy; <a href="http://www.transcendsolutions.net/" target="_blank" class="new">TranscendSolutions</a> 2011<br />
          Version: <?php print BF_VERSION; ?>
          <br /><br />
          This software is protected under international copyright law.
        </p>
      </div>
    </div>

    <br />

    <div class="panel">
      <div class="title">Loaded Classes</div>
      <div class="content">
        <p style="margin: 15px;">
          <?php
          
            // Find class files
            $classDirectory = str_replace('.class.php', '', Tools::CSV(
                                Tools::listDirectory(BF_ROOT . '/classes/'), ', '
                              ));
          
            print $classDirectory . ', ';
            
            // Find class files
            $adminClassDirectory = str_replace('.class.php', '', Tools::CSV(
                                    Tools::listDirectory(BF_ROOT . '/acp/classes/apis/'), ', '
                                   ));
          
            print $adminClassDirectory;
          
          ?>
        </p>
      </div>
    </div>
 
  </div>
  <div style="width:33%; float:left;">
    <div class="panel" style="margin: 0px 10px 10px 10px;">
      <div class="title">Local Storage</div>
      <div class="content" style="margin: 15px;">
        
        <table style="width: 100%;">
          <tbody>
          
            <tr>
              <td style="vertical-align: middle">
              
                <?php
                  
                  // Available bytes
                  $freeBytes = disk_free_space(BF_ROOT);
                  $totalBytes = disk_total_space(BF_ROOT);
                  $usedBytes = $totalBytes - $freeBytes;
                  
                  // Percentages
                  $freePercent = $freeBytes / $totalBytes * 100;
                  $usedPercent = $usedBytes / $totalBytes * 100;
                
                ?>
                
                <img class="chart" alt="Chart" src="https://chart.googleapis.com/chart?cht=p3&chd=t:<?php print intval($usedPercent); ?>,<?php print intval($freePercent); ?>&chs=160x60&chl=Used|Free&chf=bg,s,f5f5f5&chco=6666ff,aaaaff" />
              
              </td>
              <td style="vertical-align: middle">
              
                  <strong>Total: </strong> &nbsp; <?php print number_format($totalBytes / 1024 / 1024, 0); ?> MB <br /><br />
                  <strong>Used: </strong> &nbsp; <?php print number_format($usedBytes / 1024 / 1024, 0); ?> MB <br />
                  <strong>Free: </strong> &nbsp; <?php print number_format($freeBytes / 1024 / 1024, 0); ?> MB <br />
              
              </td>
            </tr>
          
          </tbody>
        </table>   
             
        <br /><br />
        
        <p style="text-align: center;">
        <?php
          
          print zoneText('local storage', $usedPercent);
        
        ?>     
        </p>
        
      </div>
    </div>
    
<?php
                    
  // Get cache stats
  $cacheStats = $BF->memcache->getStats();
  
  if($cacheStats)
  {
    
?>              

    <div class="panel" style="margin:10px;">
      <div class="title">Cache (<tt>memcached</tt>)</div>
      <div class="content" style="margin: 15px;">
        
        <table style="width: 100%;">
          <tbody>
          
            <tr>
              <td style="vertical-align: middle">
              
                <?php

                  // Available bytes
                  $totalBytes = $cacheStats['limit_maxbytes'];
                  $usedBytes = $cacheStats['bytes'];
                  $freeBytes = $totalBytes - $usedBytes;
                  
                  // Percentages
                  $freePercent = $freeBytes / $totalBytes * 100;
                  $usedPercent = $usedBytes / $totalBytes * 100;
                  
                ?>
                
                <img class="chart" alt="Chart" src="https://chart.googleapis.com/chart?cht=p3&chd=t:<?php print intval($usedPercent); ?>,<?php print intval($freePercent); ?>&chs=160x60&chl=Used|Free&chf=bg,s,f5f5f5&chco=6666ff,aaaaff" />
              
              </td>
              <td style="vertical-align: middle">
              
                  <strong>Total: </strong> &nbsp; <?php print number_format($totalBytes / 1024, 0); ?> KB <br /><br />
                  <strong>Used: </strong> &nbsp; <?php print number_format($usedBytes / 1024, 0); ?> KB <br />
                  <strong>Free: </strong> &nbsp; <?php print number_format($freeBytes / 1024, 0); ?> KB <br />
                      
                  <br />
                  
                  <a href="./?act=system&mode=info_cache_clear" title="Clear Cache">Clean Cache...</a>
              </td>
            </tr>
          
          </tbody>
        </table>        

             
        <br />
        
        <p style="text-align: center;">
        <?php
          
          print zoneText('cache', $usedPercent);
        
        ?>
             
        </p>   
        
      </div>
    </div>
    
<?php

  }
  else
  {
  
    // Notify admin of memcache failure
    $BF->admin->notifyMe('Memcache failure', 'There is a problem with the website cache.',
                         'exclamation.png', true, 'memcache-failure');

?>

    <div class="panel" style="margin:10px;">
      <div class="title">Cache (<tt>memcached</tt>)</div>
      <div class="content">
        <div style="text-align: center; margin: 15px 0px 15px 0px;">
          
          <img src="/acp/static/icon/exclamation.png" class="middle" /> &nbsp;
          <strong><tt>memcached</tt> is unavailable on this system.</strong>
          <br /><br />
          b2bFront needs <tt>memcached</tt> to work properly.<br />
          You should  <a href="http://uk.php.net/manual/en/memcached.installation.php"
           title="Install memcached" class="new" target="_blank">install it</a>
           immediately.
        </div>
      </div>
    </div>

<?php

  }

?>
    
    <div class="panel" style="margin:10px;">
      <div class="title">Server Workload</div>
      <div class="content" style="margin: 0px 15px 15px 15px;">
        
        <p>
        
          <?php
          
            // Use procfs or uptime to find load information
            if(@file_exists('/proc/loadavg'))
            {
              // Try to read load averages using procfs file
              $loadAverages = shell_exec('cat /proc/loadavg');
            }
            else
            {
              // Use uptime program and regex
              $uptimeOutput = shell_exec('uptime');
              $uptimeRegexMatches = array();
              preg_match('/averages: (\d+\.\d+ \d+\.\d+ \d+\.\d+)/', $uptimeOutput, $uptimeRegexMatches);
              $loadAverages = $uptimeRegexMatches[1];
            }
                      
              
            
            if(!$loadAverages)
            {
              print '<br /> Unable to read load information on this system.';
            }
            else
            {
              $loadAverageSplit = explode(' ', $loadAverages);
              $loadAverageSplit = array_slice($loadAverageSplit, 0, 3);
              $loadAverageSplit = array_combine(array(
                                                  'last 1 minute workload', 
                                                  'last 5 minutes workload', 
                                                  'last 15 minutes workload'
                                                )
                                               , $loadAverageSplit);
              
              
              foreach($loadAverageSplit as $key => $loadAverage)
              {
                print '<br />' . $loadAverage . ' Active CPUs&nbsp;<br />' . zoneText($key, $loadAverage) . '<br />';
              }
            }
            
          ?>
        
        </p>
        
      </div>
    </div>
    
  </div>
  <div style="width:33%; float:left;">
    <div class="panel" style="background: #fff">
      <div class="title">Cloud Services</div>
      <div class="content">
      
        <p style="text-align: center">
          
          <br />
          <img src="/acp/static/image/aui-rackspace-cloud-logo.jpg" alt="The Rackspace Cloud" />
          <br />
          
          This software outsources data storage to <br />
          <a href="http://www.rackspacecloud.com/" alt="Rackspace Cloud" class="new" target="_blank">The Rackspace Cloud</a>
          
          <br /><br />
          <br />
          
        </p>

      </div>
    </div>
    
    <br />
    
    <div class="panel">
      <div class="title">Key File Sizes</div>
      <div class="content">
        <table style="width: 100%; margin: 15px;">
          <tr style="height: 17px">
            <td><strong><?php print $BF->config->configCachePath; ?></strong></td>
            <td style="width: 30%;"><?php print number_format(filesize($BF->config->configCachePath)/1024, 2); ?> KB</td>
          </tr>
          <tr style="height: 17px">
            <td><strong><?php print $BF->logLocation; ?></strong></td>
            <td style="width: 30%;"><?php print number_format(filesize($BF->logLocation)/1024, 2); ?> KB</td>
          </tr>
        </table>            
      </div>
    </div>
    
  </div>
<br />
