<?php
/**
 * Admin Main
 * Provides the framework for the Admin interface.
 * Loads modules from /acp/modules/
 * 
 * @copyright 2010-2011 Damien Walsh
 * @package b2bfront
 * @version 1.0
 * @author Damien Walsh
 */

// Set context
define('BF_CONTEXT_ADMIN', true);
 
// Load startup
require_once('admin_startup.php');

// Load common classes
require_once(BF_ROOT . '/acp/classes/Admin.class.php');

// Load tools
include_once(BF_ROOT . '/tools.php');

// Create a new kernel object and set it's admin property
$BF = new BFClass();

// Change Config path
$BF->config->setPath('com.b2bfront.acp');
$BF->admin = new Admin(& $BF);

// Make main class global
global $BF;

// Special authentication mode ?
if($BF->in('login') == 'true')
{
  $BF->admin->logIn($BF->in('username'), $BF->in('password'));
}

if(!$BF->admin->isAdmin)
{
  include 'admin_login.php';
  $BF->shutdown();
  
  exit();
}

// Admin movement
$BF->admin->setLastActivity();

// Check for no module
$moduleName = '';

if(!$BF->in('act') && file_exists(BF_ROOT . '/acp/modules/' . $BF->in('act')))
{
  $moduleName = 'dashboard';
}
else
{
  $moduleName = Tools::removePaths($BF->in('act')); 
}

// Perform logic
$BF->admin->loadModuleLogic($moduleName);

// Begin buffering output
ob_start();

// Try to load the menu from memcache
if(!($menu = $BF->cache->getValue('com.b2bfront.acp.menu')))
{
  // Load menu property list
  $menuPListPath = '/acp/definitions/acp_menu.plist';
  $acpMenu = new PropertyList();
  $menu = $acpMenu->parseFile(
    BF_ROOT . $menuPListPath);
    
  // Failure?
  if(!$menu)
  {
    $BF->log('Unable to load ' . $menuPListPath);
    
    // Critical failure - cannot run the ACP without the menu
    throw new Exception('Cannot continue rendering ACP without ' . $menuPListPath);
    exit();
  }
  
  // Cache the menu
  $BF->cache->addValue('com.b2bfront.acp.menu', $menu);
}

// Try to generate a nice title
$pageTitle = 'Admin';

if(array_key_exists($moduleName, $menu))
{
  $pageTitle = $menu[$moduleName]['title']; 
}

?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html>
  <head>
    <title><?php print $BF->config->get('com.b2bfront.site.title', true); ?> - <?php print $pageTitle; ?></title>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <link rel="favicon" type="image/icon" href="<?php print $BF->config->get('com.b2bfront.site.url', true); ?>/favicon.ico" />
    <link rel="stylesheet" type="text/css" href="static/style/default_reset.css" />
    <link rel="stylesheet" type="text/css" href="static/style/default_main.css" />
    <link rel="stylesheet" type="text/css" href="static/style/aui_elements.css" />
    <link type="text/css" href="js_libs/jquery_ui/css/smoothness/jquery-ui-1.8.7.custom.css" rel="stylesheet" />
    <link rel="stylesheet" type="text/css" href="js_libs/jquery_filetree/css/jquery_filetree.css" />
    <link rel="stylesheet" type="text/css" href="js_libs/jquery_jnotify/css/jquery_jnotify.css" />
    <link rel="stylesheet" media="screen" type="text/css" href="js_libs/jquery_colourpicker/css/colorpicker.css" />
    <link rel="stylesheet" media="screen" type="text/css" href="js_libs/jquery_uploadify/uploadify.css" />
    <link rel="stylesheet" media="screen" type="text/css" href="js_libs/jquery_fg_menu/css/fg.menu.css" />
    <link type="text/css" href="js_libs/jquery_fg_menu/css/theme/ui.all.css" media="screen" rel="stylesheet" />
<?php
		
		  // Load the stylesheet for a module if it exists
		  if(Tools::exists('/acp/modules/' . $moduleName . '/style.css'))
		  {
		    print '    <link rel="stylesheet" type="text/css" href="' . '/acp/modules/' . $moduleName . '/style.css' . '" />';
		  }
		  
