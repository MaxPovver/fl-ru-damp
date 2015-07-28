<?php


class Form_Filter_Carusel implements Zend_Filter_Interface
{

    public function filter($value)
    {
        $value = change_q_x($value, TRUE, FALSE);
        $value = strtolower( strtr ( $value, '¨ÉÖÓÊÅÍÃØÙÇÕÚÔÛÂÀÏÐÎËÄÆÝ‗×ÑÌÈÒÜÁÞ', '¸יצףךוםדרשחץתפûגאןנמכהז‎ÿקסלטעüב‏' ) );
        $value = preg_replace('/(^|[.!?]\s+)([a-zא-ÿ])/ie',"'$1'.strtoupper(strtr ( '$2', '¸יצףךוםדרשחץתפûגאןנמכהז‎ÿקסלטעüב‏', '¨ÉÖÓÊÅÍÃØÙÇÕÚÔÛÂÀÏÐÎËÄÆÝ‗×ÑÌÈÒÜÁÞ' ))", $value);
        $value = str_replace("\r\n", "\n", $value);
        return $value;
    }
}
