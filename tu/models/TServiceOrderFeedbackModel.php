<?php

require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/tservices/validation.php");


class TServiceOrderFeedbackModel extends atservices_model 
{
        const LIFETIME = 604800;//7*24*60*60;
    
        protected $feedback;
        protected $rating;
        protected $user_id;
        protected $modified_id;
        protected $is_emp;

        private $TABLE          = 'tservices_orders_feedbacks';
        private $TABLE_ORDERS   = 'tservices_orders';

        protected $errors = array(1);
        protected $current_attributes = array();




        public function is_valid()
        {
            return empty($this->errors);
        }


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
    
    
        public function deleteFeedback($feedback_id)
        {
            return $this->db()->update($this->TABLE,array(
                'deleted' => 't',
                'r_switch' => 'f',
                'modified_id' => $this->modified_id,
                'update_time' => date('Y-m-d H:i:s')
            ),'id = ?i',$feedback_id);
        }

        

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

        
        
        public function addFeedback($order_id)
        {
            /*
            $id = $this->db()->insert($this->TABLE, array(
                'order_id' => $order_id,
                'user_id' => $this->user_id,
                'feedback' => $this->feedback,
                'rating' => $this->rating
            ),'id');
            */
            
            $id = $this->db()->val("
                INSERT INTO {$this->TABLE} (order_id, user_id, feedback, rating) 
                SELECT ?i, ?i, ?, ?i 
                WHERE NOT EXISTS(
                    SELECT 1 FROM {$this->TABLE} 
                    WHERE order_id = ?i 
                          AND user_id = ?i
                          AND deleted = FALSE
                    LIMIT 1
                ) RETURNING id;
            ", 
                $order_id, 
                $this->user_id, 
                $this->feedback, 
                $this->rating, 
                $order_id,
                $this->user_id                
            ); 
            
            if ($id > 0) {

                $prefix = $this->is_emp?'emp':'frl';

                $this->db()->update($this->TABLE_ORDERS,array(
                    "{$prefix}_feedback_id" => $id,
                    "date_{$prefix}_last" => 'NOW()'
                ),'id = ?i',$order_id);
                
                return $id;
            }
            
            return false;
        }
        
        
        
        public function getFeedback($feedback_id)
        {
            return $this->db()->row("
                SELECT
                    fb.id,
                    fb.user_id,
                    fb.feedback,
                    fb.posted_time,
                    fb.rating,
                    o.frl_id,
                    o.emp_id
                FROM {$this->TABLE} AS fb
                INNER JOIN {$this->TABLE_ORDERS} AS o 
                ON (o.emp_feedback_id = fb.id OR o.frl_feedback_id = fb.id) 
                WHERE
                    fb.deleted = FALSE 
                    AND fb.id = ?i
            ",$feedback_id);
        }
        
        
        
        public static function isAllowFeedback($close_date)
        {
            if(!$close_date) return FALSE;
            return ((strtotime($close_date) + static::LIFETIME) >= time());
        }
        
        /*
        public static function isAllowEditFeedBack($theme, $uid)
        {
            $is_time = ((strtotime($theme['posted_time']) + TServiceOrderFeedbackModel::LIFETIME) >= time());
            $is_allow_edit = ($theme['touser_id'] == $uid && $is_time);
            return $is_allow_edit;
        }*/
}