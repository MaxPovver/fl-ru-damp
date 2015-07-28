<?php

/**
 * Class OpauthModel
 *
 */
class OpauthModel {
    
    const TYPE_FACEBOOK = 1;
    const TYPE_VK = 2;
    const TYPE_ODNOKLASSNIKI = 3;
    
    private $TABLE = 'users_social';
    
    private $types_short = array(
        self::TYPE_FACEBOOK         => 'fb',
        self::TYPE_VK               => 'vk',
        self::TYPE_ODNOKLASSNIKI    => 'od'
    );

    private $types = array(
        self::TYPE_FACEBOOK => 'Facebook',
        self::TYPE_VK => 'VKontakte',
        self::TYPE_ODNOKLASSNIKI => 'Odnoklassniki'
    );
    
    private $type_names = array(
        self::TYPE_FACEBOOK => 'Facebook',
        self::TYPE_VK => 'VKontakte',
        self::TYPE_ODNOKLASSNIKI => 'Одноклассники'
    );
    
    private $url_prefixes = array(
        self::TYPE_FACEBOOK => 'https://www.facebook.com/app_scoped_user_id/%s/',
        self::TYPE_VK => 'http://vk.com/id%s',
        self::TYPE_ODNOKLASSNIKI => 'http://ok.ru/profile/%s'
    );
    
    private $provider_type;
    private $provider_id;
    
    
    
    public function getShortType()
    {
        if (isset($this->types_short[$this->provider_type])) {
            return $this->types_short[$this->provider_type];
        }
        
        return null;
    }

    

    public function setData($response)
    {
        if (isset($response['auth']['provider'])) {
            $this->setProviderType($response['auth']['provider']);
        }
        
        if (isset($response['auth']['uid'])) {
            $this->setProviderId($response['auth']['uid']);
        }
    }
    
    public function getUser()
    {
        $sql = "SELECT u.uid, u.login, u.passwd, u.role
            FROM {$this->TABLE} AS us 
            INNER JOIN users u ON us.user_id = u.uid
            WHERE provider_type = ?i AND provider_id = ?";
        
        return $this->db()->row($sql, (int)$this->provider_type, $this->provider_id);
    }
    
    public function getUserLinks($user_id)
    {
        $sql = "SELECT provider_type as type, provider_id as pid FROM {$this->TABLE} WHERE user_id = ?i";
        $data = $this->db()->rows($sql, (int)$user_id);
        
        $links = array();
        foreach ($data as $element) {
            $links[$element['type']] = sprintf($this->url_prefixes[$element['type']], $element['pid']);
        }
        return $links;
    }
    
    /**
     * Возвращает тип соцсети, у которой включена двухэтапная аунтификация
     * @param type $user_id
     * @return array|boolean
     */
    public function getMultilevel($user_id)
    {
        $sql = "SELECT provider_type FROM {$this->TABLE} WHERE multilevel = TRUE AND user_id = ?i";
        $provider_type = $this->db()->val($sql, (int)$user_id);
        
        if ($provider_type) {
            return array(
                'type' => $provider_type,
                'name' => $this->type_names[$provider_type]
            );
        }

        return false;
    }
    
    
    /**
     * Добавляет флаг двухэтапной аутентификации 
     * @param type $user_id
     * @param type $type
     * @return type
     */
    public function addMultilevel($user_id, $type)
    {
        $sql = "UPDATE $this->TABLE SET multilevel = (provider_type = ?i) WHERE user_id = ?i";
        return $this->db()->query($sql, $type, $user_id);
    }
    
    
    /**
     * Удаляет флаг двухэтапной аутентификации
     * @param type $user_id
     */
    public function removeMultilevel($user_id)
    {
        $this->db()->update($this->TABLE, array('multilevel' => false), 'user_id = ?i', $user_id);
    }
    
    
    public function create($userId, $is_registration = true, $multilevel = false)
    {
        $this->db()->insert($this->TABLE, array(
            'user_id' => (int) $userId,
            'provider_type' => $this->provider_type,
            'provider_id' => $this->provider_id,
            'registration' => $is_registration,
            'multilevel' => $multilevel
        ));
    }


    private function setProviderType($providerName)
    {
        $this->provider_type = array_search($providerName, $this->types);
    }
    
    private function setProviderId($providerId)
    {
        $this->provider_id = $providerId;
    }
    
    private function db()
    {
        if (!$this->db) {
            global $DB;
            $this->db = $DB;
        }
        return $this->db;
    }
        
}
