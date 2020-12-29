(function ($) {
  "use strict";

  var media_uploader = null;

  function open_media_uploader_image(event, imageSelectorButton) {

      var $imageSelectorScope = imageSelectorButton.parent();

      media_uploader = wp.media({
          frame: "post",
          state: "insert",
          multiple: false
      });

      media_uploader.on("insert", function () {
          var json = media_uploader.state().get("selection").first().toJSON();
          var image_id = json.id;
          var image_url = json.url;
          var image_html = '<img src="' + image_url + '" width="90" height="90">';

          var current_selector = '';
          switch (event.target.id) {
              case 'ewp_publisher_image_select':
                  current_selector = '.taxonomy-ewp-publisher #ewp_publisher_' + 'image';
                  break;
              case 'ewp_publisher_banner_select':
                  current_selector = '.taxonomy-ewp-publisher #ewp_publisher_' + 'banner';
                  break;
          }

          $(current_selector).val(image_id);
          $(current_selector + '_result').remove();

          if ($('.ewp_publisher_image_selected', $imageSelectorScope).length) {
              $('.ewp_publisher_image_selected span', $imageSelectorScope).html(image_html);
          } else {
              $imageSelectorScope.append('<div class="ewp_publisher_image_selected"><span>' + image_html + '</span></div>');
          }
          add_delete_link($imageSelectorScope);

      });

      media_uploader.open();
  }


  $('.taxonomy-ewp-publisher #ewp_publisher_image_select, .taxonomy-ewp-publisher #ewp_publisher_banner_select').on('click', function (event) {
      open_media_uploader_image(event, $(this));
  });

  //bind remove image event for edit page
  $('.taxonomy-ewp-publisher #ewp_publisher_image_select, .taxonomy-ewp-publisher #ewp_publisher_banner_select').each(function () {
      add_delete_link($(this).parent());
  });

  //clear custom fields when publisher is added
  if ($('body').hasClass('edit-tags-php') && $('body').hasClass('taxonomy-ewp-publisher')) {
      $(document).ajaxSuccess(function (event, xhr, settings) {
          //Check ajax action of request that succeeded
          if (typeof settings != "undefined" && settings.data && ~settings.data.indexOf("action=add-tag") && ~settings.data.indexOf("taxonomy=ewp-publisher")) {
              $('#ewp_publisher_image').val('');
              $('#ewp_publisher_banner').val('');
              $('.ewp_publisher_image_selected').remove();
          }
      });
  }

  function add_delete_link($imageSelectorScope) {

      $('.ewp_publisher_image_selected span', $imageSelectorScope).append('<a href="#" class="ewp_publisher_image_selected_remove">X</a>');

      $('.ewp_publisher_image_selected_remove', $imageSelectorScope).on('click', function (event) {

          event.preventDefault();
          $(this).closest('.ewp_publisher_image_selected').remove();

          //remove the img
          $('#ewp_publisher_image', $imageSelectorScope).val('');
          $('#ewp_publisher_banner', $imageSelectorScope).val('');

      });

  }

  /* ····························· Edit publishers page ····························· */
  $('.taxonomy-ewp-publisher table .column-featured > span').not('ewp-blocked').on('click', function (e) {
      e.preventDefault();
      var $currentStar = $(this);
      $currentStar.addClass('ewp-blocked');
      if ($currentStar.hasClass('dashicons-star-filled')) {
          $currentStar.removeClass('dashicons-star-filled');
          $currentStar.addClass('dashicons-star-empty');
      } else {
          $currentStar.removeClass('dashicons-star-empty');
          $currentStar.addClass('dashicons-star-filled');
      }
      var data = { 'action': 'ewp_admin_set_featured_publisher', 'publisher': $currentStar.data('publisher-id') };
      $.post(ewp_ajax_object_admin.ajax_url, data, function (response) {
          $currentStar.removeClass('ewp-blocked');
          if (response.success) {
              var $featuredCount = $('.taxonomy-ewp-publisher .ewp-featured-count > span');
              if (response.data.direction == 'up') {
                  $featuredCount.html(parseInt($featuredCount.text()) + 1);
              } else {
                  $featuredCount.html(parseInt($featuredCount.text()) - 1);
              }
          } else {
              alert(response.data.error_msg);
          }
      });
  });

  $('.taxonomy-ewp-publisher #ewp-first-featured-publishers').on('change', function (e) {
      e.preventDefault();
      $('#screen-options-apply').replaceWith('<img src="' + ewp_ajax_object_admin.site_url + '/wp-admin/images/loading.gif">');
      var data = { 'action': 'ewp_admin_save_screen_settings', 'new_val': $(this).is(':checked') };
      $.post(ewp_ajax_object_admin.ajax_url, data, function (response) { location.reload(); });
  });

  $('.ewp-edit-publishers-bottom > span').on('click', function (e) {
      e.preventDefault();
      $('.taxonomy-ewp-publisher #col-left').toggleClass('ewp-force-full-width');
      $('.taxonomy-ewp-publisher #col-right').toggleClass('ewp-force-full-width');
  });
  /* ····························· /Edit publishers page ····························· */

  /* ····························· Settings tab ····························· */

  if ($('.ewp-admin-selectwoo').length) {
      $('.ewp-admin-selectwoo').selectWoo();
  }

  // migrate publishers
  $('#wc_ewp_admin_tab_tools_migrate').on('change', function () {

      if ($(this).val() != '-') {

          if (confirm(ewp_ajax_object_admin.translations.migrate_notice)) {

              $('html').append('<div class="ewp-modal"><div class="ewp-modal-inner"></div></div>');
              $('.ewp-modal-inner').html('<p>' + ewp_ajax_object_admin.translations.migrating + '</p>');

              var data = {
                  'action': 'ewp_admin_migrate_publishers',
                  'from': $(this).val()
              };
              $.post(ewp_ajax_object_admin.ajax_url, data, function (response) {

                  setTimeout(function () {
                      location.href = ewp_ajax_object_admin.publishers_url;
                  }, 1000);

              });

          } else {

          }

      }

      $(this).val('-');//reset to default value

  });

  // dummy data
  $('#wc_ewp_admin_tab_tools_dummy_data').on('change', function () {

      if ($(this).val() != '-') {

          if (confirm(ewp_ajax_object_admin.translations.dummy_data_notice)) {

              $('html').append('<div class="ewp-modal"><div class="ewp-modal-inner"></div></div>');
              $('.ewp-modal-inner').html('<p>' + ewp_ajax_object_admin.translations.dummy_data + '</p>');

              var data = {
                  'action': 'ewp_admin_dummy_data',
                  'from': $(this).val()
              };
              $.post(ewp_ajax_object_admin.ajax_url, data, function (response) {

                  setTimeout(function () {
                      location.href = ewp_ajax_object_admin.publishers_url;
                  }, 1000);

              });

          } else {

          }

      }

      $(this).val('-');//reset to default value

  });

  var $systemStatusBtn = $('#wc_ewp_admin_tab_tools_system_status').siblings('p');
  $systemStatusBtn.addClass('button wc_ewp_admin_tab_status_btn');
  $('.wc_ewp_admin_tab_status_btn').on('click', function (e) {
      e.preventDefault();
      if (!$('#wc_ewp_admin_status_result').length) {
          var $systemStatusTextarea = $('#wc_ewp_admin_tab_tools_system_status');
          $('<pre id="wc_ewp_admin_status_result"></pre>').insertAfter($systemStatusTextarea);
          $('#wc_ewp_admin_status_result').click(function (e) {
              e.preventDefault();
              var refNode = $(this)[0];
              if ($.browser.msie) {
                  var range = document.body.createTextRange();
                  range.moveToElementText(refNode);
                  range.select();
              } else if ($.browser.mozilla || $.browser.opera) {
                  var selection = window.getSelection();
                  var range = document.createRange();
                  range.selectNodeContents(refNode);
                  selection.removeAllRanges();
                  selection.addRange(range);
              } else if ($.browser.safari) {
                  var selection = window.getSelection();
                  selection.setBaseAndExtent(refNode, 0, refNode, 1);
              }
          });
      }
      $('#wc_ewp_admin_status_result').html('<img src="' + ewp_ajax_object_admin.site_url + '/wp-admin/images/spinner.gif' + '" alt="Loading" height="20" width="20">');
      $('#wc_ewp_admin_status_result').show();
      var data = {
          'action': 'ewp_system_status'
      };
      $.post(ajaxurl, data, function (response) {
          $('#wc_ewp_admin_status_result').html(response);
          $('#wc_ewp_admin_status_result').trigger('click');
      });

  });

  /* ····························· /Settings tab ····························· */

  /* ····························· Admin notices ····························· */
  $(document).on('click', '.ewp-notice-dismissible .notice-dismiss', function (e) {

      e.preventDefault();

      var noticeName = $(this).closest('.ewp-notice-dismissible').data('notice');

      var data = {
          'action': 'dismiss_ewp_notice',
          'notice_name': noticeName
      };
      $.post(ajaxurl, data, function (response) {
          //callback
      });

  });
  /* ····························· /Admin notices ····························· */

  /* ····························· Widgets ····························· */
  ewpBindEventsToWigets();
  //Fires when a widget is added to a sidebar
  $(document).bind('widget-added', function (e, widget) {
      ewpBindEventsToWigets(widget);
  });
  //Fires on widget save
  $(document).on('widget-updated', function (e, widget) {
      ewpBindEventsToWigets(widget);
  });
  function ewpBindEventsToWigets(widget) {

      var $currentWidget = $(".ewp-select-display-as");

      if (widget != undefined) {
          $currentWidget = $(".ewp-select-display-as", widget);
      }
      $currentWidget.on("change", function () {
          if ($(this).val() == "publisher_logo") {
              $(this).parent().siblings(".ewp-display-as-logo").addClass("show");
          } else {
              $(this).parent().siblings(".ewp-display-as-logo").removeClass("show");
          }
      });
  }
  /* ····························· /Widgets ····························· */

  /* ····························· Publishers exporter ····························· */
  $('button.ewp-publishers-export').on('click', function (e) {
      e.preventDefault();

      var $clickedBtn = $(this);
      $clickedBtn.addClass('ewp-loading-overlay');
      $clickedBtn.prop("disabled", true);

      var data = { 'action': 'ewp_publishers_export' };
      $.post(ewp_ajax_object_admin.ajax_url, data, function (response) {

          if (response.success) {
              $clickedBtn.removeClass('ewp-loading-overlay');
              $clickedBtn.prop("disabled", false);

              //download export file
              $('#ewp-download-export-file').remove();
              var link = document.createElement("a");
              link.download = 'publishers.json';
              link.id = 'ewp-download-export-file';
              link.href = response.data.export_file_url;
              $('body').append(link);
              link.click();
          }

      });

  })

  $('button.ewp-publishers-import').on('click', function (e) {
      e.preventDefault();
      $('input.ewp-publishers-import-file').trigger('click');
  });

  $('input.ewp-publishers-import-file').on('change', function (e) {
      e.preventDefault();

      var $clickedBtn = $('button.ewp-publishers-import');
      $clickedBtn.addClass('ewp-loading-overlay');
      $clickedBtn.prop("disabled", true);

      var file = $(this)[0].files[0];

      var reqData = new FormData();
      reqData.append('action', 'ewp_publishers_import');
      reqData.append('file', file);

      $.ajax({
          url: ewp_ajax_object_admin.ajax_url,
          type: 'post',
          cache: false,
          dataType: 'json',
          contentType: false,
          processData: false,
          data: reqData,
          success: function (resp) {
              if (resp.success) {
                  $clickedBtn.removeClass('ewp-loading-overlay');
                  location.reload();
              } else {
                  alert('Importer error');
              }
          }
      });

  })
  
  /* ····························· /Publishers exporter ····························· */

})(jQuery)
