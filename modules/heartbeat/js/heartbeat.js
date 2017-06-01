/**
 * Created by logicp on 5/28/17.
 */
(function($, Drupal, drupalSettings) {
    Drupal.behaviors.heartbeat = {
        attach: function (context, settings) {


            Drupal.AjaxCommands.prototype.selectFeed = function(ajax, response, status) {
                console.log(response.feed);
                console.dir(drupalSettings);
                console.dir(context);
                console.dir(settings);

              $.ajax({
                type:'POST',
                url:'/heartbeat/render_feed/' + response.feed,
                success: function(response) {
                  // feedElement = document.getElementById('block-heartbeatblock');
                  feedElement = document.querySelector('.heartbeat-stream');
                  feedElement.innerHTML = response;
                  console.dir(feedElement);
                  console.dir(response);
                }
              });
              // #block-heartbeatblock
            }

            Drupal.AjaxCommands.prototype.updateFeed = function(ajax, response, status) {
              feed = response.feed;
              timestamp = response.timestamp;

            }


        }
    }
})(jQuery, Drupal, drupalSettings);
