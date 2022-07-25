"use strict";

(function ($) {
  var zoomConnection = {
    init: function init() {
      this.cacheDOM();
      this.initSelect2();
      this.eventHandlers();
    },
    cacheDOM: function cacheDOM() {
      this.formWrapper = $('#vczapi_zoom_connection');
      this.connectZoomInput = $('.vczapi-zoom-connect');
      this.enableZoomCheckbox = $('#_vczapi_enable_zoom_link');
      this.meetingData = '';
      this.zoomMeetingRecording = this.formWrapper.find('#_vczapi_meeting_or_recording');
    },
    eventHandlers: function eventHandlers() {
      this.enableZoomCheckbox.on('change', this.toggleZoomConnection.bind(this));
      this.connectZoomInput.on('change', this.updateMeetingInfo.bind(this));
      this.zoomMeetingRecording.on('change', this.toggleMeetingRecordings.bind(this));
      this.formWrapper.find('#vczapi-wc-get-recordings').on('click', this.findRecordings.bind(this));
      $(document).on('click', '#vczapi-select-all-recordings', function (e) {
        $(document).find('.vczapi-wc-retrieved-recording-item').prop("checked", this.checked);
      });
    },
    findRecordings: function findRecordings(e) {
      e.preventDefault();
      var $el = $(e.target);
      var host_id = this.formWrapper.find('#_vczapi_wc_recording_host').val();
      var year = this.formWrapper.find('#zoom_recording_year').val();
      var month = this.formWrapper.find('#zoom_recording_month').val();

      if (host_id == undefined || host_id == '') {
        alert('Please select host');
        this.formWrapper.find('#_vczapi_recording_meeting_or_webinar').focus();
        return;
      } else if (year == undefined || year == '') {
        alert('Please select year');
        this.formWrapper.find('#zoom_recording_year').focus();
        return;
      } else if (month == undefined || month == '') {
        alert('Please select month');
        this.formWrapper.find('#zoom_recording_month').focus();
        return;
      }

      $.ajax({
        method: 'POST',
        url: ajaxurl,
        data: {
          action: 'vczapi_get_admin_recordings_for_meeting',
          host_id: host_id,
          year: year,
          month: month
        },
        beforeSend: function beforeSend() {
          $('#vczapi-select-recordings').html('Loading ...');
        },
        success: function success(response) {
          if (response.success == undefined) {
            console.log('Something has gone wrong');
          }

          if (response.success) {
            $('#vczapi-select-recordings').html(response.data);
          } else if (response.success == false) {
            $('#vczapi-select-recordings').html(response.data.message);
          }
        }
      });
    },
    toggleMeetingRecordings: function toggleMeetingRecordings(e) {
      var el = $(e.target);

      if (el.val() === 'meeting') {
        this.formWrapper.find('.zoom-wc-meetings').show();
        this.formWrapper.find('.zoom-wc-recording').hide();
      } else {
        this.formWrapper.find('.zoom-wc-recording').show();
        this.formWrapper.find('.zoom-wc-meetings').hide();
      }
    },
    initSelect2: function initSelect2() {
      /*
      @todo weird things are happening with WooCommerce select2 - need to check that out we dont want a dependency problem here.
       */
      this.connectZoomInput.select2({
        minimumInputLength: 3,
        ajax: {
          url: ajaxurl + '?action=vczapi_zoom_woocommerce_link&security=' + vczapiWC.nonce,
          dataType: 'json',
          data: function data(params) {
            return {
              search: params.term,
              product_id: zoomConnection.formWrapper.find('#vczapi-product-id').val()
            };
          },
          processResults: function processResults(response) {
            zoomConnection.meetingData = response.data.meetingData; // Transforms the top-level key of the response object from 'items' to 'results'

            return {
              results: response.data.items
            };
          }
        }
      });
    },
    toggleZoomConnection: function toggleZoomConnection(e) {
      var $el = $(e.currentTarget);

      if ($el.is(':checked')) {
        $('.zoom-connection-enabled').show();
      } else {
        $('.zoom-connection-enabled').hide();
      }
    },
    updateMeetingInfo: function updateMeetingInfo(e) {
      var $el = $(e.currentTarget);
      var selectedMeeting = $el.val(); //console.log(selectedMeeting);

      var meetingHTML = this.meetingData[selectedMeeting];

      if (meetingHTML !== '') {
        $('.vczapi-woocommerce--meeting-details').find('.info').html(meetingHTML);
      }
    }
  };
  var zoomRegistrantsTable = {
    init: function init() {
      this.registrantTable = $('#vczapi-wc-meeting-registrants-dtable');

      if (this.registrantTable !== undefined && this.registrantTable.length > 0) {
        this.registrantTable.dataTable({
          "pageLength": 10
        });
      }
    }
  }; //document ready

  $(function () {
    zoomConnection.init();
    zoomRegistrantsTable.init();
  });
})(jQuery);