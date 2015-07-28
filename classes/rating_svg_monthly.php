<?php
/**
 * Класс для работы с годовым графиком
 */

require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/rating_svg.php");

class Rating_Svg_Monthly extends Rating_Svg {

    public $_pro;

    private $_labels = array(
        'Январь', 'Февраль', 'Март', 'Апрель',
        'Май', 'Июнь', 'Июль', 'Август',
        'Сентябрь', 'Октябрь', 'Ноябрь', 'Декабрь'
    );

    /**
     * Инициализация
     */
    public function init() {
        $this->_columns = 12;

        $tmp = array();
        if($this->_data)
            foreach($this->_data as $row) {
                $tmp[intval(date('m', strtotime($row['_date'])))-1] = $row;
            }

        $this->_ratingData = $tmp;
    }
    
    /**
     * Строит график
     */
    public function createGraph() {
        $periods = rating::getMonthParts(date('Y-m-d', $this->_time));

        foreach($periods as $m => $p) {
            foreach($this->_pro as $pr) {
                for($c = 0; $c < 4; $c ++) {
                    if($p[$c] >= strtotime($pr['from_time']) && $p[$c] <= strtotime($pr['to_time']))
                        $this->_ratingData[$m]["part".($c+1)] *= rating::PRO_FACTOR;
                }
            }
        }

        $dots = array();
        // месяцы
        $labels_group = $this->doc->createElement('g');
        $this->svg->appendChild($labels_group);

        $last = null;

        $c = count($this->_data);

        $max = $this->_getMax();
        $min = $this->_min;
//var_dump($min);
        if($min < 0) {
            $h_diff = $min*-1 ;
            $h_diff *= 1.2;
            $max = ($max == $min || $max < 0 ? 0 : $max) + $h_diff;
        }
        
        $r_last = 0;
        for ($i = 0; $i < $this->_columns; $i++) {

            $t = mktime(0,0,0, $i+1, date('d', $this->_time), date('Y', $this->_time));
            $m_params = array('fill' => '#B2B2B2');
            $m_params['fill'] = (date('Y-m', $this->_time) == date('Y-m', $t) ? '#666666' : $m_params['fill']);

            $label = $this->addText($this->_columnWidth * $i + $this->_columnWidth / 2, 155, $this->_labels[$i], $m_params );
            $labels_group->appendChild($label);

            if($c <= 0) continue;

            for($ii = 0; $ii < 4; $ii ++) {
                $x = $this->_columnWidth/4*$ii + $this->_columnWidth*$i + $this->_columnWidth/8;
                $y = $this->_graphSize[1];

                if(isset($this->_ratingData[$i])) {
                    $field = "part" . ($ii+1);
                    $y = $this->_graphSize[1] - ($this->_ratingData[$i][$field] + $h_diff) * $this->_graphSize[1] / $max * $this->_zoom;
                }

                if(!isset($this->_ratingData[$i]['_date']) && $last) $y = $last;

                if($ii == 0 && $i == 0 ) {
                    $this->_fillCoord[] = "M0, ".$this->_graphSize[1];
                    $this->_pathCoord[] = "M0, {$y}";
                    $this->_fillCoord[] = "L0, {$y}";
                }

                $this->_pathCoord[] = "L$x, $y";
                $this->_fillCoord[] = "L$x, $y";
                
                $this->_path[] = array($x, $y);


                $rating = $r_last;
                if(isset($this->_ratingData[$i])) {
                    $rating = $this->_ratingData[$i][$field];
                }
                $date = date('d.m.Y', $periods[$i][$ii]);
                
                $dots[] = array($x, $y, floatval($rating), $date);
                $last = $y;
                $r_last = $rating;
            }

            if(isset($this->_ratingData[$i]['_date'])) $c--;
        }
        $this->drawPart();

        // точки
        $this->_dots = $dots;
        $dots_group = $this->doc->createElement('g');
        $dots_group->setAttribute('id', 'dots_group');
        $dots_group->setIdAttribute('id', true);
        $this->svg->appendChild($dots_group);
        
        if($dots)
            foreach ($dots as $i => $dot) {
                $params = array();
                if(isset($dot[2])) $params['ratingvalue'] = $dot[2];
                if(isset($dot[3])) $params['ratingdate'] = $dot[3];
                $dots_group->appendChild($this->addCircle($dot[0], $dot[1], $params));
            }

        $this->_setPro();
    }
    
    /**
     * Возвращает максимальый рейтинг
     *
     * @return int
     */
    private function _getMax() {
        if ($this->_max !== null)
            return $this->_max;

        if(!$this->_ratingData) return;

        foreach($this->_ratingData as $row) {
            for($i = 1; $i <= 4; $i++) {
                $this->_max[] = !$row["part{$i}"] ? 0 : $row["part{$i}"];
            }
        }

        sort($this->_max);
//var_dump($this->_max);
        $this->_min = $this->_max[0];
        $this->_max = array_reverse($this->_max);
        $this->_max = $this->_max[0];

        return $this->_max;
    }
    
    /**
     * Устанавливает на графике периоды ПРО
     */
    private function _setPro() {
        $maxtime = mktime(0,0,0,12,31,date('Y', $this->_time));

        if(!$this->_pro) return;
        
        foreach($this->_pro as $pro) {
            if(date('Y', strtotime($pro['from_time'])) > date('Y', $this->_time)) continue;
            
            $diff = $this->_date_diff($pro['from_time'], $pro['to_time']);
            $day_length = $this->_columnWidth/date('t', strtotime($pro['from_time']));
            
            $x = (date('m', strtotime($pro['from_time']))-1) * $this->_columnWidth;
            $x = $x + $day_length * (date('d', strtotime($pro['from_time'])) - 1);

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
     * Возвращает разниуц в днях между двумя датами
     *
     * @param  string $date1 дата для сравнения
     * @param  string $date2 дата для сравнения
     * @return int
     */
    private function _date_diff($date1, $date2) {
        $time1 = strtotime($date1);
        $time2 = strtotime($date2);
        
        $cur = $time1;
        $count = 0;
        while($cur <= $time2) {
            $cur = strtotime("+1 day", $cur);
            $count++;
        }
        return $count;
    }
}