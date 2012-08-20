<?php
/**
 * Module: Logout
 * Mode: Confirmation Screen
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

<script type="text/javascript">

  $(function()
  {
    // Remove submenu
    $("div.sub_bar").hide();
    
    // Start countdown
    reduceTime();
  });
  
  // Remaining time
  var remainingTime = 10;
  
  // Reduce remaining time
  function reduceTime()
  {
    // Update UI
    $('#count').html(remainingTime)
               .animate({'font-size' : '11pt'}, 100)
               .animate({'font-size' : '8pt'}, 300);
    
    // Remove 's' if required
    if(remainingTime == 1)
    {
      $('#s').html('');
    }
    else
    {
      $('#s').html('s');
    }
    
    // Warn?
    if(remainingTime < 4)
    {
      $('#count').animate({'color' : 'red'}, 200);
    }
    
    // Finished?
    if(remainingTime == 0)
    {
      // Redirect
      window.location = './?act=logout&mode=logout_do';
    }
    else
    {
      // Decrement
      remainingTime --;
    
      // Restart
      setTimeout('reduceTime()', 1000);
    }
  }
  
</script>

<p style="text-align: center;">  
  <div class="panel" style="margin: 100px auto 0px auto; width: 500px;">
    <div class="title">Confirm Log Out</div>
    <div class="contents">
          
      <p style="line-height:20px;">
        Are you sure you wish to log out?<br />
        <span style="float: left;">You will be logged out automatically in &nbsp;</span>
        <span style="float: left;" id="count">10</span>
        <span style="float: left;">&nbsp; second</span><span id="s" style="float:left;">s</span>
      </p>
      
      <br class="clear" /><br /><br />
      
      <table style="width: 100%;">
        <tr>
          <td style="text-align: center;">
            <span class="button">
              <a href="./?act=logout&mode=logout_do">
                <span class="img" style="background-image:url(/acp/static/icon/tick-circle.png)">&nbsp;</span>
                Log Out
              </a>
            </span>
          </td>
          <td style="text-align: center;">
            <span class="button">
              <a href="./?act=dashboard">
                <span class="img" style="background-image:url(/acp/static/icon/cross-circle.png)">&nbsp;</span>
                Cancel
              </a>
            </span>
          </td>
        </tr>
      </table>
      
      <br /><br />
      
    </div>
  </div>
</p>