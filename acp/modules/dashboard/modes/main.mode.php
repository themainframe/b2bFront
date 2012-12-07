<?php
/**
 * Admin Module : Dashboard
 * 
 * @copyright 2010-2011 Damien Walsh
 * @package b2bfront
 * @version 1.0
 * @author Damien Walsh
 */

// Context check
if(!defined("BF_CONTEXT_ADMIN"))
{
  exit();
}

// Gain BFClass access
global $BF;

/**
 * Generate an activity graph for the past 24 hours using CCTV data
 * @return string
 */
function activityGraphData()
{
  global $BF;

  // Build a collection
  $dataPoints = array();

  // For each hour
  for($hour = 24; $hour > 0; $hour --)
  {
    // Calculate start and end points
    $startPoint = time() - ($hour * 3600);
    $endPoint = time() - (($hour - 1) * 3600);
    
    // Find activity count
    $BF->db->select('id', 'bf_user_action_logs')
           ->where('`timestamp` > {1} AND `timestamp` < {2}', $startPoint, $endPoint)
           ->execute();
       
    $dataPoints[] = $BF->db->count * 2;
  }
  
  // Scale
  $dataPoints = Tools::upperBound($dataPoints);

  // Return a CSV
  return Tools::CSV($dataPoints);
}

?>

<script type="text/javascript">
  

  $(function()
  {
    // Retrieve CCTV every second
    getCCTV();   
    
    // Add focus
    $('#issue').focus(function() {
      
      $(this).addClass('stretch', 'fast');
      
    }).blur(function() {
      
      $(this).removeClass('stretch', 'fast');
      
    });
    
    
    // Focus title
    $('#title').focus(function() { $(this).removeClass('error'); } );
    
    
    // Completion
    $('#issue_c').click(function() {
      
      // Loading
      loadingScreen();
      
      // Validate
      if($('#title').val() == '')
      {
        // Invalid
        $('#title').addClass('error');
        
        hideLoadingScreen();
        return false;
      }
      
      // All OK
      console.log('Submitting');
      
      $.ajax(
      {
        'url' : './ajax/dashboard_issue_create.ajax.php',
        
        'data' : {
          'title' : $('#title').val(),
          'body' : $('#issue').val(),
          'label' : $('#urgent').is(':checked') ? 'acp_urgent' : 'acp_general'
          
        },
        
        'error' : function(a, b) {
          
          showMessage('Sorry - the issue could not be created.<br />Please contact Transcend.',
            function() { });
          hideLoadingScreen();
          
        },
        
        'complete' : function() 
        {
          console.log('done');
          hideLoadingScreen();
          
        },
        
        'success' : function(data) {
        
        // Clear
        $('#title').val('');
        $('#issue').val('');
        $('#urgent').attr('checked', false);
        
        // Show confirmation
        hideLoadingScreen();
        showMessage('Thank you<br />The issue has been created.', function() { });
      },
      
      'type': 'POST'
        
      });
      
    });
     
  });
  
  /**
   * Get the current CCTV changes and show them in the dashboard
   * @return boolean
   */
  function getCCTV()
  {
    $.get('./ajax/dashboard_recent_activity.ajax.php', function(data) {
      
      $('#mini_cctv').html(data);
      $('.cctv_log:last').css('border-bottom', '0px');
      
      // Empty?
      if(data == '')
      {
        $('#no_cctv').show();
      }
      else
      {
        $('#no_cctv').hide();
      }
      
    });
    
    // Relaunch
    setTimeout('getCCTV()', 1000);
  }
  
</script>

<h1>Welcome, <?=$BF->admin->getInfo('full_name')?></h1>
<br />

