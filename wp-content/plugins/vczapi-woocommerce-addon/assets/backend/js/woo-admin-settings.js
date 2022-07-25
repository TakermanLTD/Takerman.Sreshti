"use strict";

(function ($) {
  $(function () {
    $('#vczapi_disable_meeting_reminder_email').on('change', function (e) {
      e.preventDefault();

      if ($(this).is(':checked')) {
        $('#meeting-reminder-time-section').hide();
      } else {
        $('#meeting-reminder-time-section').show();
      }
    });
  });
})(jQuery);