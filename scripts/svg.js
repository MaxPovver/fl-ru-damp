Raphael.fn.rating = {
    //общие стили
    styles: {
        //размеры графика
        size: [744, 140],
        //фон
        bg: {
            fill: '#eee',
            stroke: 'none'
        },
        //фон ПРО
        bg_pro: {
            fill: '#FFF9E7',
            stroke: 'none'
        },
        //дни/месяцы
        label_font: '11px Tahoma',
        label_def: '#B2B2B2',
        label_cur: '#666666',
        label_dayoff: '#ff6d1b',

        dots: {
            r: ('\v'=='v' ? 4 : 3.3),
            fill: '#5f5f5f',
            stroke: '#ffffff',
            'stroke-width': ('\v'=='v' ? 1.8 : 2)
        },
        
        dotsPRO: {
            r: ('\v'=='v' ? 4 : 3.3),
            fill: '#ff6d1b',
            stroke: '#ffffff',
            'stroke-width': ('\v'=='v' ? 1.8 : 2)
        }
    },

    /**
     * График за месяц
     */
    monthly: function(config) {
        
        graph = this.rating;
        this.config = config;

        data = config.data;
        data_r = {};
        for ( var k in data ) { data_r[k] = data[k]; }
        pro = config.pro;

        for (var i = 0; i < data.length; i++) {
            if(data[i]<0) { data[i] = 0; }
        }

        if(config.w) {
            graph.styles.size[0] = w;
        }

//        cd = config.regdate ?  config.regdate.split('-') : config.startdate.split('-');
        cd = config.startdate.split('-');
        date = new Date(cd[0],cd[1]-1,cd[2]);

        popup = graph.popupAdd();

        //основной фон
        this.rect(0, 0, graph.styles.size[0], graph.styles.size[1])
            .attr(graph.styles.bg);

        cols = config.days;
        col = graph.styles.size[0]/config.days;

        //даты
        labelSet = this.set();
        for(i = 0; i < config.days; i++) {
            l = i+1;
            x = col * i + (col/2).round();

            lb = this.text(x, (graph.styles.size[1]+12), l)
            .attr({
                fill: graph.styles.label_def
            });

            if(config.hilight.contains(i+1)) lb.attr({
                fill: graph.styles.label_dayoff
            });
            if(i+1 == config.cur) lb.attr({
                fill: graph.styles.label_cur
            });

            labelSet.push(lb);
        }
        labelSet.attr({
            font: graph.styles.label_font
        });


        //рассчитаем Y-координаты точек для начала
        coord = [];

        sortAsc = function(p1, p2) {
            return (p1-p2)
        }
        sortDesc = function(p1, p2) {
            return (p2-p1)
        }
        minMax = [];
        getMinMax = function(arr) {
            tmp = $A(arr);
//            tmp.shift();
            tmp.sort(sortAsc);
            minMax[0] = tmp[0];
            tmp.sort(sortDesc);
            minMax[1] = tmp[0];

            return minMax;
        };
        minMax = getMinMax(data);

        h_diff = minMax[0]*-1;

        if(Math.abs(minMax[0]-minMax[1]) < graph.styles.size[1] && (minMax[0] >= 0 && minMax[1]>=0)) h_diff = 0;
//        zoom = 1;
        zoom = (minMax[1]+h_diff)/graph.styles.size[1];

        mpl = 0.7;
        mpl1 = mpl*1.30;
        if(minMax[0] == 0) {
            h_diff = 0;
            mpl1 = 1;
        }

        if(minMax[0] < 0 && Math.abs(minMax[0]-minMax[1]) > (graph.styles.size[1]*mpl)) {
            pct =  Math.abs(minMax[0]-minMax[1]) / (graph.styles.size[1]*mpl);
        } else {
            pct = 1;
            if(minMax[1] > (graph.styles.size[1]*mpl)) {
                pct = minMax[1] / (graph.styles.size[1]*mpl);
            }
        }

        $each(data, function(d, i) {
            _m = (d + h_diff)/pct;
            coord[i] = graph.styles.size[1]*mpl1 - _m;
        });

        //точки и линия
        dotsSet = this.set();
        pathStr = [];
        path = [];
        pathPartsLength = [];
        for(i = 0, cc = 0; i < coord.length; i++) {
            if(data[i] == null) {
                date.increment();
                continue;
            }
            x = col * i + (col/2);
            y = coord[i];

            if(i == 0) {
                x1 = 0;
                y1 = y;

                pathStr.push("M" + x1 + ", " + y1);
                pathStr.push("L" + x + ", " + y1);
            } else {
                if(dotsSet.length == 0) {
                    x1 = x - (col/2);
                    y1 = y;

                    pathStr.push("M" + x1 + ", " + y1);
                } else {
                    x1 = dotsSet[cc-1].attr('cx') - (col/2);
                    y1 = dotsSet[cc-1].attr('cy');
                }

                pathStr.push("L" + x + ", " + y);
            }
            path.push([x, y]);

            if(i == coord.length-1) {
                pathStr.push("L" + (x+ (col/2)) + ", " + y);
                path.push([(x+ (col/2)), y]);
            }

            x2 = x;
            y2 = y;
            sqr = (x1-x2)^2 + (y1-y2)^2;
            if(sqr < 0) sqr *= -1;
            pathPartsLength[i] = Math.sqrt(sqr);

            c = this.circle(x, y, 3.3);
            $(c.node).setAttribute('rating_value', data_r[i].toFloat().round(2));
            $(c.node).setAttribute('rating_date', date.format('%d.%m.%Y'));
            dotsSet.push(c);
            cc++;

            date.increment();
        }
        dotsSet.attr(graph.styles.dots);

        pathSet = this.set();
        pathSet.push(this.path(pathStr.join('')));
        pathSet.attr({
            stroke: '#5f5f5f',
            fill: 'none',
            'stroke-width': 1
        });

        //нижняя заливка
        bottomFill = this.set();
        bfPath = $A(pathStr);

        if(path.length) {
            bfPath.push("L" + path[path.length-1][0] + ", " + graph.styles.size[1]);
            if(path[0][0] != 0) {
                _xc = path[0][0] - (col/2);
                _yc = path[0][1];
                bfPath.push("L{xc}, ".substitute({xc: _xc}) + graph.styles.size[1]);
                bfPath.push("L{xc}, {yc}".substitute({xc: _xc, yc: _yc}));
            } else {
                bfPath.push("L0, " + graph.styles.size[1]);
            }
        }

        bottomFill.push(this.path(bfPath.join('')));
        bottomFill.attr({
            fill: '#BDBDBD',
            stroke: 'none'
        });
        
        //ПРО. точки
        if(pro) {
            
            //основной фон ПРО
            bgPro = this.set();
            $each(pro, function(p, i) {
                x1 = p[0] * col - col;
                if(p.length == 1) {
                    x2 = p[0] * col;
                } else {
                    x2 = p[1] * col;
                }

                bgPro.push(this.rect(x1, 0, x2-x1, graph.styles.size[1]));
            }, this);
            bgPro.attr({
                fill: '#FFF9E7',
                stroke: 'none'
            });


            //линия ПРО
            pathPro = this.set();
            pathProStr = [];
            pathProSrc = [];
            di = []; //индексы точек
            for(i = 0; i < bgPro.length; i++) {
                pathProStr[i] = [];
                pathProSrc[i] = [];
                c[i] = [];
                x1 = bgPro[i].attr('x');
                x2 = bgPro[i].attr('x') + bgPro[i].attr('width');

                s1 = null; //координаты отрезка, пересекающего левую сторону
                for(ii = 0, cnt = 0; ii < dotsSet.length; ii++ ){
                    if(dotsSet[ii].attr('cx') >= x1 && dotsSet[ii].attr('cx') <= x2) {
                        di.push(ii);

                        _x = dotsSet[ii].attr('cx');
                        _y = dotsSet[ii].attr('cy');

                        if(cnt == 0) {
                            if(dotsSet[ii-1]) {
                                p1 = [dotsSet[ii-1].attr('cx'), dotsSet[ii-1].attr('cy')];
                            } else {
                                p1 = [0, dotsSet[ii].attr('cy')];
                            }

                            if (_x-(col/2) > x1) {
                                x1 = _x-(col/2);
                            }

                            cx = graph.getCrossPoint(p1, [_x, _y], x1);

                            pathProStr[i].push([["M", cx[0], ", ", cx[1]].join('')]);
                            pathProSrc[i].push([cx[0], cx[1]]);
                        }
                        pathProStr[i].push([["L", _x, ", ", _y].join('')]);
                        pathProSrc[i].push([_x, _y]);

                        if(dotsSet[ii+1] && dotsSet[ii+1].attr('cx') > x2) {
                            p1 = [dotsSet[ii+1].attr('cx'), dotsSet[ii+1].attr('cy')];

                            cx = graph.getCrossPoint(p1, [_x, _y], x2);

                            pathProStr[i].push([["L", cx[0], ", ", cx[1]].join('')]);
                            pathProSrc[i].push([cx[0], cx[1]]);
                        }

                        if(!dotsSet[ii+1]) {
                            xx = path[path.length-1][0];
                            yy = path[path.length-1][1];

                            pathProStr[i].push([["L", xx, ", ", yy].join('')]);
                            pathProSrc[i].push([xx, yy]);
                        }
                        cnt++;
                    }

                }
                pathPro.push(this.path(pathProStr[i].join('')));
            }
            pathPro.attr({
                stroke: '#ff6d1b',
                fill: 'none',
                'stroke-width': 1
            });

            

            //нижняя заливка ПРО
            bottomFillPro = this.set();
            for(i = 0; i < pathProStr.length; i++) {
                if(!pathProSrc[i][pathProSrc[i].length-1]) continue;
                
                bfPath = $A(pathProStr[i]);

                bfPath.push("L" + pathProSrc[i][pathProSrc[i].length-1][0] + ", " + graph.styles.size[1]);
                bfPath.push("L" + (pathProSrc[i][0][0]) + ", " + graph.styles.size[1]);

                bottomFillPro.push(this.path(bfPath.join(' ')));
            }
            bottomFillPro.attr({
                fill: '#FFEBAF',
                stroke: 'none'
            });

            dotsPro = this.set();
            for(i = 0; i < dotsSet.length; i++) {
                if(!di.contains(i)) continue;

//                x = col * i + (col/2);
//                y = coord[i];
                x = dotsSet[i].attr().cx.toFloat();
                y = dotsSet[i].attr().cy.toFloat();


                c = this.circle(x, y, 3.3);

                $(c.node).setAttribute(
                    'rating_value' , $(dotsSet[i].node).getAttribute('rating_value')
                );
                $(c.node).setAttribute(
                    'rating_date' , $(dotsSet[i].node).getAttribute('rating_date')
                );
                dotsPro.push(c);
                dotsSet[i].hide();
            }
            dotsPro.attr(graph.styles.dotsPRO);

//            bgPro.insertBefore(dotsPro);
            pathPro.insertAfter(bgPro);
            bottomFillPro.insertBefore(pathPro);
            dotsPro.insertAfter(pathPro);

            dotsPro.hover(function() {
                graph.popupShow(popup, this);
            }, function() {
                graph.popupHide(popup, this);
            });
        }



        bottomFill.insertBefore(pathSet);
        dotsSet.insertAfter(pathSet);

        //полоски =)
        lines = this.set();
        for(i = 1; i < config.days; i++) {
            x = i*col;
            line = this.rect(x, 0, 0.7, graph.styles.size[1]);
            lines.push(
                line
                );
        }
        lines.attr({
            stroke: 'none',
            fill: '#fff'
        });
        

        popup.insertAfter(lines);

        dotsSet.hover(function() {
            graph.popupShow(popup, this);
        }, function() {
            graph.popupHide(popup, this);
        });
    },

    /**
     * Годовой  график.
     */
    yearly: function(config) {
        graph = this.rating;
        this.config = config;

        data = config.data;
        data_r = {};
        for ( var k in data ) { data_r[k] = data[k]; }
        pro = config.pro;


        if(config.w) {
            graph.styles.size[0] = w;
        }

        popup = graph.popupAdd();

        data_tmp = [];
        $each(data, function(m) {
           $each(m, function(d, i) {
               i = new Date().parse(i).format('%j').toInt();
               data_tmp[i] = d;
           });
        });

        labels = [
            'Январь',
            'Февраль',
            'Март',
            'Апрель',
            'Май',
            'Июнь',
            'Июль',
            'Август',
            'Сентябрь',
            'Октябрь',
            'Ноябрь',
            'Декабрь'
        ];

        //основной фон
        this.rect(0, 0, graph.styles.size[0], graph.styles.size[1])
            .attr(graph.styles.bg);

        cols = labels.length;
        col = graph.styles.size[0]/labels.length;
        this.day = (graph.styles.size[0]/config.days).round(2);

        //месяцы
        labelSet = this.set();
        for(i = 0; i < labels.length; i++) {
            l = labels[i];
            
            x = col * i + (col/2).round();

            lb = this.text(x, (graph.styles.size[1]+12), l)
                .attr({
                    fill: graph.styles.label_def
                });
            if(i+1 == config.cur) lb.attr({
                fill: graph.styles.label_cur
            });

            labelSet.push(lb);
        }
        labelSet.attr({
            font: graph.styles.label_font
        });

        dataSet = $type(data) == 'array' ? data.length : $H(data).getLength();

        if(dataSet) {
            sortAsc = function(p1, p2) {
                return (p1-p2)
            }
            sortDesc = function(p1, p2) {
                return (p2-p1)
            }
            minMax = [];
            getMinMax = function(arr) {
                tmp = [];
                $each(data, function(vv, i) {
                    $each(arr[i], function(v, ii) {
                        tmp.push(arr[i][ii]);
                    });
                });

                tmp.sort(sortAsc);
                minMax[0] = !tmp[0] ? '0' : tmp[0];
                tmp.sort(sortDesc);
                minMax[1] = !tmp[0] ? '0' : tmp[0];

                return minMax;
            };
            minMax = getMinMax(data);

            h_diff = minMax[0]*-1;
            
            zoom = 1;
            if((minMax[1].toFloat()+h_diff.toFloat()) > graph.styles.size[1]) {
                zoom = (minMax[1].toFloat()+h_diff.toFloat())/graph.styles.size[1];
            }
            
            //рассчитаем Y-координаты точек для начала
            coord = new Hash();
            $each(data, function(v, i) {
                $each(v, function(d, ii) {
                    if(d != null) coord.set(ii, graph.styles.size[1] - (d.toFloat() + h_diff.toFloat())/(zoom*1.2));
                });
            });

            mpl = 0.7;
            mpl1 = mpl*1.30;
            if(minMax[0] == 0) {
                h_diff = 0;
                mpl1 = 1;
            }

            if(Math.abs(minMax[0]-minMax[1]) > (graph.styles.size[1]*mpl)) {
                pct =  Math.abs(minMax[0]-minMax[1]) / (graph.styles.size[1]*mpl);
            } else {
                pct = 1;
                if(minMax[1] > (graph.styles.size[1]*mpl)) {
                    pct = minMax[1] / (graph.styles.size[1]*mpl);
                }
            }

            //рассчитаем Y-координаты точек для начала
            coord = new Hash();
            _last = null;
            $each(data, function(v, i) {
                $each(v, function(d, ii) {
                    if (d != null) {
                        _m = (d.toInt()  + h_diff)/pct;
                        coord.set(ii, graph.styles.size[1]*mpl1 - _m);
                        _last = graph.styles.size[1]*mpl1 - _m;
                    } else if (_last) {
                        coord.set(ii, _last);
                    }
                });
            });

            //точки
            dotsSet = this.set();
            $each(coord, (function(yy, i) {
                dt = new Date().parse(i).format('%d.%m.%Y');
                i = new Date().parse(i).format('%j').toInt();
                x = i * this.day - this.day*2.5;
                c = this.circle(x, yy, 3.3);
                $(c.node).setAttribute('rating_value', !data_tmp[i] ? 0 : data_tmp[i].toFloat().round(2));
                $(c.node).setAttribute('rating_date', dt);
                dotsSet.push(c);
            }).bind(this));
            dotsSet.attr(graph.styles.dots);

            //основная линия
            pathSet = this.set();
            pathStr = [];
            pathSrc = [];
            for(i = 0; i < dotsSet.length; i++) {
                yy = dotsSet[i].attr('cy');
                if(i == 0) {
                    dat = new Date().parse(coord.getKeys()[0]);
                    dd = dat.format('%j').toInt();
                    
                    x = dd * this.day - this.day*2.5;
                    if(dat.get('date').toInt() <= 8) {
                        dd = dat.set('date', 1).format('%j').toInt();
                        x = dd * this.day - this.day;
                    }

                    pathStr.push(["M", x, ", ", yy].join(''));
                    pathSrc.push([x, yy]);
                }
                x = dotsSet[i].attr('cx');
                pathStr.push(["L", x, ", ", yy].join(''));
                pathSrc.push([x, yy]);

//                if(i == dotsSet.length-1 && dat.getAttribute('date').toInt() > 25) {
//                    dat = new Date().parse(coord.getKeys()[i]);
//                    dd = dat.setAttribute('date', 1).increment('month').decrement('day').format('%j').toInt();
//
//                    x = dd * this.day;
//
//                    pathStr.push(["L", x, ", ", yy].join(''));
//                    pathSrc.push([x, yy]);
//                }
            }
            pathSet.push(this.path(pathStr.join('')));
            pathSet.attr({
                stroke: '#5f5f5f',
                fill: 'none',
                'stroke-width': 1
            });


            //нижний фон не ПРО
            bottomFill = this.set();
            x = pathSrc[pathSrc.length-1][0];
            x1 = pathSrc[0][0];
            pt = ["L", x, ", ", graph.styles.size[1]].join('') + ["L", x1, ", ", graph.styles.size[1]].join('');
            bottomFill.push(this.path(pathStr.join('')+pt));
            bottomFill.attr({
                fill: '#BDBDBD',
                stroke: 'none'
            });


            if(pro) {
                //основной фон ПРО
                bgPro = this.set();
                $each(pro, function(p, i) {
                    x1 = p[0] * this.day;
                    if(p.length == 1) {
                        x2 = p[0] * this.day + this.day;
                    } else {
                        x2 = p[1] * this.day + this.day;
                    }

                    bgPro.push(this.rect(x1, 0, x2-x1, graph.styles.size[1]));
                }, this);
                bgPro.attr({
                    fill: '#FFF9E7',
                    stroke: 'none'
                });
                bgPro.insertBefore(dotsSet);

                //линия ПРО
                pathPro = this.set();
                pathProStr = [];
                pathProSrc = [];
                di = []; //индексы точек
                for(i = 0; i < bgPro.length; i++) {
                    pathProStr[i] = [];
                    pathProSrc[i] = [];
                    c[i] = [];
                    x1 = bgPro[i].attr('x');
                    x2 = bgPro[i].attr('x') + bgPro[i].attr('width');

                    s1 = null; //координаты отрезка, пересекающего левую сторону
                    for(ii = 0, cnt = 0; ii < dotsSet.length; ii++ ){
                        if(dotsSet[ii].attr('cx') >= x1 && dotsSet[ii].attr('cx') <= x2) {
                            di.push(ii);

                            _x = dotsSet[ii].attr('cx');
                            _y = dotsSet[ii].attr('cy');


                            if(cnt == 0) {
                                if(dotsSet[ii-1]) {
                                    p1 = [dotsSet[ii-1].attr('cx'), dotsSet[ii-1].attr('cy')];
                                } else {
                                    p1 = [dotsSet[ii].attr('cx'), dotsSet[ii].attr('cy')];
                                }

                                cx = graph.getCrossPoint(p1, [_x, _y], x1);

                                if(x1 == 0) {
                                    _x = 0;
                                }

                                pathProStr[i].push([["M", (ii == 0 ? _x : cx[0]), ", ", cx[1]].join('')]);
                                pathProSrc[i].push([(ii == 0 ? _x : cx[0]), cx[1]]);
                                if(x1 == 0) {
                                    _x = p1[0];
                                }
                            }
                            pathProStr[i].push([["L", _x, ", ", _y].join('')]);
                            pathProSrc[i].push([_x, _y]);

                            if(dotsSet[ii+1] && dotsSet[ii+1].attr('cx') > x2) {
                                p1 = [dotsSet[ii+1].attr('cx'), dotsSet[ii+1].attr('cy')];

                                cx = graph.getCrossPoint(p1, [_x, _y], x2);

                                pathProStr[i].push([["L", cx[0], ", ", cx[1]].join('')]);
                                pathProSrc[i].push([cx[0], cx[1]]);
                            }
                            cnt++;
                        }

                        if(dotsSet[ii-1] && x1 >= dotsSet[ii-1].attr('cx') && x2 <= dotsSet[ii].attr('cx')) {
                            _x = dotsSet[ii].attr('cx');
                            _y = dotsSet[ii].attr('cy');

                            if(cnt == 0) {
                                if(dotsSet[ii-1]) {
                                    p1 = [dotsSet[ii-1].attr('cx'), dotsSet[ii-1].attr('cy')];
                                } else {
                                    p1 = [0, dotsSet[ii].attr('cy')];
                                }

                                cx = graph.getCrossPoint(p1, [_x, _y], x1);

                                pathProStr[i].push([["M", cx[0], ", ", cx[1]].join('')]);
                                pathProSrc[i].push([cx[0], cx[1]]);
                            }

                            cx = graph.getCrossPoint(p1, [_x, _y], x2);

                            pathProStr[i].push([["L", cx[0], ", ", cx[1]].join('')]);
                            pathProSrc[i].push([cx[0], cx[1]]);

                            cnt++
                        }
                    }
                    pathPro.push(this.path(pathProStr[i].join('')));
                }
                pathPro.attr({
                    stroke: '#ff6d1b',
                    fill: 'none',
                    'stroke-width': 1
                });


                //нижний фон ПРО
                bottomFillPro = this.set();
                for(i = 0; i < pathProSrc.length; i++) {
                    if(!pathProSrc[i].length) continue;
                    x = pathProSrc[i][pathProSrc[i].length-1][0];
                    x0 = pathProSrc[i][0][0];
                    _str = ['L', x, ', ', graph.styles.size[1]].join('')
                         + ['L', x0, ', ', graph.styles.size[1]].join('');
                    bottomFillPro.push(this.path(pathProStr[i].join('') + _str));
                }
                bottomFillPro.attr({
                    fill: '#FFEBAF',
                    stroke: 'none'
                });


                //точки ПРО
                dotsPro = this.set();
                for(i = 0; i < dotsSet.length; i++) {
                    if(!di.contains(i)) continue;

                    x = dotsSet[i].attr('cx');
                    y = dotsSet[i].attr('cy');

                    c = this.circle(x, y, 3.3);

                    $(c.node).setAttribute(
                        'rating_value' , $(dotsSet[i].node).getAttribute('rating_value')
                    );
                    $(c.node).setAttribute(
                        'rating_date' , $(dotsSet[i].node).getAttribute('rating_date')
                    );
                    dotsPro.push(c);
                    dotsSet[i].hide();
                }
                dotsPro.attr(graph.styles.dotsPRO);


                bgPro.insertBefore(pathPro);
                bottomFillPro.insertBefore(pathPro);
                dotsPro.insertAfter(pathPro);

                dotsPro.hover(function() {
                    graph.popupShow(popup, this);
                }, function() {
                    graph.popupHide(popup, this);
                });
            }

            bottomFill.insertBefore(pathSet);
            if(dotsSet.length)
                dotsSet.insertAfter(pathSet);
            if(!pro) {
                dotsSet.insertAfter(pathSet);
            } else {
                if(dotsSet.length && dotsPro.length)
                    dotsSet.insertBefore(dotsPro);
            }
            
            dotsSet.hover(function() {
                graph.popupShow(popup, this);
            }, function() {
                graph.popupHide(popup, this);
            });
        }

        //полоски =)
        lines = this.set();
        last = 0;
        for(i = 1; i < labels.length; i++) {
            ds = new Date(new Date().getYear(), i, 0).getDate();

            x = (last + ds*this.day);
            line = this.rect(x, 0, 0.7, graph.styles.size[1]);
            lines.push(
                line
            );
            last = x;
        }
        lines.attr({
            stroke: 'none',
            fill: '#fff'
        });

        popup.insertAfter(lines);

    },

    getCrossPoint: function(p1, p2, x) {
        _x1 = p1[0];
        _y1 = p1[1];
        _x2 = p2[0];
        _y2 = p2[1];
        if(_x2-_x1 != 0) {
            k = (x-_x1)/(_x2-_x1);
        } else {
            k = 0;
        }
        y = _y1 + k*(_y2-_y1);

        return [x, y];
    },

    popupAdd: function() {
        popup = this.set();
        popup.push(this.rect(0, 0, 100, 42, 5).attr({
            fill: '#ffffff',
            stroke: '#ff6d1b',
            'stroke-width': 2
        }));
        popup.push(this.text(50, 10, 'Рейтинг: 000').attr({
            font: '11px Tahoma',
            fill: '#666666'
        }));
        popup.push(this.text(50, 10, '01.01.2010').attr({
            font: '10px Tahoma',
            fill: '#ff6d1b'
        }));
//        popup.hide();
        popup.attr('y', -10000);

        return popup;
    },

    popupShow: function(pop, dot) {
        graph = this.rating;

        _x = this.config.w ? this.config.w : this.rating.styles.size[0];
        _x -= 40;
        
        if ('\v'=='v') {
            dot.attr({r: 6});
        } else {
            dot.animate({r: 6}, 300);
        }
        
        x = dot.attr('cx');
        y = dot.attr('cy');

        x1 = x-50;
        x2 = x;
        
        y1 = y+10;
        y2 = y1+14;if(Browser.Engine.trident) y2 = y1+16;
        y3 = y2+14;

        if(y > 120) {
            y1 -= 60;
            y2 -= 60;
            y3 -= 60;
        }
        
        if(x > _x) {
            x1 -= 50;
            x2 -= 50;
        }

        if(x < 50) {
            x1 += 50;
            x2 += 50;
        }

        pop[0].attr({
            'x': x1,
            'y': y1,
            'fill': dot.attr('stroke'),
            'stroke': dot.attr('fill')
        });
        pop[1].attr({
            'x': x2,
            'y': y2
        });
        pop[2].attr({
            'x': x2,
            'y': y3
        });

        pop[1].attr({
            'text': 'Рейтинг: ' + $(dot.node).getAttribute('rating_value')
        });

        pop[2].attr({
            'text': $(dot.node).getAttribute('rating_date')
        });


        pop.show();
        pn = $(pop[0].node);

        if (pn.setStyle)
            pn.setStyle('visibility', 'hidden');
        
        (function() {
            if (pn.setStyle)
                pn.setStyle('visibility', 'visible');

            if ($(pop[0].node).setStyle)
                $(pop[0].node).setStyle('display', '');
            
            if ($(pop[1].node).setStyle)
                $(pop[1].node).setStyle('display', '');
            
            if ($(pop[2].node).setStyle)
                $(pop[2].node).setStyle('display', '');
            
        }).delay(10);

    },


    popupHide: function(pop, dot) {
        
        if ('\v'=='v') {
            dot.attr({r: graph.styles.dots.r});
        } else {
            dot.animate({r: graph.styles.dots.r}, 300);
        }
        pop.attr('y', -10000);

        if ($(pop[0].node).setStyle)
            $(pop[0].node).setStyle('display', 'none');
        
        if ($(pop[1].node).setStyle)
            $(pop[1].node).setStyle('display', 'none');
        
        if ($(pop[2].node).setStyle)
            $(pop[2].node).setStyle('display', 'none');
    }


};

function loadGraph(type, config) {
    $('raph').empty();

    w = 750;
    if(config.w) {
        w = config.w;
    }

    r = new Raphael($('raph'), w, 180);

    switch(type) {
        case 'year':
            r.rating.yearly(config);
            break;
        case 'prev':
        default:
            r.rating.monthly(config);
    }

//    $('raph').setStyle('display', 'none');
//    $('raph').setStyle('display', '');
}