<div class="threebox">
  <div class="box" style="border-right: 1px solid #cfcfcf;">
    <table>
      <tr>
        <td class="key">Items</td>
        <td class="value">
          <?php print $BF->admin->api('Items')->count(); ?>
        </td>
        <td class="link">
          <a href="./?act=inventory">View</a>
        </td>
      </tr>
      <tr>
        <td class="key">Dealers</td>
        <td class="value">
          <?php print $BF->admin->api('Dealers')->count(); ?>
        </td>
        <td class="link">
          <a href="./?act=dealers">View</a>
        </td>
      </tr>
      <tr>
        <td class="key">Unprocessed Orders</td>
        <td class="value">
          <?php print $BF->admin->api('Orders')->countUnprocessed(); ?>
        </td>
        <td class="link">
          <a href="./?act=orders">View</a>
        </td>
        </td>
      </tr>
      <tr>
        <td class="key">Held Orders</td>
        <td class="value">
          <?php print $BF->admin->api('Orders')->countHeld(); ?>
        </td>
        <td class="link">
          <a href="./?act=orders&mode=held">View</a>
        </td>
      </tr>
    </table>
  </div>
  <div class="box">
    <table>
      <tr>
        <td class="key">Questions</td>
        <td class="value"><?php print $BF->admin->api('Questions')->countUnanswered(); ?></td>
        <td class="link">
          <a href="./?act=dealers&mode=questions">View</a>
        </td>
      </tr>
      <tr>
        <td class="key">Uncategorised Items</td>
        <td class="value">
          <?php print $BF->admin->api('Items')->countUncategorised(); ?>
        </td>
        <td class="link">
          <a href="./?act=inventory&mode=browse&f_in=sku&f_filter=-1&f_term=&f_in=sku&f_category=-1&f_filter=-1">View</a>
        </td>
      </tr>
      <tr>
        <td class="key">Unclassified Items</td>
        <td class="value">
          <?php print $BF->admin->api('Items')->countUnclassified(); ?>
        </td>
        <td class="link">
          <a href="./?act=orders">View</a>
        </td>
      </tr>
    </table>
  </div>
  <div class="box" style="text-align:center; border-left: 1px solid #cfcfcf;">
    
    <a style="cursor: pointer;" href="./?act=dealers&mode=cctv" title="Last 24 Hours Website Activity">
      <img class="chart" alt="Last 24 Hours Website Activity" src="http://chart.apis.google.com/chart?chxr=0,0,8|1,0,24&chs=280x50&cht=lc:nda&chco=afafaf&chd=t:<?php print activityGraphData(); ?>&chm=B,cfcfcf,0,0,0&chf=bg,s,f5f5f5" />
      <br /><img src="/acp/static/image/aui-chart.png" alt="scale" />
   </a>
  
  </div>
  <br style="clear: both;" />
</div>


  <div style="width:31%; float:left;">
  

<?php
  if($BF->in('login'))
  {
?>
  <div class="panel" style="margin: 20px 0px 10px 0px;">
    <div class="title">Your Last ACP Login</div>
    <div class="contents">
      <img src="./static/icon/information.png" alt="Info" 
        class="middle" style="position: relative; top: -2px;" /> &nbsp; 
      <?php print Tools::longDate($BF->admin->getInfo('last_login_timestamp')); ?>
    </div>
  </div>
<?php
  }
?>

<?php

  if($BF->admin->can('chat'))
  {
  
?>

      <div class="panel" style="margin: 20px 0px 10px 0px;">
      <div class="title">Staff</div>
      <div class="content" id="im-system">
      
          <div style="text-align: center; margin: 15px 0px 15px 0px;" id="no-staff">
          <strong>No staff are currently logged in.</strong>
          </div>

      </div>
    </div>
    
    <a href="./?act=system&mode=admins"  style="margin:10px 10px 10px 10px;">Manage Staff...</a>
 
  
<?php

  }
  
?>

  
    <div class="panel" style="margin:20px 0px 10px 0px;">
      <div class="title">Unprocessed Orders</div>
      <div class="content">
      
<?php

  // Obtain unprocessed
  $BF->db->select('*', 'bf_orders')
         ->where('processed = 0')
         ->order('timestamp', 'DESC')
         ->limit(5)
         ->execute();

  while($order = $BF->db->next())
  {
    ?>
        <div class="notification_log gradient" <?php print($BF->db->last() ? 'style="border:0;"' : '') ?>>
          <img style="vertical-align: middle;" src="/acp/static/icon/money-coin.png" alt="Icon" />
          &nbsp; <strong>
          <?php print $BF->config->get('com.b2bfront.ordering.order-id-prefix', true) . $order->id; ?></strong><br />
          <p>
            <?php print Tools::longDate($order->timestamp); ?> &nbsp;
            <a href="./?act=orders&mode=unprocessed_view&id=<?php print $order->id; ?>" title="View">View...</a>
          </p>
        </div>
    <?php
  }
  
  if($BF->db->count == 0)
  {
    ?>
    
        <div style="text-align: center; margin: 15px 0px 15px 0px;">
          <strong>There are no unprocessed orders.</strong>
        </div>
    
    <?php
  }           

?>
      

      </div>
    </div>
    
    
    
    <a href="./?act=orders&mode=unprocessed"  style="margin:10px 10px 10px 10px;">All unprocessed orders...</a>

    <br /><br />

  </div>
  

  </div>

  
  <div style="width:33%; float:left;">

