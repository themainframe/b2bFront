<?php
/**
 * Admin Index
 * Provides the admin login screen
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

// Startup
require_once('admin_startup.php');

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html>
  <head>
    <title><?php print $BF->config->get('com.b2bfront.site.title', true); ?> - Staff Only</title>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <link rel="stylesheet" type="text/css" href="static/style/default.css" />
    <link rel="stylesheet" type="text/css" href="js_libs/jquery_jnotify/css/jquery_jnotify.css" />
    <link type="text/css" href="js_libs/jquery_ui/css/smoothness/jquery-ui-1.8.7.custom.css" rel="stylesheet" />
    <script type="text/javascript" src="js_libs/jquery_ui/js/jquery-1.4.4.min.js"></script>
    <script type="text/javascript" src="js_libs/jquery_jnotify/js/jquery_jnotify.js"></script>
    <script type="text/javascript" src="static/js/admin_login.js"></script>
<?php
  if((isset($_GET['login']) && $_GET['login'] == 'true'))
  {
?>    
    <script type="text/javascript" src="static/js/admin_login_error.js"></script>
    <script type="text/javascript">
      $(function() {
        notify('Error', 'You have not been logged in.<br />Your credentials were incorrect.',
               'cross-circle.png', true);
      });
    </script>  
<?php
  }
?>

<?php
  if(isset($_GET['m']))
  { 
    switch($_GET['m'])
    {
      case '0':
?>    
    <script type="text/javascript">
      $(function() {
        notify('Error', 'You have not been logged in.<br />A supervisor has disabled your account.',
               'exclamation.png', true);
      });
    </script>  
<?php
      break;
      
      case '1':
?>    
    <script type="text/javascript">
      $(function() {
        notify('Automatic Logout', 'You have been logged out<br />' + 
               ' automatically for security reasons.',
               'key.png', true);
      });
    </script>  
<?php
      break;
      
      case '2':
?>    
    <script type="text/javascript">
      $(function() {
        notify('Busy', 'You have been logged out because<br />' + 
               'the system is currenty busy.',
               'control-record.png', true);
      });
    </script>  
<?php
      break;
    }
  }
?>

  </head>
  <body>  
    <div id="notifications"></div>
    <div class="container_red"></div>
    <div class="container">
      <div class="content">
        <div class="login">
          <div class="login_form">
            <form action="./?login=true" method="post">
              User name <br />
              <input type="text" class="field" name="username" />
              <br /><br />
              Password <br />
              <input type="password" class="field" name="password" />
              <br /><br />
              <input type="submit" class="submit" value="Log In" />
            </form>
          </div>
        </div>
      </div>
    </div>
  </body>
</html>