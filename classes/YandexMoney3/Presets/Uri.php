<?php

namespace YandexMoney3\Presets;


class Uri
{
    //@todo: у яƒ тест работает только припервом подключении
    //const API_TEST = 'https://bo-demo02.yamoney.ru:9094/webservice/deposition/api/%s';
    const API      = 'https://calypso.yamoney.ru:9094/webservice/deposition/api/%s';
    
    const TEST_DEPOSITION                   = 'testDeposition';
    const MAKE_DEPOSITION                   = 'makeDeposition';
    const TEST_IDENTIFICATION_DEPOSITION    = 'testIdentificationDeposition';
    const MAKE_IDENTIFICATION_DEPOSITION    = 'makeIdentificationDeposition';
    const BALANCE                           = 'balance';
} 