<?php

require_once('DocGenFormatter.php');

class DocGenBillFormatter extends DocGenFormatter 
{
    const BANK_INVOICE_NUM = "Б-%06d";
    
    const FIO_JURI_TML  = '%s (в лице _____________________________, действующего на основании ________________)';
    const FIO_JURI_TML2 = '%s «%s» (в лице _____________________________, действующего на основании ________________)';
    
    
    
    public function name($reqv)
    {
        $fio = $reqv['fio'];
        
        if($reqv['type'] === null) { 
            $fio = $reqv['full_name'];
        } elseif($reqv['type'] == sbr_meta::TYPE_IP) { 
            $fio = sbr_meta::$types_short[(int)$reqv['type']] . ' ' . $fio;
        } else {
            $fio = sprintf("%s «%s»", sbr_meta::$types_short[(int)$reqv['type']], $reqv['full_name']); 
        }

        return html_entity_decode($fio, ENT_QUOTES);
    }
    
    
    public function fio($reqvs)
    {
        if(!$reqvs || !$reqvs['form_type']) return false;
        $reqv = $reqvs[$reqvs['form_type']];
        
        $fio = $reqv['fio'];
        if($reqvs['form_type'] == sbr::FT_JURI)
        {
            if($reqv['type'] === null) $fio = sprintf(self::FIO_JURI_TML, $reqv['full_name']);
            elseif($reqv['type'] == sbr_meta::TYPE_IP) $fio = sbr_meta::$types_short[(int)$reqv['type']] . ' ' . $reqv['full_name'];
            else $fio = sprintf(self::FIO_JURI_TML2, sbr_meta::$types_short[(int)$reqv['type']], $reqv['full_name']);
        }
        
        return $fio;
    }
    
    
    public function nonds($value)
    {
        if(!$value) return false;
        
        $value = $value/1.18;
        return $this->price($value);
    }
    
    
    public function nds($value)
    {
        if(!$value) return false;
        
        $value = $value * 18 / 118;
        return $this->price($value);
    }
    
    
    public function num($value)
    {
        return sprintf(self::BANK_INVOICE_NUM, $value);
    }
    
    
    public function price($value)
    {
        $value = round($value, 2);
        return number_format($value, 2, ',', '');
    }
    
    
    public function pricelong($value)
    {
        $value = round($value, 2);
        return num2strL($value) . ' (' . num2str($value) . ')';
    }
    
    
    public function pricends($value)
    {
        if(!$value) return false;
        
        $value = $value * 18 / 118;
        return $this->price($value);
    }
}