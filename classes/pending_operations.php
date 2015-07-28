<?php 


class pending_operations
{
    
    /**
     * Список отложенных операций
     * 
     * @global type $DB
     * @param type $uid
     * @return type 
     */
    public function getOperations($uid=false) {
        global $DB;
        if(!$uid) $uid = $_SESSION['uid'];
        $sql = "SELECT * FROM draft_account_operations WHERE uid = ?";
        return $DB->rows($sql, $uid);
    }
    
    public function paidOperation($id) {
        global $DB;
        if(!$id) return false;
        require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/projects.php");
        
        $operation = $DB->row("SELECT * FROM draft_account_operations WHERE id = ? AND status IS NULL", $id);
        $account   = new account();
        $this->_transactionId = $account->start_transaction($_SESSION['uid'], $this->_transactionId);
        
        switch($operation['op_type']) {
            case "project":
                $project = $DB->row("SELECT id, payed_info FROM projects WHERE id = ?i", $operation['parent']);
                if(!$project['id']) {
                    return "Ошибка обработки операции.";
                }
                 
                if ($account->sum >= $operation['ammount']) {
                    if ($error = $account->Buy($bill_id, $this->_transactionId, $operation['op_code'], $_SESSION['uid'], trim($operations['descr'], '/'), trim($operations['comments'], '/'), 1, true)) {
                        return $error;
                    }
                }
                if(!$bill_id) {
                    return 'Не хватает денег.';
                }
                $update = array();
                switch($operation['option']) {
                    case "color":
                        $update['is_color']    = true;
                        $update['payed_items'] = $project['payed_info'] | "010";
                        break;
                    case "bold":
                        $update['is_bold'] = true;
                        $update['payed_items'] = $project['payed_info'] | "001";
                        break;
                    case "top":
                        $update['top_from'] = date('d.m.Y H:i', strtotime("now"));
                        $update['top_to']   = date('d.m.Y H:i', strtotime("now +{$operation['op_count']} day"));
                        break;
                    case "logo":
                        $update['logo_id'] = $operation['src_id'];
                        $update['payed_items'] = $project['payed_info'] | "100";
                        break;
                }
                
                $update['billing_id'] = $bill_id;
                
                $DB->update("projects", $update, "id = ?", $operation['parent']);
                header("Location: /bill/success/");
                exit; 
                break;
            case "contest":
                // Публикация конкурса
                if ($account->sum >= $operation['ammount']) {
                    if ($error = $account->Buy($bill_id, $this->_transactionId, $operation['op_code'], $_SESSION['uid'], trim($operations['descr'], '/'), trim($operations['comments'], '/'), 1, true)) {
                        return $error;
                    }
                }
                
                if(!$bill_id) {
                    return 'Не хватает денег.';
                }
                $key = md5(microtime());
                $prj = new tmp_project($key);
                if(get_uid()) {
                    $prj->init(1);
                }
                
                $draft = new drafts();
                $project = $drafts->getDraft($operation['parent'], $_SESSION['uid'], 1);
                
                require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/attachedfiles.php");
                $attachedfiles = new attachedfiles();
                $attachedfiles_tmpdraft_files = drafts::getAttachedFiles($operation['parent'], 4);
                if ($attachedfiles_tmpdraft_files) {
                    $attachedfiles_prj_files = array();
                    foreach ($attachedfiles_tmpdraft_files as $attachedfiles_draft_file) {
                        $attachedfiles_draft_files[] = $attachedfiles_draft_file;
                    }
                    $attachedfiles->setFiles($attachedfiles_draft_files, 1);
                }
                
                $insert = array(
                    "user_id"    => $_SESSION['uid'],
                    "name"       => $project['name'],
                    "descr"      => $project['descr'],
                    "kind"       => $project['kind'],
                    "cost"       => $project['cost'],
                    "currency"   => $project['currency'],
                    "country"    => ( $project['country'] > 0 ? $project['country'] : 'NULL' ),
                    "city"       => ( $project['city'] > 0 ? $project['city'] : 'NULL' ),
                    "payed"      => '0',
                    "pro_only"   => $project['pro_only'], 
                    "logo_id"    => 'NULL',
                    "link"       => $project['logo_link'],
                    "is_color"   => 'f',
                    "is_bold"    => 'f',
                    "billing_id" => 0,
                    "payed_items"=> "000",
                    "folder_id"  => 0,
                    "budget_type"=> $project['budget_type'],
                    "priceby"    => $project['priceby'],
                    "prefer_sbr" => $project['prefer_sbr'],
                    "end_date"   => $project['p_end_date'],
                    "win_date"   => $project['p_win_date'],
                );

                if (!empty($project['categories'])) {
                    $cat           = explode("|", $project['categories']);
                    $categories[0] = array('category_id' => intval($cat[0]), 'subcategory_id' => intval($cat[1]));
                }
                
                $create = $prj->addPrj($insert, $attachedfiles_draft_files, $categories); // Добавляем проект
                if($create) {
                    $drafts->DeleteDraft($draft_id, $uid, 1);
                    header("Location: /bill/success/");
                    exit; 
                }
                
                break;
            case "account":
                // Покупка ПРО
                break;
        }
    }
}

?>