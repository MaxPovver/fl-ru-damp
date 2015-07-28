function getOpacityProperty()
{
  if (typeof document.body.style.opacity == 'string')
    return 'opacity';
  else if (typeof document.body.style.MozOpacity == 'string')
    return 'MozOpacity';
  else if (typeof document.body.style.KhtmlOpacity == 'string')
    return 'KhtmlOpacity';
  else if (document.body.filters)
    return 'filter';

  return false;
}

function setElementOpacity(elem, nOpacity)
{
  var opacityProp = getOpacityProperty();
  if (!elem || !opacityProp) return;
  if (opacityProp=="filter") {
    nOpacity *= 100;
    var oAlpha = elem.filters['DXImageTransform.Microsoft.alpha'] || elem.filters.alpha;
    if (oAlpha) oAlpha.opacity = nOpacity;
    else elem.style.filter += "progid:DXImageTransform.Microsoft.Alpha(opacity="+nOpacity+")"; // Для того чтобы не затереть другие фильтры используем "+="
  }
  else
    elem.style[opacityProp] = nOpacity;
}


function createHTMLElement(tag, styles, properties, obj, parent, before)
{
	var elm = obj!=null ? obj : document.createElement(tag);

	if(parent==null)
		document.body.appendChild(elm);
	else {
		if(before==null)
		  parent.appendChild(elm);
		else
		  parent.insertBefore(elm, before);
	}

	for(sty in styles) {
		if(sty.toLowerCase()=='opacity')
			setElementOpacity(elm, styles[sty]);
		else
		  elm.style[sty] = styles[sty];
	}

	for(prop in properties)
		elm[prop] = properties[prop];

	return elm;
}


function frameObject(styles, properties, specEffect, obj, parent, before)
{
	if(obj)
		this.object = obj;
	else {
	  this.object = document.createElement('DIV');
		if(!parent)
		  document.body.appendChild(this.object);
		else {
			if(before==null)
		    parent.appendChild(this.object);
			else
		    parent.insertBefore(this.object, before);
		}
	}

	this.updateOpacity=function(opa) { setElementOpacity(this.object, opa); }

	this.update
	=function(styles, properties, specEffect)
	{
		for(sty in styles) {
			if(sty.toLowerCase()=='opacity')
				this.updateOpacity(styles[sty]);
			else
			  this.object.style[sty] = styles[sty];
		}

		for(prop in properties)
		  this.object[prop] = properties[prop];
		if(specEffect)
		  specEffect(this);
	}

	this.update(styles, properties, specEffect);
}


// Нормальный плейер. На каждый кадр вызывается onplay. Без всяких причуд.
function RTPlayer(
 instanceName,
 onplay, // функция, которая будет вызываться при каждом новом кадре. Принимает данный объект.
         // Должна возвращать true, чтобы плейер продолжал работу, false -- срабатывает stop().
 interval)
// Пример:
// var bebePlayer=new RTPlayer('bebePlayer',
//                              function(ply) { alert('bebe');return (ply.frameNumber<10); },
//														 200);
// bebePlayer.play();
{
	
// public :
	
	this.instanceName = instanceName;
	this.interval     = interval;
	this.frameNumber  = 0;
	this.step         = 1;
  this.onplay       = onplay;
	this.status       = 'Stopped';

	this.play
	=function(timer)
	{
		if(this.status=='Playing')
			return;

		this.status = 'Playing';

		this.frameNumber = 0;
		if (this.interval && this.interval>0)
	  	this.intervalID = window.setInterval("try {"+this.instanceName+".__p()} catch(e) { alert('RTPlayer: '+e.message); "+this.instanceName+".stop() }", this.interval);
		else
			this.__p();
	};

	this.stop
	=function()
	{
		if(this.intervalID)
			window.clearInterval(this.intervalID);

		this.intervalID = null;
		this.status = 'Stopped';
	};


// private :

	this.intervalID = null;
	this.__p
	=function()
	{
		if(!this.onplay || this.onplay(this)===false)
			this.stop();

		this.frameNumber++;
	};
};
