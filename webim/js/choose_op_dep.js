var url = null;

function select_change(s) {
	var idx = s.selectedIndex;
  document.getElementById('start-chat').disabled = idx == 0;
}

function select_change_optional(s) {
	var idx = s.selectedIndex;
  url = s.options[idx].value;
}

function click_button(b) {
	b.disabled = true;
  var s = document.getElementById('choose-select');
  var idx = s.selectedIndex;
	window.location.href = s.options[idx].value;
}