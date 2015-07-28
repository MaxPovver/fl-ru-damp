<?php
/*
 * Основной класс для построения svg графика
 *
 */

abstract class Rating_Svg {

    /**
     * Массив с данными
     *
     * @var array 
     */
    public $_data;

    /**
     * Размеры svg-объекта (ширина, высота)
     *
     * @var array
     */
    public $_canvasSize = array(744, 180);

    /**
     * Размеры графика (ширина, высота)
     *
     * @var array
     */
    public $_graphSize = array(744, 140);

    /**
     * Коэффициент, определяющий максимальную высоту графика по вертикали
     *
     * @var float
     */
    public $_zoom = 0.95;

    /**
     * Радиус точек
     *
     * @var int
     */
    public $_dotsRadius = 3;

    /**
     * Массив с периодами, когда у пользователя был оплачен ПРО аккаунт
     *
     * @var array
     */
    public $_pro = array();

    /**
     * Цвет линии
     *
     * @var string
     */
    public $_stroke = '#5f5f5f';

    /**
     * Цвет линии для ПРО
     *
     * @var string
     */
    public $_strokePro = '#ff6d1b';

    /**
     * Цвет нижней заливки
     *
     * @var string
     */
    public $_fill = '#BDBDBD';

    /**
     * Цвет нижней заливки для ПРО
     *
     * @var string
     */
    public $_fillPro = '#FFEBAF';

    /**
     * Цвет основного фона
     *
     * @var string
     */
    public $_bg = '#eeeeee';

    /**
     * Цвет основного фона для ПРО
     *
     * @var string
     */
    public $_bgPro = '#FFF9E7';

    /**
     * Количество сегментов по горизонтали
     *
     * @var int
     */
    public $_columns;

    /**
     * Время в формате unix timestamp
     *
     * @var int
     */
    public $_time;

    /**
     * Максимальное значение рейтинга
     *
     * @var float
     */
    public $_max = null;

    /**
     * Минимальное значение рейтинга
     *
     * @var float
     */
    public $_min = null;

    /**
     * Ширина сегмента
     *
     * @var integer
     */
    public $_columnWidth;


    public $_ratingData = array();
    public $_last = 0;
    public $_next;


    /**
     * Массив с координатами пути
     *
     * @var array
     */
    public $_path = array();

    /**
     * Массив с координатами пути в формате SVG
     *
     * @var array
     */
    public $_pathCoord = array();

    /**
     * Массив с координатами нижней заливки в формате SVG
     *
     * @var array
     */
    public $_fillCoord = array();

    /**
     * Массив с координатами точек на графике
     *
     * @var array
     */
    public $_dots = array();

    /**
     * Конструктор класса
     *
     * @param integer $_time Время графика в формате unix timestamp
     * @param array $_data Массив с данными
     */
    public function __construct($_time, $_data) {
        $this->_time = $_time;
        $this->_data = $_data;

        if (method_exists($this, 'init'))
            $this->init();
    }

    /**
     * Добавляет сетку к графику
     */
    public function addGrid() {
        // сетка
        $g = $this->doc->createElement('g');
//        $g->setAttribute('opacity', '0.5');
        $this->svg->appendChild($g);
        for ($i = 0; $i < $this->_columns - 1; $i++) {
            $lines = $this->doc->createElement('rect');
            $lines->setAttribute('x', $this->_columnWidth * $i + $this->_columnWidth);
            $lines->setAttribute('y', 0);
            $lines->setAttribute('width', '1');
            $lines->setAttribute('height', '140');
            $lines->setAttribute('fill', '#ffffff');
            $g->appendChild($lines);
        }
    }

