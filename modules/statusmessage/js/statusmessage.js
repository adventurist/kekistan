/**
 * Created by logicp on 6/05/17.
 */
(function($, Drupal, drupalSettings) {
  Drupal.behaviors.status= {
    attach: function (context, settings) {

      if (Drupal.AjaxCommands){
        Drupal.AjaxCommands.prototype.viewsScrollTop = null;
      }

      Drupal.AjaxCommands.prototype.generatePreview = function(ajax, response, status) {

        if (validateUrl(response.url)) {

          var cleanUrl = response.url.replace(/^http(s?):\/\//i, "");
          // console.log(cleanUrl);
          $.ajax({
            type: 'POST',
            url: '/statusmessage/generate-preview/build' ,
            data: {'data': cleanUrl},
            success: function (response) {

              // console.log(response.data);
              if (response.data != null) {
                var parser = new DOMParser();
                // var doc = parser.parseFromString(response.data, "text/html");
                let markup = document.createElement('div');
                markup.innerHTML = response.data;

                let statusTextBox = document.getElementById('edit-message');
                let oldPreviewIframe = document.querySelector('.statusmessage-preview-iframe');

                if (oldPreviewIframe !== null) {
                  oldPreviewIframe.parentNode.removeChild(oldPreviewIframe);

                }
                previewIframe = document.createElement('iframe');
                previewIframe.classList.add('statusmessage-preview-iframe');
                statusTextBox.parentNode.appendChild(previewIframe);
                previewIframe.contentWindow.document.open();
                previewIframe.contentWindow.document.appendChild(markup);
                previewIframe.contentWindow.document.close();
              }
            }
          });
        }
      };

      function validateUrl(input) {
        if (input !== null) {
          return input.match(new RegExp("([a-zA-Z0-9]+://)?([a-zA-Z0-9_]+:[a-zA-Z0-9_]+@)?([a-zA-Z0-9.-]+\\.[A-Za-z]{2,4})(:[0-9]+)?(/.*)?"));
        }
      }

      Drupal.AjaxCommands.prototype.clearPreview = function(ajax, response, status) {
        if (response.clear == true) {
          let oldPreviewIframe = document.querySelector('.statusmessage-preview-iframe');
          if (oldPreviewIframe !== null) {
            oldPreviewIframe.parentNode.removeChild(oldPreviewIframe);
          }
        }
      }


      let statusPostButton = document.getElementById('edit-post');

      statusPostButton.addEventListener('click', function() {
        let textBox = document.getElementById('edit-message');
        textBox.value = "";
      })
    }
  };

})(jQuery, Drupal, drupalSettings);

