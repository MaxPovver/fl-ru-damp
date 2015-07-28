<?php

namespace YandexMoney3\Presets;


class ApiKey
{
    //Deposition & Balance
    const AGENT_ID          = 'agentId';
    const SUBAGENT_ID       = 'subAgentId';
    const CLIENT_ORDER_ID   = 'clientOrderId';
    const REQUEST_DT        = 'requestDT';
    const DST_ACCOUNT       = 'dstAccount';
    const AMOUNT            = 'amount';
    const CURRENCY          = 'currency';
    const CONTRACT          = 'contract';
    
    //Identification
    const DOC_TYPE          = 'docType';
    const DOC_NUMBER        = 'docNumber';
    const ISSUE_DATE        = 'issueDate';
    const AUTHORITY_NAME    = 'authorityName';
    const AUTHORITY_CODE    = 'authorityCode'; 
    const RESIDENCE         = 'residence';
    const NATIONALITY       = 'nationality';
    const BIRTH_DATE        = 'birthDate';
    const BIRTH_PLACE       = 'birthPlace';
    const SURNAME           = 'surname';
    const NAME              = 'name';
    const PATRONYMIC        = 'patronymic';
    
    //PaymentParams
    const SKR_DESTINATION_CARD_SYNONIM  = 'skr_destinationCardSynonim';
    const PDR_FIRSTNAME                 = 'pdr_firstName';
    const POF_OFFER_ACCEPTED            = 'pof_offerAccepted';
    //const PDR_SECONDNAME                = 'pdr_secondName';
    const PDR_MIDDLENAME                = 'pdr_middleName';
    const PDR_LASTNAME                  = 'pdr_lastName';
    const CPS_PHONENUMBER               = 'cps_phoneNumber';
    const PDR_DOC_TYPE                  = 'pdr_docType';
    const PDR_DOC_NUM                   = 'pdr_docNum';
    const PDR_POSTCODE                  = 'pdr_postcode';
    const PDR_COUNTRY                   = 'pdr_country';
    const PDR_CITY                      = 'pdr_city';
    const PDR_ADDRESS                   = 'pdr_address';
    const PDR_DOC_NUMBER                = 'pdr_docNumber';
    const PDR_DOC_ISSUE_YEAR            = 'pdr_docIssueYear';
    const PDR_DOC_ISSUE_MONTH           = 'pdr_docIssueMonth';
    const PDR_DOC_ISSUE_DAY             = 'pdr_docIssueDay';
    const PDR_INN                       = 'pdr_inn';
    const PDR_SNILS                     = 'pdr_snils';
    const PDR_BIRTH_DATE                = 'pdr_birthDate';
    const PDR_BIRTH_PLACE               = 'pdr_birthPlace';
    const PDR_DOC_ISSUED_BY             = 'pdr_docIssuedBy';
    const SMS_PHONE_NUMBER              = 'smsPhoneNumber';
    const BANK_NAME                     = 'BankName';
    const BANK_CITY                     = 'BankCity';
    const BANK_BIK                      = 'BankBIK';
    const BANK_COR_ACCOUNT              = 'BankCorAccount';
    const BANK_KPP                      = 'BankKPP';
    const BANK_INN                      = 'BankINN';
    const DEPOSIT_ACCOUNT               = 'DepositAccount';
    const FACE_ACCOUNT                  = 'FaceAccount';
    const RUB_ACCOUNT                   = 'RubAccount';
    const TMP_LAST_NAME                 = 'tmpLastName';
    const TMP_FIRST_NAME                = 'tmpFirstName';
    const TMP_MIDDLE_NAME               = 'tmpMiddleName';
    const CUST_ACCOUNT                  = 'CustAccount';
    const CUST_CARD                     = 'CustCard';
    const PROPERTY1                     = 'Property1';
    const PROPERTY2                     = 'Property2';
}