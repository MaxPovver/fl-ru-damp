var WM_localized;
var WM_params;

function WM_initVisitorChat(soundOnTitleString, soundOffTitleString, webimRoot, threadId, token, frameCSS, whoisUrl) {
  WM_localized = {
    soundOn: soundOnTitleString,
    soundOff: soundOffTitleString
  };

  WM_params = {
    servl: webimRoot + "/thread.php",
    wroot: webimRoot,
    frequency: 2,
    isvisitor: true,
    threadid: threadId,
    token: token,
    framecss: frameCSS,
    whoisUrl : whoisUrl 
  };
}

function WM_initOperatorChat(soundOnTitleString, soundOffTitleString, urlPromptString, confirmClosingString,
                             webimRoot, threadId, token, isViewOnly, frameCSS, whoisUrl) {

  WM_localized = {
    soundOn: soundOnTitleString,
    soundOff: soundOffTitleString,
    urlPrompt: urlPromptString,
    confirmClosing: confirmClosingString
  };

  WM_params = {
    servl: webimRoot + "/thread.php",
    wroot: webimRoot,
    frequency: 2,
    threadid: threadId,
    token: token,
    isViewOnly: isViewOnly,
    framecss: frameCSS,
    whoisUrl: whoisUrl
  };
}

function WM_initChatList(joinChatString, viewChatString, banVisitorString, soundOnString, soundOffString, noClientsString,
                         updaterUrl, wroot, agentUrl, whoisUrl) {
  WM_localized = {
    joinChat: joinChatString,
    viewChat: viewChatString,
    banVisitor: banVisitorString,
    soundOn: soundOnString,
    soundOff: soundOffString,
    noClients: noClientsString
  };

  WM_params = {
    url: updaterUrl,
    wroot: wroot,
    agentservl: agentUrl,
    whoisUrl: whoisUrl
  };
}

function WM_initVisitorList(inviteVisitorString, noVisitorsString, viewVisitInfoString, landingPageString, exitPageString,
                            updaterUrl, wroot, visitDetailsUrl, whoisUrl) {
  WM_localized = {
    inviteVisitor: inviteVisitorString,
    noVisitors: noVisitorsString,
    viewVisitInfo: viewVisitInfoString,
    landingPage: landingPageString,
    exitPage: exitPageString
  };

  WM_params = {
    url: updaterUrl,
    wroot: wroot,
    visitdetails: visitDetailsUrl,
    whoisUrl: whoisUrl
  };
}