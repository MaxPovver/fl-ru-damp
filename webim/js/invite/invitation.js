var wmAnimationBoxSty;
var wmDocWidth;
var wmDocHeight;
var wmDocXTop;
var wmCurrentX;
var wmCurrentY;
var wmXIncrementSaved;
var wmXIncrement;
var wmCurrentAminTime = 0;
var wmAnimationTime = '<?php echo($p_amination_duration * 1000) ?>';
var wmSingleStepTime = 20;
var wmAnimationStep = 1;
var wmInvitationTimer = null;

{
  var windowSize = getWindowSize();
  wmDocWidth = windowSize[0];
  wmDocHeight = windowSize[1];
}

window.onscroll = onScroll;
if (document.getElementById) {
  doc = 'document.getElementById("';
  sty = '").style';
  htm = '")';
}

if (document.layers) {
  doc = 'document.';
  sty = '';
  htm = '.document';
}

document.invitediv.innerHTML = '<?php echo $p_invitation ?>';


startInviting();

function onScroll() {
  var scrollXY = getScrollXY();
  wmAnimationBoxSty.left = (scrollXY[0] + wmCurrentX) + 'px';
  wmAnimationBoxSty.top = (scrollXY[1] + wmCurrentY) + 'px';
}

function closeInvitation() {

  sendTrackEvent("reject");
}

function sendTrackEvent(event) {


  document.invitediv.innerHTML = '';


  // clear timer
  clearTimeout(wmInvitationTimer);
  wmInvitationTimer = null;

  // hide invitation box
  wmAnimationBoxSty.display = 'none';


  // send hid invitation request to the server
  var tracker_url = '<?php echo $p_hideanim ?>event=' + event + '&current=' + (new Date()).getTime() + '&pageid=<?php echo $p_pageid ?>';
  new Image().src = tracker_url;
}

function animationStep() {
  if (wmAnimationBoxSty.display == 'none') {
    return;
  }

  var scrollXY = getScrollXY();
  var newX = wmCurrentX + wmXIncrement;
  var paddingRight = 20;

  if ((newX < wmDocXTop - paddingRight && wmXIncrement > 0) || (wmCurrentX > 0 && wmXIncrement < 0)) {
    wmCurrentX = newX;
    wmAnimationBoxSty.left = (scrollXY[0] + wmCurrentX) + 'px';
  } else if (wmXIncrement != 0) {
    wmXIncrementSaved = -wmXIncrementSaved;
    wmXIncrement = wmXIncrementSaved;
  }

  if (wmXIncrement != 0) {
    wmCurrentAminTime += wmSingleStepTime;
  }

  if (wmCurrentAminTime < wmAnimationTime) {
    wmInvitationTimer = setTimeout('animationStep()', wmSingleStepTime);
  } else {
    sendTrackEvent("timeout");
  }

}

function startInviting() {


  var invitation_div_name = doc + 'invitediv' + htm;
  var invitation_div = eval(invitation_div_name);

  if (invitation_div == null) {
    // there is no invitation div code on the page

    return;
  }

  // set invitation message
  var invmessage_div = eval(doc + 'webim-invatation-message' + htm);
  invmessage_div.innerHTML = '<?php echo $p_message?>';

  wmAnimationBoxSty = eval(doc + 'invitediv' + sty);

  var box_width = parseInt(invitation_div.style.width);
  var box_height = parseInt(invitation_div.style.height);

  wmDocXTop = wmDocWidth - box_width;

  wmXIncrementSaved = wmAnimationStep;
  wmXIncrement = wmXIncrementSaved;

  wmCurrentX = (wmDocWidth - box_width) * 0.1;
  wmCurrentY = (wmDocHeight - box_height) * 0.5;

  var scrollXY = getScrollXY();
  wmAnimationBoxSty.left = (scrollXY[0] + wmCurrentX) + 'px';
  wmAnimationBoxSty.top = (scrollXY[1] + wmCurrentY) + 'px';

  wmAnimationBoxSty.display = 'block';
  wmInvitationTimer = setTimeout('animationStep()', 3000);


}

function getScrollXY() {
  var scrOfX = 0, scrOfY = 0;
  if (typeof(window.pageYOffset) == 'number') {
    //Netscape compliant
    scrOfY = window.pageYOffset;
    scrOfX = window.pageXOffset;
  } else if (document.body && (document.body.scrollLeft || document.body.scrollTop)) {
    //DOM compliant
    scrOfY = document.body.scrollTop;
    scrOfX = document.body.scrollLeft;
  } else if (document.documentElement && (document.documentElement.scrollLeft || document.documentElement.scrollTop)) {
    //IE6 standards compliant mode
    scrOfY = document.documentElement.scrollTop;
    scrOfX = document.documentElement.scrollLeft;
  }
  return [scrOfX, scrOfY];
}

function getWindowSize() {
  var myWidth = 0, myHeight = 0;
  if (typeof(window.innerWidth) == 'number') {
    //Non-IE
    myWidth = window.innerWidth;
    myHeight = window.innerHeight;
  } else if (document.documentElement && (document.documentElement.clientWidth || document.documentElement.clientHeight)) {
    //IE 6+ in 'standards compliant mode'
    myWidth = document.documentElement.clientWidth;
    myHeight = document.documentElement.clientHeight;
  } else if (document.body && ( document.body.clientWidth || document.body.clientHeight )) {
    //IE 4 compatible
    myWidth = document.body.clientWidth;
    myHeight = document.body.clientHeight;
  }
  return [myWidth, myHeight];
}

function openChat(a_key_message, a_initial_question, a_name, a_email) {
  sendTrackEvent("accept");
  var chat_url = '<?php echo $p_location ?>/client.php?lang=<?php echo $p_lang ?>&thread=<?php echo $p_threadid ?>&token=<?php echo $p_token ?>&level=<?php echo $p_level ?>';
  window.open(chat_url, 'webim_invitation_<?php echo getWindowNameSuffix() ?>', 'toolbar=0,scrollbars=0,location=0,menubar=0,width=540,height=480,resizable=1');
  return false;
}

function wmPauseAnimationImpl() {
  wmXIncrement = 0;
}

function wmResumeAnimationImpl() {
  wmXIncrement = wmXIncrementSaved;
}
