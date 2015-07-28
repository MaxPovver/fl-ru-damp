(function(){
    window.addEvent('domready', function(){
        initTopPayed();
    });
    
    var
        mainCheckbox, catalogCheckbox, headCarusel, textCarusel, buyBtn, buyBtnText, errorMessage, errorMessageText,
        advert, advertFrm, advertPrompt, advertHead, advertText, advertImg, advertHeadDefault, advertTextDefault, advertImgDefaultSrc, advertImgDefaultWidth, advertImgDefaultHeight,
        advertNeedMoney, advertBill,
        adHeadSend, adTextSend,
        attachBlock, attachBlockInfoShow, attachBlockInfoHide, attachBlockInfo, attachImgPath, adLastImg;
        
    // инициализация страницы
    function initTopPayed () {
        mainCheckbox = $('top-payed-maincarusel');
        catalogCheckbox = $('top-payed-catalogcarusel');
        headCarusel = $('top-payed-headcarusel');
        textCarusel = $('top-payed-textcarusel');
        buyBtn = $('top-payed-buybtn');
        buyBtnText = $('top-payed-buybtn-text');
        errorMessage = $('top-payed-errormessage');
        errorMessageText = $('top-payed-errormessage-text');
        advertFrm = $('top-payed-frm');
        advertNeedMoney = $('top-payed-need-money');
        advertBill = $('top-payed-bill');
        
        advert = $('top-payed-advert');
        advertPrompt = $('top-payed-advertprompt');
        advertHead = $('top-payed-adverthead');
        advertText = $('top-payed-adverttext');
        advertImg = advert.getElement('img');
        
        advertHeadDefault = 'Заголовок объявления';
        advertTextDefault = 'Текст объявления';
        advertImgDefaultSrc = advertImg.get('src');
        advertImgDefaultWidth = advertImg.get('width');
        advertImgDefaultHeight = advertImg.get('height');
        
        adHeadSend = $('ad_head');
        adTextSend = $('ad_text');
        adLastImg = $('ad_last_img');
        
        mainCheckbox.addEvent('change', checkAdvert);
        //catalogCheckbox.addEvent('change', checkAdvert);
        headCarusel.addEvent('change', checkAdvert);
        headCarusel.addEvent('keyup', checkAdvert);
        headCarusel.addEvent('focus', checkAdvert);
        textCarusel.addEvent('change', checkAdvert);
        textCarusel.addEvent('keyup', checkAdvert);
        textCarusel.addEvent('focus', checkAdvert);
        buyBtn.addEvent('click', saveAdvert);
        

        // блок загрузки файла
        attachBlock = $('attach_carusellogo');
        new attachedFiles2(
            attachBlock,
            {
                session: TopPayed.session,
                hiddenName: "carusellogo[]",
                files: TopPayed.attached,
                onComplete: function(obj, file){
                    setAdvertImg(file);
                },
                onDelete: function (obj) {
                    setAdvertImg('default');
                }
            },
            TopPayed.session
        );
        //attachBlockInfo = attachBlock.getElement('#attachedfiles_info');
        //attachBlockInfoShow = attachBlock.getElement('.b-fileinfo');
        //attachBlockInfoHide = attachBlock.getElement('.b-shadow__icon_close');
        //attachBlockInfoShow.addEvent('click', function(){
        //    attachBlockInfo.removeClass('b-shadow_hide');
        //});
        //attachBlockInfoHide.addEvent('click', function(){
        //    attachBlockInfo.addClass('b-shadow_hide');
        //});
        
        checkAdvert();
    }
    
    
    // проверяет параметры объявления и отображает/скрывает его
    // а также выводит сообщения об ошибках и дизейблит кнопку
    function checkAdvert () {
        var head, text;
        head = headCarusel.get('value').trim() || advertHeadDefault;
        text = textCarusel.get('value').trim() || advertTextDefault;
        
        if (head.length > 22) {
            head = head.slice(0, 22) + '...';
        }
        
        head1 = TopPayedReplace(head);
        text1 = TopPayedReplace(text);

        advertHead.set('text', head1);
        advertText.set('html', text1.replace(/</gi, '&lt;').replace(/>/gi, '&gt;').replace(/\n/gi, '<br>'));
        
        var disableBtn = false;
        
        // где разместить объявление
        if (mainCheckbox.get('checked') /*|| catalogCheckbox.get('checked')*/) {
            errorMessage.addClass('b-layout_hide');
        } else {
            errorMessage.removeClass('b-layout_hide');
            errorMessageText.set('text', 'Выберите страницу размещения.');
            disableBtn = true;
        }
        
        // стоимость размещения
        var price = mainCheckbox.get('checked') * TopPayed.adCost /*+ catalogCheckbox.get('checked') * TopPayed.adCost2*/;
        if(toppayed_c_btn==1) {
            buyBtnText.set('text', 'Купить за ' + price + ending(price, ' рубль', ' рубля', ' рублей'));
        }
//        if (TopPayed.accSum < price) {
//            var needMoney = price - TopPayed.accSum;
//            advertNeedMoney.set('text', 'Вам не хватает ' + needMoney.toFixed(2) + ending(needMoney, ' рубля', ' рубля', ' рублей'));
//            advertBill.setStyle('display', '');
//            disableBtn = true;
//        } else {
//            advertNeedMoney.set('text', '');
//            advertBill.setStyle('display', 'none');
//        }
        
        if (disableBtn) {
            disableBuyBtn();
        } else {
            activateBuyBtn();
        }
        
    }
    
    function disableBuyBtn () {
        buyBtn.addClass('b-button_disabled');
        disallowSendForm = true;
        
    }
    
    function activateBuyBtn () {
        buyBtn.removeClass('b-button_disabled');
        disallowSendForm = false;
    }
    
    // установить картинку в блок КАК БУДЕТ ВЫГЛЯДЕТЬ ОБЪЯВЛЕНИЕ
    function setAdvertImg (file) {
        adLastImg.set('value', ''); // изображение из предыдущего объявления уже не нужно
        if (file === 'default') { // установить юзерпик или заглушку
            advertImg.set('src', advertImgDefaultSrc);
            advertImg.set('width', advertImgDefaultWidth);
            advertImg.set('height', advertImgDefaultHeight);
            attachImgPath = null;
        } else if (typeof file === 'string') { // прямой путь к файлу картинки
            advertImg.set('src', file);
            advertImg.erase('width');
            advertImg.erase('height');
        } else { // массив с информацией о файле полученный от attachedfiles
            var path = ___WDCPREFIX + '/' + file.path + file.name;
            advertImg.set('src', path);
            advertImg.erase('width');
            advertImg.erase('height');
            attachImgPath = path;
        }
    }
    
    var disallowSendForm = false; // если true то запрещено отправлять форму, скорее всего она уже отправляется сейчас
    // сохранить (купить) объявление
    function saveAdvert (event) {
        if (disallowSendForm) {
            return;
        }
        
        var head = headCarusel.get('value');
        var text = textCarusel.get('value');
        
        if (!head.trim() || !text.trim()) {
            errorMessage.removeClass('b-layout_hide');
            errorMessageText.set('text', 'Заполните заголовок и текст объявления');
            disableBuyBtn();
            return;
        } else {
            errorMessage.addClass('b-layout_hide');
        }
        adHeadSend.set('value', head);
        adTextSend.set('value', text);
        
        if(toppayed_c_btn==1) {        
            quickCAR_show();
        } else {
            disableBuyBtn();
            advertFrm.submit();
        }
    }
    
})()