?>

    <script type="text/javascript" src="js_libs/jquery_ui/js/jquery-1.4.4.min.js"></script>
    <script type="text/javascript" src="js_libs/jquery_ui/js/jquery-ui-1.8.16.custom.min.js"></script>
    <script type="text/javascript" src="js_libs/jquery_url_preview/url_preview.js"></script>
    <script type="text/javascript" src="js_libs/jquery_filetree/js/jquery_filetree.js"></script>
    <script type="text/javascript" src="js_libs/jquery_jnotify/js/jquery_jnotify.js"></script>
    <script type="text/javascript" src="js_libs/jquery_colourpicker/js/colorpicker.js"></script>
    <script type="text/javascript" src="js_libs/jquery_uploadify/jquery.uploadify.v2.1.4.min.js"></script>
    <script type="text/javascript" src="js_libs/jquery_uploadify/swfobject.js"></script>
    <script type="text/javascript" src="js_libs/jquery_scrollto/jquery_scrollto.min.js"></script>
    <script type="text/javascript" src="js_libs/jquery_fg_menu/fg.menu.js"></script>
    <script type="text/javascript" src="js_libs/jquery_jfeed/jatom.js"></script>
    <script type="text/javascript" src="js_libs/jquery_jfeed/jfeeditem.js"></script>
    <script type="text/javascript" src="js_libs/jquery_jfeed/jfeed.js"></script>
    <script type="text/javascript" src="js_libs/ckeditor/ckeditor.js"></script>
<?php

  // Enable floating table headers
  if($BF->config->get('com.b2bfront.acp.floating-data-headers', true))
  {

?>
    <script type="text/javascript" src="js_libs/jquery_floating_table_headers/floating-table-headers.js"></script>
    <style type="text/css">
      table.data {
        border-top: none;
      }
      table.data thead tr {
        border-top: 1px solid #afafaf;
      }
    </style>
<?php
  
  }

?>
    <script type="text/javascript" src="static/js/admin.js"></script>
    <script type="text/javascript" src="static/js/admin_demo.js"></script>
<?php
		  
		  // Load scripts for a module
		  if(Tools::exists('/acp/modules/' . $moduleName . '/main.js'))
		  {
		    print '    <script type="text/javascript" src="' . '/acp/modules/' . $moduleName . '/main.js' . '"></script>';
		  }
		  
?>
    <script type="text/javascript">
    $(function() {
<?php

  // Automatically re-fill all f_ fields
  foreach($BF->inputs as $fieldName => $fieldValue)
  {
    // Error mark?
    if(substr($fieldName, 0, 2) == 'e_')
    {
    ?>
      error('<?php print Tools::safe(substr($fieldName, 2)); ?>',
            '<?php print Tools::safe($BF->in('message')); ?>');
    <?php
    }
  
    // Non field value?
    if(substr($fieldName, 0, 2) != 'f_')
    {
      continue;
    }
    
    ?>
      $('[name="<?php print Tools::safe($fieldName); ?>"]')
        .attr('value', '<?php print Tools::safe($fieldValue); ?>');
    <?php
  }

?>
  
      // Clear errors on click
      $('.error').click(function() {
        $(this).removeClass('error');
      });

    });
      
    </script>
  </head>
  <body>
    <div class="header">
      <div class="quick_search">
        <div class="quick_search_box">
          <input type="text" class="hinted" style="width: 200px" id="quickGo" 
             value="Quick Go..." />
           <div id="quickGoResults"></div>
        </div>
      </div>
      <div class="info">
        <?php print Tools::longDate(); ?>
        <?php print (date('d') == 1 && date('m') == 1 ? '- <strong>Happy New Year</strong>' : '' ) ?><br />
        <span class="grey">b2bFront (<?php print BF_VERSION; ?>) &copy; TranscendSolutions 2011</span>
      </div>
    </div>
    <img src="./static/image/aui-loader.gif" class="ghost" />
    <div id="notifications"></div>
    <div id="item-selector" class="ghost" style="padding: 0px;">
      <div style="padding: 6px !important; background: url(./static/image/aui-subbar.png) repeat-x 0px 10px;
        border-bottom: 1px solid #afafaf;">
        &nbsp;
        <strong>Search For:</strong> &nbsp;
        <input id="item-selector-term" type="text" style="width: 120px" class="hinted"
          value="SKU or Name…" /> &nbsp;
        <select id="item-selector-category" class="item-selector-option">
          <option value="-1" style="background: #afafaf">Categorised as…</option>
<?php

  $categories = $BF->db->query();
  $categories->select('*', 'bf_categories')
             ->order('name', 'asc')
             ->execute();
  
  while($category = $categories->next())
  {
    print '          <option value="' . $category->id . '">' . 
      Tools::truncate($category->name, 14) . '</option>' . "\n";
  }
  
?>
  
        </select>
        <select id="item-selector-label" class="item-selector-option">
          <option value="-1" style="background: #afafaf">Labelled as…</option>
<?php

  $labels = $BF->db->query();
  $labels->select('*', 'bf_item_labels')
         ->order('name', 'asc')
         ->execute();
  
  while($label = $labels->next())
  {
    print '          <option value="' . $label->id . '">' . 
      Tools::truncate($label->name, 14) . '</option>' . "\n";
  }
  
