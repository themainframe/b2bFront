<?php
/**
 * Module: Data
 * Mode: Scheduled Modify
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

// Load the schedule to modify
$scheduleID = $BF->inInteger('id');

// Query for it
$BF->db->select('*', 'bf_scheduled_imports')
           ->where('id = \'{1}\'', $scheduleID)
           ->limit(1)
           ->execute();
    
// Success?
if($BF->db->count != 1)
{
  // Failed
  header('Location: ./?act=data&mode=scheduled');
  exit();
}

$scheduleRow = $BF->db->next();

?>

<script type="text/javascript">

  $(function() {
  
    // Render datepicker
    $('#f_schedule_date').datepicker();

  });

</script>

<h1><?php print $scheduleRow->name; ?></h1>
<br />

<form action="./?act=data&mode=scheduled_modify_do" method="post">
<input type="hidden" value="<?php print $scheduleRow->id; ?>" name="f_schedule_id" />

<div class="panel">
  <div class="title">Import Data</div>
  <div class="message">
    <p>
      <span style="float: left; margin-right: 0.3em;" class="ui-icon ui-icon-info"></span>
      <strong>Required Fields</strong> - You need to complete all of the fields in this panel.
      <br class="clear" />
    </p> 
  </div>
  <div class="contents fieldset">
    <table class="fields">
      <tbody>
      
        <tr>
          <td class="key">
            <strong>Create New Items</strong><br />
            Automatically create any SKUs that do not exist.
          </td>
          <td class="value">
            <input type="checkbox" name="f_new_skus" id="f_new_skus"<?php print ($scheduleRow->create_new_skus ? ' checked="checked"' : ''); ?> />
          </td>
        </tr>
        
        <tr>
          <td class="key">
            <strong>Scheduled Date</strong><br />
            Choose a date on which the data will be applied.
          </td>
          <td class="value">
            <input type="text" style="width: 100px;" name="f_schedule_date" id="f_schedule_date" value="<?php print date('d M Y', $scheduleRow->timestamp);?>" />
          </td>
        </tr>
  
        <tr>
          <td class="key">
            <strong>Scheduled Time</strong><br />
            Choose the time at which the data will be applied.<br />
            <span class="grey">24 Hour: &nbsp; 0 - Midnight &nbsp; 12 - Noon</span>
          </td>
          <td class="value">
              
            <?php
              
              // Calculate selected hour and minute
              $hours = date('G', $scheduleRow->timestamp);
              $minutes = date('i', $scheduleRow->timestamp);
            
            ?>
              
            <select name="f_schedule_time_hours">
              <?php
                for($hour = 0; $hour < 24; $hour ++)
                {
                  $paddedHour = str_pad($hour, 2, '0', STR_PAD_LEFT);
                  print '              <option value="' . $hour . '"' . 
                        ($hours == $hour ? ' selected="selected"' : '') . '>' . $paddedHour . '</option>';
                }
              ?>
            </select>
            
            &nbsp; Hours &nbsp; &nbsp;
            
            <select name="f_schedule_time">
              <?php
                for($minute = 0; $minute < 60; $minute ++)
                {
                  $paddedMinute = str_pad($minute, 2, '0', STR_PAD_LEFT);
                  print '              <option value="' . $minute . '"' . 
                        ($minutes == $paddedMinute ? ' selected="selected"' : '') . '>' . $minute . '</option>';
                }
              ?>
            </select>
            
            &nbsp; Minutes
            
          </td>
        </tr>
        
        <tr class="last">
          <td class="key">
            <strong>Notifications</strong><br />
            Choose how you wish to be notified when the data is applied.
          </td>
          <td class="value">
           
            <table class="suboptions">
            <thead>

            
              <tr class="header">
                <td>
                  <strong>Notification Type</strong>
                </td>
                <td class="value">
                  <strong>Enable</strong>
                </td>
              </tr>

              
            </thead>
          
            <tbody>
      
              <tr>
                <td style="background: #dfdfdf;">
                  Email</td>
                <td class="value">
                  <input type="checkbox" name="f_schedule_notify_email" value="1"<?php print ($scheduleRow->notification_email ? ' checked="checked"' : ''); ?> />
                </td>
              </tr>
               
              <?php
                if($BF->admin->getInfo('mobile_number') != '')
                {
              ?>   
                      
              <tr>
                <td style="background: #dfdfdf;">
                  SMS</td>
                <td class="value">
                  <input type="checkbox" name="f_schedule_notify_sms" value="1"<?php print ($scheduleRow->notification_sms ? ' checked="checked"' : ''); ?> />
                </td>
              </tr>
              
              <?php
                }
              ?>
    
            </tbody>
          </table>

          </td>
        </tr>
      
      
      </tbody>
    </table>
        
  </div>
</div>

<br />

<div class="panel" style="border: 1px solid #50954b">
  <div class="contents">
    <p style="padding: 7px 0px 0px 5px; float: left; color: #50954b;">
      <strong>Click the button to the right to save the modifications to this Scheduled Import.</strong>
    </p>
    <input id="submit_button" class="submit ok" type="submit" style="float: right;" value="Save and Exit" />
    <br class="clear" />
  </div>
</div>

</form>