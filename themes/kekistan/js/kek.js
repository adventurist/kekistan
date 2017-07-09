(function($, Drupal, drupalSettings) {
  drupalSettings.filterMode = true;
  Drupal.behaviors.custom= {
    attach: function (context, settings) {

      let feedFilterBlock = document.getElementById('block-views-feed-filter-block');
      let terms = feedFilterBlock.querySelectorAll('a');
      let events = ['click', 'touchend'];

      terms.forEach(function (term) {
        let tid = term.href.substring(term.href.lastIndexOf('/') + 1);

        events.forEach(function(eventname) {
          term.addEventListener(eventname, function (event) {
            drupalSettings.filterMode = true;
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
        })
      });

      let fraction = 0.65;
      let videos = document.getElementsByTagName('video');
      // console.dir(videos);
      for (let i = videos.length - 1; i > -1; i--) {
        let video = videos[i];
        video.addEventListener('loadedmetadata', function () {
          video.loop = video.duration < 5;
          video.volume = 0.2;

          window.addEventListener('scroll', checkScroll, false);
          window.addEventListener('resize', checkScroll, false);
        });

      }

      function checkScroll() {

        console.log('scroll');

        for (var i = 0; i < videos.length; i++) {

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
      displayCounts();
      flagTooltips();
      textareaAutoHeight();
      listenReplyButtons();
    }
  };

  function flagTooltips() {
    var likeFlags = document.querySelectorAll('.flag-heartbeat_like');
    var jihadFlags = document.querySelectorAll('.flag-jihad_flag');

    for (let i = 0; i < likeFlags.length; i++) {
      let tip = document.createElement('div');
      tip.innerText = "Praise";
      tip.style.display = "none";
      tip.className = "praisetip";
      likeFlags[i].parentNode.appendChild(tip);

      likeFlags[i].addEventListener('mouseover', function() {
        let tip = likeFlags[i].parentNode.querySelector('.praisetip');
        tip.style.display = "block";
      });
      likeFlags[i].addEventListener('mouseout', function() {
        let tip = likeFlags[i].parentNode.querySelector('.praisetip');
        tip.style.display = "none";
      });
    }

    for (let i = 0; i < jihadFlags.length; i++) {
      let tip = document.createElement('div');
      tip.innerText = "Jihad!";
      tip.style.display = "none";
      tip.className = "jihadtip";
      jihadFlags[i].parentNode.appendChild(tip);

      jihadFlags[i].addEventListener('mouseover', function() {
        console.log('mouse over');
        let tip = jihadFlags[i].parentNode.querySelector('.jihadtip');
        tip.style.display = "block";
      });
      jihadFlags[i].addEventListener('mouseout', function() {
        console.log('mouse over');
        let tip = jihadFlags[i].parentNode.querySelector('.jihadtip');
        tip.style.display = "none";
      });
    }
  }

  function displayCounts() {
    var likefelFlags = document.querySelectorAll('.flag-heartbeat_like');
    var jihadFlags = document.querySelectorAll('.flag-jihad_flag');
    for (let i = 0; i < likefelFlags.length; i++) {

      // console.dir(likefelFlags[i]);
      for (let g = 0; g < likefelFlags[i].childNodes.length; g++) {
        if (likefelFlags[i].childNodes[g].tagName === 'A') {
          let anchor = likefelFlags[i].childNodes[g];
          let countSpan = document.createElement('span');
          // if (anchor.nextSibling !== null && anchor.nextSibling.tagName !== 'span') {

            countSpan.innerText = anchor.innerText.substring(anchor.innerText.lastIndexOf("(") + 1, anchor.innerText.lastIndexOf(")"));
            // console.log(countSpan.innerText);
            anchor.after(countSpan);
          // }
        }
      }
    }
    for (let i = 0; i < jihadFlags.length; i++) {

      console.dir(jihadFlags[i]);
      for (let g = 0; g < jihadFlags[i].childNodes.length; g++) {
        if (jihadFlags[i].childNodes[g].tagName === 'A') {
          let anchor = jihadFlags[i].childNodes[g];
          let countSpan = document.createElement('span');


          // if (anchor.nextSibling !== null && anchor.nextSibling.tagName !== 'span') {


            countSpan.innerText = anchor.innerText.substring(anchor.innerText.lastIndexOf("(") + 1, anchor.innerText.lastIndexOf(")"));
            console.log(countSpan.innerText);
            anchor.after(countSpan);
          // }
        }
      }
    }
  }

  function textareaAutoHeight() {

    let textAreas = document.querySelectorAll('.heartbeat-comment-form textarea,.heartbeat-sub-comment-form .form-textarea');

    for (let m = textAreas.length - 1; m > 0; m--) {
      let textArea = textAreas[m];
      textArea.addEventListener('keydown', function(e) {
        if (e.keyCode == 13) {
          console.dir(e);
          console.dir(textArea);
          textArea.style.height = textArea.scrollHeight + "px";
        }
      });
    }
  }

  function listenReplyButtons() {
    let replyButtons = document.querySelectorAll('.heartbeat-comment-form .form-submit, .heartbeat-sub-comment-form .form-submit');

    for (let m = 0; m < replyButtons.length; m++) {
      replyButtons[m].addEventListener('click', function() {
        let replyText = replyButtons[m].parentNode.querySelector('textarea');
        replyText.value = '';
        replyText.innerText = '';
      })
    }
  }

})(jQuery, Drupal, drupalSettings);

