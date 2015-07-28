var wmInvitationJS = null;
var wmPrev = 0;
var wmTotal = 0;
var wmTimeout = 10000; // polling timeout
var wmResponseImage = new Image();









wmAddEvent(wmResponseImage, 'load', function() {
  if (wmResponseImage != null) {
    var action = wmResponseImage.height;
    if (action == 4) {
      invite('<?php echo $p_invitescript ?>');
    } else if ((action == 1) && (typeof(closeInvitation) != 'undefined') && wmInvitationTimer) {
      closeInvitation();
      remove_nodes();
    }
  }
});

wmAddEvent(window, 'load', createInvitationDiv);
wmAddEvent(window, 'unload', sendLeftEvent);

wmPoll();

function wmAddEvent(object, eventType, func, useCapture) {
  if (object.addEventListener) {
    object.addEventListener(eventType, func, useCapture);
    return true;
  } else if (object.attachEvent) {
    return object.attachEvent('on' + eventType, func);
  }
}

function sendLeftEvent() {
  wmResponseImage.src = '<?php echo $p_location ?>/track.php?event=left&theme=<?php echo Browser::getCurrentTheme();?>&pageid=<?php echo $p_pageid ?>&issecure=<?php echo $p_issecure ?>';
}

function wmResumeAnimation() {
  (wmResumeAnimationImpl || function() {
  })();
}

function wmPauseAnimation() {
  (wmPauseAnimationImpl || function() {
  })();
}

function createInvitationDiv() {
  var body = document.getElementsByTagName('body')[0];

  var invitationDiv = document.createElement('div');
  invitationDiv.setAttribute('id', 'invitediv');
  invitationDiv.setAttribute('name', 'invitediv');

  invitationDiv.style.width = '360px';
  invitationDiv.style.height = '150px';

  body.appendChild(invitationDiv);

  wmAddEvent(invitationDiv, 'mouseout', wmResumeAnimation);
  wmAddEvent(invitationDiv, 'mouseover', wmPauseAnimation);
}

function get_head_or_body() {
  var target;
  var heads = document.getElementsByTagName("head");
  if (heads != null && heads.length > 0) {
    target = heads[0];
  } else {
    target = document.body;
  }
  return target;
}

function get_invite_div() {
  var div = null;
  if (document.getElementById) {
    div = document.getElementById("invitediv");
  } else if (document.layers) {
    div = document.invitediv.document;
  }
  return div;
}

function invite(sessionUrl) {
  if (typeof(wmInvitationTimer) != "undefined" && wmInvitationTimer != null) {
    return;
  }

  document.invitediv = get_invite_div();

  if (wmInvitationJS == null) {
    var target = get_head_or_body();

    wmInvitationJS = document.createElement('script');
    wmInvitationJS.id = 'invitation_script';
    wmInvitationJS.type = 'text/javascript';
    wmInvitationJS.src = sessionUrl;
    target.appendChild(wmInvitationJS);
  } else if (typeof(startInviting) != "undefined") {
    startInviting();
  }
}

function remove_nodes() {
  if (wmInvitationJS != null) {
    wmInvitationJS.parentNode.removeChild(wmInvitationJS);
    wmInvitationJS = null;
  }
}

function wmGetTitle() {
  var titles = document.getElementsByTagName('title');
  if(titles.length > 0 && titles[0].firstChild && titles[0].firstChild.nodeType == 3) {
    return titles[0].firstChild.nodeValue;
  }

  return null;
}

function wmPoll() {  

  var curr = new Date().getTime();
  if (curr - wmPrev < wmTimeout / 2) {

    return;
  }
  wmPrev = curr;
  wmTotal++;
  if (wmTotal > 20) {
    wmTimeout = 30000;
  }



  var poll_src = '<?php echo $p_location ?>/track.php?event=poll&theme=<?php echo Browser::getCurrentTheme();?>&curr=' + curr + '&timeout=' + wmTimeout + '&url=' + escape(document.location.href) + '&title=' + encodeURIComponent(wmGetTitle()) + '&pageid=<?php echo $p_pageid ?>&issecure=<?php echo $p_issecure ?>';
  wmResponseImage.src = poll_src;
  setTimeout('wmPoll()', wmTimeout);
}
