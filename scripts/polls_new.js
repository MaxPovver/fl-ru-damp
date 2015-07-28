/**
 * создание опросов и голосование
 * нужен mootols.js
 */
var poll_new = {  
    // создание опросов 
	max: 10, // максимальное количество ответов
	sess: '',
	parentObj: '',
	elements: [0],
	types: [],
	
	init: function(namespace, parentObj, max, sess) {
		this.parentObj = parentObj;
		this.max  = max;
		this.sess = sess;
		var count = 0;
		if ($(this.parentObj) && (count = $(this.parentObj).getElements('.poll-answer').length)) {
			this.elements = [];
			for (var i=0; i<count; i++) this.elements[i] = i;
			this.index = count;
			if (count < this.max) this.btn(namespace, $($(this.parentObj).getElements('.poll-line')[this.elements[this.elements.length-1]]).getElements('.poll-add')[0].parentNode);
		}
	},

	add: function(namespace) {
            // скрыть сообщение об ошибке
   	    if($('poll_ans_home') && $('poll_ans_home').getElement('li.poll-line div.tip-t2') != undefined) {
	       $('poll_ans_home').getElement('li.poll-line div.tip-t2').destroy();    
	    }
            var answers = $$('table[id^=poll-]'); // список ответов
            var s  = answers.length - 1; // номер последнего ответа
            var sr = answers[s]; // последний ответ до добавления (потом становится предпоследним)
            if (s + 1 >= this.max) return;
            var clone = sr.cloneNode(true); // дублируем последний ответ
            clone.getElement('input[type=text]').addEvent('focus', function(){
                document.onkeydown = null;
            });
            clone.getElement('input[type=text]').addEvent('blur', function(){
                document.onkeydown = NavigateThrough;
            });
            clone.getElement('input[type=text]').value = '';
            clone.getElement('input[type=text]').name = 'answers[]';
            clone.getElement('input[type=text]').tabindex = '20' + s + 1;
            var id = +clone.id.match(/poll-(\d+)/)[1];
            var new_id = id + 1;
            clone.id = 'poll-' + new_id;
            sr.parentNode.appendChild(clone, sr.parentNode); // добавляем поле ля нового ответа
            var d = s + 1; // новое количество ответов
            var dr = $$('table[id^=poll-]')[d]; // новый последний ответ
            // меняем кнопку у, теперь уже предыдущего, ответа
            $(sr).getElement('tr td:last-child').innerHTML = '<a href="javascript:void(0)" onclick="poll_new.del(\'' + namespace + '\', ' + id + '); return false;" class="b-button b-button_m_delete"></a>';
            // убираем возможность добавить ответов больше чем положено
            if (d + 1 >= this.max) {
                $(dr).getElement('tr td:last-child').innerHTML = '<a href="javascript:void(0)" onclick="poll_new.del(\'' + namespace + '\', ' + new_id + '); return false;" class="b-button b-button_m_delete"></a>';
            }
            $(dr).getElement('input[type=text]').focus();
            this.elements[this.elements.length] = d;
	},
	
	del: function(namespace, index) {
            $$('table[id=poll-' + index + ']')[0].dispose();
	},
        
	btn: function(namespace, obj) {
		var a = document.createElement('a');
		a.href = 'javascript:void(0)';
		a.className = 'poll-add';
		if ( namespace == 'Blogs' ) {
		    a.innerHTML = '<img id="pollimg" src="/images/addpoll.png" width="15" height="15" border="0" alt="Добавить ответ" title="Добавить ответ">';
		}
		else {
            a.innerHTML = '<img id="pollimg" src="/images/btns/btn-add-s.png" border="0" alt="Добавить ответ" title="Добавить ответ">';
		}
		a.onclick = function() {
			poll.add(namespace); 
			return false;
		}
		obj.innerHTML = '';
		obj.appendChild(a);
	},
 	
	vote: function(namespace, id) {
		var votes = [];
		var answers = $$("input[id^='poll-" + id + "']");
		for (var i=0,j=0; i<answers.length; i++) {
			if (answers[i].checked) {
				votes[j++] = answers[i].value;
			}
		}
		if (votes.length) {
			for (var i=0; i<answers.length; i++) {
				answers[i].disabled = true;
			}
			$('poll-btn-vote-'+id).innerHTML = '';
			$('poll-btn-result-'+id).innerHTML = '';
			eval('xajax_'+namespace+'Poll_Vote(id, votes, this.sess)');
		} else {
			alert('Выберите вариант ответа');
			return false;
		}
	},
	
	showResult: function(namespace, id) {
		var radio = $$('#poll-'+id+' input[name=poll_vote]');
		for (var i=0; i<radio.length; i++) radio[i].disabled = true;
		
		radio = $$('#poll-'+id+' input[type=radio]');
		this.types[id] = ( radio.length ) ? 1 : 0;
		
		$('poll-btn-vote-'+id).innerHTML = '';
		$('poll-btn-result-'+id).innerHTML = '';
		eval('xajax_'+namespace+'Poll_Vote(id, "", "")');
	},
	
	showPoll: function(namespace, id) {
		$('poll-btn-vote-'+id).innerHTML = '';
		$('poll-btn-result-'+id).innerHTML = '';
		eval('xajax_'+namespace+'Poll_Show(id, this.types[id])');
	},
	
	close: function(namespace, id) {
		var radio = $$('#poll-'+id+' input[name=poll_vote]');
		for (var i=0; i<radio.length; i++) radio[i].disabled = true;
		$('poll-btn-vote-'+id).innerHTML = '';
		$('poll-btn-result-'+id).innerHTML = '';
		$('poll-btn-close-'+id).innerHTML = '';
		eval('xajax_'+namespace+'Poll_Close(id)');
	},
	
	remove: function(namespace, id) {
		if (confirm('Уверены, что хотите удалить опрос?')) {
			var radio = $$('#poll-'+id+' input[name=poll_vote]');
			for (var i=0; i<radio.length; i++) radio[i].disabled = true;
			$('poll-btn-vote-'+id).innerHTML = '';
			$('poll-btn-result-'+id).innerHTML = '';
			$('poll-btn-close-'+id).innerHTML = '';
			$('poll-btn-remove-'+id).innerHTML = '';
			eval('xajax_'+namespace+'Poll_Remove(id)');
		}
	}
	
};