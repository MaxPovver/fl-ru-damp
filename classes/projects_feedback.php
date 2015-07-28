<?php

/**
 * Класс projects_feedback
 * Модель для работы с отзывами по проекту 
 */
class projects_feedback 
{
        const LIFETIME = 604800;//7*24*60*60;
    
        protected $feedback;
        protected $rating;
        protected $user_id;
        protected $modified_id;
        protected $is_emp;
        protected $show;
        protected $touser_id;

        private $TABLE              = 'projects_feedbacks';
        private $TABLE_PROJECTS     = 'projects';

        protected $errors = array(1);
        protected $current_attributes = array();


        //----------------------------------------------------------------------


        public function is_valid()
        {
            return empty($this->errors);
        }


        //----------------------------------------------------------------------
        
        
        /**
         * Проверка атрибутов класса
         * 
         * @param string $key
         * @param mixed $value
         * @return boolean
         */
        public function validation($key, $value)
        {
            $error = FALSE;
            
            switch($key)
            {
                case 'feedback': 
                    $error = empty($value) || (strlen($value) > 500); 
                    break;
                case 'rating':
                    $error = !in_array($value, array('1','-1'));
                    break;
                case 'user_id':
                case 'modified_id':
                    $error = !(is_int($value) && ($value > 0));
                    break;
            }
            
            if($error) 
            {
                $this->errors[$key] = $error;
                return FALSE;
            }
            
            return TRUE;
        }
        
        
        //----------------------------------------------------------------------
        
        
        /**
         * Фильтр атрибутов класса
         * 
         * @param string $key
         * @param mixed $value
         * @return mixed
         */
        public function filter($key, $value)
        {
            switch($key)
            {
                case 'feedback':
                    $value = substr(trim(htmlspecialchars(stripslashes($value))),0,500);
                    break;
                case 'user_id':
                case 'modified_id':
                    $value = intval($value);
                    break;
            }
            
            return $value;
        }

        
        
        
        //----------------------------------------------------------------------



        /**
         * Инициализация или получение аттрибутов класса
         * 
         * @param array $attributes
         * @return type
         */
        public function attributes($attributes = null) 
        {
            if (is_null($attributes)) 
            {
                return get_object_vars($this);
            }
            
            $this->errors = array();
            
            foreach ($attributes as $key => $value) 
            {
                if (property_exists($this, $key)) 
                {
                    $value = $this->filter($key, $value);
                    $is_valid = $this->validation($key, $value);
                    if($is_valid) 
                    {
                        $this->current_attributes[] = $key;
                        $this->{$key} = $value;
                    }
                }
            }
            
            return $this->is_valid();
        }
    
    
        //----------------------------------------------------------------------
        
        
        /**
         * Помечаем отзы как удаленный
         * 
         * @param int $feedback_id
         * @return boolean
         */
        public function deleteFeedback($feedback_id)
        {
            return $this->db()->update($this->TABLE,array(
                'deleted' => 't',
                'modified_id' => $this->modified_id,
                'update_time' => date('Y-m-d H:i:s')
            ),'id = ?i',$feedback_id);
        }

        
        //----------------------------------------------------------------------
        
        
        /**
         * Обновление отзыва
         * 
         * @param int $feedback_id
         * @return boolean
         */
        public function updateFeedback($feedback_id)
        {
            if(empty($this->current_attributes)) return FALSE;
            
            $data = array();
            
            foreach($this->current_attributes as $attr_key)
            {
                $data[$attr_key] = $this->{$attr_key};
            }
            
            $data['update_time'] = date('Y-m-d H:i:s');
            return $this->db()->update($this->TABLE,$data,'id = ?i',$feedback_id); 
        }

        
        //----------------------------------------------------------------------
        

        /**
         * Добавление отзыва
         * 
         * @param int $project_id
         * @return int ID
         */
        public function addFeedback($project_id)
        {
            return $this->db()->insert($this->TABLE, array(
                'project_id' => $project_id,
                'user_id' => $this->user_id,
                'feedback' => $this->feedback,
                'rating' => $this->rating,
                'is_emp' => $this->is_emp,
                'show' => $this->show,
                'touser_id' => $this->touser_id
            ),'id');
        }
        
        
        //----------------------------------------------------------------------
        
        
        public function getFeedbackByProjectID($project_id)
        {
            $result = array();
            
            $rows = $this->db()->rows("
                SELECT
                    *
                FROM {$this->TABLE} 
                WHERE 
                    project_id = ?i
                    AND deleted = FALSE 
                LIMIT 2
            ",$project_id);
                
            if(!$rows) return $result;
            
            foreach($rows as $el)
            {
                $prefix = ($el['is_emp'] == 't')?'emp':'frl';
                $result[$prefix . '_feedback'] = $el['feedback'];
                $result[$prefix . '_posted_time'] = $el['posted_time'];
                $result[$prefix . '_rating'] = $el['rating'];
            }
        
            return $result;
        }


        //----------------------------------------------------------------------
        

        public function getFeedback($feedback_id)
        {
            return $this->db()->row("
                SELECT
                    fb.id,
                    fb.feedback,
                    fb.posted_time,
                    fb.rating,
                    fb.user_id
                FROM {$this->TABLE} AS fb
                INNER JOIN {$this->TABLE_PROJECTS} AS p ON p.id = fb.project_id
                WHERE
                    fb.deleted = FALSE 
                    AND fb.id = ?i
            ",$feedback_id);
        }
        
        //----------------------------------------------------------------------
        
        
        /**
         * Проверка разрешено ли еще добавлять редактировать отзывы
         * 
         * @param string $close_date
         * @return boolean
         */
        public static function isAllowFeedback($close_date)
        {
            if(!$close_date) return FALSE;
            return ((strtotime($close_date) + static::LIFETIME) >= time());
        }
        

        
        //----------------------------------------------------------------------    
        
        
        
        /**
        * @return DB
        */
        public function db()
        {
            return $GLOBALS['DB'];
        }
}