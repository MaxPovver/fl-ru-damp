function cdLocalTime(container, servertimestring, offsetMinutes, targetdate, debugmode) {
    if (!document.getElementById || !document.getElementById(container)) return;
    this.container=document.getElementById(container);
    this.localtime=this.serverdate=new Date(servertimestring);
    this.targetdate=new Date(targetdate);
    this.debugmode=0;
    this.timesup=false;
    this.localtime.setTime(this.serverdate.getTime()+offsetMinutes*60*1000);
    this.updateTime();
}

cdLocalTime.prototype.updateTime=function() {
    var thisobj=this;
    this.localtime.setSeconds(this.localtime.getSeconds()+1);
    setTimeout(function(){thisobj.updateTime()}, 1000);
};

cdLocalTime.prototype.displaycountdown=function(baseunit, functionref) {
    this.baseunit=baseunit;
    this.formatresults=functionref;
    this.showresults();
};

cdLocalTime.prototype.showresults=function() {
    var thisobj=this;
    var debugstring=(this.debugmode)? "<p style=\"background-color: #FCD6D6; color: black; padding: 5px\"><big>Debug Mode on!</big><br /><b>Current Local time:</b> "+this.localtime.toLocaleString()+"<br />Verify this is the correct current local time, in other words, time zone of count down date.<br /><br /><b>Target Time:</b> "+this.targetdate.toLocaleString()+"<br />Verify this is the date/time you wish to count down to (should be a future date).</p>" : "";
    
    var timediff=(this.targetdate-this.localtime)/1000;
    if (timediff<0){
        this.timesup=true;
        this.container.innerHTML=this.formatresults();
        return;
    }
    var oneMinute=60;
    var oneHour=60*60;
    var oneDay=60*60*24;
    var dayfield=Math.floor(timediff/oneDay);
    var hourfield=Math.floor((timediff-dayfield*oneDay)/oneHour);
    var minutefield=Math.floor((timediff-dayfield*oneDay-hourfield*oneHour)/oneMinute);
    var secondfield=Math.floor((timediff-dayfield*oneDay-hourfield*oneHour-minutefield*oneMinute));
    if (this.baseunit=="hours"){
        hourfield=dayfield*24+hourfield;
        dayfield="n/a";
    }
    else if (this.baseunit=="minutes"){
        minutefield=dayfield*24*60+hourfield*60+minutefield;
        dayfield=hourfield="n/a";
    }
    else if (this.baseunit=="seconds"){
        var secondfield=timediff;
        dayfield=hourfield=minutefield="n/a";
    }
    this.container.innerHTML=debugstring+this.formatresults(dayfield, hourfield, minutefield, secondfield);
    setTimeout(function(){thisobj.showresults()}, 1000);
};

function formatresults() {
    if (this.timesup==false){
        var displaystring= zz(arguments[1])+" "+getTermination(arguments[1],[['час'],['часа'],['часов']])+" "+zz(arguments[2])+" "+getTermination(arguments[2],[['минуту'],['минуты'],['минут']])+" "+zz(arguments[3])+" "+getTermination(arguments[3],[['секунду'],['секунды'],['секунд']]);
    }
    else {
        var displaystring='... уже скрыт';
        var vbx=document.getElementById('valentin');
        if(vbx)vbx.style.display='none';
    }
    return displaystring;
}

function formatresultVerify() {
    
    var days = zz(arguments[0]),
        hour = zz(arguments[1]),
        minute = zz(arguments[2]);

    var html = '<div class="b-layout__txt b-layout__txt_inline-block b-layout__txt_valign_top">';
        html += days.replace(/^(\d{1})/, '<span class="b-promo__digital b-promo__digital_margright_3">$1</span>').replace(/(\d{1})$/, '<span class="b-promo__digital">$1</span>');
        html += '<div class="b-layout__txt b-layout__txt_fontsize_11">дней</div>';
        html += '</div>&nbsp;&#58;&nbsp;';
        html += '<div class="b-layout__txt b-layout__txt_inline-block b-layout__txt_valign_top">'
        html += hour.replace(/^(\d{1})/, '<span class="b-promo__digital b-promo__digital_margright_3">$1</span>').replace(/(\d{1})$/, '<span class="b-promo__digital">$1</span>');
        html += '<div class="b-layout__txt b-layout__txt_fontsize_11">часов</div>';
        html += '</div>&nbsp;&#58;&nbsp;';
        html += '<div class="b-layout__txt b-layout__txt_inline-block b-layout__txt_valign_top">';
        html += minute.replace(/^(\d{1})/, '<span class="b-promo__digital b-promo__digital_margright_3">$1</span>').replace(/(\d{1})$/, '<span class="b-promo__digital">$1</span>');
        html += '<div class="b-layout__txt b-layout__txt_fontsize_11">минут</div>';
        html += '</div>';
        
    if (this.timesup==false){
        var displaystring = html;
    } else {
        var displaystring='... уже скрыт';
        $$('.verify_date').hide();
    }
    return displaystring;
}

function formatresults3() {
    var h = Number(arguments[1] < 10) ? '0'+arguments[1] : arguments[1];
    var m = Number(arguments[2] < 10) ? '0'+arguments[2] : arguments[2];
    var s = Number(arguments[3] < 10) ? '0'+arguments[3] : arguments[3];
    
    if (this.timesup==false){
        var displaystring=""+h+":"+m+":"+s+"";
    }
    else{ 
        var displaystring="00:00:00";
    }
    return displaystring;
}

function getTermination(number,variants) {
    var mod1 = number % 10;
    if (mod1 == 1 && (number < 10 || number > 20)) {
        return variants[0];
    } else if ((number >= 10 && number <= 20) || mod1 > 4 || mod1 == 0) {
        return variants[2];
    } else {
        return variants[1];
    }
}
function zz(s) { return ('0'+s).replace(/0(..)$/, '$1'); }
