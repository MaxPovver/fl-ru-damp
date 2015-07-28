<?php
require_once(__DIR__ . '/../models/GuestNewProjectForm.php');
require_once(__DIR__ . '/../models/GuestNewVacancyForm.php');


class GuestHelper {
    
    /**
     * ѕреобразует данные из массива
     * 
     * @param array $post
     * @return array
     */
    public static function overrideData($post)
    {
        if (!isset($post['kind'])) {
            return false;
        }
        
        $form = $post['kind'] == 4
                ? new GuestNewVacancyForm(array('is_adm' => false))
                : new GuestNewProjectForm(array('is_adm' => false));

        //MultiDropdown работает только с $_POST
        if (isset($post['el-location_columns'])) {
            $_POST['el-location_columns'] = $post['el-location_columns'];
        }
        
        $form->populate($post);
        
        $data = $form->getValues();
        
        $data['kind'] = $post['kind'];
        
        if ($data['kind'] == 4) {
            unset($data['location']);
            $data['country'] = $form->getElement('location')->getColumnId(0);
            $data['city'] = $form->getElement('location')->getColumnId(1);
        }
        
        unset($data['profession']);
        $data['categories'][] = array(
            'category_id' => $form->getElement('profession')->getGroupDbIdValue(),
            'subcategory_id' => $form->getElement('profession')->getSpecDbIdValue()
        );

        $data['IDResource'] = @$data['IDResource'][0];
        
        $cost_element = $form->getElement('cost');
        $is_agreement = $cost_element->getValue('agreement') == 1;
        $data['cost'] = $is_agreement ? 0 : $data['cost'];
        $data['currency'] = $is_agreement ? 0 : $cost_element->getValue('currency_db_id');
        $data['priceby'] = $is_agreement ? 1 : $cost_element->getValue('priceby_db_id');
        $data['agreement'] = $is_agreement;
        
        $filter = isset($data['filter']) && $data['filter'] ? $data['filter'] : array();
        
        $data['pro_only'] = in_array('pro_only', $filter);
        $data['verify_only'] = in_array('verify_only', $filter);
        unset($data['filter']);
        
        return $data;
    }
    
    
    /**
     * ѕреобразует данные из строки
     * 
     * @param string $query
     * @return array
     */
    public static function overrideDataFromString ($query)
    {
        $post = array();
        parse_str($query, $post);

        $post = encodeCharset2('utf-8', 'cp1251', $post);

        return self::overrideData($post);
    }
}
