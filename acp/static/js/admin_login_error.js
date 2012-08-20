/**
 * Admin Login Error
 * 
 * @copyright 2010-2011 Damien Walsh
 * @package b2bFront
 * @version 1.0
 * @author Damien Walsh
 */
 
var WARNING_TIME = 700;

$(function() {
  
  // Show red container
  $("div.container_red").fadeIn(WARNING_TIME);
  
  // Hide after the animation completes
  setTimeout('hideRedContainer()', WARNING_TIME);
  
});

function hideRedContainer()
{
  $("div.container_red").fadeOut(WARNING_TIME);
}