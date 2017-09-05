$(document).ready(function() {
  $('.carousel').carousel();
  $('h2:first').addClass('animated zoomIn');
  $('#profile-container .row:first').addClass('animated zoomIn');

  $(window).on('scroll', function() {
    var docViewTop = $(this).scrollTop();
    var docViewBottom = docViewTop + $(this).height();

    $('h2').each(function() {
      if (scrolled($(this), docViewBottom)) {
        $(this).addClass('animated zoomIn');
      }
    });

    $('#profile-container .contact-box').each(function() {
      if (scrolled($(this), docViewBottom)) {
        $(this).addClass('animated zoomIn');
      }
    });
  });

  function scrolled(element, docViewBottom) {
      var elemTop = $(element).offset().top;
      var elemBottom = elemTop + $(element).height();
      return elemTop <= docViewBottom;
  }
});
