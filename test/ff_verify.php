<? 
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/stdf.php");
if(is_release()) {
    exit;
}
?>
<html>
<head>
    <title>Верификация через сервис FF.RU</title>
</head>
<body>
    <table>
        <colgroup>
            <col width="200"/>
        </colgroup>
        <tr>
            <td>ФИО</td>
            <td>Фамилия Имя Отчество</td>
        </tr>
        <tr>
            <td>Дата рождения</td>
            <td>1950-01-01</td>
        </tr>
        <tr>
            <td>Документ</td>
            <td>Паспорт</td>
        </tr>
        <tr>
            <td>Номер паспорта</td>
            <td>1900 100001</td>
        </tr>
        <tr>
            <td>Дата выдачи</td>
            <td>2000-01-01</td>
        </tr>
        <tr>
            <td>Выдан</td>
            <td>УВД г. Города</td>
        </tr>
        <tr>
            <td>Телефон</td>
            <td>+79001000000</td>
        </tr>
    </table>
    <script>
        function ver_ff() {
            window.opener.location = '/income/ff.php?code=test';
            window.close();
        }
    </script>
    <input type="button" value="Верифицироваться" onclick="ver_ff()"/>
    
</body>
</html>