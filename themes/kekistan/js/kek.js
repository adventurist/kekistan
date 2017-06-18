(function($, Drupal, drupalSettings) {
  Drupal.behaviors.custom= {
    attach: function (context, settings) {

      let fraction = 0.8;
      let videos = document.getElementsByTagName('video');
      console.dir(videos);
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