<!--

    <div class="panel" style="margin: 0px 10px 10px 10px;">
 <div class="title">Account Requests</div>
      <div class="content">
          <div style="text-align: center; margin: 15px 0px 15px 0px;">
          <strong>There are no account requests.</strong>
        </div>
      </div>

    </div>
        <a href="./?act=dealers&mode=unapproved"  style="margin:10px 10px 10px 10px;">All account requests...</a>
        
    -->    
        

   <div class="panel" style="margin: 0px 10px 10px 10px;">
    <div class="title">Raise an Issue...</div>
      
      <div class="content issues">
 
         <div style="text-align: center; margin-top: 6px; font-weight: bold">
           Tell us about a problem or suggest an improvement...
         </div>
         
                 <div class="grey" style="text-align: center; margin-top: 6px">Please be specific - include relevant URLs if you can!</div>
         
         <br />
        
        <strong id="title_title">Title: </strong> &nbsp; <input type="text" id="title" /> 
        <br /> <br />
        

               
        <textarea id="issue"></textarea>
        
        
        <br />
        
        <div class="issue_lhs">
          <input type="checkbox" id="urgent" value="acp_urgent" /> &nbsp; <strong>Issue is urgent</strong>
        </div>
        
        <div class="issue_rhs">
        <input class="submit ok" type="button" id="issue_c" style="float: right; margin-top: 10px;" value="Create Issue" />
        </div>
        
 
        
       <br /><br />
        
      </div><br class="clear" />
   </div>




        
<?php

  // Fortune mode?
  if($BF->config->get('com.b2bfront.acp.dashboard-motd-fortune', true))
  { 
    $topTips = array();
    $topTips[0]['title'] = 'How fortunate...';
    $topTips[0]['content'] = str_replace('  ', '&nbsp;', str_replace("\n", '<br />',
      shell_exec('/usr/games/fortune -s')));
  }
  else
  {
    // Load top tips from PList
    $topTipsPListParser = new PropertyList();
    $topTips = $topTipsPListParser->parseFile(
      BF_ROOT . '/acp/definitions/top_tips.plist');
      
    // Randomise
    shuffle($topTips);
  }  
?>
    
    <div class="panel" style="margin: 0px 10px 10px 10px;">
      <div class="title">Message Of The Day</div>
      <div class="content">
        <div style="margin: 10px 15px 15px 15px; font-style: italic">
          <img src="./static/icon/wand-hat.png" title="Kerblamo!" alt="Tip:" 
            style="position: relative; top: 3px;" />&nbsp;
          <strong><?php print $topTips[0]['title']; ?></strong>...
          <br /><br />
          <?php print $topTips[0]['content']; ?>
        </div>
      </div>
    </div>

  </div>
  
  
  <div style="width:33%; float:left;">
    <div class="panel" style="margin:0px 0px 10px 0px;">
      <div class="title">Recent Notifications</div>
      <div class="content">
<?php

  // Obtain notifications
  $BF->db->select('*', 'bf_admin_notifications')
             ->where('admin_id = {1} AND logged = 1', $BF->admin->AID)
             ->order('timestamp', 'DESC')
             ->limit(5)
             ->execute();
  
  while($notification = $BF->db->next())
  {
    ?>
        <div class="notification_log gradient" <?php print($BF->db->last() ? 'style="border:0;"' : '') ?>>
          <img style="vertical-align: middle;" src="/acp/static/icon/<?=$notification->icon_url?>" alt="Icon" />
          &nbsp; <strong><?=$notification->title?></strong>
          
          <a href="./?act=dashboard&mode=notifications_remove_do&id=<?php print $notification->id; ?>"
             class="grey" style="float: right;">Dismiss</a>
          
          <br class="clear" />
          <p>
            <?=$notification->content?>
          </p>
        </div>
    <?php
  }
    
  if($BF->db->count == 0)
  {
  
  ?>
  
        <div style="text-align: center; margin: 15px 0px 15px 0px;">
          <strong>There are no notifications.</strong>
          <br /><br />
          Notifications are generated when<br />important events occur.
        </div>
  
  <?php           

  }

?>
      </div>
    </div>
    <a href="./?act=dashboard&mode=notifications"  style="margin:10px;">All notifications...</a>
    
    <br /><br />
    
    <div class="panel" style="margin:0px 0px 10px 0px;">
      <div class="title">Activity</div>
      <div class="content" id="mini_cctv">
      </div>
      <div id="no_cctv" style="text-align: center; margin: 15px 0px 15px 0px;">
        <strong>There is no activity right now.</strong>
        <br /><br />
        This view normally displays what users<br />
        are doing on the website.
      </div>
    </div>
    
    <a href="./?act=dealers&mode=cctv"  style="margin:10px;">View more activity information...</a>
    
    
  </div>
<br />

<br /><br /><br /><br />
