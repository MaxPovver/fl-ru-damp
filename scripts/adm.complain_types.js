// логика для админки ТИПЫ ЖАЛОБ НА ПРОЕКТЫ
(function(){
window.addEvent('domready', function(){
    var
        $complainTypes = $('complain_types'),
        $complainTypesForm = $('complain_types_form'),
        $complainTypeTemplate = $('complain_type_template').getElement('div'),
        $addComplainType = $('add_complain_type'),
        $saveComplainTypes = $('save_complain_types'),
        $noComplains = $('no_complains');


    $addComplainType.addEvent('click', addComplainType);
    $complainTypes.addEvent('click', delComplainType);
    $complainTypes.addEvent('change', checkboxChanged);
    $saveComplainTypes.addEvent('click', saveComplainTypes);

    // добавить новую строку для ввода типа жалобы
    function addComplainType () {
        $complainTypeTemplate.clone().inject($complainTypes, 'bottom');
        $noComplains.setStyle('display', 'none');
    }

    // удаляет тип жалобы
    function delComplainType (event) {
        // проверяем что нажата именно кнопка удалить
        if (!event.target.hasClass('del_complain_type')) {
            return;
        }
        // не удаляем, а скрываем и помечаем как удаленный
        var $parent = event.target.getParent('div.complain-type');
        $parent.setStyle('display', 'none');
        $parent.addClass('complain-type-deleted');
        $parent.getElement('input[name="del[]"]').set('value', 1);
        
        // если удалили последнюю жалобу, то выводим надпись
        if ($complainTypes.getElements('div.complain-type:not(.complain-type-deleted)').length === 0) {
            $noComplains.setStyle('display', '');
        }
    }
    
    // изменяет value рядом с измененным checkbox'ом
    // если чекбокс отмечен, то value = 1, иначе 0
    // это нужно, потомучто если чекбокс не отмечен, то он не передается на сервер
    function checkboxChanged (event) {
        var $checkbox = event.target;
        if ($checkbox.get('type') !== 'checkbox') {
            return;
        }
        
        var hiddenInput = $checkbox.getNext('input[type="hidden"]');
        hiddenInput && hiddenInput.set('value', +$checkbox.get('checked'));
    }
    
    // сохраняет все изменения
    function saveComplainTypes () {
        $complainTypesForm.submit();
    }
    
    

});
}());