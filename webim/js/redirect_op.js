	var url = null;
	var s_op = null;
	
	function select_change_dep(s) {
    var c = document.getElementById('operator-select-container');
    for (var childItem in c.childNodes) {
      if (c.childNodes[childItem].nodeType == 1) {
        c.childNodes[childItem].style.display = 'none';
      }
    }
		var v = s.options[s.selectedIndex].value;
		if(v == '') {
//			if(s_op) {
//				document.getElementById('operators-' + s_op).style.display = 'none';
//				s_op = null;
//			}
//			
			url = null;
			document.getElementById('popup-redirect-btn').disabled = true;
		} else {
			if(v != 'no-dep') {
				url = s.options[s.selectedIndex].title;
				document.getElementById('popup-redirect-btn').disabled = false;
			} 
			s_op = v;
			document.getElementById('operators-' + s_op).style.display = '';
		}
	}
	
	function select_change_op(s) {
		var v = s.options[s.selectedIndex].value;
		if(v == '') {
			url = null;
			document.getElementById('popup-redirect-btn').disabled = true;
		} else {
			url = v;
			document.getElementById('popup-redirect-btn').disabled = false;
		}
	}

	function click_button(b) {
		b.disabled = true;
		window.location.href = url;
	}