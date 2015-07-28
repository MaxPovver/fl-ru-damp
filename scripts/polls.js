/**
 * создание опросов и голосование
 * нужен mootols.js
 */

var poll = {  
    // создание опросов 
	max: 0,
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
        if($('poll_ans_home') && $('poll_ans_home').getElement('li.poll-line div.tip-t2') != undefined) {
	       $('poll_ans_home').getElement('li.poll-line div.tip-t2').destroy();    
	    }
		var s  = $(this.parentObj).getElements('.poll-line').length - 1;
		var sr = $(this.parentObj).getElements('.poll-line')[s];
		if (s + 1 >= this.max) return;
		var clone = sr.cloneNode(true);
        clone.getElement('input[type=text]').value = '';
        clone.getElement('input[type=text]').name = 'answers[]';
        if ( namespace == 'Blogs' ) {
            sr.parentNode.insertBefore(clone, ((sr.nextSibling.tagName != 'undefined') ? sr.nextSibling: sr.nextSibling.nextSibling));
        }
        else {
            sr.parentNode.appendChild(clone, sr.parentNode);
        }
		var d = s + 1;
		var dr = $(this.parentObj).getElements('.poll-line')[d];
		if ( namespace == 'Blogs' ) {
            $(sr).getElements('.poll-add')[0].parentNode.innerHTML = '<span class="poll-add">&nbsp;</span>';
		}
		else {
		    $(sr).getElements('.poll-add')[0].parentNode.innerHTML = '<a class="poll-del" href="javascript:void(0)" onclick="poll.del(\''+namespace +'\', '+s+'); return false;"><img src="/images/btns/btn-remove-s.png" alt=""/></a>';
		}
		if (d + 1 >= this.max) {
		    if ( namespace == 'Blogs' ) {
                $(dr).getElements('.poll-add')[0].parentNode.innerHTML = '<span class="poll-add">&nbsp;</span>';
		    }
		    else {
                $(dr).getElements('.poll-add')[0].parentNode.innerHTML = '<a class="poll-del" href="javascript:void(0)" onclick="poll.del(\''+namespace +'\', '+d+'); return false;"><img src="/images/btns/btn-remove-s.png" alt=""/></a>';
		    }
		} else {
			this.btn(namespace, $(dr).getElements('.poll-add')[0].parentNode);
		}
		if ( namespace == 'Blogs' ) {
		    $(dr).getElements('.poll-del')[0].onclick = function(c) { 
                return function() {
                    poll.del(namespace, c); 
                    return false;
                }
    		}(this.elements.length);
		}
        $(dr).getElements('.poll-answer')[0].addEvent('focus', function(){
            document.onkeydown = null;
        });
        $(dr).getElements('.poll-answer')[0].addEvent('blur', function(){
            document.onkeydown = NavigateThrough;
        });
		$(dr).getElements('.poll-answer')[0].value = '';
		$(dr).getElements('.poll-answer')[0].disabled = false;
		$(dr).getElements('.poll-answer')[0].name = 'answers[]';
		$(dr).getElements('.poll-answer')[0].tabindex = '20'+d;
        $(dr).getElements('.poll-answer')[0].focus();
		if ($(dr).getElements('.poll-answer-exists')[0]) $(dr).getElements('.poll-answer-exists')[0].parentNode.removeChild($(dr).getElements('.poll-answer-exists')[0]);
		if ( namespace == 'Blogs' ) {
            $(dr).getElements('.poll-num')[0].innerHTML = d + 1;
		}
		this.elements[this.elements.length] = d;
	},
	
	del: function(namespace, index) {
		var addbtn = false;
		var o = $(this.parentObj).getElements('.poll-line');
		var n = this.elements[index];
		if (o.length == 1) {
			$(o[n]).getElements('.poll-answer')[0].value = '';
			$(o[n]).getElements('.poll-answer')[0].disabled = false;
			$(o[n]).getElements('.poll-answer')[0].name = 'answers[]';
			if ($(o[n]).getElements('.poll-answer-exists')[0]) {
				$(o[n]).getElements('.poll-answer-exists')[0].parentNode.removeChild($(o[n]).getElements('.poll-answer-exists')[0]);
			}
			return;
		}
		if ( namespace == 'Blogs' ) {
            o[n].parentNode.removeChild(o[n]);
		}
		else {
		    var v = $('poll_ans_home').getElements('.poll-line');
            $('poll_ans_home').removeChild(v[index]);
            
            for (var i=index+1; i<v.length; i++) {
                if ( $(v[i]).getElements('.poll-del')[0] )
    			$(v[i]).getElements('.poll-del')[0].parentNode.innerHTML = '<a class="poll-del" href="javascript:void(0)" onclick="poll.del(\''+namespace +'\', '+i+'); return false;"><img src="/images/btns/btn-remove-s.png" alt=""/></a>';
    		}
            
            this.btn(namespace, $('poll_ans_home').getElements('.poll-line')[$(this.parentObj).getElements('.poll-line').length -1].getElement('span'));
		}
                
		for (var i=0,c=this.elements.length; i<c; i++) {
			if (this.elements[i] > n) --this.elements[i];
		}
		delete this.elements[index];
        if ( namespace == 'Blogs' ) {
    		for (var i=n+1; i<o.length; i++) {
    			$(o[i]).getElements('.poll-num')[0].innerHTML = i;
    		}
        }
		if (n == o.length-1) {
			this.btn(namespace, $(o[o.length-2]).getElements('.poll-add')[0].parentNode);
		} else if (o.length+1 >= this.max) {
		    if (namespace, $(o[o.length-1]).getElements('.poll-add')[0])
			this.btn(namespace, $(o[o.length-1]).getElements('.poll-add')[0].parentNode);
		}
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
			if ( namespace == 'Blogs' ) {
                            xajax_BlogsPoll_Vote(id, votes, this.sess);
                        } else if ( namespace == 'Commune' ) {
                            xajax_CommunePoll_Vote(id, votes, this.sess);
                        } else {
                            eval('xajax_'+namespace+'Poll_Vote(id, votes, this.sess)');
                        }
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
                if ( namespace == 'Blogs' ) {
                    xajax_BlogsPoll_Vote(id, "", "");
                } else if ( namespace == 'Commune' ) {
                    xajax_CommunePoll_Vote(id, "", "");
                } else {
                    eval('xajax_'+namespace+'Poll_Vote(id, "", "")');
                }
	},
	
	showPoll: function(namespace, id) {
		$('poll-btn-vote-'+id).innerHTML = '';
		$('poll-btn-result-'+id).innerHTML = '';
                if ( namespace == 'Blogs' ) {
                    xajax_BlogsPoll_Show(id, this.types[id]);
                } else if ( namespace == 'Commune' ) {
                    xajax_CommunePoll_Show(id, this.types[id]);
                } else {
                    eval('xajax_'+namespace+'Poll_Show(id, this.types[id])');
                }
	},
	
	close: function(namespace, id) {
		var radio = $$('#poll-'+id+' input[name=poll_vote]');
		for (var i=0; i<radio.length; i++) radio[i].disabled = true;
		$('poll-btn-vote-'+id).innerHTML = '';
		$('poll-btn-result-'+id).innerHTML = '';
		$('poll-btn-close-'+id).innerHTML = '';
                if ( namespace == 'Blogs' ) {
                    xajax_BlogsPoll_Close(id);
                } else if ( namespace == 'Commune' ) {
                    xajax_CommunePoll_Close(id);
                } else {
                    eval('xajax_'+namespace+'Poll_Close(id)');
                }
	},
	
	remove: function(namespace, id) {
		if (confirm('Уверены, что хотите удалить опрос?')) {
			var radio = $$('#poll-'+id+' input[name=poll_vote]');
			for (var i=0; i<radio.length; i++) radio[i].disabled = true;
			$('poll-btn-vote-'+id).innerHTML = '';
			$('poll-btn-result-'+id).innerHTML = '';
			$('poll-btn-close-'+id).innerHTML = '';
			$('poll-btn-remove-'+id).innerHTML = '';
                        if ( namespace == 'Blogs' ) {
                            xajax_BlogsPoll_Remove(id);
                        } else if ( namespace == 'Commune' ) {
                            xajax_CommunePoll_Remove(id);
                        } else {
                            eval('xajax_'+namespace+'Poll_Remove(id)');
                        }
		}
	}
	
};
