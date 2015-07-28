	
	function isNumeric(str){
		var numericExpression = /^[0-9]+(\.[0-9]+)?$/;
		if(str.match(numericExpression)){
			return true;
		}else{
			alert("¬ведите корректную сумму!");
			return false;
		}
	}
	
	function ch_p(v){
		sum = document.getElementById("fmsum");
		val = v.value / exch[v.name];
		if ( isNaN(val) ) return false;
		sum.innerHTML = Math.round(val*100)/100;
		for (i = 0; kd = document.getElementById("payment").kind[i]; i++){
			if (kd.value == v.name)	kd.checked=true;
		}
	}
	
	function ch_k(v){
		frm = document.getElementById("payment");
		ch_p(frm[v.value]);
	}