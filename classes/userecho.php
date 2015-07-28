<?php
/**
* Класс для работы с UserEcho
*/
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/users.php");

class UserEcho
{
    /**
     * ID приватного форума (Helpdesc)
     */
    const FORUM_ID_HELPDESC = 27458; 
    
    /**
     * Тип топиков "Жалоба на проект"
     */
    const TYPE_QUESTIONS = 26343;
    
    /**
     * Категория "Жалобы на пользователей сайта"
     */
    const CATEGORY_COMPLAIN = 10901;
    
    
    /**
    * @param string $api_key API ключ UserEcho
    * @param string $project_key Ключ UserEcho
    * @param array $user_info
    * @return SSO KEY
    */
    public static function get_sso_token($api_key, $project_key, $user_info)
    {
        $sso_key = '';

        if ($uid = get_uid(false)) {
            $user = new users();
            $user->GetUserByUID($uid);

            $iv  = str_shuffle('memoKomo1234QWER');

            $message = array(
                "guid" => $uid,
                "expires_date" => gmdate("Y-m-d H:i:s", time()+(86400)),
                "display_name" => $user->login,
                "email" => $user->email,
                "locale" => 'ru',
                "verified_email" => true
            );

            // key hash, length = 16
            $key_hash = substr( hash('sha1', $api_key.$project_key, true), 0, 16);

            $message_json = json_encode(encodeCharset('CP1251', 'UTF-8', $message));

            // double XOR first block message_json
            for ($i = 0; $i < 16; $i++) {
                $message_json[$i] = $message_json[$i] ^ $iv[$i];
            }

            // fill tail of message_json by bytes equaled count empty bytes (to 16)
            $pad = 16 - (strlen($message_json) % 16);
            $message_json = $message_json . str_repeat(chr($pad), $pad);

            // encode json
            $cipher = mcrypt_module_open(MCRYPT_RIJNDAEL_128,'','cbc','');
            mcrypt_generic_init($cipher, $key_hash, $iv);
            $encrypted_bytes = mcrypt_generic($cipher,$message_json);
            mcrypt_generic_deinit($cipher);

            // encode bytes to url safe string
            $sso_key = urlencode(base64_encode($encrypted_bytes));
        }

        return $sso_key;
    }
    
    /**
     * Создает новый топик - жалобу
     * @param string $name Заголовок топика
     * @param string $message Текст топика
     * @return string Url топика или 0, если неуспешно
     */
    public function newTopicComplain($name, $message) 
    {
        $sso_token = self::get_sso_token(USERECHO_API_KEY, USERECHO_PROJECT_KEY, array());
        $url_template = 'https://userecho.com/api/v2/forums/%d/topics.json?sso_token=%s&access_token=%s';
        $url = sprintf($url_template, self::FORUM_ID_HELPDESC, $sso_token, USERECHO_API_TOKEN);
        
        $params = array(
            'header' => iconv('cp1251', 'utf-8', 'Жалоба на проект - '.$name), 
            'description' => iconv('cp1251', 'utf-8', $message),
            'type' => self::TYPE_QUESTIONS, 
            'category' => self::CATEGORY_COMPLAIN               
        );
                
        $params_json = json_encode($params);
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $params_json);
        ob_start();
        curl_exec($ch);
        $complete = ob_get_clean();
        $response = json_decode($complete, true);
        
        $topic_Url = 0;
        if ($response['status'] == 'success') {
            $topic_Url = 'https://feedback.fl.ru/topic/' . @$response['data']['id'];
        }

        return $topic_Url;
    }
    
    /**
     * Формирует текст топика
     * @param type $project_url
     * @param type $project_name
     * @param type $text
     * @param type $files
     */
    public static function constructMessage($project_url, $project_name, $text, $files = array())
    {
        $message = "Проект <a href='{$project_url}'>{$project_name}</a><br /><br />";
        $message .= str_replace("\n", "<br />", $text);
        if (count($files)) {
            foreach ($files as $file) {
                $message .= "<br><a href='{$file['link']}' class='i_item_file'><i class='icon-file'></i> {$file['name']}</a>";
            }
        }
        return $message;
    }
}