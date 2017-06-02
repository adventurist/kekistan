/**
 * Created by logicp on 5/28/17.
 */
(function($, Drupal, drupalSettings) {
    Drupal.behaviors.heartbeat = {
        attach: function (context, settings) {
          console.dir(drupalSettings);

          feedElement = document.querySelector('.heartbeat-stream');
          console.dir(feedElement);

          if (drupalSettings.feedUpdate == true) {
            console.log('stop here man');

            updateFeed();
          }

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
                  console.dir(feedElement);

                  if (feedElement != null) {
                    feedElement.innerHTML = response;
                  } else {

                    feedBlock = document.getElementById('block-heartbeatblock');
                    insertNode = document.createElement('div');
                    insertNode.innerHTML = response;
                    feedBlock.appendChild(insertNode);

                  }
                  console.dir(feedElement);
                  console.dir(response);
                }
              });
              // #block-heartbeatblock
            };

            Drupal.AjaxCommands.prototype.updateFeed = function(ajax, response, status) {
              console.dir(response.timestamp);
              if ($response.update) {
                $.ajax({
                  type: 'POST',
                  url:'/heartbeat/update_feed/' + response.timestamp,
                  success: function(response) {
                    // feedElement = document.getElementById('block-heartbeatblock');
                    // feedElement = document.querySelector('.heartbeat-stream');
                    // feedElement.innerHTML = response;
                    // console.dir(feedElement);
                    console.dir(response);
                  }
                });
              }
            }
        }
    }


  function updateFeed() {

    $.ajax({
      type: 'POST',
      url: '/heartbeat/form/heartbeat_update_feed',
      success: function (response) {
        console.dir(response);
        console.log('We are succeed!');
      }
    })

  }

})(jQuery, Drupal, drupalSettings);

