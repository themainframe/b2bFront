/**
 * Floating Table Headers
 * Modified for use in b2bFront by Damien Walsh
 *
 * http://code.google.com/p/js-floating-table-headers/
 *
 * @author Damien Walsh <damien@transcendsolutions.net>
 * @version 1.0
 * @package b2bfront
 */ 
function UpdateTableHeaders() {
  $("div.divTableWithFloatingHeader").each(function() {
      var originalHeaderRow = $(".tableFloatingHeaderOriginal", this);
      var floatingHeaderRow = $(".tableFloatingHeader", this);
      var offset = $(this).offset();
      var scrollTop = $(this).hasClass('parent-scroll') ? $(this).parent().parent().scrollTop() : $(window).scrollTop();
      if ((scrollTop > offset.top) && (scrollTop < offset.top + $(this).height())) {
          floatingHeaderRow.css("display", "block");
          // floatingHeaderRow.css("top", Math.min(scrollTop - offset.top, $(this).height() - floatingHeaderRow.height()) + "px");

          floatingHeaderRow
            .stop(true)
            .animate(
            { 'top': Math.min(scrollTop - offset.top, $(this).height() - floatingHeaderRow.height()) + "px" }, 30);

          // Copy cell widths from original header
          $("td", floatingHeaderRow).each(function(index) {
              var cellWidth = $("td", originalHeaderRow).eq(index).width();
              $(this).css('width', cellWidth + 'px');
          });

          // Copy row width from whole table
          floatingHeaderRow.css("width", $(this).css("width"));
      }
      else {
          floatingHeaderRow.css("display", "none");
          floatingHeaderRow.css("top", "0px");
      }
  });
}

$(document).ready(function() {
  $("table.data").not('.parent-scroll').each(function() {
      
      var isInDiv = $(this).hasClass('parent-scroll');
      
      $(this).wrap("<div class=\"divTableWithFloatingHeader " + (isInDiv ? 'parent-scroll' : '') + 
        "\" style=\"position:relative\"></div>");

      var originalHeaderRow = $("tr:first", this)
      originalHeaderRow.before(originalHeaderRow.clone());
      var clonedHeaderRow = $("tr:first", this)

      clonedHeaderRow.addClass("tableFloatingHeader");
      clonedHeaderRow.css("position", "absolute");
      clonedHeaderRow.css("top", "0px");
      clonedHeaderRow.css("left", $(this).css("margin-left"));
      clonedHeaderRow.css("display", "none");
      clonedHeaderRow.css('z-index', '9999');
      clonedHeaderRow.css('-moz-box-shadow', '0 0 10px #9f9f9f');
      clonedHeaderRow.css('-webkit-box-shadow', '0 0 10px #9f9f9f');
      clonedHeaderRow.css('box-shadow', '0 0 10px #9f9f9f');
      

      originalHeaderRow.addClass("tableFloatingHeaderOriginal");
  });
  
  UpdateTableHeaders();
  $(window).scroll(UpdateTableHeaders);
  $(window).resize(UpdateTableHeaders);

  // iOS
  $(window).bind('touchmove', function(e) {
    UpdateTableHeaders();
  });
});
