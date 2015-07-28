<?

require_once($_SERVER['DOCUMENT_ROOT'] . "/xajax/team.common.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/teams.php");

session_start();

/**
 * добавить в избранное
 * @param int $user_id кто добавляет
 * @param string $target_login кого добавляет
 * @param bool $fromPayPlace - добавление из оплаченного места, под каталогом фрилансеров
 * @param bool $fromSdelau - добавление из раздела СДЕЛАЮ
 * @return boolean|\xajaxResponse 
 */
function AddInTeam($user_id, $target_login, $fromPayPlace = false, $fromSdelau = false) {
    $objResponse = new xajaxResponse();
    if ($_SESSION['login']) {
        
        $team = new teams();
        if ($team->teamsAddFavorites($_SESSION['uid'], $target_login)) return false;
        
        // добавление из оплаченного места
        if ( $fromPayPlace ) {
            $objResponse->assign('addToFavorites', 'style.display', 'none');
            $objResponse->assign('delFromFavorites', 'style.display', '');
            $objResponse->script("favInProgress = false");
        }
        // добавление из раздела СДЕЛАЮ
        if ($fromSdelau) {
            $onclick = 'xajax_DelInTeam(' . $user_id . ', "' . $target_login . '", null, true); return false;';
            $objResponse->script("$$('.fav_action_$target_login').set('onclick', '$onclick')");
            $objResponse->script("$$('.fav_title_$target_login').set('text', 'Убрать из избранных')");
        }

    }
    return $objResponse;
}

/**
 * Удаляет из избранных
 * @param int $user_id кто удаляет
 * @param string $target_login кого удаляет
 * @param bool $fromPayPlace удаление из платных мест
 * @param bool $fromSdelau удаление из раздела СДЕЛАЮ
 * @return boolean|\xajaxResponse
 */
function DelInTeam($user_id, $target_login, $fromPayPlace = false, $fromSdelau = false) {
    $objResponse = new xajaxResponse();
    if ($_SESSION['login']) {
        
        $team = new teams();
        if ($team->teamsDelFavoritesByLogin($_SESSION['uid'], $target_login)) return false;
        
        // удаление из оплаченного места
        if ( $fromPayPlace ) {
            $objResponse->assign('addToFavorites', 'style.display', '');
            $objResponse->assign('delFromFavorites', 'style.display', 'none');
            $objResponse->script("favInProgress = false");
        }
        // удаление из раздела СДЕЛАЮ
        if ($fromSdelau) {
            $onclick = 'xajax_AddInTeam(' . $user_id . ', "' . $target_login . '", null, true); return false;';
            $objResponse->script("$$('.fav_action_$target_login').set('onclick', '$onclick')");
            $objResponse->script("$$('.fav_title_$target_login').set('text', 'В избранное')");
        }
        
    }
    return $objResponse;
}

function addFavorite($user_id, $target_login) {
    $objResponse = new xajaxResponse();
    if ($_SESSION['login']) {
        
        $team = new teams();
        if ($team->teamsAddFavorites($_SESSION['uid'], $target_login)) return false;
        
        $objResponse->script("
        $$('.fav_title_action_{$target_login}').getParent().getElement('span').removeClass('b-username__star_white').addClass('b-username__star_yellow');        
        $$('.fav_title_action_{$target_login}').set('html', 'У вас в избранных');
        $$('.fav_title_action_{$target_login}').removeClass('b-username__link_dot_0f71c8').addClass('b-username__link_dot_000');
        $$('.fav_action_{$target_login}').setProperty('onclick', '');
        $$('.fav_action_{$target_login}').removeEvents('click');
        $$('.fav_action_{$target_login}').addEvent('click', function(){
            xajax_delFavorite('{$_SESSION['login']}','{$target_login}');  
            return false;  
        })");
    }
    
    return $objResponse;
}

function delFavorite($user_id, $target_login) {
    $objResponse = new xajaxResponse();
    if ($_SESSION['uid']) {
        
        $team = new teams();
        if ($team->teamsDelFavoritesByLogin($_SESSION['uid'], $target_login)) return false;
        
        $objResponse->script("
        $$('.fav_title_action_{$target_login}').getParent().getElement('span').removeClass('b-username__star_yellow').addClass('b-username__star_white');        
        $$('.fav_title_action_{$target_login}').set('html', 'Добавить в избранное');
        $$('.fav_title_action_{$target_login}').removeClass('b-username__link_dot_000').addClass('b-username__link_dot_0f71c8');
        $$('.fav_action_{$target_login}').setProperty('onclick', '');
        $$('.fav_action_{$target_login}').removeEvents('click');
        $$('.fav_action_{$target_login}').addEvent('click', function(){
            xajax_addFavorite('{$_SESSION['login']}','{$target_login}'); 
            return false;   
        })");
    }
    
    return $objResponse;
}

function AddInTeamNew($login) {
    $objResponse = new xajaxResponse();
    if ($_SESSION['login']) {

        $team = new teams();
        if ($team->teamsAddFavorites($_SESSION['uid'], $login)) return false;
        
        $objResponse->script("
            $('team_{$login}').removeClass('uprj-bar');
            $('team_{$login}').addClass('uprj-bar-act');

            r = $('team_{$login}').getElement('.uprj-st1');
            r.set('html', 'Этот исполнитель у вас в избранных (<a href=\"javascript:void(0)\" onclick=\"delFromFav(\'$login\')\" class=\"lnk-dot-grey\">убрать</a>)');
            r.removeClass('uprj-st1');
            r.addClass('uprj-st2');
        ");

    }
    return $objResponse;
}

function DelInTeamNew($login) {
    $objResponse = new xajaxResponse();
    if ($_SESSION['login']) {

        $team = new teams();
        if ($team->teamsDelFavoritesByLogin($_SESSION['uid'], $login)) return false;

        $objResponse->script("
            $('team_{$login}').removeClass('uprj-bar-act');
            $('team_{$login}').addClass('uprj-bar');

            r = $('team_{$login}').getElement('.uprj-st2');
            r.set('html', '<a href=\"javascript:void(0)\" onclick=\"addToFav(\'$login\')\" class=\"lnk-dot-grey\">Добавить в избранные</a>');
            r.removeClass('uprj-st2');
            r.addClass('uprj-st1');
        ");

    }
    return $objResponse;
}

$GLOBALS['xajax']->processRequest();
?>