?>
        </select>
        <select id="item-selector-classification" class="item-selector-option">
          <option value="-1" style="background: #afafaf">Classified as…</option>
<?php

  $classifications = $BF->db->query();
  $classifications->select('*', 'bf_classifications')
                  ->order('name', 'asc')
                  ->execute();
  
  while($classification = $classifications->next())
  {
    print '          <option value="' . $classification->id . '">' . 
      Tools::truncate($classification->name, 14) . '</option>' . "\n";
  }
  
?>
        </select>
      </div>
      <input type="hidden" id="item-selector-csv" value="" />
      <div id="item-selector-contents" style="height: 387px; overflow: auto">
        <table style="width: 100%; display: none;" class="data parent-scroll">
          <thead>
            <tr class="header" style="border:0px">
              <td style="width: 20px"><input type="checkbox" id="item-selector-all" /></td>
              <td style="width: 60px">SKU</td>
              <td>Name</td>
            </tr>
          </thead>
          <tbody>
          
          </tbody>
        </table>
      </div>
    </div>
    <div id="page-transition-loader" class="ghost"></div>
    <div id="confirmation" title="Please Confirm" class="ghost"></div>
    <div id="info" title="Information" class="ghost"></div>
  
<?php
  
  if($BF->admin->can('chat') && $BF->config->get('com.b2bfront.acp.sounds', true))
  {
  
?>     
<script type="text/javascript">
  function getPlayer(pid) {
  	var obj = document.getElementById(pid);
  	if (obj.doPlay) return obj;
  	for(i=0; i<obj.childNodes.length; i++) {
  		var child = obj.childNodes[i];
  		if (child.tagName == "EMBED") return child;
  	}
  }
  function doPlay(fname) {
  	var player=getPlayer("audio1");
  	if(!player)
  	{
  	 return false;
  	}
  	player.play(fname);
  }
  function doStop() {
  	var player=getPlayer("audio1");
  	player.doStop();
  }
</script>
<?php

  }
  else
  {
  
?> 
<script type="text/javascript">
  function doPlay(fname) {
  	return true;
  }
</script>
<?php
  
  }
  
?>

    <div id="message-system" title="Live Chat" class="ghost" style="padding: 10px; overflow: hidden">
            
          <div style=" width: 200px; float: left; height: 100%;">
            
            <div class="panel" style="height: 87%; background: white; overflow:auto">
              <div class="title">Online Dealers</div>
              <div class="content" id="dealers-list"></div>
            </div>
            
            <br />
            
            <div class="panel mystatus" id="mystatus" style="height: 8%;" title="Toggle Status">
              <div class="content" style="padding: 10px;">
                <div id="mystatus-led" class="d-status">&nbsp;</div>
                <?php print $BF->admin->getInfo('full_name'); ?>
              </div>
            </div>

          </div>
            
            
            <div class="right comm_panel" style="float: right; width: 570px">
              <div class="chatHeader" style="height: 33px; padding-top: 7px; padding-left: 10px;">
                <h2 id="chatTitle" style="float: left"></h2>
                <span class="grey" style="float: right; position: relative; top: 5px" id="cctv_state"></span>
              </div>
              <div class="chatArea" style="overflow: auto; height: 330px; margin-bottom: 10px; border: 1px solid #afafaf">
              </div>
              <textarea class="inputArea"></textarea>
           </div>
           
           <div class="right info_panel d-overlay">
             <div style="padding-top: 200px">
               <strong>There are currently no dealers available for chat.</strong>
               <br /><br />
               Please wait until a dealer with a <img src="/acp/static/icon/status.png" alt="LED" class="middle" /> to the left of their name appears under <em>Online Dealers</em>.
             </div>
           </div>
           
           <div class="right offline_panel d-overlay">
             <div style="padding-top: 200px">
               <strong>You are currently offline.</strong>
               <br /><br />
               You need to be online to chat with dealers.<br />
               Click <a href="#" onclick="switchOnlineOffline()">here</a> to go online now.
             </div>
           </div>
           
    </div>
    
<?php

  if($BF->config->get('com.b2bfront.site.maintenance', true) == '1')
  {
  
?> 
    <div class="maintenance">
      
      <img src="/acp/static/icon/construction.png" class="middle" alt="Under Construction" />
       &nbsp;
      <strong>Maintenance mode</strong> &nbsp; - &nbsp;
      The frontend is currently unavailable.
      
      <a href="/acp/?act=system&mode=config_maintenance_exit">Click here</a> to return to normal mode.
    </div>
  
<?php
  
  }

?>  
    
    <div class="menu">
      <ul class="menu">
