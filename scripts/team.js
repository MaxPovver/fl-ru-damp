function ShowAddCategoryForm() {
    HideEditCategoryForm();
    HidePeopleTeamForm();
    $('addcategoryform').setStyle('display','block');
    window.location = '#addcategoryform';
}

function HideAddCategoryForm() {
    $('addcategoryform').setStyle('display','none');
}

function ShowEditCategoryForm(id) {
    HideAddCategoryForm();
    HidePeopleTeamForm();
    $('editcategoryform').setStyle('display','block');
    if(id!=0) {
        $('ecf_id').value = id;
        $('ecf_name').value = $('d_category_name_'+id).value;
        $('ecf_number').value = $('d_category_number_'+id).value;
    }
    window.location = '#editcategoryform';
}

function HideEditCategoryForm() {
    $('editcategoryform').setStyle('display','none');
}

function DeleteCategory(id) {
    $('dcf_id').value = id;
    $('dcf').submit();
}

function DeleteTeamPeople(id) {
    $('dtf_id').value = id;
    $('dtf').submit();
}

function ShowAddPeopleTeamForm() {
    HideAddCategoryForm();
    HideEditCategoryForm();
    $('people_team_form').setStyle('display','block');
    $('people_team_form_header').set('html','Добавить сотрудника');
    $('teampeopleaction').value="addpeople";
    $('people_team_form_btn').value="Добавить";
    $('btnteamdeletephoto').setStyle('display','none');
    $('pt_photo_file').setStyle('display','none');
    window.location = '#peopleteamform';
}

function HidePeopleTeamForm() {
    $('people_team_form').setStyle('display','none');
}

function ShowEditPeopleTeamForm(id) {
    $('people_team_form').setStyle('display','block');
    $('people_team_form_header').set('html','Редактировать сотрудника');
    $('teampeopleaction').value="updatepeople";
    $('people_team_form_btn').value="Сохранить";
    $('btnteamdeletephoto').setStyle('display','inline');
    $('pt_photo_file').setStyle('display','none');
    xajax_GetPeopleTeamInfo(id);
    window.location = '#peopleteamform';
}

function HideErrorMessages() {
    $$('.errorBox').setStyle('display','none');
}

function ResetTeamForm() {
    $('pt_login').set('value','');
    $('pt_name').set('value','');
    $('pt_occupation').set('value','');
    $('pt_position').set('value','');
    $('pt_info').set('value','');
    $('pt_photo_file').setStyle('display','none');
}

