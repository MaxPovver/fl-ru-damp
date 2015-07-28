<?php
/**
 * Класс для работы с месячным графиком
 */

require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/rating_svg.php");

class Rating_Svg_Daily extends Rating_Svg {

    private $_prevRating;

    private $_proDays = array();

    /**
     * Инициализация
     */
    public function init() {
        $this->_columns = date('t', $this->_time);

        for ($i = 0; $i < $this->_columns; $i++) {
            $this->_ratingData[$i] = 0;
            $this->_proDays[$i] = false;
        }

        if(!$this->_data) return;
        $prev_month = array();

        foreach ($this->_data as $i => $row) {
            if(date('m', strtotime($row['_date'])) != date('m', $this->_time) ) {
                $prev_month[] = $row;
                unset($this->_data[$i]);
                continue;
            }
            $this->_ratingData[intval(date('d', strtotime($row['_date']))) - 1] = $row;
        }
        
//        if(!$this->_data) return;

        if(count($prev_month) && !$this->_ratingData[0]) {
            $this->_data[0] = $prev_month[count($prev_month)-1];
            $this->_ratingData[0] = $prev_month[count($prev_month)-1];
            $this->_ratingData[0]['_date'] = date('Y-m-01', $this->_time);
        }


        $tmpval = false;
        foreach($this->_ratingData as $i => $rw) {
            if(!$this->_ratingData[$i] && $tmpval!==false) {
                $this->_ratingData[$i] = array(
                    'rating' => $tmpval['rating'],
                    'tmp' => true
                );
            } else {
                $tmpval = $rw;
            }
        }
//        var_dump($this->_data, $this->_ratingData);
    }
    
    /**
     * Строит график
     */
    public function createGraph() {

        foreach($this->_pro as $pr) {
            $ft = strtotime(date('Y-m-d', strtotime($pr['from_time'])));
            $tt = strtotime(date('Y-m-d', strtotime($pr['to_time'])));
            
            foreach($this->_ratingData as $i => $r) {
                $d = mktime(0,0,0,date('m', $this->_time), $i+1, date('Y', $this->_time));

                if($ft <= $d && $tt >= $d && $this->_ratingData[$i]) {
                    $this->_ratingData[$i]['rating'] *= rating::PRO_FACTOR;
                }
            }
        }
        
        $r_last = 0;

        $max = $this->_getMax();
        $min = $this->_min;

        $h_diff = 0;

        if($min < 0) {
            $h_diff = $min*-1 ;
            $h_diff *= 1.2;
            $max = ($max == $min || $max < 0 ? 0 : $max) + $h_diff;
        }
//var_dump($this->_ratingData);
            
        $cnt = count($this->_data);
        for ($i = 0; $i < $this->_columns; $i++) {

            $t = mktime(0,0,0,date('m', $this->_time), $i+1, date('Y', $this->_time));
            $days_params = array(
                'fill' => '#B2B2B2'
            );
            $days_params['fill'] = (date('w', $t) == 0 || date('w', $t) == 6 ? '#ff6d1b' : $days_params['fill']);
            $days_params['fill'] = (date('Y-m-d') == date('Y-m-d', $t) ? '#666666' : $days_params['fill']);
            
            $this->addText($this->_columnWidth * $i + $this->_columnWidth / 2, 155, $i + 1, $days_params);

            if ( !count($this->_data) || (date('Y-m', $t) == date('Y-m') && date('d', $t) > date('d')) || date('Y-m-d') == date('Y-m-d', $t))
                continue;

            // координаты линии
            if ($i == 0) { // начало
                $x = 0;
                $_rating = 0;
                if(intval($max)) $_rating = ((!$this->_ratingData[$i] ? $this->_data[0]['rating'] : $this->_ratingData[$i]['rating']) + $h_diff) * $this->_graphSize[1] / $max * $this->_zoom;

                $y = $this->_graphSize[1] - $_rating;

                if(!$this->_ratingData[$i]) $y = $this->_graphSize[1] - ($h_diff*$this->_graphSize[1]/$max*$this->_zoom);
//                var_dump($y, $h_diff);

                $this->_pathCoord[] = "M0, {$y}";
                $this->_fillCoord[] = "M0, {$this->_graphSize[1]}";
                $this->_fillCoord[] = "L0, $y";
            }

            $x = $this->_columnWidth * $i + $this->_columnWidth / 2;
            if ($this->_ratingData[$i]) {
                $_rating = 0;
                if(intval($max)) $_rating = ((!$this->_ratingData[$i] ? $last : $this->_ratingData[$i]['rating']) + $h_diff)* $this->_graphSize[1] / $max * $this->_zoom;
                
                $y = $this->_graphSize[1] - $_rating  ;
//                if($_rating < 0 ) $y = $this->_graphSize[1] - $y;
                $cnt--;
            } else {
                $y = !$this->_last ? $y : $this->_last;
//                $y -= $h_diff;
            }

            if((in_array($i+1, $this->_pro) && array_search($i+1, $this->_pro) == 0) ||
                (!in_array($i+1, $this->_pro) && in_array($i, $this->_pro)) ||
                    (array_search($i+1, $this->_pro) != 0 && !count($this->_pathCoord))) {
                $_x = $x - $this->_columnWidth / 2;

                $y2 = $y - ($y - $this->_last)/2;
                if($i == 0) $y2 = $y;

                $this->_pathCoord[] = "M{$_x}, {$y2}";
                $this->_fillCoord[] = "M{$_x}, {$this->_graphSize[1]}";
                $this->_fillCoord[] = "L{$_x}, {$y2}";
            }


            $this->_pathCoord[] = "L$x, $y";
            $this->_fillCoord[] = "L$x, $y";
            
            $this->_path[] = array($x, $y);

            $rating = !$this->_ratingData[$i] ? $r_last : $this->_ratingData[$i]['rating'];
            $date = date('d.m.Y', mktime(0,0,0,date('m', $this->_time), $i+1, date('Y', $this->_time)));

            $this->_dots[$i] = array($x, $y, floatval($rating), $date);
            $this->_last = $y;
            $r_last = $rating;

            // закрываем сегмент, если тип следующего (ПРО/не ПРО) отличается от текущего
//            if((!in_array($i+1, $this->_pro) && in_array($i+2, $this->_pro))
//                || (in_array($i+1, $this->_pro) && !in_array($i+2, $this->_pro))) {
//
//                $_x = $x + $this->_columnWidth/2;
//
//                if(isset($this->_ratingData[$i+1]) && $this->_ratingData[$i+1]) {
//                    $y2 = $this->_graphSize[1] - (!$this->_ratingData[$i+1] ? $this->_last : $this->_ratingData[$i+1]['rating']) * $this->_graphSize[1] / $this->_getMax() * $this->_zoom;
//                    $y = $y + ($y2 - $y)/2;
//                }
//
//                $this->_pathCoord[] = "L{$_x},$y";
//                $this->_fillCoord[] = "L{$_x},$y";
//                $this->drawPart(!in_array($i+2, $this->_pro));
//            }
        }

        if(count($this->_path)) {
            $pth_last = $this->_path[count($this->_path) - 1];
            
            $x = $pth_last[0]+$this->_columnWidth/2;
            $this->_pathCoord[] = "L" . $x . "," . $pth_last[1];
            $this->_fillCoord[] = "L" . $x . "," . $pth_last[1];
            
            $this->_path[] = array($x, $pth_last[1]);
        }

        $this->drawPart();

        // точки
        $g = $this->doc->createElement('g');
        $g->setAttribute('id', 'dots_group');
        $g->setIdAttribute('id', true);
        $this->svg->appendChild($g);
        foreach ($this->_dots as $i => $dot) {
            $params = array('fill' => (in_array($i+1, $this->_pro) ? $this->_strokePro : $this->_stroke));
            if(isset($dot[2])) $params['ratingvalue'] = $dot[2];
            if(isset($dot[3])) $params['ratingdate'] = $dot[3];
            $d = $this->addCircle($dot[0], $dot[1], $params);

            $g->appendChild($d);
        }
        
        $this->_setPro();
    }
    