    /**
     * Создает SVG документ и базовые элементы
     */
    public function createDocument() {

        $this->_columnWidth = $this->_graphSize[0] / $this->_columns;

        // документ
        $this->doc = new DOMDocument('1.0', 'utf-8');
        $this->doc->formatOutput = true;
        $this->svg = $this->doc->createElement('svg');
        $this->svg->setAttribute('xmlns', 'http://www.w3.org/2000/svg');
        $this->svg->setAttribute('xmlns:xlink', 'http://www.w3.org/1999/xlink');
        $this->svg->setAttribute('version', '1.1');
        $this->svg->setAttribute('width', $this->_canvasSize[0]);
        $this->svg->setAttribute('height', $this->_canvasSize[1]);
        $this->svg->setAttribute('onload', 'docInit();');
        $this->doc->appendChild($this->svg);

        // js
        $js = $this->doc->createElement('script');
        $js->setAttribute('type', 'text/javascript');
//        $js->setAttribute('xlink:href', HTTP_PREFIX."{$_SERVER['HTTP_HOST']}/scripts/rating.js");
        $js->appendChild($this->doc->createCDATASection(file_get_contents("{$_SERVER['DOCUMENT_ROOT']}/scripts/rating.js")));
        $this->svg->appendChild($js);

        // основной фон
        $bg = $this->doc->createElement('rect');
        $bg->setAttribute('width', $this->_graphSize[0]);
        $bg->setAttribute('height', $this->_graphSize[1]);
        $bg->setAttribute('fill', $this->_bg);
        $this->svg->appendChild($bg);
    }

    /**
     * Добавляет текстовый элемент
     *
     * @param integer $x
     * @param integer $y
     * @param string $_text Текст
     * @param array $params Массив с параметрами (опционально)
     * @return DOMElement
     */
    public function addText($x, $y, $_text, $params = array()) {
        $_text = iconv('CP1251', 'UTF-8', $_text);
        $text = $this->doc->createElement('text');
        $text->setAttribute('x', $x);
        $text->setAttribute('y', $y);
        $text->setAttribute('text-anchor', 'middle');
        $text->setAttribute('style', 'font-family: Tahoma; font-size: 11px;');
        $text->setAttribute('font', '11px Tahoma');
        $text->setAttribute('stroke', 'none');
        
        $text->setAttribute('fill', '#666666');
        if (isset($params['fill'])) $text->setAttribute('fill', $params['fill']);

        if(isset($params['font'])) {
            preg_match_all('/(\d+)px\s(.*?)$/', $params['font'], $font);
//            var_dump($font);
            $text->setAttribute('style', "font-family: {$font[2][0]}; font-size: {$font[1][0]}px;");
            $text->setAttribute('font', $params['font']);
        }
        
        $this->svg->appendChild($text);

        // число
        $tspan = $this->doc->createElement('tspan');
        $tspan->appendChild($this->doc->createTextNode($_text));
        $text->appendChild($tspan);

        return $text;
    }

    /**
     * Добавляет прямоугольник
     *
     * @param <type> $x
     * @param <type> $y
     * @param <type> $width ширина
     * @param <type> $height высота
     * @param <type> $params Массив с параметрами (опционально)
     * @return DOMElement
     */
    public function addRect($x, $y = 0, $width, $height, $params = array()) {

        $x = ceil($x);
        $y = ceil($y);


        $bg = $this->doc->createElement('rect');
        $bg->setAttribute('width', $width);
        $bg->setAttribute('height', $height);
        $bg->setAttribute('x', $x);

        if (isset($params['fill']))
            $bg->setAttribute('fill', $params['fill']);
        if (isset($params['stroke']))
            $bg->setAttribute('stroke', $params['stroke']);
        if (isset($params['stroke-width']))
            $bg->setAttribute('stroke-width', $params['stroke-width']);
        if (isset($params['r'])) {
            $bg->setAttribute('r', $params['r']);
            $bg->setAttribute('rx', $params['r']);
            $bg->setAttribute('ry', $params['r']);
        }
        if (isset($params['style']))
            $bg->setAttribute('stroke-width', $params['$style']);

        $this->svg->appendChild($bg);

        return $bg;
    }

    /**
     * Создает тултип )
     */
    public function addTooltip() {
        $g = $this->doc->createElement('g');
        $g->setAttribute('id', 'gToolTip');
        $g->setAttribute('style', 'display:none;');
//        $g->setAttribute('opacity', '0');
        $this->svg->appendChild($g);

        $t = $this->addRect(0, 0, 100, 40, array(
                'r' => 5,
                'fill' => '#ffffff',
                'stroke' => $this->_strokePro,
                'stroke-width' => 2
            ));

        $g->appendChild($t);

        $rating = $this->addText(50, 16, 'Рейтинг: 1');
        $g->appendChild($rating);
        $date = $this->addText(50, 30.5, '01.01.2010', array(
            'fill' => $this->_strokePro,
            'font' => '10px Tahoma'
            ));
        $g->appendChild($date);
    }