<?php
        
        foreach($menu as $key => $value)
        {
        
          $show = false;
        
          // Should this option be possible?
          if(isset($value['permissions']))
          {
            if(is_array($value['permissions']))
            {
              foreach($value['permissions'] as $permissionName)
              {
                if($BF->admin->can($permissionName))
                {
                  $show = true;
                }
              }
            }
            else
            {
              if($BF->admin->can($value['permissions']))
              {
                $show = true;
              }
            }
          }
          else
          {
            $show = true;
          }
          
          if(!$show)
          {
            continue;
          }
          
          print '        <li class="' . Tools::conditional($key, $moduleName, 'selected') . '">' . "\n";
          print '          <a class="' . $key . '" href="./?act=' . $key . '">' . $value['title'] . "\n";
          
          // Badges?
          $unprocessedOrderCount = $BF->admin->api('Orders')->countUnprocessed();
          if($key == 'orders' && $unprocessedOrderCount > 0)
          {
            // Print badge
            print '          <span class="badge">' . 
                  $unprocessedOrderCount . '</span>';
          }
          
          print '        </a></li>' . "\n";

        }
        
?>
      </ul>
      
      <ul class="menu" style="float:right">
      
<?php

  // Get emergency count
  $BF->db->select('*', 'bf_events')
         ->where('attention_required = 1 AND level = 4')
         ->execute();
             
  // Required?
  if($BF->db->count > 0)
  {
  
?>
           
        <li id="emergency-indicator" style="background: transparent; border-left: none; border-right: 1px solid #fff;">
         <a href="./?act=system&mode=events" target="_blank">
         <img title="There <?php print ($BF->db->count == 1 ? 'is' : 'are') ?> <?php print $BF->db->count; ?> <?php print ($BF->db->count == 1 ? 'emergency' : 'emergencies') ?>.  Please address emergencies ASAP." src="/acp/static/icon/emergency.gif" />
         </a>
        </li>
        
<?php

  }
  
  if($BF->admin->can('chat'))
  {
  
?>
       
       
        <li id="messages-toggle" style="padding-right: 1px; border-left: 1px solid #a2a2a2; border-right: 1px solid #fff;">
          <a title="Live Chat" class="shortcut messages-menu <?php print ($BF->admin->getInfo('online', true) == '1' ? '' : 'messages-offline'); ?>" href="#"></a>
        </li>
      
<?php

  }
  
?>
      
      </ul>
      
    </div>
    <div class="sub_bar">
      <ul class="sub_menu">
      <?php
        // Try to load the menu, this is *optional*
        if(!$BF->admin->loadModuleMenu($moduleName))
        {
          print '&nbsp;';
        }
      ?>
      </ul>
    </div>
    <div class="container">

<?php
  
  // Track buffer size
  $bufferSize = ob_get_length();

  // Load module
  if(!$BF->admin->loadModule($moduleName))
  {
    ?>
      <h1 class="subheader">Not Found</h1>
      <br />
      <p>
        The requested module could not be loaded.<br />
        This exception generated an <a href="./?act=system&mode=events" title="Events" class="new">event</a>.
      </p>
    <?php
  
    // Log event
    $BF->logEvent('ACP Error',
                      'Index could not find the module file: ' . $moduleName);
    
  }
  
  // Check if output was made
  if(ob_get_length() == $bufferSize)
  {
    ?>
      <h1 class="subheader">Nothing to Display</h1>
      <br />
      <p>
        The requested module did not produce any output.<br />
        The associated action probably didn't execute.<br /><br />
        This exception generated an <a href="./?act=system&mode=events" title="Events" class="new">event</a>.
      </p>
    <?php
    
    if(substr($BF->in('mode'), strlen($BF->in('mode')) - 3, 3) != '_do')
    {  
      // There was no output written by the loaded module.
      $BF->logEvent('ACP Error',
                        'The module didn\'t create any output: ' . $moduleName);
    }
  }
  
?>
    </div>
    
<object classid="clsid:d27cdb6e-ae6d-11cf-96b8-444553540000"
    width="1"
    height="1"
    id="audio1"
    style="visibility: hidden; float: left"
    align="middle" >
    <embed id="playerOb" src="/acp/static/swf/wavplayer.swf?gui=mini&h=1&w=1&sound=/acp/static/media/attention.wav&"
        bgcolor="#ffffff"
        width="1"
        height="1"
        allowScriptAccess="always"
        type="application/x-shockwave-flash"
        pluginspage="http://www.macromedia.com/go/getflashplayer"
    />
</object>
    
  </body>
</html>

<?php

// Finished, clear buffer
ob_end_flush();

// Finish rendering
$BF->shutdown();

?>