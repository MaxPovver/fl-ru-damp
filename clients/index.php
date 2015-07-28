<?	
$g_page_id = "0|5";
$rpath="../";
$grey_catalog = 1;
$stretch_page = true;
$showMainDiv  = true;
error_reporting(E_ERROR);
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/clients.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/users.php");
session_start();

$clnt = new clients();
$prof_id = __paramInit('int', 'prof', 'prof', 0);

if ( $prof_id ) {
    require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/professions.php");
	$prof_link = professions::GetProfField($prof_id, 'link');
}


if($_GET['a'] == 1 && hasPermissions('users')) {
    require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/CFile.php");
    
    $admin = 1;
    $page  = intval($_GET['page']);
    $upage = $page>0?"&page=".$page:"";
    $page  = $page<=0?$page+1:$page;
    $edit  = intval($_GET['edit']);
    
    if($_POST['action']) $action = $_POST['action'];
    if((int)$_GET['delete'] > 0) $action = "delete_client";
    
    switch ($action) {
        case "new_client":
            $name = trim(substr($_POST['name_client'],0,128));
            $link = trim(substr($_POST['link_client'],0,255));
            $logo = new CFile($_FILES['logo']);
            
            if ( $name && $link && $logo->size ) {
                $link = str_replace("http://", "", $link);
                $link = "http://".$link;
                
                $clnt->newClient($name, $link, $logo, $error);
                header("Location: /clients/?a=1".$upage);   
            } else {
                $error = "Заполнены не все поля";
            }
            
            break;
        case "delete_client":
            $clnt->deleteClient(intval($_GET['delete']));
            header("Location: /clients/?a=1{$upage}"); 
            die();
            break;   
        case "edit_client":
            $cid  = intval($edit);
            $name = trim(substr($_POST['name_client'],0,128));
            $link = trim(substr($_POST['link_client'],0,255));
            
            if($_FILES['logo']['name'] != "") $logo = new CFile($_FILES['logo']);
            
            if ( $name && $link && ( !$_FILES['logo']['name'] || $logo->size) ) {
                $link = str_replace("http://", "", $link);
                $link = "http://".$link;
                
                $clnt->editClient($name, $link, $logo, $cid, $error_edit); 
            } else {
                $error_edit = "Заполнены не все поля";
            }
            
            
            if(!$error_edit) {
                if($logo) {
                    $logo_tmp = new CFile($_POST['logo_tmp']);
                    $logo_tmp->Delete($logo_tmp->id);
                }
                
                header("Location: /clients/?a=1{$upage}"); 
                die();
            }
            break;         
    }
    $limit = 10; // Сколько на одной странице выдавать
    $clients = $clnt->getAdminClients($page, $count, $limit);
} else {
    $clients = $clnt->getClients();
}

$content = "content.php";
$header = "../header.php";
$footer = "../footer.html";
$freelancers_catalog = true;
$css_file = array( 'main.css', 'nav.css' );

include ("../template2.php");

?>