    /**
     * Устанавливает на графике периоды ПРО
     */
    private function _setPro() {
        $maxtime = mktime(0,0,0,date('m', $this->_time),date('t', $this->_time),date('Y', $this->_time));
        
        foreach($this->_pro as $p => $pro) {
            if(date('Ym', strtotime($pro['from_time'])) > date('Ym', $this->_time)) continue;

            if(date('m', strtotime($pro['from_time'])) < date('m', $this->_time)) {
                $pro['from_time'] = date('Y-m-01', $this->_time);
            }
            if(date('m', strtotime($pro['to_time'])) > date('m', $this->_time)) {
                $pro['to_time'] = date('Y-m-d', $maxtime);
            }

            $diff = $this->_date_diff($pro['from_time'], $pro['to_time']);

            $day_length = $this->_columnWidth;

            $x = (date('d', strtotime($pro['from_time']))-1) * $this->_columnWidth;

            $w = ($diff) * $day_length ;

            $this->addRect($x, $this->_graphSize[1], $w, $this->_graphSize[1], array('fill' => $this->_bgPro));

            $y = $this->_graphSize[1];

            $x1 = $x + $w;

            $this->_pathCoord[] = "M$x, $y";
            $this->_fillCoord[] = "M$x, $y";

            if($x ==0) {
                $this->_pathCoord[] = "M0, {$this->_path[0][1]}";
                $this->_fillCoord[] = "L0, {$this->_path[0][1]}";
            }

            $start_x = $x;

            foreach($this->_path as $i => $path) {
                if(($path[0] >= $x && $path[0] <= $x1) ) {
                    if(isset($this->_path[$i-1]) && $this->_path[$i-1][0] < $x) {
                        $p = $this->_path[$i-1];

                        $m = ($x - $p[0]) / ($path[0] - $p[0]);
                        $yy = $p[1] + $m * ($path[1] - $p[1]);

                        $p[0] = $x;
                        $p[1] = $yy;

                        $this->_pathCoord[] = "M{$p[0]}, {$p[1]}";
                        $this->_fillCoord[] = "L{$p[0]}, {$p[1]}";
                    }

                    $this->_pathCoord[] = "L{$path[0]}, {$path[1]}";
                    $this->_fillCoord[] = "L{$path[0]}, {$path[1]}";

                    if(isset($this->_path[$i+1]) && $this->_path[$i+1][0] > $x1) {
                        $pp = $this->_path[$i+1];

                        $m = ($x1 - $path[0]) / ($pp[0] - $path[0]);
                        $yy = $path[1] + $m * ($pp[1] - $path[1]);

                        $pp[0] = $x1;
                        $pp[1] = $yy;

                        $this->_pathCoord[] = "L{$pp[0]}, {$pp[1]}";
                        $this->_fillCoord[] = "L{$pp[0]}, {$pp[1]}";
                    }

                }
                elseif ($path[0] < $x
                    && (isset($this->_path[$i+1]) && $this->_path[$i+1][0] > $x1)) {
                        $p = $path;
                        $p2 = $this->_path[$i+1];

                        $m = ($x - $p[0]) / ($p2[0] - $p[0]);
                        $yy = $p[1] + $m * ($p2[1] - $p[1]);

                        $this->_pathCoord[] = "M{$x}, {$yy}";
                        $this->_fillCoord[] = "L{$x}, {$yy}";

                        $m = ($x1 - $p[0]) / ($p2[0] - $p[0]);
                        $yy = $p[1] + $m * ($p2[1] - $p[1]);

                        $this->_pathCoord[] = "L{$x1}, {$yy}";
                        $this->_fillCoord[] = "L{$x1}, {$yy}";
                }
            }

            $this->drawPart(1, 0);

            // точки
            $dots_group = $this->doc->createElement('g');
            $this->svg->appendChild($dots_group);
            foreach($this->_dots as $dot) {
                if($dot[0] >= $x && $dot[0] <= $x1) {

                    $params = array('fill' => $this->_strokePro);
                    if(isset($dot[2])) $params['ratingvalue'] = $dot[2];
                    if(isset($dot[3])) $params['ratingdate'] = $dot[3];
                    $d = $this->addCircle($dot[0], $dot[1], $params);

                    $dots_group->appendChild($d);
                }
            }


        }
    }
    
