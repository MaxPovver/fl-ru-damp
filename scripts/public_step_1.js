(function(){
    window.Public || (window.Public = {});
    
    window.addEvent('domready', init);
    
    var $projectName, $projectNameCounter, $projectDescr, $projectDescrCounter, $projectLocationWrap, $projectAgreement, $projectCost, $projectCurrency, $projectPriceby, $projectLocation,
    $projectProfession0, $projectProfession1, $projectProfession2,
    $projectProfession0Spec, $projectProfession1Spec, $projectProfession2Spec,
    $projectNameError, $projectNameErrorText, $projectDescrError, $projectDescrErrorText, $projectCostError, $projectCostErrorText, $projectLocationError, $projectProfessionError, $projectDoubleProfessionError,
    $projectRemoveProf, $projectAddProf,// $projectProfs,
    $projectEndDate, $projectWinDate, $projectEndDateError, $projectWinDateError,
    $projectTopOk, $projectTopPrice, $projectTopDaysWrap, $projectTopDays, $projectTopDaysText, $projectTopDaysPrice,
    $projectLogoOk, $projectLogoDelLink, $projectLogo, $projectLogoLink, $projectLogoFile, $projectLogoError, $projectLogoErrorText, $projectLogoLinkError, $projectLogoLinkErrorText, $projectLogoImg, $projectLogoImgWrap, $projectLogoLink, $projectLogoInput,
    $projectSaveBtn, $projectSaveBtnText, $projectSaveBtnSum, $projectNeedMoney, $projectNeedMoneyText, saveButtonName,
    $projectPreviewBtn, $projectPreviewBtnWrap, $projectSaveToDraftBtn, $projectSaveToDraftBtn2,
    $projectTitle, $projectForm,
    $projectPreview, $projectPreviewLenta, $projectPreviewContent,
    $ablePublicBlock, $disablePublicBlock,
    //$preferSbrWrap, $preferSbr, 
    $projectUrgent, $projectHide, 
    $contestTax, $contestTaxWrap,
    
    $projectPreferSbrYes, $projectPreferSbrNo,
    $projectPreferSbrError, $projectPreferSbrErrorText;
    
    // инициализация формы для шага 1
    function init () {
        // элементы
        
        $projectPreferSbrYes = $('el-pay_type-0');
        $projectPreferSbrNo = $('el-pay_type-1');
        $projectPreferSbrError = $('el-pay_type-error');
        $projectPreferSbrErrorText = $('el-pay_type-error-text');
        
        //Показываем предупреждение если выбрата прямая оплата исполнителю на не БС
        if ($projectPreferSbrNo) {
            $projectPreferSbrNo.addEvent('change', function(e){
                alert("Обращаем ваше внимание: при сотрудничестве напрямую (вне сервиса \"Безопасная сделка\") стороны несут все риски самостоятельно.");
            });
        } 
        
        $projectName = $('project_name');
        $projectNameCounter = $('project_name_counter');
        $projectNameError = $('project_name_error');
        $projectNameErrorText = $('project_name_error_text');
        
        $projectDescr = $('f2');
        $projectDescrCounter = $('project_descr_counter');
        $projectDescrError = $('project_descr_error');
        $projectDescrErrorText = $('project_descr_error_text');
        
        if (Public.isVacancy) {
            $projectLocationWrap = $('project_location_wrap');
            $projectLocation = $('project_location');
            $projectLocationError = $('project_location_error');
        }        
        
        !Public.isContest && ($projectAgreement = $('project_agreement'));
        $projectCost = $('project_cost');
        $projectCurrency = $('project_currency');
        $projectPriceby = $('project_priceby');
        $projectCostError = $('project_cost_error');
        $projectCostErrorText = $('project_cost_error_text');
        
        if (Public.isContest) {
            $projectEndDate = $('project_end_date');
            $projectWinDate = $('project_win_date');
            $projectEndDateError = $('project_end_date_error');
            $projectWinDateError = $('project_win_date_error');
        }
        
        if (!Public.isPersonal) {
            $projectProfession0 = $('project_profession0');
            $projectProfession0Spec = $('project_profession0_spec');
            $projectProfession1 = $('project_profession1');
            $projectProfession1Spec = $('project_profession1_spec');
            $projectProfession2 = $('project_profession2');
            $projectProfession2Spec = $('project_profession2_spec');
            
            $projectProfessionError = $('project_profession_error');
            $projectDoubleProfessionError = $('project_double_profession_error');
            $projectRemoveProf = $$('.project_remove_prof');
            $projectAddProf = $('project_add_prof');

            $projectUrgent = $('project_urgent');
            $projectHide = $('project_hide');

            $projectTopOk = $('project_top_ok');
            $projectTopPrice = $('project_top_price');
            $projectTopDays = $('project_top_days');
            $projectTopDaysWrap = $('project_top_days_wrap');
            $projectTopDaysText = $('project_top_days_text');
            $projectTopDaysPrice = $('project_top_days_price');

            $projectLogoDelLink = $('project_logo_img_del');
            $projectLogoOk = $('project_logo_ok');
            $projectLogoLink = $('project_logo_link');
            $projectLogo = $('project_logo');
            $projectLogoFile = $('project_logo_file');
            $projectLogoImgWrap = $('project_logo_img_wrap');
            $projectLogoImg = $('project_logo_img');
            $projectLogoError = $('project_logo_error');
            $projectLogoErrorText = $('project_logo_error_text');
            $projectLogoLinkError = $('project_logo_link_error');
            $projectLogoLinkErrorText = $('project_logo_link_error_text');
            $projectLogoLink = $('project_logo_link');
            $projectLogoInput = $('project_logo_input');
        }
        
        $projectSaveBtn = $('project_save_btn');
        $projectSaveBtnText = $('project_save_btn_text');
        $projectSaveBtnSum = $('project_save_btn_sum');
        $projectNeedMoney = $('project_need_money');
        $projectNeedMoneyText = $('project_need_money_text');
        saveButtonName = (Public.isEdit ? ((Public.step > 1)?'Выделите ':'Сохранить ') : 'Опубликовать ') + (Public.isContest ? 'конкурс' : (Public.isVacancy ? 'вакансию' : 'проект'));
        
        if(Public.isVacancy && !Public.isVacancyPayed) {
            saveButtonName = 'Опубликовать вакансию';
        }
        
        $projectPreviewBtn = $('project_preview_btn');
        $projectPreviewBtnWrap = $('project_preview_btn_wrap');
        
        $projectSaveToDraftBtn = $('project_save_to_draft_btn');

        $projectForm = $('frm');
        $projectTitle = $('project_title');
        $projectPreview = $('project_preview');
        $projectPreviewLenta = $('project_preview_lenta');
        $projectPreviewContent = $('project_preview_content');
        
        $ablePublicBlock = $('project_able_public');
        $disablePublicBlock = $('project_disable_public');
        
        //$preferSbrWrap = $('project_prefer_sbr_wrap');
        //$preferSbr = $('project_prefer_sbr');
        
        $contestTax = $('contest_tax');
        $contestTaxWrap = $('contest_tax_wrap');
        
        // события
        $projectName.addEvent('input', projectNameLength);
        $projectName.addEvent('keyup', projectNameLength);
        $projectName.addEvent('keydown', projectNameLength);
        
        $projectDescr.addEvent('input', projectDescrLength);
        $projectDescr.addEvent('keyup', projectDescrLength);
        $projectDescr.addEvent('keydown', projectDescrLength);
        
        $projectSaveBtn && $projectSaveBtn.addEvent('click', saveProject);
        $projectName.addEvent('focus', projectNameFocus);
        $projectDescr.addEvent('focus', projectDescrFocus);
        if (Public.isVacancy) {
            $projectLocation.addEvent('focus', projectLocationFocus);
        }
        !Public.isContest && $projectAgreement.addEvent('change', projectAgreementChanged);
        //!Public.isContest && $projectAgreement.addEvent('change', preferSbrCheck);
        $projectCost.addEvent('blur', projectCostBlur);
        $projectCost.addEvent('focus', projectCostFocus);
        $projectCurrency.addEvent('focus', projectCostFocus);
        $projectCurrency.addEvent('change', updateContestTax);
        $projectCurrency.addEvent('change', validateBudget);
        $projectPriceby && $projectPriceby.addEvent('focus', projectCostFocus);
        
        //Если проект то тогда позволяем подгружать ТУ
        /* @todo: правая колонка переделана и теперь в этом смысла нет
        if(!Public.isPersonal && !Public.isContest && !Public.isVacancy) {
            $projectProfession0.addEvent('change', projectProfessionChange);
        }
        */
       
        if (!Public.isPersonal) {
            $projectProfession0.addEvent('focus', projectProfessionFocus);
            $projectProfession0Spec.addEvent('focus', projectProfessionFocus);
            
            $projectProfession1 && $projectProfession1.addEvent('focus', projectProfessionFocus);
            $projectProfession1Spec && $projectProfession1Spec.addEvent('focus', projectProfessionFocus);
            
            $projectProfession2 && $projectProfession2.addEvent('focus', projectProfessionFocus);
            $projectProfession2Spec && $projectProfession2Spec.addEvent('focus', projectProfessionFocus);

            $projectRemoveProf && $projectRemoveProf.addEvent('click', projectRemoveProf);
            $projectAddProf && $projectAddProf.addEvent('click', projectAddProf);
            
            //Инициализация 2ого выпадающего списка специализаций при выборе раздела в первом
            $projectProfession0.addEvent('change', projectGetSpec);
            $projectProfession1 && $projectProfession1.addEvent('change', projectGetSpec);
            $projectProfession2 && $projectProfession2.addEvent('change', projectGetSpec);
        }
        

        
        if (Public.isContest) {
            $projectEndDate.addEvent('focus', projectEndDateFocus);
            $projectWinDate.addEvent('focus', projectWinDateFocus);
        }
        
        
        if (Public.isEdit || (Public.step > 1) ) {
            $projectUrgent.addEvent('change', calcSum);
            $projectHide.addEvent('change', calcSum);

            $projectTopOk.addEvent('change', projectTopChanged);
            $projectTopDays.addEvent('change', projectTopDaysChanged);
            $projectTopDays.addEvent('input', projectTopDaysChanged);
            $projectTopDays.addEvent('keyup', projectTopDaysChanged);

            $('project_logo_img_del').addEvent('click', projectLogoDel);
            $projectLogoOk.addEvent('change', projectLogoChanged);
            $projectLogoLink.addEvent('focus', projectLogoLinkFocus);
            $projectLogoLink.addEvent('blur', projectLogoLinkBlur);
            //$projectLogoInput.addEvent('change', projectLogoInputClick);
        }
        
        $projectPreviewBtn && $projectPreviewBtn.addEvent('click', projectPreviewBtnClick);
        $projectSaveToDraftBtn && $projectSaveToDraftBtn.addEvent('click', projectSaveToDraftBtnClick);

        if ($projectLogoFile) {
            // блок загрузки файла-логотипа
            var logoAttach = new attachedFiles2(
                $projectLogoFile,
                {
                    session: Public.attachLogoSession,
                    hiddenName: "project_logo[]",
                    files: Public.attachLogoFiles,
                    selectors: {
                        template: '.project_logo_file_template',
                        uploadedfileIcon: '.project_logo_file_icon',
                        uploadedfileName: '.project_logo_file_name',
                        uploadedfileDel: '.project_logo_file_del'
                    },
                    onError: function(attach, message) {
                        $('project_logo_error').getChildren().removeClass('b-shadow_hide');
                        $('project_logo_error_txt').set('html', message.error);
                    },
                    onComplete: function (attach, file) {
                        $projectLogoError.addClass('b-layout_hide');
                        $('project_logo').setStyle('display', 'none');
                        $('project-logo-btn-del').setStyle('display', '');

                        var wrap = $('project_logo_img_wrap');
                        wrap.setStyle('display', '');
                        var prjlg1 = $('project_logo_1');
                        prjlg1.setStyle('display', 'none');

                        var img = $('project_logo_img');
                        var src = ___WDCPREFIX + '/' + file.path + file.name;
                        img.set('src', src);
                        img.addEvent('load', function(){
                            projectCorrectLogoPosition();
                            img.removeEvents('load');
                        });
                        $('project_logo_del').set('value', 0);

                        //$('project_logo').getElement('.b-file').addClass('b-file_hover');

                        //$('project_logo_file_btn').setStyle('display', 'none');
                        
                        $('project_logo_img_del').addEvent('click', projectLogoDel);
                    }

                },
                Public.attachLogoSession
            );
        }
            
        (Public.isEdit || (Public.step > 1)) && projectTopDaysChanged();
        projectNameLength();
        projectDescrLength();
        Public.isContest && updateContestTax();
        !Public.isContest && projectAgreementChanged();
        projectCorrectLogoPosition();
        calcSum();
        //!Public.isPersonal && projectProfessionChange();
        
        DraftInit(1);
        
        $block_payment = $('block_payment');
        if ($block_payment && $block_payment.get('data-choosebs')) {
            var myFx = new Fx.Scroll(window, {
                duration: 300,
                wait: false,
                offset: {
                    x: 0,
                    y: -30
                }
            }).toElement('block_payment');
            $('el-pay_type-0').set('checked', 'checked');
        }
        
    }
    
    
    function projectGetSpec (obj) {
        
        var element_input_id = obj.target.get('id');
        $(element_input_id + '_spec_ui').removeClass('b-combo_hide');
        
        var spec = window.ComboboxManager.getInput(element_input_id + '_spec');
        if (spec) {
            var dbid = $(element_input_id + '_db_id').get("value");
            if (Number(dbid)) {
                spec.breadCrumbs[-1] = dbid;  
                spec.reload('');    
                //spec.show(true);                    
            }
        }
    }
    
    
    function projectNameFocus () {
        $projectNameError.addClass('b-layout_hide');
    }
    function projectDescrFocus () {
        $projectDescrError.addClass('b-layout_hide');
        $projectDescr.getParent('.b-textarea').removeClass('b-textarea_error');
    }
    
    // проверка длины названия проекта
    function projectNameLength () {
        var length = $projectName.get('value').trim().length;
        if (length > Public.nameMaxLength) {
            $projectNameCounter.addClass('b-layout__txt_color_fd6c30');
        } else {
            $projectNameCounter.removeClass('b-layout__txt_color_fd6c30');
        }
        $projectNameCounter.set('text', length);
    }
    // проверка длины описания проекта
    function projectDescrLength () {
        var length = $projectDescr.get('value').trim().length;
        if (length > Public.descrMaxLength) {
            $projectDescrCounter.addClass('b-layout__txt_color_fd6c30');
        } else {
            $projectDescrCounter.removeClass('b-layout__txt_color_fd6c30');
        }
        $projectDescrCounter.set('text', length);
    }
    
    
    function projectAgreementChanged () {
        $projectCost.getParent('.b-combo__input').removeClass('b-combo__input_error');
        $projectCostError.addClass('b-layout_hide');
        if ($projectAgreement.get('checked')) {
            $projectCost.getParent('.b-combo__input').addClass('b-combo__input_disabled');
            $projectCurrency.getParent('.b-combo__input').addClass('b-combo__input_disabled');
            $projectPriceby && $projectPriceby.getParent('.b-combo__input').addClass('b-combo__input_disabled');
        } else {
            $projectCost.set('disabled', false);
            $projectCost.getParent('.b-combo__input').removeClass('b-combo__input_disabled');
            $projectCurrency.getParent('.b-combo__input').removeClass('b-combo__input_disabled');
            $projectPriceby && $projectPriceby.getParent('.b-combo__input').removeClass('b-combo__input_disabled');
        }
    }
    
    function projectCostBlur () {
        var value = $projectCost.get('value');
        value = value.replace(/[^\d\.,]/gi, ''); // удаляем все недопустимые символы
        if (isNaN(+value)) { // если введенное не является числом
            value = value.replace(/[\.,].*/gi, '');
            value = +value;
        } else {
            value = Math.floor(value);
        }

        $projectCost.set('value', value);
        
        //preferSbrCheck();
        validateBudget();
        updateContestTax(); // пересчитываем стоимость публикации конкурса
    }
    
    /**
     * обновляет стоимость публикации конкурса
     */
    function updateContestTax () {
        // это только для конкурсов с новой системой расчета стоимости публикации
        if (!Public.isContest || !$contestTaxWrap || !Public.newContestBudget) {
            return;
        }
        var currencyID = +$('project_currency_db_id').get('value');
        var budget = $projectCost.get('value');
        var budgetRub = convertToRub(budget, currencyID);
        var contestTax = getContestTax(budgetRub, Public.userIsPro);
        /*
        Public.contestPrice = contestTax; // сохраняем для дальнейшего расчета общей стоимости проекта
        if (contestTax) {
            $contestTax.set('text', contestTax);
            $contestTaxWrap.setStyle('display', '');
        } else {
            $contestTaxWrap.setStyle('display', 'none');
        }
        */
        
        calcSum(); // пересчитываем общую стоимость публикации конкурса (с логотипом и закреплением)
    }
    
    /**
     * определение стоимости публикации конкурса
     * повторяет функционал php функции new_projects::getContestTax()
     * budget - бюджет в рублях
     */
    function getContestTax (budget, isPro) {
        if (budget < Public.minBudget || !budget) {
            return null;
        }
        var proKey = isPro ? 'pro' : 'nopro';
        var i;
        var taxes = Public.contestTaxes[proKey];
        var tax;
        for (i in taxes) {
            if (!taxes.hasOwnProperty(i)) {
                continue;
            }
            tax = taxes[i];
            if (tax['min'] <= budget && budget <= tax['max']) {
                break;
            }
        }
        return tax['sum'];
    }
    
    /**
     * проверяет нужно ли показать/скрыть блок с чекбоксом ПРЕДПОЧИТАЮ РАБОТАТЬ ЧЕРЕЗ СБР
     */
    /*
    function preferSbrCheck () {
        // в конкурсах этот чекбокс никогда не убирается
        // в вакансиях и персональных проектах никогда не показывается
        if (Public.isVacancy || Public.isContest || Public.isPersonal) {
            return;
        }
        // бюджет
        var value = +$projectCost.get('value');
        // валюта
        var currencyID = +$('project_currency_db_id').get('value');
        // конвертируем в рубли
        var valueRub = convertToRub(value, currencyID);
        // если сумма менее положенной и бюджет не по договоренности, то скрываем пункт ПРЕДПОЧИТАЮ СБР
        if (value === 0 || valueRub >= Public.minSbrBudget || ($projectAgreement && $projectAgreement.get('checked'))) {
            $preferSbrWrap.setStyle('display', '');
            $preferSbr.set('value', 1);
        } else {
            $preferSbrWrap.setStyle('display', 'none');
            $preferSbr.set('value', 0);
        }
    }
    */
   
   
   
    function projectCostFocus () {
        $projectCost.getParent('.b-combo__input').removeClass('b-combo__input_error');
        $projectCostError.addClass('b-layout_hide');
    }
    
    function projectProfessionFocus () {
        $projectProfession0.getParent('.b-combo__input').removeClass('b-combo__input_error');
        $projectProfession1 && $projectProfession1.getParent('.b-combo__input').removeClass('b-combo__input_error');
        $projectProfession2 && $projectProfession2.getParent('.b-combo__input').removeClass('b-combo__input_error');
        $projectProfessionError.addClass('b-layout_hide');
        $projectDoubleProfessionError.addClass('b-layout_hide');
    }
    
    function projectRemoveProf () {
        // при удалении надо соблюдать следующее
        // комбобокс с id = project_profession0 - должен быть заполнен обязательно, это будет основная специализация проекта
        // по-этому при удалени первой профессии переносим данные второй профессии в первый комбобокс, а также если есть из третьего во второй
        // если комбобокс профессии всего один, то он не удаляется а просто очищается
        
        var profsCount = $$('.project_prof:not(.b-layout_hide)').length; // количество видимых комбобоксов (оно же сколько профессий выбрано)
        if (profsCount === 1) { // просто очищаем
            clearProf(0);
        } else {
            var profID = +this.getParent('.project_prof').get('project_prof_id');
            if (profID === 0) { // если удаляется профессия из первого комбобокса
                copyProf(1, 0);
                if (profsCount === 3) {
                    copyProf(2, 1);
                    clearProf(2);
                    $projectProfession2.getParent('.project_prof').addClass('b-layout_hide');
                } else {
                    clearProf(1);
                    $projectProfession1.getParent('.project_prof').addClass('b-layout_hide');
                }
            } else if (profID === 1) { // из второго комбобокса
                if (profsCount === 3) {
                    copyProf(2, 1);
                    clearProf(2);
                    $projectProfession2.getParent('.project_prof').addClass('b-layout_hide');
                } else {
                    clearProf(1);
                    $projectProfession1.getParent('.project_prof').addClass('b-layout_hide');
                }
            } else { // из третьего комбобокса
                clearProf(2);
                $projectProfession2.getParent('.project_prof').addClass('b-layout_hide');
            }
        }
        $projectAddProf.removeClass('b-layout_hide');
        projectProfessionFocus();
    }
    
    function clearProf (profID) {
        ComboboxManager.getInput('project_profession' + profID).setDefaultValue();
        $('project_profession' + profID + '_spec_ui').addClass('b-combo_hide');
        $('project_profession' + profID).set('value', '');
        $('project_profession' + profID).getParent('.b-combo__input').setStyle('width', '');
        $('project_profession' + profID + '_column_id').set('value', '');
        $('project_profession' + profID + '_db_id').set('value', '');
        $$('input[name=project_profession' + profID + '_columns[0]]')[0].set('value', '');
        $$('input[name=project_profession' + profID + '_columns[1]]')[0].set('value', '');
        //ComboboxManager.getInput('project_profession' + profID);
    }
    function copyProf (fromID, toID) {
        $('project_profession' + toID).set('value', $('project_profession' + fromID).get('value'));
        $('project_profession' + toID).getParent('.b-combo__input').setStyle('width', $('project_profession' + fromID).getParent('.b-combo__input').getStyle('width'));
        $('project_profession' + toID + '_column_id').set('value', $('project_profession' + fromID + '_column_id').get('value'));
        $('project_profession' + toID + '_db_id').set('value', $('project_profession' + fromID + '_db_id').get('value'));
        $$('input[name=project_profession' + toID + '_columns[0]]')[0].set('value', $$('input[name=project_profession' + fromID + '_columns[0]]')[0].get('value'));
        $$('input[name=project_profession' + toID + '_columns[1]]')[0].set('value', $$('input[name=project_profession' + fromID + '_columns[1]]')[0].get('value'));
    }
    
    // открывает первый скрытый комбобокс выбора профессии
    function projectAddProf () {
        var hiddenProfs = $$('.project_prof.b-layout_hide');
        if (hiddenProfs.length === 0) {
            return;
        } else if (hiddenProfs.length === 1) {
            $projectAddProf.addClass('b-layout_hide');
        }
        hiddenProfs[0].removeClass('b-layout_hide');
        projectProfessionFocus();
    }
    
    function projectProfessionChange () {
        var prof = $$('input[name=project_profession0_columns[0]]')[0].value;
        var spec = $$('input[name=project_profession0_columns[1]]')[0].value;
        //console.log('tu ' + prof + '-' + spec);
        xajax_getRelativeTU(prof, spec);
    }
    
    function projectLocationFocus () {
        $projectLocation.getParent('.b-combo__input').removeClass('b-combo__input_error');
        $projectLocationError.addClass('b-layout_hide');
    }
    
    function projectTopChanged () {
        if ($projectTopOk.get('checked')) {
            $('project_top_ok_s_info').addClass('b-layout_hide');
            $('project_top_ok_s_days').removeClass('b-layout_hide');
            $('project_top_days_price_2').setStyle('display', 'none');
            $('project_top_days_price').setStyle('display', '');
            //$projectTopDaysWrap.removeClass('b-layout_hide');
            //$projectTopPrice.setStyle('display', 'none');
        } else {
            $('project_top_ok_s_days').addClass('b-layout_hide');
            $('project_top_ok_s_info').removeClass('b-layout_hide');
            $('project_top_days_price_2').setStyle('display', '');
            $('project_top_days_price').setStyle('display', 'none');
//            $projectTopDaysWrap.addClass('b-layout_hide');
//            $projectTopPrice.setStyle('display', '');
        }
        projectTopDaysChanged();
    }
    
    /**
     * изменено количество дней закрепления проекта
     */
    function projectTopDaysChanged () {
        var days = $projectTopDays.get('value');
        if (days !== days.replace(/\D*/gi, '')) {
            days = days.replace(/\D*/gi, '')
            $projectTopDays.set('value', days);
        }
        var price = days * Public.topDayPrice;
        $projectTopDaysText.set('text', ending(days, 'день', 'дня', 'дней'));
        $projectTopDaysPrice.set('text', price+'  руб.');
        calcSum();
    }

    function projectLogoDel() {
        $('project_logo_img_wrap').setStyle('display', 'none');
        $('project_logo_1').setStyle('display', '');
        $('project-logo-btn-del').setStyle('display', 'none');
        $('project_logo').setStyle('display', '');
        $('project_logo_del').set('value', 1);
        $('project_logo_img').set('src', null);
        return false;
    }
    
    function projectLogoChanged () {
        if ($projectLogoOk.get('checked')) {
            $projectLogo.setStyle('display', '');
            $('project_logo_link_block').setStyle('display', '');
            $('project_logo_2').setStyle('display', 'none');
            $projectLogoError.getChildren().addClass('b-shadow_hide');
            if($('project_logo_img').get('src')==null) {
                $('project_logo').setStyle('display', '');
                $('project-logo-btn-del').setStyle('display', 'none');
				$('project_logo_img_wrap').setStyle('display', 'none');
				$('project_logo_1').setStyle('display', '');
            } else {
                $('project_logo').setStyle('display', 'none');
                $('project-logo-btn-del').setStyle('display', '');
				$('project_logo_img_wrap').setStyle('display', '');
				$('project_logo_1').setStyle('display', 'none');
            }
        } else {
            $projectLogo.setStyle('display', 'none');
            $('project_logo_link_block').setStyle('display', 'none');
			$('project_logo_img_wrap').setStyle('display', 'none');
            $('project_logo_1').setStyle('display', '');
            $('project_logo_2').setStyle('display', '');
            $('project-logo-btn-del').setStyle('display', 'none');
        }
        calcSum();
    }
    
    function projectCorrectLogoPosition () {
        return ;
        var wrap = $('project_logo_img_wrap');
        var img = $('project_logo_img');
        var size = img.getSize();
        wrap.setStyle('marginLeft', (150 - size.x) / 2 - 5);
        wrap.setStyle('marginTop', (150 - size.y) / 2 - 5);
    }
    
    var defaultLinkText = 'Адрес сайта по желанию';
    function projectLogoLinkFocus () {
        if ($projectLogoLink.get('value') === defaultLinkText) {
            $projectLogoLink.set('value', '');
            $projectLogoLink.removeClass('b-combo__input-text_color_a7');
        }
    }
    function projectLogoLinkBlur () {
        if ($projectLogoLink.get('value').trim().length === 0) {
            $projectLogoLink.set('value', defaultLinkText);
            $projectLogoLink.addClass('b-combo__input-text_color_a7');
        }        
    }
    /*function projectLogoInputClick () {
        $projectLogoError.addClass('b-layout_hide');
    }*/
    
    function projectEndDateFocus () {
        $projectEndDate.getParent('.b-combo__input').removeClass('b-combo__input_error2');
        $projectEndDateError.addClass('b-layout_hide');
    }
    function projectWinDateFocus () {
        $projectWinDate.getParent('.b-combo__input').removeClass('b-combo__input_error2');
        $projectWinDateError.addClass('b-layout_hide');
    }
    
    /**
     * считает стоимость платных услуг
     * если сумма больше нуля, то показывает результат на кнопке
     */
    function calcSum () {
        var vacancy = 0, top = 0, logo = 0, contest = 0, urgent = 0, hide = 0;
        
        if (Public.isVacancy) {
            vacancy = Public.vacancyPrice;
        }
        
        if (Public.isContest) {
            contest = Public.contestPrice;
        }        
        
        if (Public.isEdit || Public.step > 1) {
            
            if ($projectTopOk.get('checked')) {
                top = $projectTopDays.get('value') * Public.topDayPrice;
            }
            if ($projectLogoOk.get('checked')) {
                logo = Public.logoPrice;
            }
            if ($projectUrgent.get('checked')) {
                if($('hidden_project_urgent').get('value')==0) {
                    urgent = Public.urgentPrice;
                }
            }
            if ($projectHide.get('checked')) {
                if($('hidden_project_hide').get('value')==0) {
                    hide = Public.hidePrice;
                }
            }
        }
        var sum = vacancy + top + logo + contest + urgent + hide;
        Public.usePopup = false;
        if (sum === 0) { // если ничего платить не надо
            Public.usePopup = false;
            $projectSaveBtn && $projectSaveBtn.removeClass('b-button_disabled');
            $projectSaveBtnText && $projectSaveBtnText.set('text', saveButtonName);
            $projectSaveBtnSum && $projectSaveBtnSum.set('text', '');
            $projectNeedMoney && $projectNeedMoney.setStyle('display', 'none');
            $projectPreviewBtnWrap && $projectPreviewBtnWrap.setStyle('display', '');
            
            $previewSaveBtn && $previewSaveBtn.removeClass('b-button_disabled');
            $previewSaveBtnText && $previewSaveBtnText.set('text', saveButtonName);
            $previewSaveBtnSum && $previewSaveBtnSum.set('text', '');
            $previewNeedMoney && $previewNeedMoney.setStyle('display', 'none');            
        } else { // если денег хватает
            Public.usePopup = true;
            $projectSaveBtn && $projectSaveBtn.removeClass('b-button_disabled');
            $projectSaveBtnText && $projectSaveBtnText.set('text', saveButtonName + ' за ');
            $projectSaveBtnSum && $projectSaveBtnSum.set('text', sum + ' руб.');
            $projectNeedMoney && $projectNeedMoney.setStyle('display', 'none');
            $projectPreviewBtnWrap && $projectPreviewBtnWrap.setStyle('display', '');
            
            $previewSaveBtn && $previewSaveBtn.removeClass('b-button_disabled');
            $previewSaveBtnText && $previewSaveBtnText.set('text', saveButtonName + ' за ');
            $previewSaveBtnSum && $previewSaveBtnSum.set('text', sum + ' руб.');
            $previewNeedMoney && $previewNeedMoney.setStyle('display', 'none');
        } 
//        else { // если денег не хватает
//            $projectSaveBtn && $projectSaveBtn.addClass('b-button_disabled');
//            $projectSaveBtnText && $projectSaveBtnText.set('text', saveButtonName + ' за ');
//            $projectSaveBtnSum && $projectSaveBtnSum.set('text', sum + ' ' + ending(sum, ' рубль', ' рубля', ' рублей'));
//            $projectNeedMoney && $projectNeedMoney.setStyle('display', '');
//            var needSum = Math.abs(sum - Public.accSum);
//            needSum = Math.round(needSum * 100) / 100;
//            $projectNeedMoneyText && $projectNeedMoneyText.set('text', 'вам не хватает ' + needSum + ' ' + ending(needSum, ' рубля', ' рублей', ' рублей'));
//            $projectPreviewBtnWrap && $projectPreviewBtnWrap.setStyle('display', 'none');
//        }
        
    }
            
    function validateProject () {
        var ok = true,
            scrollTo = false; // самый первый элемент с ошибкой, к нему будем скроллить
    
        if ($projectSaveBtn.hasClass('b-button_disabled')) {
            return;
        }
        
        // скрываем ошибки
        $projectCost.getParent('.b-combo__input').removeClass('b-combo__input_error');
        $projectCostError.addClass('b-layout_hide');
        
        
        var nameLength = $projectName.get('value').trim().length;
        if (nameLength === 0 || nameLength > Public.nameMaxLength) {
            ok = false;
            scrollTo || (scrollTo = $projectName);
            $projectNameError.removeClass('b-layout_hide');
            $projectName.getParent('.b-combo__input').addClass('b-combo__input_error');
            $projectNameErrorText.set('text', nameLength === 0 ? 'Необходимо ввести название' : 'Название не должно превышать ' + Public.nameMaxLength + ' символов');
        }
        
        var descrLength = $projectDescr.get('value').trim().length;
        if (descrLength === 0 || descrLength > Public.descrMaxLength) {
            ok = false;
            scrollTo || (scrollTo = $projectDescr);
            $projectDescrError.removeClass('b-layout_hide');
            $projectDescr.getParent('.b-textarea').addClass('b-textarea_error');
            $projectDescrErrorText.set('text', descrLength === 0 ? 'Необходимо ввести описание' : 'Описание не должно превышать ' + Public.descrMaxLength + ' символов');
        }
        
        // если публикуется вакансия, то страна и город должны быть выбраны
        /*if (Public.isVacancy) {
            var $country = $$('input[name=project_location_columns[0]]')[0];
            var $city = $$('input[name=project_location_columns[1]]')[0];
            if (Public.isVacancy && (!$country || !parseInt($country.get('value'), 10) || !$city || !parseInt($city.get('value'), 10))) {
                ok = false;
                scrollTo || (scrollTo = $projectLocation);
                $projectLocation.getParent('.b-combo__input').addClass('b-combo__input_error');
                $projectLocationError.removeClass('b-layout_hide');
            }
        }*/
        
        // в конкурсе должна быть указана сумма (валюта и период стоят сразу)
        if (Public.isContest && +$projectCost.get('value') === 0) {
            ok = false;
            scrollTo || (scrollTo = $projectCost);
            $projectCost.getParent('.b-combo__input').addClass('b-combo__input_error');
            $projectCostError.removeClass('b-layout_hide');
            $projectCostErrorText.set('text', 'Укажите бюджет проекта');
        }
        if ((Public.isContest || !$projectAgreement.get('checked')) && $projectCost.get('value') < 0) {
            ok = false;
            scrollTo || (scrollTo = ($projectAgreement || $projectCost)); // скроллим к чекбоксу ПО ДОГОВОРЕННОСТИ или к полю СУММА БЮДЖЕТА
            $projectCost.getParent('.b-combo__input').addClass('b-combo__input_error');
            $projectCostError.removeClass('b-layout_hide');
            $projectCostErrorText.set('text', 'Введите положительную сумму');
        }
        if ((Public.isContest || !$projectAgreement.get('checked')) && $projectCost.get('value') > 999999) {
            ok = false;
            scrollTo || (scrollTo = ($projectAgreement || $projectCost));
            $projectCost.getParent('.b-combo__input').addClass('b-combo__input_error');
            $projectCostError.removeClass('b-layout_hide');
            $projectCostErrorText.set('text', 'Слишком большая сумма');
        }
        // валидация бюджета
        if (!validateBudget()) {
            ok = false;
            scrollTo || (scrollTo = $projectCost);
        }

        if (Public.isContest) {
            // сперва сбрасываем все ошибки связанные с датами
            $projectEndDate.getParent('.b-combo__input').removeClass('b-combo__input_error2');
            $projectEndDateError.addClass('b-layout_hide');
            $projectWinDate.getParent('.b-combo__input').removeClass('b-combo__input_error2');
            $projectWinDateError.addClass('b-layout_hide');
            
            var split_end = $('project_end_date_eng_format').get('value').split('-');
            var split_win = $('project_win_date_eng_format').get('value').split('-');
            var endDate = new Date(split_end[0], split_end[1]-1, split_end[2]);
            var winDate = new Date(split_win[0], split_win[1]-1, split_win[2]);
            var nowDate = new Date();
            
            var endDateInPast = endDate < nowDate;
            var noEndDate = $projectEndDate.get('value').trim().length === 0;
            if (endDateInPast || noEndDate) {
                ok = false;
                scrollTo || (scrollTo = $projectEndDate);
                $projectEndDate.getParent('.b-combo__input').addClass('b-combo__input_error2');
                $projectEndDateError.removeClass('b-layout_hide');
                var endDateErrorText = noEndDate ? 'Необходимо указать дату завершения конкурса' : 'Дата окончания конкурса не может находиться в прошлом';
                $projectEndDateError.getElement('.project-error-text').set('text', endDateErrorText);
            }
            
            var winDateInPast = winDate < nowDate;
            var noWinDate = $projectWinDate.get('value').trim().length === 0;
            var winDateAfterEndDate = !noEndDate && (winDate <= endDate);
            if (winDateInPast || noWinDate || winDateAfterEndDate) {
                ok = false;
                scrollTo || (scrollTo = $projectWinDate);
                $projectWinDate.getParent('.b-combo__input').addClass('b-combo__input_error2');
                $projectWinDateError.removeClass('b-layout_hide');
                var winDateErrorText;
                if (noWinDate) {
                    winDateErrorText = 'Необходимо указать дату объявления победителей конкурса';
                } else if (winDateInPast) {
                    winDateErrorText = 'Дата подведения итогов конкурса не может находиться в прошлом';
                } else {
                    winDateErrorText = 'Дата определения победителя не должна предшествовать дате окончания конкурса';
                }
                $projectWinDateError.getElement('.project-error-text').set('text', winDateErrorText);
            }
        }
        
        if (!Public.isPersonal) {
            // профессия обязательна для каждого неперсонального проекта, хотя бы одна
            var $profCat0 = $$('input[name=project_profession0_columns[0]]')[0];
            var $profSubcat0 = $$('input[name=project_profession0_spec_columns[0]]')[0];
            var profCat0 = $profCat0.get('value');
            var profSubcat0 = $profSubcat0 && $profSubcat0.get('value');
            var prof0 = $profCat0 && +profCat0;
            var $profCat1 = $$('input[name=project_profession1_columns[0]]')[0];
            var $profSubcat1 = $$('input[name=project_profession1_spec_columns[0]]')[0];
            var profCat1 = $profCat1 && $profCat1.get('value');
            var profSubcat1 = $profSubcat1 && $profSubcat1.get('value');
            var prof1 = $profCat1 && +profCat1;
            var $profCat2 = $$('input[name=project_profession2_columns[0]]')[0];
            var $profSubcat2 = $$('input[name=project_profession2_spec_columns[0]]')[0];
            var profCat2 = $profCat2 && $profCat2.get('value');
            var profSubcat2 = $profSubcat2 && $profSubcat2.get('value');
            var prof2 = $profCat2 && +profCat2;
            if (!prof0 && !prof1 && !prof2) {
                ok = false;
                scrollTo || (scrollTo = $projectProfession0);
                $projectProfession0.getParent('.b-combo__input').addClass('b-combo__input_error');
                $projectProfessionError.removeClass('b-layout_hide');
            }
            // не должно быть двух одинаковых профессий
            var profCode0 = profCat0 + '|' + profSubcat0;
            var profCode1 = profCat1 + '|' + profSubcat1;
            var profCode2 = profCat2 + '|' + profSubcat2;
            if ((profCat0>0 && profCode0 === profCode1) || (profCat2>0 && profCode0 == profCode2) || (profCat1>0 && profCode1 === profCode2)) {
                ok = false;
                scrollTo || (scrollTo = $projectProfession0);
                if (profCat0 && profCode0 === profCode1) {
                    $projectProfession0.getParent('.b-combo__input').addClass('b-combo__input_error');
                    $projectProfession1.getParent('.b-combo__input').addClass('b-combo__input_error');
                }
                if (profCat0 && profCode0 === profCode2) {
                    $projectProfession0.getParent('.b-combo__input').addClass('b-combo__input_error');
                    $projectProfession2.getParent('.b-combo__input').addClass('b-combo__input_error');
                }
                if (profCat1 && profCode1 === profCode2) {
                    $projectProfession1.getParent('.b-combo__input').addClass('b-combo__input_error');
                    $projectProfession2.getParent('.b-combo__input').addClass('b-combo__input_error');
                }
                $projectDoubleProfessionError.removeClass('b-layout_hide');
            }
        }
        
        if ( Public.isEdit || (Public.step > 1) ) {
            if ($projectLogoOk.get('checked') && !$('project_logo_img').get('src')) {
                ok = false;
                scrollTo || (scrollTo = $projectLogoOk);
                $('project_logo_error').getChildren().removeClass('b-shadow_hide');
                $('project_logo_error_txt').set('html', 'Вы не загрузили логотип');
            }
        }
        
        //if ($projectLogoOk.get('checked') && ( $('project_logo_link').get('value') == '' || $('project_logo_link').get('value') == 'Адрес сайта по желанию') ) {
        //    ok = false;
        //    scrollTo || (scrollTo = $projectLogoOk);
            //$projectLogoLink.getParent('.b-combo__input').addClass('b-combo__input_error');
            //$projectLogoLinkError.removeClass('b-layout_hide');
            //$projectLogoLinkErrorText.set('text', 'Введите адрес сайта');
        //}



        if ($projectPreferSbrError) {
            $projectPreferSbrError.addClass('b-layout_hide');
            
            if ($projectPreferSbrYes && $projectPreferSbrNo) {
                if(!$projectPreferSbrYes.get('checked') && !$projectPreferSbrNo.get('checked')) {
                    ok = false;
                    scrollTo || (scrollTo = $projectPreferSbrYes);
                    $projectPreferSbrErrorText.set('html','Укажите способ, которым вы оплатите работу исполнителя в проекте. <br/>Рекомендуем оплату через "Безопасную сделку".');
                    $projectPreferSbrError.removeClass('b-layout_hide');
                }
            }
        }


        if (scrollTo) {
            JSScroll(scrollTo, true);
        }
        return ok;
    }

    /**
     * делает проверку бюджета
     * если проверка пройдена возвращает true, иначе - false
     * в случае ошибки выделяет поле красным и выводит соответствующую надпись
     */
    function validateBudget () {
        if (!Public.isContest || !Public.minBudget) {
            return true;
        }
        var currencyID = +$('project_currency_db_id').get('value');
        var cost = +$projectCost.get('value'); // бюджет в выбранной валюте
        var costRub = convertToRub(cost, currencyID); // бюджет в рублях

        if (costRub < Public.minBudget) {
            $projectCost.getParent('.b-combo__input').addClass('b-combo__input_error');
            $projectCostError.removeClass('b-layout_hide');
            var minBudgetError = 'Сумма бюджета не должна быть меньше ' + Public.minBudget + ' руб.';
            if (currencyID === 0) {
                minBudgetError += ' (' + Math.ceil(Public.minBudget / Public.pExrates.usd) + ' USD)'
            } else if (currencyID === 1) {
                minBudgetError += ' (' + Math.ceil(Public.minBudget / Public.pExrates.euro) + ' EUR)'
            }

            $projectCostErrorText.set('text', minBudgetError);
            return false;
        }
        return true;
    }

    function saveProject () {
        if (!$projectSaveBtn.hasClass('submitted')) {
            if (!validateProject()) {
                return;
            }
            $projectSaveBtn.addClass('b-button_disabled');
            $previewSaveBtn && $previewSaveBtn.addClass('b-button_disabled');
            useYaMetrik();
            calcSum();
            if(Public.usePopup==true) {
                quickPRJ_show();
            } else if(Public.step > 1) {
                alert('Вы не выбрали опцию для выделения проекта в ленте. Выберите одну или несколько опций и ваш проект будет заметнее для лучших исполнителей.');
            } else {    
                $projectSaveBtn.addClass('submitted');
                $('frm').set('target', '');
                $('is_exec_quickprj').set('value', '0');
                $projectForm.submit();
            }
        }        
    }
    
    function projectPreviewBtnClick () {
        if (!validateProject()) {
            return;
        }
        xajax_GetPreview(xajax.getFormValues($projectForm));
    }
    
    function projectSaveToDraftBtnClick () {
        DraftSave();
    }
    
    var $previewSaveBtn, $previewSaveBtnText, $previewSaveBtnSum, $previewNeedMoney, $previewNeedMoneyText;
    function projectShowPreview () {
        $projectTitle.setStyle('display', 'none');
        $projectForm.setStyle('display', 'none');
        $projectPreview.setStyle('display', '');
        
        // первый проект
        var $firstPrj = $$('div[id^=project-item]')[0];
        $firstPrj.set('html', $projectPreviewContent.get('html'));
        $projectPreviewContent.set('html', '');
        $firstPrj.addClass('b-post_preview');
        JSScroll($firstPrj);
        
        var $editBtn = $projectPreview.getElement('.project_preview_edit_btn');
        
        $previewSaveBtn = $projectPreview.getElement('.project_preview_save_btn');
        $previewSaveBtnText = $projectPreview.getElement('.project_preview_save_btn_text');
        $previewSaveBtnSum = $projectPreview.getElement('.project_preview_save_btn_sum');
        $previewNeedMoney = $projectPreview.getElement('.project_preview_need_money');
        $previewNeedMoneyText = $projectPreview.getElement('.project_preview_need_money_text');
        
        $editBtn.addEvent('click', projectHidePreview);
        $previewSaveBtn.addEvent('click', saveProject);
        
        calcSum();
    }
    function projectHidePreview () {
        $projectTitle.setStyle('display', '');
        $projectForm.setStyle('display', '');
        $projectPreview.setStyle('display', 'none');
    }
    
    /**
     * конвертирует валюту в рубли
     * sum - сумма
     * currencyID - валюта
     */
    function convertToRub (sum, currencyID) {
        var sumRub;
        if (currencyID === 0) { // USD
            sumRub = sum * Public.pExrates.usd;
        } else if (currencyID === 1) { // EURO
            sumRub = sum * Public.pExrates.euro;
        } else { // RUB
            sumRub = sum;
        }
        return sumRub;
    }
    

    Public.showPreview = projectShowPreview;
    Public.hidePreview = projectHidePreview;
    
    
        
})()