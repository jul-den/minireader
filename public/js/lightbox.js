$(document).ready(function() {
  // Open lightbox on click
  $('.profile-thumb').on('click', function() {
    var src = $(this).attr('src');
    var alt = $(this).attr('alt');
    $('#overlay-img').attr('src', src).attr('alt', alt);
    $('#image-overlay').fadeIn(200);
  });
  // Open lightbox
  $('#image-overlay').on('click', function() {
    $(this).fadeOut(200);
    $('#overlay-img').attr('src', '');
  });
});