    /**
     * Вычисляет периоды ПРО
     */
    private function _setProDays() {
        $maxtime = mktime(0,0,0,date('m', $this->_time),date('t', $this->_time),date('Y', $this->_time));

        foreach($this->_pro as $p => $pro) {
            if(date('m', strtotime($pro['from_time'])) < date('m', $this->_time)) {
                $pro['from_time'] = date('Y-m-01', $this->_time);
            }
            if(date('m', strtotime($pro['to_time'])) > date('m', $this->_time)) {
                $pro['to_time'] = date('Y-m-d', $maxtime);
            }

            $diff = $this->_date_diff($pro['from_time'], $pro['to_time']);

            $d = intval(date('d', strtotime($pro['from_time'])))-1;
            if(isset($this->_ratingData[$d]['tmp'])) {
                for($i = $d; $i<=$diff+$d; $i++) {
                    if(!isset($this->_ratingData[$i]['tmp'])) break;
                    $this->_ratingData[$i]['rating'] *= rating::PRO_FACTOR;
                }
            }

            $d = intval(date('d', strtotime($pro['to_time'])));
            if(isset($this->_ratingData[$d]['tmp'])) {
                for($i = $d; $i<count($this->_ratingData); $i++) {
                    if(!isset($this->_ratingData[$i]['tmp'])) break;
                    $this->_ratingData[$i]['rating'] = round($this->_ratingData[$i]['rating']/rating::PRO_FACTOR, 2);
                }
            }
        }

    }
    
    /**
     * Возвращает максимальый рейтинг
     *
     * @return int
     */
    private function _getMax() {

        if ($this->_max !== null)
            return $this->_max;

        if(!$this->_data) return;

        foreach ($this->_ratingData as $row) {
            $this->_max[] = !$row['rating'] ? 0 : $row['rating'];
        }
        sort($this->_max);
        $this->_min = $this->_max[0];
        $this->_max = array_reverse($this->_max);
        $this->_max = $this->_max[0];

        return $this->_max;
    }
    
    /**
     * Возвращает разниуц в днях между двумя датами
     *
     * @param  string $date1 дата для сравнения
     * @param  string $date2 дата для сравнения
     * @return int
     */
    private function _date_diff($date1, $date2) {
        $time1 = strtotime($date1);
        $time2 = strtotime($date2);

        $time1 = mktime(0, 0, 0, date('m', $time1), date('d', $time1), date('Y', $time1));
        $time2 = mktime(0, 0, 0, date('m', $time2), date('d', $time2), date('Y', $time2));

        $cur = $time1;
        $count = 0;
        while($cur <= $time2) {
            $cur = strtotime("+1 day", $cur);
            $count++;
        }
        return $count;
    }
}

