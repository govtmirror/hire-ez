var count_words;

$(document).on("change", "input.project-application-check", function() {
  return $(this).closest('.project').find('.why-great').collapse('toggle');
});

count_words = function(e) {
  var $input, count, max, remaining, value;
  $input = $(this);
  value = $input.val();
  count = $.trim(value).split(/\s+/).length;
  max = 150;
  remaining = !value ? 150 : max - count;
  return $input.closest('.control-group').find('.words-remaining').text(remaining);
};

$(document).on("input", ".why-great-fellow textarea, .why-great textarea", count_words);

$(document).on("keydown", "#locationInput", function(e) {
  if (e.keyCode === 13) {
    return e.preventDefault();
  }
});

$(document).on("ready page:load", function() {
  var editor;
  editor = $('.wysihtml5').wysihtml5({
    image: false
  });
  $('.control-group.why-great.collapse.in').removeClass('in');
  $('.words-remaining-wrapper.hidden').removeClass('hidden');
  $('.words-max-wrapper').addClass('hidden');
  return $("#new-vendor-form .project textarea").each(function() {
    if ($(this).val()) {
      return $(this).closest(".project").find("input[type=checkbox]").attr('checked', true).trigger('change');
    }
  });
});

Rfpez.initialize_google_autocomplete = function() {
  var autocomplete;
  autocomplete = new google.maps.places.Autocomplete(document.getElementById('locationInput'), {});
  return google.maps.event.addListener(autocomplete, 'place_changed', function() {
    var place;
    place = autocomplete.getPlace();
    if (place.geometry) {
      $("#latitudeInput").val(place.geometry.location.lat());
      return $("#longitudeInput").val(place.geometry.location.lng());
    } else {
      $("#latitudeInput").val('');
      return $("#longitudeInput").val('');
    }
  });
};
