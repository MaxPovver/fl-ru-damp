	var acttab;
	acttab = 1;
	function refreshInsets(e,leftn,leftc,rightn,rightc,load_xajax,filter, kind)
	{
		cat_comments = new Array(4);
		cat_comments[0] = 'В данную категорию публикуются разовые проекты';
		cat_comments[1] = 'В данную категорию публикуются разовые проекты';
		cat_comments[2] = 'В данной категории публикуются вакансии на постоянную удаленную работу с оплатой за период';
		cat_comments[3] = 'В данной категории публикуются конкурсные проекты';
		cat_comments[4] = 'В данной категории публикуются вакансии на постоянную или попроектную работу в офисе';
		
			for (i = 0; i < 4; i++)
			{
			  if (i == (rightn-1))
			  {
          document.getElementById('b' + i).className = 'act_menu';
			  }
			  else
			  {
  			  if (i == rightn)
  			  {
            document.getElementById('b' + i).className = 'user_menu_la';
  			  }
  			  else
  			  {
  			    if (i == 0)
  			    {
              document.getElementById('b' + i).className = 'user_menu_l';
  			    }
  			    else
  			    {
              document.getElementById('b' + i).className = 'user_menu';
  			    }
  			  }
			  }
			}
			if (rightn == 4)
			{
        document.getElementById('rc').src = '/images/menu_activ_r.gif';
			}
			else
			{
        document.getElementById('rc').src = '/images/menu_passiv_r.gif';
			}
	  	
		document.getElementById('cat_comment').innerHTML = cat_comments[rightn];
		acttab = kind;
		if (!acttab) acttab = 0;
		if (acttab == 1) acttab = 0;
		//if (acttab == 2) acttab = 3 else if (acttab == 3) acttab = 2;
		
		if (load_xajax){
			document.getElementById('processing').style.visibility = 'visible';
			xajax_TabChange(acttab, 1, filter);
		}
	}
	
	var exists;
	var allStretch;
	var lastobj;

	function inpscr(str){
		return str.replace("<BR>", "\n");
	}
	
	function init(gr_id){
		var stretchers = $$('div.menu_content'); 
		var togglers = $$('div.display'); 
		
		preLoad = new Image(); preLoad.src = '/images/white-arrowd.gif';
		
		var myAccordion = new Fx.Accordion(togglers, stretchers, { opacity: false, alwaysHide: true,
		 transition: Fx.Transitions.quadOut, show: gr_id, duration: 400,
			
			onActive: function(toggler, stretcher){
				toggler.setStyle('backgroundImage', 'url(\'/images/white-arrowd.gif\')');
			},
		
			onBackground: function(toggler, stretcher){
				toggler.setStyle('backgroundImage', 'url(\'/images/white-arrow.gif\')');
			}
		});
		document.getElementById('fl2_sidemenu').style.visibility = 'visible';
	}
	
	function ResetErrors(){
		var stretchers = $$('div.errorBox');
		stretchers.each(function(h3, i){
			h3.outerHTML="";
		});
	}

	function toggleActivity(element) {
		if ($(element).id=='newOfferContactsLink_inactive')
			$(element).id='newOfferContactsLink_active';
		else
			$(element).id='newOfferContactsLink_inactive';
	}
		
	function SwitchArrow(obj){
		if (obj.id != 'ch'){
			obj.style.backgroundImage='url(\'/images/white-arrowd.gif\')';
			if (lastobj) lastobj.style.backgroundImage='url(\'/images/white-arrow.gif\')';
		}
		lastobj = obj;
	} 
		
	function hidePayed(chk){
		if (chk){
			document.getElementById('checkbox1').disabled=true;
			document.getElementById('checkbox2').disabled=true;
			if (document.getElementById('checkbox2').checked) {
				payed2Slider.hide();
				document.getElementById('checkbox2').checked=false;
			}
			if (document.getElementById('checkbox1').checked) {
				payed1Slider.hide();
				document.getElementById('checkbox1').checked=false;
			}
		}
		else{
			document.getElementById('checkbox1').disabled=false;
			document.getElementById('checkbox2').disabled=false;
		}
	}
	