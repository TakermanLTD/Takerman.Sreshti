"use strict";

jQuery(function ($) {
  //Display Table for recordings
  var vczapi_wc_recordings_tbl = {
    init: function init() {
      this.cacheDOM();
      this.listeners();
    },
    cacheDOM: function cacheDOM() {
      this.$recordingsTable = $('.vczapi-woocommerce-recordings-datatable');
      this.$clearRecordingsCache = $('.vczapi-woocommerce-clear-recordings-cache');
    },
    listeners: function listeners() {
      $(document).on('click', '.vczapi-wc-view-recording', this.viewRecordingModal.bind(this));
      $(document).on('click', '.vczapi-modal-close', this.recordingsCloseModal.bind(this));
      this.$clearRecordingsCache.on('click', this.clearCache.bind(this));
      this.$recordingsTable.dataTable({
        ajax: {
          url: vczapi_wc_addon.ajaxurl + '?action=get_author_recordings'
        },
        responsive: true,
        columns: [{
          data: 'title'
        }, {
          data: 'start_date'
        }, {
          data: 'meeting_id'
        }, {
          data: 'total_size'
        }, {
          data: 'view_recording'
        }],
        order: [[2, "desc"]]
      });
    },
    clearCache: function clearCache(e) {
      e.preventDefault();
      var postData = {
        action: 'clear_purchased_recording_cache'
      };
      $('.vczapi-modal').html('<p class="vczapi-modal-loader">' + vczapi_wc_addon.loading + '</p>').show();
      $.post(vczapi_wc_addon.ajaxurl, postData).done(function (response) {
        $('.vczapi-modal-content').remove();
        $('.vczapi-modal').hide();
        location.reload();
      });
    },
    viewRecordingModal: function viewRecordingModal(e) {
      e.preventDefault();
      var recording_id = $(e.currentTarget).data('recording-id');
      var downloadable = $(e.currentTarget).data('downloadable');
      var postData = {
        recording_id: recording_id,
        action: 'get_recording',
        downlable: downloadable
      };
      $('.vczapi-modal').html('<p class="vczapi-modal-loader">' + vczapi_wc_addon.loading + '</p>').show();
      $.get(vczapi_wc_addon.ajaxurl, postData).done(function (response) {
        $('.vczapi-modal').html(response.data).show();
      });
    },
    recordingsCloseModal: function recordingsCloseModal(e) {
      e.preventDefault();
      $('.vczapi-modal-content').remove();
      $('.vczapi-modal').hide();
    }
  };
  vczapi_wc_recordings_tbl.init();
});