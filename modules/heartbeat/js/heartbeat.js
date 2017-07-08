/**
 * Created by logicp on 5/28/17.
 */
(function($, Drupal, drupalSettings) {
    Drupal.behaviors.heartbeat = {
        attach: function (context, settings) {

          if (drupalSettings.friendData != null) {
            var divs = document.querySelectorAll('.flag-friendship a.use-ajax');

            for (let i = 0; i < divs.length; i++) {
              let anchor = divs[i];
              var userId = anchor.href.substring(anchor.href.indexOf('friendship') + 11, anchor.href.indexOf('?destination'));
              JSON.parse(drupalSettings.friendData).forEach(function (friendship) {
                if (friendship.uid_target === userId && friendship.uid == drupalSettings.user.uid && friendship.status == 0) {
                  anchor.innerHTML = 'Friendship Pending';
                }
              });
            }
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
              if (response.update) {
                $.ajax({
                  type: 'POST',
                  url:'/heartbeat/update_feed/' + response.timestamp,
                  success: function(response) {

                  }
                });
              }
            };

            listenImages();
            listenCommentPost();

          Drupal.AjaxCommands.prototype.myfavouritemethodintheworld = function(ajax, response, status) {
            console.dir(response);
            if (response.cid) {
              console.log('this shit is getting called again');
              let parentComment = document.getElementById('heartbeat-comment-' + response.cid);
              let text = parentComment.querySelector('.form-textarea');

              text.addEventListener('keydown', function (e) {
                console.dir(e);
                if (e.keyCode === 13) {
                  let submitBtn = parentComment.querySelector('.form-submit');
                  submitBtn.click();
                }
              });
            }
          }
        }
    };


  function updateFeed() {

    $.ajax({
      type: 'POST',
      url: '/heartbeat/form/heartbeat_update_feed',
      success: function (response) {
      }
    })
  }

  function listenImages() {
    let cboxOptions = {
      width: '95%',
      height: '95%',
      maxWidth: '960px',
      maxHeight: '960px',
    };

    $('.heartbeat-content').find('img').each(function() {
      let parentClass = $(this).parent().prop('className');
      let phid = parentClass.substring(parentClass.indexOf('hid') + 4);
      $(this).colorbox({rel: phid, href: $(this).attr('src'), cboxOptions});
    });
  }

  function listenCommentPost() {
    //TODO is drupal data selector enough? I doubt it.
    let comments = document.querySelectorAll('[data-drupal-selector]');

    for (let i = 0; i < comments.length; i++) {
      let comment = comments[i];
      // console.dir(comment);
      comment.addEventListener('click', function() {
        getParent(comment);
      })
    }
  }

  function getParent(node) {
    console.dir(node);
    if (node.classList.contains('heartbeat-comment')) {
      let id = node.id.substr(node.id.indexOf('-') + 1);
      $.ajax({
        type: 'POST',
        url:'/heartbeat/commentupdate/' + id,
        success: function(response) {
        }
      });
    } else {
      getParent(node.parentNode);
    }
  }

  function getScrollXY() {
    var scrOfX = 0, scrOfY = 0;
    if( typeof( window.pageYOffset ) == 'number' ) {
      //Netscape compliant
      scrOfY = window.pageYOffset;
      scrOfX = window.pageXOffset;
    } else if( document.body && ( document.body.scrollLeft || document.body.scrollTop ) ) {
      //DOM compliant
      scrOfY = document.body.scrollTop;
      scrOfX = document.body.scrollLeft;
    } else if( document.documentElement && ( document.documentElement.scrollLeft || document.documentElement.scrollTop ) ) {
      //IE6 standards compliant mode
      scrOfY = document.documentElement.scrollTop;
      scrOfX = document.documentElement.scrollLeft;
    }
    return [ scrOfX, scrOfY ];
  }

//taken from http://james.padolsey.com/javascript/get-document-height-cross-browser/
  function getDocHeight() {
    var D = document;
    return Math.max(
      D.body.scrollHeight, D.documentElement.scrollHeight,
      D.body.offsetHeight, D.documentElement.offsetHeight,
      D.body.clientHeight, D.documentElement.clientHeight
    );
  }

  document.addEventListener("scroll", function (event) {

    if (getDocHeight() == getScrollXY()[1] + window.innerHeight) {

      let streams = document.querySelectorAll('.heartbeat-stream');
      let stream = streams.length > 1 ? streams[streams.length - 1] : streams[0];

      if (stream !== null) {
        console.dir(stream);
        let lastHeartbeat = stream.lastElementChild;

        if (lastHeartbeat !== null) {

          let hid = lastHeartbeat.id.substring(lastHeartbeat.id.indexOf('-') + 1);
          $.ajax({
            type: 'POST',
            url: '/heartbeat/update_feed/' + hid,
            success: function (response) {

              feedBlock = document.getElementById('block-heartbeatblock');
              insertNode = document.createElement('div');
              insertNode.innerHTML = response;
              feedBlock.appendChild(insertNode);
            }
          });
        }
      }
    }
  });

  jQuery(document).bind('cbox_open', function(){
    jQuery("#colorbox").swipe( {
      //Generic swipe handler for all directions
      swipeLeft:function(event, direction, distance, duration, fingerCount) {
        jQuery.colorbox.prev();
      },
      swipeRight:function(event, direction, distance, duration, fingerCount) {
        jQuery.colorbox.next();
      },
      //Default is 75px, set to 0 for demo so any distance triggers swipe
      threshold:0
    });
  });

})(jQuery, Drupal, drupalSettings);

