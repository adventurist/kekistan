/**
 * Created by logicp on 5/28/17.
 */

(function($, Drupal, drupalSettings) {

  const flagListen = function(e) {

    let stringWithHid = e.srcElement.href.substring(0, e.srcElement.href.indexOf('?destination'));
    let hid = stringWithHid.substring(stringWithHid.lastIndexOf('/') + 1);
    let flagId = stringWithHid.substring(stringWithHid.lastIndexOf('/flag/'), stringWithHid.lastIndexOf(hid) - 1);

    flagId = flagId.substring(flagId.lastIndexOf('/') + 1);

    $.ajax({
      type: 'POST',
      url:'/heartbeat/userflaggings',
      data: {
        entity_id: hid,
        entity_type: 'heartbeat',
        flag_id: flagId,
        uid: drupalSettings.user.uid
      },
      success: function(response) {
        console.dir(response);
      }
    });
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
    let cboxOptions = { maxWidth: '960px', maxHeight: '960px' };
    let images = document.querySelectorAll('.heartbeat-content img');

    $('.heartbeat-content').find('img').each(function () {
      let parentClass = $(this).parent().prop('className');
      let phid = parentClass.substring(parentClass.indexOf('hid') + 4);
      $(this).colorbox({rel: phid, href: $(this).attr('src'), cboxOptions});
    });
  }

  function listenCommentPost() {
    let comments = document.querySelectorAll('[data-drupal-selector]');

    for (let i = 0; i < comments.length; i++) {
      let comment = comments[i];
      comment.addEventListener('click', function () {
        getParent(comment);
      })
    }
  }

  function commentFormListeners() {
    let cFormButtons = document.querySelectorAll('.heartbeat-comment-button');

    for (let b = 0; b < cFormButtons.length; b++) {
      cFormButtons[b].removeEventListener('click', commentListen);
      cFormButtons[b].addEventListener('click', commentListen);
    }
  }

  function flagListeners() {

    if (arguments[0] !== null && arguments.constructor !== Array) {
      //noinspection JSValidateTypes
      arguments = [arguments[0]];
    }

    let flags = arguments[0] == undefined ? document.querySelectorAll('.flag .use-ajax') : arguments[0];
    console.log("Reloading FLAG LISTENERS");

    if (flags.constructor === NodeList) {
      for (let f = 0; f < flags.length; f++) {
        flags[f].removeEventListener('click', flagListen);
        flags[f].addEventListener('click', flagListen);
      }
    } else if (flags.constructor === Array && (flags[0].nodeType !== null && flags[0].nodeType > 0)) {
      flags.forEach(function(node) {
        node.removeEventListener('click', flagListen);
        node.addEventListener('click', flagListen);
      });
    } else if (flags.nodeName !== null && flags.nodeName !== undefined) {
      flags.removeEventListener('click', flagListen);
      flags.addEventListener('click', flagListen);
    } else {
      console.debug('FlagListen called with no flags available');
    }
  }

  function getParent(node) {
    if (node != null && node != undefined && node.classList != undefined && node.classList.contains('heartbeat-comment')) {
      let id = node.id.substr(node.id.indexOf('-') + 1);
      $.ajax({
        type: 'POST',
        url: '/heartbeat/commentupdate/' + id,
        success: function (response) {
        }
      });
    } else {
      if (node != null && node.nodeName !== 'body') {
        getParent(node.parentNode);
      }
    }
  }

  function getScrollXY() {
    let scrOfX = 0, scrOfY = 0;
    if (typeof( window.pageYOffset ) == 'number') {
      scrOfY = window.pageYOffset;
      scrOfX = window.pageXOffset;
    } else if (document.body && ( document.body.scrollLeft || document.body.scrollTop )) {
      scrOfY = document.body.scrollTop;
      scrOfX = document.body.scrollLeft;
    } else if (document.documentElement && ( document.documentElement.scrollLeft || document.documentElement.scrollTop )) {
      scrOfY = document.documentElement.scrollTop;
      scrOfX = document.documentElement.scrollLeft;
    }
    return [scrOfX, scrOfY];
  }

  //taken from http://james.padolsey.com/javascript/get-document-height-cross-browser/
  function getDocHeight() {
    let D = document;
    return Math.max(
      D.body.scrollHeight, D.documentElement.scrollHeight,
      D.body.offsetHeight, D.documentElement.offsetHeight,
      D.body.clientHeight, D.documentElement.clientHeight
    );
  }

  Drupal.AjaxCommands.prototype.selectFeed = function (ajax, response, status) {
    $.ajax({
      type: 'POST',
      url: '/heartbeat/render_feed/' + response.feed,
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
        Drupal.attachBehaviors();
      }
    });
  };

  Drupal.AjaxCommands.prototype.updateFeed = function (ajax, response, status) {
    if (response.update) {
      $.ajax({
        type: 'POST',
        url: '/heartbeat/update_feed/' + response.timestamp,
        success: function (response) {

        }
      });
    }
  };

  Drupal.AjaxCommands.prototype.myfavouritemethodintheworld = function (ajax, response, status) {
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
  };

  function hideCommentForms() {
    let forms = document.querySelectorAll('.heartbeat-comment-form .js-form-type-textarea, .heartbeat-comment-form .form-submit');

    for (let f = 0; f < forms.length; f++) {
      console.log('adding hidden class to comment form');
      if (!forms[f].classList.contains('comment-form-hidden')) {
        forms[f].className += ' comment-form-hidden';
      }
    }
  }

  /******** Load Login Block **********
   ******** append to document ********
   ******** Hover in middle of screen */

  function loginModal() {

    $('#heartbeat-loader').show(225);

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
        closeBtn.className = 'kekistan-login-block-close';
        closeBtn.innerHTML = '✖';
        loginBlock.appendChild(closeBtn);
        mainContainer.appendChild(loginBlock);

        closeBtn.addEventListener('click', function () {
          loginBlock.innerHTML = '';
          mainContainer.removeChild(loginBlock);
        });

      },
      complete: function () {
        $('#heartbeat-loader').hide(225);
        Drupal.attachBehaviors()
      }
    });
  }

  function toggleCommentElements(node) {

    console.dir(node);
    if (node.classList.contains('comment-form-hidden')) {
      console.log('removing comment-form-hidden class from element');
      node.classList.remove('comment-form-hidden');
    } else {
      node.className += ' comment-form-hidden';
    }
  }

  const commentListen = function(e) {

    if (drupalSettings.user.uid > 0) {

      let commentBlock = e.srcElement.parentNode.parentNode.querySelector('.heartbeat-comments');

      if (!commentBlock.classList.contains('heartbeat-comments-visible')) {
        commentBlock.className += ' heartbeat-comments-visible';
      } else {
        commentBlock.classList.remove('heartbeat-comments-visible');
      }

      let childs = e.srcElement.parentNode.querySelectorAll('.form-submit, .js-form-type-textarea');

      for (let c = 0; c < childs.length; c++) {
        toggleCommentElements(childs[c]);
      }
    } else {
      loginModal();
    }
  };

  Drupal.behaviors.heartbeat = {
    attach: function (context, settings) {

      if (drupalSettings.friendData != null) {
        let divs = document.querySelectorAll('.flag-friendship a.use-ajax');

        for (let i = 0; i < divs.length; i++) {
          let anchor = divs[i];
          let userId = anchor.href.substring(anchor.href.indexOf('friendship') + 11, anchor.href.indexOf('?destination'));
          JSON.parse(drupalSettings.friendData).forEach(function (friendship) {
            if (friendship.uid_target === userId && friendship.uid == drupalSettings.user.uid && friendship.status == 0) {
              anchor.innerHTML = 'Friendship Pending';
            }
          });
        }
      }
      listenImages();
      listenCommentPost();
      commentFormListeners();
      feedElement = document.querySelector('.heartbeat-stream');

      if (drupalSettings.feedUpdate == true) {
        updateFeed();
      }
    }
  };


  $(document).ready(function() {
    const loader = document.createElement('div');
    loader.id = 'heartbeat-loader';
    const body = document.getElementsByTagName('body')[0];
    body.appendChild(loader);

    flagListeners();

    let stream = document.getElementById('block-heartbeatblock');

    let observer = new MutationObserver(function (mutations) {
      console.log('observer observes a change');
      // Drupal.attachBehaviors();
      // listenImages();
      hideCommentForms();
      // commentFormListeners();
      // flagListeners();
      // listenVideos();
    });

    let config = {attributes: true, childList: true, characterData: true};

    observer.observe(stream, config);

    let flagObserver = new MutationObserver(function(mutations) {
      console.log('observer observes a change');
      if (mutations[0].target !== null && mutations[0].target.children !== null && mutations[0].target.children.length > 0 && mutations[0].target.children[0].classList.contains('flag')) {
        flagListeners();
      }
    });

    let flagObserveConfig = {subTree: true, childList: true};

    let flags = Array.from(document.querySelectorAll('.heartbeat-like, .heartbeat-unlike'));

    flags.forEach(function(flag) {
      flagObserver.observe(flag, flagObserveConfig);
    });


    document.addEventListener("scroll", function () {

      if (drupalSettings.filterMode == false && (getScrollXY()[1] + window.innerHeight) / getDocHeight() > 0.99) {

        let streams = document.querySelectorAll('.heartbeat-stream');
        let stream = streams.length > 1 ? streams[streams.length - 1] : streams[0];

        if (stream !== null) {
          console.dir(stream);
          let lastHeartbeat = stream.lastElementChild;

          if (lastHeartbeat !== null) {

            let hid = lastHeartbeat.id.substring(lastHeartbeat.id.indexOf('-') + 1);
            if (drupalSettings.lastHid !== hid) {

              drupalSettings.lastHid = hid;

              $('#heartbeat-loader').show(225);

              //For Reference -> this is how you can trigger an ajax request via the Drupal Ajax api
              // Drupal.ajax({url: '/heartbeat/update_feed/' + hid}).execute();

              $.ajax({
                type: 'POST',
                url: '/heartbeat/update_feed/' + hid,

                success: function (response) {
                  feedBlock = document.getElementById('block-heartbeatblock')
                  insertNode = document.createElement('div')
                  insertNode.innerHTML = response
                  feedBlock.appendChild(insertNode)
                  Drupal.attachBehaviors()
                },

                complete: function () {
                  $('#heartbeat-loader').hide(225);
                }
              });
            }
          }
        }
      }
    });

    $(document).on('cbox_open', function () {
        $("#colorbox").swipe({
          //Generic swipe handler for all directions
          swipeLeft: function (event, direction, distance, duration, fingerCount) {
            $.colorbox.prev();
          },
          swipeRight: function (event, direction, distance, duration, fingerCount) {
            $.colorbox.next();
          },
          //Default is 75px, set to 0 for demo so any distance triggers swipe
          threshold: 25
        });
        let cboxCloseBtn = $('#cboxClose');
        cboxCloseBtn.on('click touchstart', function () {
          $.colorbox.close();
        });
        cboxCloseBtn.on('keyup', function (e) {
          if (e.keyCode == 27) {
            $.colorbox().close();
          }
        });
        return true;
      }
    );
  })
})(jQuery, Drupal, drupalSettings);
