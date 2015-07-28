<?php
/* 
 * 
 * Данный файл является частью проекта Веб Мессенджер.
 * 
 * Все права защищены. (c) 2005-2009 ООО "ТОП".
 * Данное программное обеспечение и все сопутствующие материалы
 * предоставляются на условиях лицензии, доступной по адресу
 * http://webim.ru/license.html
 * 
 */
?>
<?php 

    class CWebim {
        function BeforeUserAdd(&$arFields) {
            if (strstr($_SERVER['HTTP_REFERER'], "webim")) {
                $filter = array("STRING_ID" => "webim");
                $by = 'c_sort';
                $order = 'DESC';
                $rsGroups = CGroup::GetList($by, $order, $filter);
                $is_filtered = $rsGroups->is_filtered;

                if ($row = $rsGroups->GetNext()) {
                    $arFields["GROUP_ID"][] = array("GROUP_ID" => $row["ID"]);
                }
            }
        }

        function UserDelete($id) {
          Operator::getInstance()->DeleteOperator($id);
        }

        static function getAvatar($uid = false) {
            global $USER;

            if ($uid === false) {
                $uid = $USER->GetID();
            }

            $rsUser = CUser::GetByID($uid);
            $arResult = $rsUser->GetNext(false);
            if (!empty($arResult) && !empty($arResult["PERSONAL_PHOTO"])) {
              $db_img = CFile::GetByID($arResult["PERSONAL_PHOTO"]);
              $db_img_arr = $db_img->Fetch();
              
              if (!empty($db_img_arr)) {
                $strImageStorePath = COption::GetOptionString("main", "upload_dir", "upload");
                $sImagePath = "/".$strImageStorePath."/".$db_img_arr["SUBDIR"]."/".$db_img_arr["FILE_NAME"];
                $sImagePath = str_replace("//","/",$sImagePath);
                return $sImagePath;
              }
            }

            return false;
        }
    }

?>