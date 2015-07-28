   /**
    * Зависит от:
    * b-combo-dynamic-input.js
    * b-combo-multi_dropdown.js
    */
    var countryPhoneCodes = {0:'7:Россия:-660',1:'380:Украина:-2002',2:'375:Беларусь:-1100',3:'77:Казахстан:-1210',4:'373:Молдова:-2685',5:'998:Узбекистан:-1001',6:'371:Латвия:-1936',7:'49:Германия:-2509',8:'1:США:-44',9:'972:Израиль:-341',10:'370:Литва:-1122',11:'372:Эстония:-2410',12:'374:Армения:-176',13:'994:Азербайджан:-1243',14:'61:Австралия:-1716',15:'420:Чехия:-2256',16:'44:Великобритания:-55',17:'33:Франция:-1012',18:'1:Канада:-1375',19:'996:Кыргызстан:-1617',20:'995:Грузия:-858',21:'43:Австрия:-1331',22:'359:Болгария:-2586',23:'34:Испания:-1155',24:'48:Польша:-1177',25:'39:Италия:-143',27:'32:Бельгия:0',28:'358:Финляндия:-1903',29:'30:Греция:-165',30:'1876:Ямайка:-1727',31:'64:Новая Зеландия:-1540',32:'504:Гондурас:-2156',33:'90:Турция:-1606',34:'1441:Бермуды:-1914',35:'54:Аргентина:-2377',36:'81:Япония:-429',37:'1340:Американские Виргинские острова:-1782',38:'297:Аруба:-792',39:'599:Бонэйр, Синт-Эстатиус и Саба:-2719',40:'1284:Британские Виргинские острова:-1408',41:'226:Буркина-Фасо:-726',42:'379:Ватикан:-2322',43:'299:Гренландия:-1760',44:'1671:Гуам:-2366',45:'246:Диего-Гарсия:-55',46:'1345:Каймановы острова:-308',47:'599:Кюрасао:-2729',48:'596:Мартиника:-198',49:'692:Маршалловы острова:-1144',50:'1664:Монсеррат:-583',51:'31:Нидерланды:-1441',52:'683:Ниуэ:-2079',53:'687:Новая Каледония:-1276',54:'971:Объединенные Арабские Эмираты:-2223',55:'247:Остров Вознесения:-55',56:'6723:Остров Норфолк:-209',57:'290:Остров Святой Елены:-495',58:'682:Острова Кука:-2267',59:'1649:Острова Тёркс и Кайкос:-1309',60:'970:Палестина:-1199',61:'1:Северные Марианские острова:-704',62:'248:Сейшелы:-1045',63:'590:Сен-Бартелеми:-1012',64:'590:Сен-Мартен:-55',65:'1721:Сен-Мартен (нидерландская часть):-2773',66:'508:Сен-Пьер и Микелон:-1078',67:'1869:Сент-Китс и Невис:-99',68:'1758:Сент-Люсия:-1397',69:'690:Токелау:-2751',70:'681:Уоллис и Футуна:-1012',71:'298:Фарерские острова:-1111',72:'500:Фолклендские острова:-2762',73:'594:Французская Гвиана:-2234',74:'689:Французская Полинезия:-1705',75:'236:Центрально-Африканская Республика:-1837',78:'352:Люксембург:-1474',79:'423:Лихтенштейн:-979',81:'264:Намибия:-1881',82:'261:Мадагаскар:-1287',83:'218:Ливия:-132',84:'960:Мальдивы:-616',85:'65:Сингапур:-22',86:'1767:Доминика:-2432',87:'1868:Тринидад и Тобаго:-440',88:'234:Нигерия:-2476',89:'855:Камбоджа:-242',90:'964:Ирак:-649',91:'973:Бахрейн:-1496',92:'82:Южная Корея:-2245',93:'686:Кирибати:-374',94:'245:Гвинея-Бисау:-1925',95:'507:Панама:-847',96:'1473:Гренада:-2399',98:'213:Алжир:-528',99:'962:Иордания:-1463',100:'1784:Сент-Винсент и Гренадины:-2619',101:'95:Мьянма:-11',102:'291:Эритрея:-715',103:'676:Тонга:-1089',105:'53:Куба:-748',106:'94:Шри-Ланка:-2641',107:'965:Кувейт:-2487',108:'1787:Пуэрто-Рико:-473',109:'975:Бутан:-1848',110:'253:Джибути:-2101',111:'211:Южный Судан:-2741',112:'856:Лаос:-451',113:'597:Суринам:-2663',114:'258:Мозамбик:-638',115:'212:Марокко:-2333',116:'503:Сальвадор:-1639',117:'240:Экваториальная Гвинея:-1507',118:'231:Либерия:-2068',119:'225:Кот-д\Ивуар:-1661',120:'267:Ботсвана:-2707',121:'93:Афганистан:-2311',122:'84:Вьетнам:-968',124:'389:Македония:-1353',125:'853:Макао:-2597',126:'252:Сомали:-1364',127:'967:Йемен:-1672',128:'263:Зимбабве:-2046',129:'254:Кения:-2630',130:'968:Оман:-2454',131:'376:Андорра:-594',133:'691:Микронезия:-1738',134:'1264:Ангилья:-1980',135:'501:Белиз:-484',136:'60:Малайзия:-1870',137:'266:Лесото:-2190',138:'670:Восточный Тимор:-2784',139:'243:Конго, Демократическая Республика:-1518',140:'386:Словения:-1221',141:'593:Эквадор:-1188',142:'350:Гибралтар:-275',143:'880:Бангладеш:-1771',144:'378:Сан-Марино:-2123',145:'688:Тувалу:-286',146:'241:Габон:-880',147:'92:Пакистан:-2035',148:'63:Филиппины:-1815',149:'222:Мавритания:-253',150:'382:Черногория:-2167',151:'1268:Антигуа и Барбуда:-869',152:'260:Замбия:-1595',153:'591:Боливия:-1650',154:'598:Уругвай:-2608',155:'269:Коморы и Майотта:-1430',156:'502:Гватемала:-935',157:'974:Катар:-462',158:'62:Индонезия:-1958',159:'257:Бурунди:-1892',161:'421:Словакия:-2212',163:'674:Науру:-1749',164:'220:Гамбия:-627',165:'223:Мали:-2520',166:'685:Самоа:-2300',167:'976:Монголия:-2553',168:'229:Бенин:-1298',170:'255:Танзания:-2289',171:'251:Эфиопия:-2443',172:'250:Руанда:-2674',173:'216:Тунис:-539',174:'232:Сьерра-Леоне:-737',175:'221:Сенегал:-2134',177:'233:Гана:-2112',178:'354:Исландия:-1991',179:'679:Фиджи:-1859',180:'977:Непал:-110',182:'268:Свазиленд:-2278',183:'58:Венесуэла:-1056',184:'966:Саудовская Аравия:-33',185:'1246:Барбадос:-1573',186:'595:Парагвай:-2344',187:'230:Маврикий:-2179',188:'678:Вануату:-1265',189:'238:Кабо-Верде:-2652',190:'265:Малави:-2145',191:'244:Ангола:-1947',192:'963:Сирия:-1826',193:'235:Чад:-814',194:'592:Гайана:-803',195:'228:Того:-605',196:'673:Бруней:-1683',197:'57:Колумбия:-330',198:'505:Никарагуа:-154',199:'387:Босния и Герцеговина:-1584',200:'237:Камерун:-2057',201:'677:Соломоновы Острова:-1067',202:'680:Палау:-231',203:'239:Сан-Томе и Принсипи:-2388',206:'262:Реюньон:-264',207:'66:Таиланд:-957',208:'86:Китай:-825',209:'20:Египет:-2201',210:'355:Албания:-1034',211:'1684:Американское Самоа:-1562',212:'1242:Багамы:-363',213:'55:Бразилия:-770',214:'36:Венгрия:-682',215:'509:Гаити:-319',216:'590:Гваделупа:-407',217:'224:Гвинея:-2575',218:'852:Гонконг:-2696',219:'45:Дания:-1386',220:'1809:Доминиканская Республика:-1529',221:'91:Индия:-1694',222:'98:Иран:-2013',223:'353:Ирландия:-1969',224:'357:Кипр:-561',225:'242:Конго:-1793',226:'506:Коста-Рика:-2090',227:'225:Кот-д\'Ивуар:-1661',228:'961:Ливан:-1254',229:'356:Мальта:-1551',230:'52:Мексика:-2024',231:'377:Монако:-913',232:'227:Нигер:-550',233:'47:Норвегия:-836',234:'675:Папуа – Новая Гвинея:-1485',235:'51:Перу:-946',236:'351:Португалия:-517',237:'40:Румыния:-671',238:'850:Северная Корея:-1804',239:'381:Сербия:-2465',240:'249:Судан:-352',241:'992:Таджикистан:-187',242:'886:Тайвань:-506',243:'66:Таиланд:-957',244:'993:Туркменистан:-2542',245:'256:Уганда:-1166',246:'385:Хорватия:-902',247:'56:Чили:-1342',248:'41:Швейцария:-1320',249:'46:Швеция:-385',250:'27:ЮАР:-2355'};
     
    var countryPhoneCodesQiwi = {0:'7:Россия:-660',1:'77:Казахстан:-1210'};
   //Определение класса  CPhoneCodesCountries
    /**
    *  Вызывает инициализацию CMultiLevelDropDown  и добавляет специфические для списка телефонов нюансы
    * @param HtmlDivElement htmlDiv
    * @param Array           cssSelectors
    */
    function CPhoneCodesCountries(htmlDiv, cssSelectors) {
        this.initMultilevelDropDown(htmlDiv, cssSelectors);        //наследуемся от CMultiLevelDropDown
        this.shadow.setStyle("width", "310px");
        this.countryCode = 7;
        this.outerDiv.getElements("input[type=hidden]").dispose();
        // не когда было разбиратся в том как это все должно нормально работать @todo переделать нормально
        if(this.outerDiv.hasClass("b-combo__input_disabled")) {
            var code = this.parseCode(this.b_input.value);
            this.b_input.value = this.b_input.value.replace("+", "");
            var style = "background-position: 0px "+ code.data.split(":")[2] + "px; cursor: pointer;"
            this.setCode(style, '');
        }
    }
    CPhoneCodesCountries.prototype = new CMultiLevelDropDown();    //наследуемся от CMultiLevelDropDown
    /**
     * Получить из номера код
     * @param v - телефонный номер
     * @return mixed {idx: код страны, data: "код:НазваниеСтраны:позиция флага страны на изображении флагов"} or FALSE
     */
    CPhoneCodesCountries.prototype.parseCode = function(v) {
        var _countryPhoneCodes = window[ this.initVarName ];
        function getCountryPhoneCodes(N) {
            for (var i in _countryPhoneCodes) {
                if (String(_countryPhoneCodes[i]).indexOf(":") != -1) {
                    var j = _countryPhoneCodes[i].split(":")[0];
                    if (j == N) {
                        return _countryPhoneCodes[i];
                    }
                }
            }
            return -1;
        }
        var L = 4;
        if (v.indexOf("+") == 0) {
            L = 5;
        }
        var sbstr = v.substring(0, L);
        for (var i = L; i > -1; i--) {
            var idx = sbstr.substring(0, i);
            idx = idx.replace("+", "").trim();
            var data = getCountryPhoneCodes(idx);
            if (idx.length && data != -1) {
                return {data:data, idx:idx};
            }
        }
        return false;
    }
    
    /**
    * после того как данные списка загружены, устанавливает необходимые слушатели событий
    * @param v - значение атрибута input.value
    * @param n - номер столбца
    */
    CPhoneCodesCountries.prototype.fillColumn = function(v, n) {
        var o = this.parseCode(v);
        if (!o) {
            return;
        }
        var idx = o.idx;
        var data = o.data;
        var a = data.split(":");
        this.setCode("background-position: 0px " + a[2] + "px; cursor: pointer;", idx, true, false);
        var re = new RegExp("^\\+?\\d{" + String(idx).length + "}\\s?");
        this.b_input.value = this.b_input.value.replace(re, "+" + this.countryCode + (this.lastKey == 8?'':""));
        var ls = this.columns[0].getElements("li");
        ls.removeClass(this.HOVER_CSS);
        for (var i = 0; i < ls.length; i++) {
            if ( ls[i].getElement("span").getProperty("dbid") == idx) {
                ls[i].addClass(this.HOVER_CSS);
                break;
            }
        }
        
    }
    /**
    * после того как данные списка загружены, устанавливает необходимые слушатели событий
    */
    CPhoneCodesCountries.prototype.setEventListeners = function() {
        this.outerDiv.setProperty("valueContainer", "li");
        var toggler = this.outerDiv.getElement('span.b-combo__tel');
        if (!toggler) toggler = this.outerDiv;
        this.toggler = toggler;
        toggler.self = this;
        toggler.addEvent('click', this.onToggle);
        this.b_input.addEvent('click', function() {this.self.show(0);} );
        
        this.b_input.self = this;
        this.b_input.addEvent("keyup", this.onKeyUp);
        this.b_input.addEvent("blur", function ()  {
            var self = this.self;
            self.fillColumn(self.b_input.value, 0);
            if ( self.outerDiv.hasClass("b-combo__input_disabled") ) {
                return;
            }
            if (self.b_input.value == "" && !self.isEmpty() && (!self.ALLOW_CREATE_VALUE || self.selectors.indexOf(" disallow_null") != -1)) {
                self.err =1;
            }
        });
        toggler.self = this;
        
        if ( parseInt( this.b_input.value.replace(/[\D]/, '') ) ) {
            var v = this.b_input.value;
            this.onKeyUp({code:0});
            var a = [], L = 23, c = 0, n = 0;
            for (var i = v.length - 1; i > -1; i--, c++, n++) {
                a.push(v.charAt(i) );
                if (c == L && n < 11) {
                    a.push(' ');
                    c = 0;
                }
            }
            a = a.reverse();
            v = a.join('');
            this.b_input.value = "+" + v;
        }
    }
    
    /**
    *@param int index              - идентификатор элемента из таблицы БД
    *@param String       value     - отображаемое значение
    *@param unsigned int column    - уровень вложенности. 
    *@param int          parentId  - идентификатор записи-родителя в таблице БД.
    *@param Bool         nocache    = false   - кешировать ли в this.columnsCache.
    *@param Bool         append     = true    - добавлять ли в список.
    *@param Bool         clickable  = true    - добавлять ли атрибут onclick.
    *@param String       extendsSelectors  = '' если аргумент не пуст, будут добавлены в атрибут class
    */
    CPhoneCodesCountries.prototype.addItem = function (index, text, column, parentId, nocache, append, clickable, extendsSelectors) {
        index = parseInt(index);
        if (!index) {
            index = 0;
        }
        if (this.labels[column]) {
            if (this.labels[column].id  == index) text = this.labels[column].text;
        }
        if (this.exclude[column]) {
            if (this.exclude[column].id == index) return;
        }
        if (!extendsSelectors) extendsSelectors = '';
            else extendsSelectors = ' ' + extendsSelectors;
        var ul = this.columns[column];
        if (!ul) {         
            this.appendColumn(this.defaultColumnCss, this.row);
            ul = this.columns[column];
        }
        var td = ul.parentNode;
        td.style.display = "";
        if (String(append) == "undefined")    append = 1;
        if (String(clickable) == "undefined") clickable = 1;
        
        
        if (append) {
            var content = text.split(":");
            var li = new Element('li', {'class':'b-combo__item b-combo__txt b-combo__txt_tel', 'html':(content[1] + " +" + content[0])});
            li.inject(ul, 'bottom');
            var span = new Element('span', {'class':'b-combo__flag' + extendsSelectors, 'html':''});
            span.inject(li, 'top');
            if (extendsSelectors.indexOf(this.HOVER_CSS) != -1) {
                var w = this.outerDiv.getStyle("width");
                if (w) {
                    span.setStyle("min-width",  w);
                }
            }
            span.setStyle("background-position",  "0 " + content[2] + "px");
            span.setProperty('dbid' , content[0]);
            if (parentId) span.setProperty('dbprid' , parentId);
            span.self = this;
            li.self = this;
            if (clickable) {
                //span.addEvent('click', this.onItemClick);
                li.addEvent('click', this.onItemClick);
                li.addEvent('mouseover', this.onItemHover);
                span.setStyle("cursor", "pointer");
                li.setStyle("cursor", "pointer");
            }else {
                span.setStyle("cursor", "default");
                li.setStyle("cursor", "default");
            }
        }
    }
    
    /**
    * Строим контейнер, в котором будут располагаться колонки списка
    */
    CPhoneCodesCountries.prototype.buildTable = function() {
     var p = this.extendElementPlace;
     this.columns      = new Array();
     this.columnsCache = new Array();
     var ul = new Element('ul', {'class':'b-combo__list b-combo__body_overflow-x_yes'});
     ul.inject(p, 'bottom');
     this.columns.push(ul);
     this.columnsCache.push(new Array());
     this.breadCrumbs.push(-1);
     this.defaultColumnCss = 'b-layout__right b-layout__right_bordleft_cdd1d3 b-layout__right_hide';
    }
    
    /**
     *@param Bool flag определяет, вызван ли обработчик принудительно при наборе текста
    * listener (callback)
    */
    CPhoneCodesCountries.prototype.onItemClick = function (span, flag) {
        var item = this;
        if (flag) {
            item = span;
        }
        if ( item.tagName.toLowerCase() == "li" ) {
            item = item.getElement("span");
        }
        var self = item.self;
        var style = item.getProperty("style");
        var code = item.getProperty("dbid");
        localStorage.setItem("phoneList_style", style);
        localStorage.setItem("phoneList_code", code);
        self.setCode(style, code);
        if (flag) {
            self.show(0);
        }
        try {
            self.onchangeHandler();
        }catch (e) {
            ;
        }
    }
    /**
     * Выдергивает код страны из номера, устанавливает флаг соотв. страны на тоглере
     * @param String style значение атрибута style для превью флага
     * @param Number code значение кода страны
     * @param Bool key  true если усьтанавливаем код при наборе номера с клавиатуры (а не обрабатываем клик мыши)
     * @param Bool setFocus = true указывает, установить ли фокус на элементе ввода после смены кода
     */
    CPhoneCodesCountries.prototype.setCode = function (style, code, key, setFocus) {
        if (String(setFocus) == "undefined") {
            setFocus = true;
        }
        var _code = this.countryCode;
        if ( String(_code).indexOf(code) == 0 || String(code).indexOf(_code) == 0  ) {
            if ( code.length > _code.length && ( this.b_input.value.indexOf('+' + code) == 0 ) ) {
                _code = code;
            }
        }
        var re = new RegExp("^\\+" + _code + "\\s?");
        if ( this.b_input.value.indexOf("+" + this.countryCode) == -1 && key != true) {
            this.b_input.value = "+" + this.countryCode + (this.lastKey == 8?'':" ") + this.b_input.value;
        }
        this.countryCode = code;
        var s = this.b_input.value.replace(re, "+" + this.countryCode);
        if ( parseInt(code) && localStorage.getItem("phoneList_code") == code) {
            style = localStorage.getItem("phoneList_style");
        } 
        this.toggler.getElement("span.b-combo__flag").setProperty("style", style );
        this.b_input.value = s;
        if ( setFocus ) {
            this.b_input.focus();
        }
    }
    /**
     * Есть ли среди кодов стран код как у введеного телефона
     * @return bool truе если код не удалось найти
     * */
    CPhoneCodesCountries.prototype.isInvalidCode =  function () {
        if ( !this.parseCode(this.b_input.value) ) {
            return true;
        }
        return false;
    }
    //Конец определения класса CPhoneCodesCountries
    
