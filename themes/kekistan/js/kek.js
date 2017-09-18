(function($, Drupal, drupalSettings) {

  function listenVideos() {
    let videos = document.getElementsByTagName('video');
    for (var i = videos.length - 1; i >= 0; i--) {
      let video = videos[i];
      video.addEventListener('loadedmetadata', function() {
        video.loop = video.duration < 5;
        video.muted = true;

        video.addEventListener('click', function() {
          if (video.paused) {
            video.muted = false;
            video.play();
          } else {
            video.muted = true;
            video.pause();
          }
        });
      });
    }
  }

  function listenReplyButtons() { //reply grey button to reply
    let replyButtons = document.querySelectorAll('.heartbeat-comment-form .form-submit, .heartbeat-sub-comment-form .form-submit');
    for (let m = 0; m < replyButtons.length; m++) {
      replyButtons[m].addEventListener('click', function(event) {
        let replyText = replyButtons[m].parentNode.querySelector('textarea');
        console.dir(replyText);
        console.dir(event);
        replyText.value = '';
        replyText.innerText = '';
      })
    }
  }

  /**
   * to open a form by clicking reply hyperlink to sub-comment.
   */
  function listenReplyLinks() {
    let replyLinks = document.querySelectorAll('.sub-comment a.button');
    for (let i = 0; i < replyLinks.length; i++) {
      replyLinks[i].addEventListener('click', function(e) {
        if (findSubCommentForm(e.srcElement.parentElement.parentElement)) {
          event.preventDefault();
          event.stopPropagation();
          event.stopImmediatePropagation();
        }
      });
    }
  }

  function findSubCommentForm(e) {
    let search = false;
    for (let p = 0; p < e.children.length; p++) {
      if (e.children[p].classList.contains('heartbeat-sub-comment-form')) {
        search = true;
      } else if (e.children[p].children !== null && e.children[p].children.length > 0) {
        for (let c = 0; c < e.children[p].children.length; c++) {
          if (e.children[p].children[c].classList.contains('heartbeat-sub-comment-form')) {
            search = true;
          }
        }
      }
    }
    return search;
  }

  function hideCommentForms() {
    let forms = document.querySelectorAll('.heartbeat-comment-form .js-form-type-textarea, .heartbeat-comment-form .form-submit');

    for (let f = 0; f < forms.length; f++) {
      forms[f].className += ' comment-form-hidden';
    }
  }

  function flagToolListen() {

    let likeFlags = document.querySelectorAll('.flag-heartbeat_like');
    let jihadFlags = document.querySelectorAll('.flag-jihad_flag');

    for (let i = 0; i < likeFlags.length; i++) {
      likeFlags[i].addEventListener('mouseover', function() {
        likeFlags[i].className += ' selected';
      });
      likeFlags[i].addEventListener('mouseout', function() {
        likeFlags[i].classList.remove('selected');
      });
    }

    for (let i = 0; i < jihadFlags.length; i++) {

      jihadFlags[i].addEventListener('mouseover', function() {
        jihadFlags[i].className += ' selected';

      });
      jihadFlags[i].addEventListener('mouseout', function() {
        jihadFlags[i].classList.remove('selected');
      });
    }
  }

//TODO add username if viewing user profile
  function userPagePrintName() {
    if (window.location.pathname.indexOf('/user/') === 0) {
      if (document.getElementById('kekistan-userprofile-username')) {
        document.getElementById('kekistan-userprofile-username').remove();
      }
      let userImgData = document.getElementById('block-kekistan-content').querySelector('article div a img');
      let userDom = document.createElement('h3');
      console.dir(userImgData);
      if (userImgData !== null) {
        userDom.innerText = userImgData.alt.substring(25);
        userDom.id = 'kekistan-userprofile-username';
        userImgData.parentNode.appendChild(userDom);
      }
    }
  }

  let listenNavLeft = function() {
    drupalSettings.filterPageEnd = false;
    if (drupalSettings.filterPage !== 0) {
      let currentIndexStart = (drupalSettings.filterPage + 1) * 10 - 10;
      let currentIndexEnd = (drupalSettings.filterPage + 1) * 10;

      let replacementTerms = drupalSettings.hashtags.slice(currentIndexStart - 10, currentIndexEnd - 10);

      console.log('Moving range to ' + (currentIndexStart - 10) + ' to ' + (currentIndexEnd - 10));
      let displayedTags = document.querySelectorAll('.kekfilter-tag');
      for (let i = 0; i < 10 ; i++) {
        let replaceWrap = document.createElement('div');
        replaceWrap.className = 'kekfilter-tag';
        if (replacementTerms[i] !== undefined) {
          replaceWrap.innerHTML = '#' + replacementTerms[i].name;
          let replaceTid = document.createElement('span');
          replaceTid.className = 'kekfilter-tid';
          replaceTid.textContent = replacementTerms[i].tid;
          replaceWrap.appendChild(replaceTid);
        }
        displayedTags[i].parentNode.appendChild(replaceWrap);
        displayedTags[i].parentNode.removeChild(displayedTags[i]);
      }
      drupalSettings.filterPage--;
      kekfilterListeners();
    }
  };
  let listenNavRight = function() {
    if (!drupalSettings.filterPageEnd) {
      let currentIndexStart = (drupalSettings.filterPage + 1) * 10 - 10;
      let currentIndexEnd = (drupalSettings.filterPage + 1) * 10;

      let replacementTerms = drupalSettings.hashtags.slice(currentIndexStart + 10, currentIndexEnd + 10);
      // console.dir(replacementTerms);

      let end = (currentIndexEnd + 10) > drupalSettings.hashtags.length;
      let modifier = end ? replacementTerms.length : 10;

      console.log('Moving range to ' + (currentIndexStart + 10) + ' to ' + (currentIndexEnd + 10));
      let displayedTags = document.querySelectorAll('.kekfilter-tag');
      let i = 0;
      for (let f = ((drupalSettings.filterPage + 1 ) * 10) - 10; f < ((drupalSettings.filterPage + 1) * 10) - 10 + modifier; f++) {
        let replaceWrap = document.createElement('div');
        replaceWrap.className = 'kekfilter-tag';
        if (replacementTerms[i] !== undefined) {
          replaceWrap.innerHTML = '#' + replacementTerms[i].name;
          let replaceTid = document.createElement('span');
          replaceTid.className = 'kekfilter-tid';
          replaceTid.textContent = replacementTerms[i].tid;
          replaceWrap.appendChild(replaceTid);
        }
        displayedTags[i].parentNode.appendChild(replaceWrap);
        displayedTags[i].parentNode.removeChild(displayedTags[i]);
        i++;
      }
      drupalSettings.filterPage++;
      drupalSettings.filterPageEnd = end;
      kekfilterListeners();
    }
  };

  function listenNav() {
    let navLeft = document.querySelector('.kek-filter-left');
    let navRight = document.querySelector('.kek-filter-right');
    navLeft.removeEventListener('click', listenNavLeft);
    navRight.removeEventListener('click', listenNavRight);
    navLeft.addEventListener('click', listenNavLeft);
    navRight.addEventListener('click', listenNavRight);
  }

  function kekfilterListeners() {
    let feedFilterBlock = document.getElementById('kekfilter-block');
    console.dir(feedFilterBlock);
    if (feedFilterBlock !== null) {
      let terms = feedFilterBlock.querySelectorAll('.kekfilter-tag');

      //TODO Convert the following two event listeners to a more elegant syntax
      terms.forEach(function (term) {
        console.dir(term);
        let tid = term.querySelector('.kekfilter-tid').textContent;
        //add listeners to all taxonomy (mobile)
        term.addEventListener("touchstart", function (event) {
          if (drupalSettings.user.uid > 0) {
            $('#heartbeat-loader').show(225);
            drupalSettings.filterMode = true;
            event.preventDefault();
            event.stopPropagation();

            $.ajax({
              type: 'GET',
              url: '/heartbeat/filter-feed/' + tid,
              success: function (response) {
                let feedBlock = document.getElementById('block-heartbeatblock');
                let feedElement = document.querySelector('.heartbeat-stream');

                if (feedElement != null) {
                  feedBlock.removeChild(feedElement);
                }

                let insertNode = document.createElement('div');
                insertNode.className = 'heartbeat-stream';
                insertNode.innerHTML = response;
                feedBlock.appendChild(insertNode);
              },
              complete: function () {
                $('#heartbeat-loader').hide(225);
                Drupal.attachBehaviors()
              }
            });
            return false;
          } else {
            loginModal();
          }
        });

        //add listeners to all taxonomy (desktop)
        term.addEventListener("click", function (event) {
          console.log('term clicked');
          if (drupalSettings.user.uid > 0) {
            $('#heartbeat-loader').show(225);

            drupalSettings.filterMode = true;
            event.preventDefault();
            event.stopPropagation();

            $.ajax({
              type: 'GET',
              url: '/heartbeat/filter-feed/' + tid,
              success: function (response) {
                let feedBlock = document.getElementById('block-heartbeatblock');
                console.dir(feedBlock);
                let feedElement = document.querySelector('.heartbeat-stream');

                if (feedElement != null) {
                  if (feedBlock === null) {
                    feedBlock = document.getElementById('block-heartbeatmoreblock');
                    if (feedBlock === null) {
                      feedBlock = document.getElementById('block-heartbeat')
                    }
                  }
                  feedBlock.removeChild(feedElement);
                }

                let insertNode = document.createElement('div');
                insertNode.className = 'heartbeat-stream';
                insertNode.innerHTML = response;
                feedBlock.appendChild(insertNode);
              },
              complete: function () {
                $('#heartbeat-loader').hide(225);
                Drupal.attachBehaviors()
              }
            });
            return false;
          } else {
            loginModal();
          }
        });
      });
    }
  }
  // add listeners to all hashtags in heartbeat stream
  function streamHashtagListeners() {
    let hashtags = document.querySelectorAll('.heartbeat-message .heartbeat-hashtag a');
    for (let h = 0; h < hashtags.length; h++) {
      let hashTagID = hashtags[h].href.substring(hashtags[h].href.lastIndexOf('/') + 1);
      //add listeners to all taxonomy (mobile)
      hashtags[h].addEventListener("touchstart", function (event) {
        console.dir(event.srcElement);

        if (drupalSettings.user.uid > 0) {
          $('#heartbeat-loader').show(225);
          drupalSettings.filterMode = true;
          event.preventDefault();
          event.stopPropagation();

          $.ajax({
            type: 'GET',
            url: '/heartbeat/filter-feed/' + hashTagID,
            success: function (response) {
              let feedBlock = document.getElementById('block-heartbeatblock');
              let feedElement = document.querySelector('.heartbeat-stream');

              if (feedElement != null) {
                feedBlock.removeChild(feedElement);
              }

              let insertNode = document.createElement('div');
              insertNode.className = 'heartbeat-stream';
              insertNode.innerHTML = response;
              feedBlock.appendChild(insertNode);
            },
            complete: function () {
              $('#heartbeat-loader').hide(225);
              Drupal.attachBehaviors()
            }
          });
          return false;
        } else {
          loginModal();
        }
      });

      //add listeners to all taxonomy (desktop)
      hashtags[h].addEventListener("click", function (event) {
        console.dir(event.srcElement);

        if (drupalSettings.user.uid > 0) {
          $('#heartbeat-loader').show(225);

          drupalSettings.filterMode = true;
          event.preventDefault();
          event.stopPropagation();

          $.ajax({
            type: 'GET',
            url: '/heartbeat/filter-feed/' + hashTagID,
            success: function (response) {
              let feedBlock = document.getElementById('block-heartbeatblock');
              let feedElement = document.querySelector('.heartbeat-stream');

              if (feedElement != null && feedBlock.contains(feedElement)) {
                feedBlock.removeChild(feedElement);
              }

              let insertNode = document.createElement('div');
              insertNode.className = 'heartbeat-stream';
              insertNode.innerHTML = response;
              feedBlock.appendChild(insertNode);
            },
            complete: function () {
              $('#heartbeat-loader').hide(225);
              Drupal.attachBehaviors()
            }
          });
          return false;
        } else {
          loginModal();
        }
      });
    }
  }

  function checkScroll() {

    let videos = document.getElementsByTagName('video');
    let fraction = 0.45;

    for (let i = 0; i < videos.length; i++) {

      let video = videos[i];

      let x = video.offsetLeft, y = video.offsetTop, w = video.offsetWidth, h = video.offsetHeight, r = x + w, //right
        b = y + h, //bottom
        visibleX, visibleY, visible;

      visibleX = Math.max(0, Math.min(w, window.pageXOffset + window.innerWidth - x, r - window.pageXOffset));
      visibleY = Math.max(0, Math.min(h, window.pageYOffset + window.innerHeight - y, b - window.pageYOffset));

      visible = visibleX * visibleY / (w * h);
      let state = visible > fraction;
      let paused = video.paused;

      if (video.paused) {
        if (visible > fraction && visible < (fraction * 1.1)) {
          video.play();
          console.log('play dat shit');
        } else {
          video.pause();
        }
      } else {
        if (visible < fraction) {
          video.pause();
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

  function userMenuBehaviour() {
    let userMenu = document.getElementById('block-kekistan-account-menu');
    let menuItems = userMenu.querySelectorAll('.menu-item');

    for (let i = 0; i < menuItems.length; i++) {
      let   menuItem = menuItems[i].querySelector('a');
      ['mouseover', 'focus'].map(function(event) {
        menuItem.addEventListener(event, function() {
          menuItem.classList.add('menu-item-visible');
        })
      });
      ['mouseout', 'focusout'].map(function(event) {
        menuItem.addEventListener(event, function() {
          menuItem.classList.remove('menu-item-visible');
        })
      })
    }
  }

  /******** Load Login Block **********
   ******** append to document ********
   ******** Hover in middle of screen */
  function loginModal() {

    $.ajax({
      type: 'GET',
      url: '/user/modal/login',
      success: function (response) {
        mainContainer = document.getElementById('main');
        loginBlock = document.createElement('div');
        loginBlock.innerHTML = response;
        loginBlock.className = 'kekistan-login-block';
        loginBlock.id = 'kekistan-login-block';
        closeBtn = document.createElement('div');
        closeBtn.className =  'kekistan-login-block-close';
        closeBtn.innerHTML = '✖';
        loginBlock.appendChild(closeBtn);
        mainContainer.appendChild(loginBlock);

        closeBtn.addEventListener('click', function() {
          loginBlock.innerHTML = '';
          mainContainer.removeChild(loginBlock);
        });
        Drupal.attachBehaviors()
      }
    });
  }

  drupalSettings.filterMode = false;
  Drupal.behaviors.custom= {
    attach: function (context, settings) {
      if (context === document) {
        streamHashtagListeners();
        kekfilterListeners();
        listenNav();
      }

      if (drupalSettings.admin) {
        //Header offset behaviour to account for top menu
        if (window.innerWidth < 415) {
          let header = document.getElementById('header');
          $(window).scroll(function () {
            if ($(window).scrollTop() >= 39) {
              header.style.top = 0;
              console.log('greater');
            } else {
              console.log('less');
              header.style.top = 0.75 * (39 - $(window).scrollTop()) + 'px';
            }
          });
        }
      }
      //Laundry list of listeners
      flagToolListen();
      userPagePrintName();
      listenReplyButtons();
      listenReplyLinks();
      listenVideos();
      textareaAutoHeight();
      userMenuBehaviour();
      hideCommentForms();
      window.removeEventListener('scroll', checkScroll);
      window.removeEventListener('resize', checkScroll);
      window.addEventListener('scroll', checkScroll, false);
      window.addEventListener('resize', checkScroll, false);
    }
  };

  $(document).ready(function() {
    //Filter is always on page "1" (0) on page load
    drupalSettings.filterPage = 0;
    drupalSettings.filterPageEnd = false;
  });
})(jQuery, Drupal, drupalSettings);

