/**
 * Created by logicp on 5/28/17.
 */
(function($, Drupal, drupalSettings) {
    Drupal.behaviors.heartbeat = {
        attach: function (context, settings) {

          if (drupalSettings.friendData != null) {
            var divs = document.querySelectorAll('.flag-friendship a.use-ajax');
            divs.forEach(function (anchor) {
              var userId = anchor.href.substring(anchor.href.indexOf('user') + 5, anchor.href.indexOf('&token'));
              JSON.parse(drupalSettings.friendData).forEach(function (friendship) {
                if (friendship.uid_target === userId && friendship.uid == drupalSettings.user.uid && friendship.status === 0) {
                  anchor.innerHTML = 'Friendship Pending';
                }
              });
            });
          }

          feedElement = document.querySelector('.heartbeat-stream');

          if (drupalSettings.feedUpdate == true) {
            updateFeed();
          }

            Drupal.AjaxCommands.prototype.selectFeed = function(ajax, response, status) {

              $.ajax({
                type:'POST',
                url:'/heartbeat/render_feed/' + response.feed,
                success: function(response) {

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

                }
              });
            };

            Drupal.AjaxCommands.prototype.updateFeed = function(ajax, response, status) {
              console.dir(response.timestamp);
              if ($response.update) {
                $.ajax({
                  type: 'POST',
                  url:'/heartbeat/update_feed/' + response.timestamp,
                  success: function(response) {

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

