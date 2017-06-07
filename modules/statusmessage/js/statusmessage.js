/**
 * Created by logicp on 6/05/17.
 */
(function($, Drupal, drupalSettings) {
  Drupal.behaviors.status= {
    attach: function (context, settings) {

      Drupal.AjaxCommands.prototype.generatePreview = function(ajax, response, status) {

        if (validateUrl(response.url)) {
          console.dir(response);
        }
        var cleanUrl = response.url.replace(/^http(s?):\/\//i, "");
        console.log(cleanUrl);
        $.ajax({
          type:'POST',
          url:'/statusmessage/generate-preview/' + cleanUrl,
          success: function(response) {
            var statusBlock = document.getElementById('block-statusblock');
            var previewIframe = document.createElement('iframe');
            statusBlock.appendChild(previewIframe);
            previewIframe.contentWindow.document.open();
            previewIframe.contentWindow.document.write(response.data);
            previewIframe.contentWindow.document.close();
          }
        });
      };

      function validateUrl(input) {
        return input.match(new RegExp("([a-zA-Z0-9]+://)?([a-zA-Z0-9_]+:[a-zA-Z0-9_]+@)?([a-zA-Z0-9.-]+\\.[A-Za-z]{2,4})(:[0-9]+)?(/.*)?"));
      }

    }
  };

})(jQuery, Drupal, drupalSettings);

