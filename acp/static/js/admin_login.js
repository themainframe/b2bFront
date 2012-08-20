/**
 * Admin Login
 * 
 * @copyright 2010-2011 Damien Walsh
 * @package b2bFront
 * @version 1.0
 * @author Damien Walsh
 */

$(function() {

  $('#notifications')
                   .jnotifyInizialize({
                       oneAtTime: false,
                       appendType: 'append'
                   })
                   .css({ 'position':   'absolute',
                          'marginTop':  '20px',
                          'right':      '20px',
                          'width':      '250px',
                          'z-index':    '9999'
                   });
                   

});

/**
 * Show a notification
 * @param string title The title of the notification
 * @param string message The content of the notification
 * @return boolean
 */
function notify(title, message, iconURL, persist)
{
 $('#notifications').jnotifyAddMessage({
                     text: '<strong>' + title + '</strong><br /><p>' + message + '</p>',
                     permanent: persist,
                     icon: 'static/icon/' + iconURL
                   });
                   
  //setIcon();

  return true;
}