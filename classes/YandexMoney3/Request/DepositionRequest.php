<?php

namespace YandexMoney3\Request;

require_once(__DIR__ . '/BaseRequest.php');

use YandexMoney3\Presets\ApiKey;


class DepositionRequest extends BaseRequest 
{
    
    public function __construct() 
    {
        parent::__construct();
        $this->setContract('');
    }
    
    
    public function setSubAgentId($subAgentId)
    {
        $this->setAttr(ApiKey::CLIENT_ORDER_ID, $subAgentId);
    }
    
    
    public function setDstAccount($dstAccount)
    {
        $this->setAttr(ApiKey::DST_ACCOUNT, $dstAccount);
    }
    
    
    public function setAmount($amount)
    {
        $this->setAttr(ApiKey::AMOUNT, $amount);
    }
    
    
    public function setCurrency($currency)
    {
        $this->setAttr(ApiKey::CURRENCY, $currency);
    }
    
    
    public function setContract($contract)
    {
        $this->setAttr(ApiKey::CONTRACT, $contract);
    }
    
    
    
    
    public function setSkrDestinationCardSynonim($value)
    {
        $this->setPaymentParams(ApiKey::SKR_DESTINATION_CARD_SYNONIM, $value);
    }

    public function setPdrFirstName($value)
    {
        $this->setPaymentParams(ApiKey::PDR_FIRSTNAME, $value);
    }

    /*
    public function setPdrSecondName($value)
    {
        $this->setPaymentParams(ApiKey::PDR_SECONDNAME, $value);
    }
    */
    
    public function setPdrMiddleName($value)
    {
        $this->setPaymentParams(ApiKey::PDR_MIDDLENAME, $value);
    }

    public function setPdrLastName($value)
    {
        $this->setPaymentParams(ApiKey::PDR_LASTNAME, $value);
    }
    
    public function setPdrDocType($value)
    {
        $this->setPaymentParams(ApiKey::PDR_DOC_TYPE, $value);
    }
    
    /*
    public function setPdrDocNum($value)
    {
        $this->setPaymentParams(ApiKey::PDR_DOC_NUM, $value);
    }     
    */
    
    public function setPdrDocNumber($value)
    {
        $this->setPaymentParams(ApiKey::PDR_DOC_NUMBER, $value);
    }

    public function setPdrDocIssueYear($value)
    {
        $this->setPaymentParams(ApiKey::PDR_DOC_ISSUE_YEAR, $value);
    }

    public function setPdrDocIssueMonth($value)
    {
        $this->setPaymentParams(ApiKey::PDR_DOC_ISSUE_MONTH, $value);
    }  
    
    public function setPdrDocIssueDay($value)
    {
        $this->setPaymentParams(ApiKey::PDR_DOC_ISSUE_DAY, $value);
    } 
    
    public function setSmsPhoneNumber($value)
    {
        $this->setPaymentParams(ApiKey::SMS_PHONE_NUMBER, $value);
    }

    public function setPdrInn($value)
    {
        $this->setPaymentParams(ApiKey::PDR_INN, $value);
    }

    public function setPdrSnils($value)
    {
        $this->setPaymentParams(ApiKey::PDR_SNILS, $value);
    }    
    
    public function setPdrCountry($value)
    {
        $this->setPaymentParams(ApiKey::PDR_COUNTRY, $value);
    }
    
    public function setPdrCity($value)
    {
        $this->setPaymentParams(ApiKey::PDR_CITY, $value);
    }
    
    public function setPdrPostcode($value)
    {
        $this->setPaymentParams(ApiKey::PDR_POSTCODE, $value);
    }
    
    public function setPdrBirthDate($value)
    {
        $this->setPaymentParams(ApiKey::PDR_BIRTH_DATE, $value);
    }
    
    public function setPdrBirthPlace($value)
    {
        $this->setPaymentParams(ApiKey::PDR_BIRTH_PLACE, $value);
    }
    
    public function setPdrDocIssuedBy($value)
    {
        $this->setPaymentParams(ApiKey::PDR_DOC_ISSUED_BY, $value);
    }
    
    public function setPdrAddress($value)
    {
        $this->setPaymentParams(ApiKey::PDR_ADDRESS, $value);
    }

    public function setPofOfferAccepted($value)
    {
        $this->setPaymentParams(ApiKey::POF_OFFER_ACCEPTED, $value);
    }

    public function setBankName($value)
    {
        $this->setPaymentParams(ApiKey::BANK_NAME, $value);
    }

    public function setBankCity($value)
    {
        $this->setPaymentParams(ApiKey::BANK_CITY, $value);
    }    

    public function setBankBIK($value)
    {
        $this->setPaymentParams(ApiKey::BANK_BIK, $value);
    }
    
    public function setBankCorAccount($value)
    {
        $this->setPaymentParams(ApiKey::BANK_COR_ACCOUNT, $value);
    }
    
    public function setBankKPP($value)
    {
        $this->setPaymentParams(ApiKey::BANK_KPP, $value);
    }
    
    public function setBankINN($value)
    {
        $this->setPaymentParams(ApiKey::BANK_INN, $value);
    }    
    
    public function setDepositAccount($value)
    {
        $this->setPaymentParams(ApiKey::DEPOSIT_ACCOUNT, $value);
    }

    public function setFaceAccount($value)
    {
        $this->setPaymentParams(ApiKey::FACE_ACCOUNT, $value);
    }

    public function setRubAccount($value)
    {
        $this->setPaymentParams(ApiKey::RUB_ACCOUNT, $value);
    }

    public function setCustAccount($value)
    {
        $this->setPaymentParams(ApiKey::CUST_ACCOUNT, $value);
    }
    
    public function setTmpLastName($value)
    {
        $this->setPaymentParams(ApiKey::TMP_LAST_NAME, $value);
    }

    public function setTmpFirstName($value)
    {
        $this->setPaymentParams(ApiKey::TMP_FIRST_NAME, $value);
    }

    public function setTmpMiddleName($value)
    {
        $this->setPaymentParams(ApiKey::TMP_MIDDLE_NAME, $value);
    }

    
    


    protected function setPaymentParams($key, $value)
    {
        $this->paramsArray['paymentParams'][$key] = $value;
    }
    
    
    
}
