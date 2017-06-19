(function($, Drupal, drupalSettings) {
  Drupal.behaviors.custom= {
    attach: function (context, settings) {

      let feedFilterBlock = document.getElementById('block-views-feed-filter-block');
      let terms = feedFilterBlock.querySelectorAll('a');

      terms.forEach(function(term) {
        console.dir(term);
        let tid = term.href.substring(term.href.lastIndexOf('/') + 1);
        term.addEventListener('click touchstart', function (event) {
          console.log('clicked ' + term + ' ' + tid);
          event.preventDefault();
          event.stopPropagation();

          $.ajax({
            type: 'GET',
            url: '/heartbeat/filter-feed/' + tid,
            success: function (response) {

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
          return false;
        });
      });

      let fraction = 0.65;
      let videos = document.getElementsByTagName('video');
      // console.dir(videos);
      for (let i = videos.length - 1; i > -1; i--) {
        let video = videos[i];
        video.addEventListener('loadedmetadata', function() {
          video.loop = video.duration < 5;

          window.addEventListener('scroll', checkScroll, false);
          window.addEventListener('resize', checkScroll, false);
        });

      }

      function checkScroll() {

        console.log('scroll');

        for(var i = 0; i < videos.length; i++) {

          var video = videos[i];

          var x = video.offsetLeft, y = video.offsetTop, w = video.offsetWidth, h = video.offsetHeight, r = x + w, //right
            b = y + h, //bottom
            visibleX, visibleY, visible;

          visibleX = Math.max(0, Math.min(w, window.pageXOffset + window.innerWidth - x, r - window.pageXOffset));
          visibleY = Math.max(0, Math.min(h, window.pageYOffset + window.innerHeight - y, b - window.pageYOffset));

          visible = visibleX * visibleY / (w * h);

          if (visible > fraction) {
            video.play();
          } else {
            video.pause();
          }

        }

      }
    }
  };


})(jQuery, Drupal, drupalSettings);