    /**
     * Добавляет точку
     *
     * @param <type> $x
     * @param <type> $y
     * @param <type> $params Массив с параметрами (опционально)
     * @return DOMElement
     */
    public function addCircle($x, $y, $params = array()) {
        $x = floor($x);
        $y = floor($y);
        $circle = $this->doc->createElement('circle');
        $circle->setAttribute('cx', $x);
        $circle->setAttribute('cy', $y);
        $circle->setAttribute('r', $this->_dotsRadius);
        $circle->setAttribute('fill', $this->_stroke);

        if (isset($params['fill'])) {
            $circle->setAttribute('fill', $params['fill']);
        }

        if (isset($params['ratingvalue'])) $circle->setAttribute('ratingvalue', $params['ratingvalue']);
        if (isset($params['ratingdate'])) $circle->setAttribute('ratingdate', $params['ratingdate']);

        $circle->setAttribute('stroke', '#ffffff');
        $circle->setAttribute('stroke-width', 2);

        return $circle;
    }

    /**
     * Рисует кусок графика (линию и заливку снизу) на основании координат,
     * указанных в массиве $this->_pathCoord.
     * Очищает массив $this->_pathCoord
     *
     * @param <type> $isPro Выделять или нет цветом ПРО данный кусок графика
     * @param <type> $drawBg
     */
    public function drawPart($isPro = false, $drawBg = true) {

        if ($isPro && $drawBg) {
            $x = preg_replace("/M(.*?),.*/", "$1", $this->_pathCoord[0]);
            $x2 = preg_replace("/L(.*?),.*/", "$1", $this->_pathCoord[(count($this->_pathCoord) - 1)]);
            $w = $x2 - $x;

            $this->addRect($x, 0, $w, $this->_graphSize[1], array(
                'fill' => $this->_bgPro
            ));
        }


        // нижняя заливка
        if ($this->_fillCoord) {
            $path = $this->doc->createElement('path');
            $path->setAttribute('fill', ($isPro ? $this->_fillPro : $this->_fill));
            $path->setAttribute('stroke', 'none');

            $this->_fillCoord[] = preg_replace("/L(.*?),(.*?)$/", "L$1, {$this->_graphSize[1]}Z", $this->_fillCoord[count($this->_fillCoord) - 1]);
            $path_c = implode(" ", $this->_fillCoord);
            $path->setAttribute('d', implode(" ", $this->_fillCoord));
            $this->svg->appendChild($path);
        }
        
        // линия
        $path = $this->doc->createElement('path');
        $path->setAttribute('fill', 'none');
        $path->setAttribute('stroke', ($isPro ? $this->_strokePro : $this->_stroke));
        $path->setAttribute('stroke-width', 1.2);
        $p = $this->_pathCoord;
//        unset($p[1]);
        $path->setAttribute('d', implode(" ", $p));
        $this->svg->appendChild($path);

        $this->_pathCoord = array();
        $this->_fillCoord = array();
    }

    /**
     * Устанавливает периоды ПРО
     *
     * @param array $arr
     */
    public function setPro($arr = array()) {
        $this->_pro = is_array($arr) ? $arr : array();
    }

    /**
     * Генерирует график и возвращает результат в виде xml
     *
     * @return string
     */
    public function render() {
        $this->createDocument();
        $this->createGraph();
        $this->addGrid();
        $this->addTooltip();
        
        return $this->doc->saveXML();
    }

    /**
     * Возвращает минимальный рейтинг
     * 
     * @return int
     */
    public function _getMin() {
        if(!count($this->_data)) return;

        if ($this->_min !== null)
            return $this->_min;

        foreach ($this->_data as $row) {
            $this->_min[] = $row['rating'];
        }
        sort($this->_min);
        $this->_min = $this->_min[0];

        return $this->_min;
    }

    /**
     * Метод, в котором должны быть расчитаны координаты
     * линий и заливки в графике. ($_pathCoord, $_fillCoord)
     */
    abstract public function createGraph();
}
