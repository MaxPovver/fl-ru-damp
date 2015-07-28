window.addEvent('domready', 
    function() {    
        new tcal ({'formname': 'frm_filter',  'controlname': 'from_date', 'iconId': 'from_date_btn' });
        new tcal ({'formname': 'frm_filter',  'controlname': 'to_date', 'iconId': 'to_date_btn' });
        
        $('clear_btn').addEvent('click', function(){
            $('invoiced').set('checked', false);
            $('accepted').set('checked', false);
            $('search_string').set('value', '');
            
            var date = new Date();
            var day  = date.getDate();
            var mon  = date.getMonth() + 1;
            var year = date.getFullYear();
            
            if ( day < 10 ) day = '0' + day;
            if ( mon < 10 ) mon = '0' + mon;
            
            $('from_date').set('value', '01-'+mon+'-'+year);
            $('to_date').set('value', day+'-'+mon+'-'+year);
        });
    }
);

function bankpfCheckDateFilter() {
    var oDateF = $('from_date');
    var oDateT = $('to_date');
    var sError = '';
    
    if ( oDateF && oDateT ) {
        sError = _bankpfCheckDateFilter( $('from_date').get('value'), '”кажите корректную начальную дату' );
        
        if ( !sError ) {
            sError = _bankpfCheckDateFilter( $('to_date').get('value'), '”кажите корректную конечную дату' );
        }
    }
    
    if ( sError ) {
        alert( sError );
        return false;
    }
    else {
        return true;
    }
}

function _bankpfCheckDateFilter( sDate, sErrMsg ) {
    if ( sDate ) {
        aDate = sDate.split('-');
        
        if ( aDate.length == 3 ) {
            var nDay  = parseInt( aDate[0], 10 );
            var nMon  = parseInt( aDate[1], 10 );
            var nYear = parseInt( aDate[2], 10 );
            
            if ( isNaN(nDay) || nDay != aDate[0] 
                || isNaN(nMon) || nMon != aDate[1] || nMon <= 0 || nMon > 12 
                || isNaN(nYear) || nYear != aDate[2] || nYear <= 0 
            ) {
                return sErrMsg;
            }
            
            var aDaysNum = new Array( 31, 28, 31, 30, 31, 30, 31, 31, 30, 31, 30, 31 );
            
            if ( nMon == 2 ) {
                if ( nYear % 400==0 || ( nYear % 100!=0 && nYear % 4==0 ) ) {
                   aDaysNum[1] = 29;
                }
            }
            
            if ( nDay <= 0 || nDay > aDaysNum[nMon-1] ) {
                return sErrMsg;
            }
        }
        else {
            return sErrMsg;
        }
    }
    else {
        return sErrMsg;
    }
    
    return '';
}