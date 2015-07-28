
docInit = function(is_vml) {

    svg = document;

    if(is_vml) {
        svg.getElementById('gToolTip').style.display = 'none';
        dots = svg.getElementsByTagName("oval");
        for(i = 0; i<dots.length; i++) {
            dots[i].attachEvent('onmouseover', function(ev) {
                el = ev.srcElement;

                el.style.width = 8;
                el.style.height = 8;
                el.style.top = parseInt(el.style.top)-1 + 'px';
                el.style.left = parseInt(el.style.left)-1 + 'px';

                x = parseFloat(el.style.left) - 50;
                y = parseFloat(el.style.top);

                if(y > 90) {
                    y -= 50;
                } else {
                    y += 15;
                }
                if(x < 0) x += (x*-1)+1;
                if(x+100 > 744) x -= x+100-740;

                svg.getElementById('gToolTip').style.left = x;
                svg.getElementById('gToolTip').style.top = y;
                svg.getElementById('gToolTip').style.display = '';

                str = svg.getElementById('gToolTip').getElementsByTagName('shape')[0];
                str.getElementsByTagName('stroke')[0].setAttribute('color', el.getElementsByTagName('fill')[0].getAttribute('color'));

                ratingstr = svg.getElementById('gToolTip').getElementsByTagName('shape')[1];
                ratingstr.getElementsByTagName('textpath')[0].setAttribute(
                    'string',
                    ratingstr.getElementsByTagName('textpath')[0]
                        .getAttribute('string')
                        .replace(/:(.*?)$/i, ': ' + el.getAttribute('ratingvalue'))
                );

                ratingdate = svg.getElementById('gToolTip').getElementsByTagName('shape')[2];
                ratingdate.getElementsByTagName('textpath')[0].setAttribute(
                    'string',
                    el.getAttribute('ratingdate')
                );
            });
            dots[i].attachEvent('onmouseout', function(ev) {
                el = ev.srcElement;

                el.style.width = 6;
                el.style.height = 6;
                el.style.top = parseInt(el.style.top)+1 + 'px';
                el.style.left = parseInt(el.style.left)+1 + 'px';

                svg.getElementById('gToolTip').style.display = 'none';

            });

        }
    } else {
        dots = svg.getElementsByTagName('circle');
        for(i = 0; i<dots.length; i++) {
            dots[i].addEventListener('mouseover', function() {
                tt = svg.getElementById('gToolTip');
                tt.setAttribute('style', 'display: ;');
                x = parseFloat(this.getAttribute('cx')) - 50;
                y = parseFloat(this.getAttribute('cy'));

                if(y > 90) {
                    y -= 50;
                } else {
                    y += 10;
                }

                if(x < 0) x += (x*-1)+1;
                if(x+100 > 744) x -= x+100-740;

                tt.setAttribute('transform', 'translate(' + x + ',' + y + ')');
                tt.getElementsByTagName('rect')[0].setAttribute('stroke', this.getAttribute('fill'));

                _tx = tt.getElementsByTagName('text')[0].textContent.split(':');
                _tx[1] = this.getAttribute('ratingvalue');
                
                tt.getElementsByTagName('text')[0].textContent = _tx.join(': ');
                tt.getElementsByTagName('text')[1].textContent = this.getAttribute('ratingdate');

                this.setAttribute('r', 5);
            }, false);

            dots[i].addEventListener('mouseout', function() {
                tt = svg.getElementById('gToolTip');
                tt.setAttribute('style', 'display: none;');
                this.setAttribute('r', 3);
            }, false);
        }
    }
};

var ie = /*@cc_on!@*/false;
if(ie) {
    onload = function() {
      if(!document.getElementById('dots_group')) {
          setTimeout(function() {
              onload();
          }, 300);
      } else {
          docInit(1);
      }
    };
}