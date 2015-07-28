Raphael.fn.drawGrid = function (x, y, w, h, wv, hv, color) {
    color = color || "#000";
    var path = ["M", x, y, "L", x + w, y, x + w, y + h, x, y + h, x, y],
        rowHeight = h / hv,
        columnWidth = w / wv;
    for (var i = 1; i < hv; i++) {
        path = path.concat(["M", x, y + i * rowHeight, "L", x + w, y + i * rowHeight]);
    }
    for (var i = 1; i < wv; i++) {
        path = path.concat(["M", x + i * columnWidth, y, "L", x + i * columnWidth, y + h]);
    }
    return this.path(path.join(",")).attr({stroke: color});
};



window.onload = function () {
    // Grab the data
    var labels = [],
        data = [];
    $each($('datam').getElements("tfoot td"), function (item) {
        labels.push(item.get('html'));
    });
    $each($('datam').getElements("tbody td"), function (item) {
        data.push(item.get('html'));
    });
    
    // Draw
    var width = 750,
        height = 180,
        leftgutter = 0,
        bottomgutter = 20,
        topgutter = 20,
        colorhue = "#FFEBAF",
        color = "#FF6D1B",
        r = Raphael("holderm", width, height),
        txt = {font: '11px Tahoma', fill: "#666"},
        txt1 = {font: '10px Tahoma', fill: "#666"},
        txt2 = {font: '12px Tahoma', fill: "#000"},
        X = (width - leftgutter) / labels.length,
        max = Math.max.apply(Math, data),
        Y = (height - bottomgutter - topgutter) / max;
    var path = r.path().attr({stroke: "#FF6D1B", "stroke-width": 2, "stroke-linejoin": "round"}),
        bgp = r.path().attr({stroke: "none", opacity: .3, fill: color}).moveTo(leftgutter + X * .5, height - bottomgutter),
        frame = r.rect(10, 10, 100, 40, 5).attr({fill: "#fff", stroke: "#FF6D1B", "stroke-width": 2}).hide(),
        label = [],
        is_label_visible = false,
        leave_timer,
        blanket = r.set();
    label[0] = r.text(60, 10, "Рейтинг: 24").attr(txt).hide();
    label[1] = r.text(60, 40, "22.09.2010").attr(txt1).attr({fill: color}).hide();

    for (var i = 0, ii = labels.length; i < ii; i++) {
        var y = Math.round(height - bottomgutter - Y * data[i]),
            x = Math.round(leftgutter + X * (i + .5)),
            t = r.text(x, height - 6, labels[i]).attr(txt).toBack();
        bgp[i == 0 ? "lineTo" : "cplineTo"](x, y, 10);
        path[i == 0 ? "moveTo" : "cplineTo"](x, y, 10);
        var dot = r.circle(x, y, 3).attr({fill: color, stroke: "#fff"});
        blanket.push(r.rect(leftgutter + X * i, 0, X, height - bottomgutter).attr({stroke: "#fff", "stroke-width": 0.5, fill: "#FFF9E7", opacity: 0.8}));
        var rect = blanket[blanket.length - 1];
        (function (x, y, data, lbl, dot) {
            var timer, i = 0;
            $(rect.node).addEvents({
                'mouseover': function () {
                    clearTimeout(leave_timer);
                    var newcoord = {x: +x + 7.5, y: y - 19};
                    if (newcoord.x + 100 > width) {
                        newcoord.x -= 114;
                    }
                    frame.show().animate({x: newcoord.x, y: newcoord.y}, 200 * is_label_visible);
                    label[0].attr({text: "Рейтинг: " + data}).show().animateWith(frame, {x: +newcoord.x + 50, y: +newcoord.y + 12}, 200 * is_label_visible);
                    label[1].attr({text: lbl + ".09.2010"}).show().animateWith(frame, {x: +newcoord.x + 50, y: +newcoord.y + 27}, 200 * is_label_visible);
                    dot.attr("r", 6);
                    is_label_visible = true;
                },
                'mouseout': function () {
                    dot.attr("r", 3);
                    leave_timer = setTimeout(function () {
                        frame.hide();
                        label[0].hide();
                        label[1].hide();
                        is_label_visible = false;
                        // r.safari();
                    }, 1);
                }
            });
        })(x, y, data[i], labels[i], dot);
    }
    bgp.lineTo(x, height - bottomgutter).andClose();
    frame.toFront();
    label[0].toFront();
    label[1].toFront();
    blanket.toFront();
};