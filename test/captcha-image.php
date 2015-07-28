<?


require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/stdf.php");


/**
 * Класс работы с CAPTCHA
 *
 */
class captcha
{

    /**
     * Изображение
     *
     * @var object
     */
    private $img;

    /**
     * Ширина картинки
     *
     * @var integer
     */
	public $width = 100;

    /**
     * Высота картинки
     *
     * @var integer
     */
	public $height = 45;

    /**
     * Минимальный размер шрифта
     *
     * @var integer
     */
	public $font_size_min = 17;

    /**
     * Максимальный размер шрифта
     *
     * @var integer
     */
	public $font_size_max = 17;

    /**
     * Шрифты, которые используются для текста
     *
     * @var array
     */
    public $fonts = array();

    /**
     * Максимальное смещение символа по вертикали 
     *
     * @var integer
     */
	public $y_offset = 15;

    /**
     * Максимальный угол поворота символа
     *
     * @var integer
     */
	public $angle = 2;

    /**
     * Путь где находятся файлы со шрифтами
     *
     * @var string
     */
	public $font_path;

    /**
     * Символы используемые при генерации текста
     *
     * @var string
     */
	public $characters = "2345789ABCDEGHKMNPSUVXYZ";

    /**
     * Кол-во символов в тексте
     *
     * @var integer
     */
	public $chars_count = 5;



    /**
     * Ключ в сессии для хранения числа CAPTCHA
     *
     * @var string
     */
    public $CAPTCHANUM = 'image_number';

    /**
     * Цвет фона
     *
     * @var integer
     */
    public $bgcolor;

    /**
     * Цвет текста
     *
     * @var integer
     */
    public $fgcolor;

    /**
     * Доступные цвета
     *
     * @var array
     */
    public $colors = array(
                            0 => array(255,255,255),
                            1 => array(10,10,10),
                            2 => array(10,255,10)
                          );


    /**
     * Конструктор класса
     *
     * @param string $num суффикс для ключа сессии по которому хранится CAPTCHA
     * @param integer $bgcolor номер цвета фона (0 - белый, 1, 2 )
     * @param integer $fgcolor номер цвета текста (0 - белый, 1, 2)
     */
	public function __construct($num='', $bgcolor=0, $fgcolor=1) {
        if(!array_key_exists($bgcolor, $this->colors)) {
            $this->bgcolor = 0;
        } else {
            $this->bgcolor = $bgcolor;
        }
        if(!array_key_exists($fgcolor, $this->colors)) {
            $this->fgcolor = 1;
        } else {
            $this->fgcolor = $fgcolor;
        }
        $this->font_path = $_SERVER['DOCUMENT_ROOT'].'/fonts';
		if (is_dir($this->font_path)) {
			if ($dh = opendir($this->font_path)) {
				while (($file = readdir($dh)) !== FALSE) {
					if (preg_match("/.ttf$/", $file)) $this->fonts[] = $file;
				}
			}
        }
        closedir($dh);
        if($num) { $this->CAPTCHANUM = $this->CAPTCHANUM.$num; }
	}

    /**
    * Искажение надписи
    *
    */
	public function multi_wave() {
		
		// для удобства
		$width = $this->width;
		$height = $this->height;
		$img =& $this->img;
	
		$center = ($this->width - 10) / 2;

		//$fg = mt_rand(0, 100);
		//$bg = mt_rand(250, 255);
		
		//$foreground_color = array($fg, $fg, $fg);
		$foreground_color = array($this->colors[$this->fgcolor][0], $this->colors[$this->fgcolor][1], $this->colors[$this->fgcolor][2]);
		//$background_color = array($bg, $bg, $bg);
		$background_color = array($this->colors[$this->bgcolor][0], $this->colors[$this->bgcolor][1], $this->colors[$this->bgcolor][2]);


		$img2 = imagecreatetruecolor($this->width, $this->height);
		$foreground = imagecolorallocate($img2, $foreground_color[0], $foreground_color[1], $foreground_color[2]);
		$background = imagecolorallocate($img2, $background_color[0], $background_color[1], $background_color[2]);
		imagefilledrectangle($img2, 0, 0, $this->width - 1, $this->height - 1, $background);		

		// periods
		$rand1=mt_rand(750000,1200000)/11000000;
		$rand2=mt_rand(750000,1200000)/11000000;
		$rand3=mt_rand(750000,1200000)/11000000;
		$rand4=mt_rand(750000,1200000)/11000000;
		// phases
		$rand5=mt_rand(0,31415926)/13000000;
		$rand6=mt_rand(0,31415926)/13000000;
		$rand7=mt_rand(0,31415926)/13000000;
		$rand8=mt_rand(0,31415926)/13000000;
		// amplitudes
		$rand9=mt_rand(330,420)/110;
		$rand10=mt_rand(330,450)/110;

		//wave distortion
		for($x=0;$x<$width;$x++){
			for($y=0;$y<$height;$y++){
				$sx=$x+(sin($x*$rand1+$rand5)+sin($y*$rand3+$rand6))*$rand9-$width/2+$center+1;
				$sy=$y+(sin($x*$rand2+$rand7)+sin($y*$rand4+$rand8))*$rand10;

				if($sx<0 || $sy<0 || $sx>=$width-1 || $sy>=$height-1){
					continue;
				}else{
					$color=imagecolorat($img, $sx, $sy) & 0xFF;
					$color_x=imagecolorat($img, $sx+1, $sy) & 0xFF;
					$color_y=imagecolorat($img, $sx, $sy+1) & 0xFF;
					$color_xy=imagecolorat($img, $sx+1, $sy+1) & 0xFF;
				}

				if($color==255 && $color_x==255 && $color_y==255 && $color_xy==255){
					continue;
				}else if($color==0 && $color_x==0 && $color_y==0 && $color_xy==0){
					$newred=$foreground_color[0];
					$newgreen=$foreground_color[1];
					$newblue=$foreground_color[2];
				}else{
					$frsx=$sx-floor($sx);
					$frsy=$sy-floor($sy);
					$frsx1=1-$frsx;
					$frsy1=1-$frsy;

					$newcolor=(
						$color*$frsx1*$frsy1+
						$color_x*$frsx*$frsy1+
						$color_y*$frsx1*$frsy+
						$color_xy*$frsx*$frsy);

					if($newcolor>255) $newcolor=255;
					$newcolor=$newcolor/255;
					$newcolor0=1-$newcolor;

					$newred=$newcolor0*$foreground_color[0]+$newcolor*$background_color[0];
					$newgreen=$newcolor0*$foreground_color[1]+$newcolor*$background_color[1];
					$newblue=$newcolor0*$foreground_color[2]+$newcolor*$background_color[2];
				}

				imagesetpixel($img2, $x, $y, imagecolorallocate($img2, $newred, $newgreen, $newblue));
			}
		}
		
		// save
		$this->img = $img2;
	}

    /**
     * Сохраняет случайное число в сессии
     * Необходимо вызвать данный метод в главном скрипте, формирующем страницу с капчей
     *
     * return void
     */
    function setNumber()
    {
		$count = 0;
		$result = '';
        mt_srand();
		while ($count++ < 10) {
			//echo $count;
			for ($i=0; $i<$this->chars_count; $i++) {
				$char = $this->characters{ mt_rand(0, strlen($this->characters)-1) };
				if ( $i > 0 && $char == $result{$i-1} )	$i--; else $result .= $char;
			}
			if(preg_match('/cp|cb|ck|c6|c9|rn|rm|mm|co|do|cl|db|qp|qb|dp|ww/i', $result)) $result = ''; else break;
		}
        $_SESSION[$this->CAPTCHANUM] = $result;
    }



    /**
     * Получение ранее сохраненного числа капчи
     *
     * return string                     число капчи
     */
    function getNumber()
    {
        return $_SESSION[$this->CAPTCHANUM];
    }



    /**
     * Проверка числа капчи на правильность
     *
     * @param integer $num               число капчи
     *
     * return boolean                    1 в случае правильности, 0 в случае неудачи
     */
    function checkNumber($num)
    {
        return ($this->getNumber() && strtolower($this->getNumber())==strtolower($num));
    }

    /**
    * Добавляет текст в изображение
    *
    * @param    string  $code   текст
    */
	public function draw_code($code) {
  		$this->img = imagecreatetruecolor($this->width, $this->height);
		$white = imagecolorallocate($this->img, 255, 255, 255);
		$font_color = imagecolorallocate($this->img, 50, 50, 50);
		imagefilledrectangle($this->img, 0, 0, $this->width - 1, $this->height - 1, $white);

		$x = 10;
		
		for ($i=0; $i<strlen($code); $i++) {
			$font_size = mt_rand($this->font_size_min, $this->font_size_max);
			$font = $this->font_path . '/' . $this->fonts[ mt_rand(0, count($this->fonts)-1) ];
			$char_info = imagettfbbox($font_size, 0, $font, substr($code,$i,1));
			$char_line = abs($char_info[7] - $char_info[1]);	
			$char_line = $font_size + $this->y_offset;
			imagettftext($this->img, $font_size, mt_rand(-$this->angle, $this->angle), $x, mt_rand($char_line - 5, $char_line + 5), $font_color, $font, substr($code,$i,1));
			$x += $char_info[2] - $char_info[0] - 3;
		}
	}

    /**
    * Рисует линии на фоне
    *
    */
	public function lines() {
        global $lines, $cc, $acc;
        $n = 4;
        if($lines==2) {
            $n=8;
        }
        if($lines==3) {
            $n=12;
        }
        for ($i=0; $i<$n; $i++) {
            $color = imagecolorallocate($this->img, mt_rand(0, 255), mt_rand(0, 200), mt_rand(0, 255));
            if($_GET['cc']==1 || $_GET['acc']) {
            $color = imagecolorallocate($this->img, 255, 255, 255);
        }
            $a1 = mt_rand(0, 20);
            $a2 = mt_rand(1, 50);
            $a3 = mt_rand(150, 180);
            $a4 = mt_rand(1, 50);

            //$a1 = 1;
            //$a3 = 129;
            //$a4 = mt_rand(20, 40);
            //$a2=$a4;
            imageline($this->img, $a1, $a2, $a3, $a4, $color);
        }
	}

    /**
     * Получение изображения капчи
     *
     * return image                      объект изображения
     */
    function getImage()
    {
    	global $type, $nois, $lines;
        $number = $this->getNumber();
		$this->draw_code($number);

if($lines) {
    $this->lines();
}

		$this->multi_wave();
		if($nois==1) {
			$this->nois();
		}

if($_GET['acc']) {
    $this->lines();
}

		if($type!=2) {
        	imagefilter($this->img, IMG_FILTER_SMOOTH, 10);
        }

        return $this->img;
    }

        function nois() {
        $white_noise_density=1/6;
        $black_noise_density=1/30;
        $x=100;
        $white=imagecolorallocate($this->img, 255, 255, 255);
        $black=imagecolorallocate($this->img, 0, 0, 0);
        for($i=0;$i<(($this->height-30)*$x)*$white_noise_density;$i++){
            imagesetpixel($this->img, mt_rand(0, $x-1), mt_rand(10, $this->height-15), $white);
        }
        for($i=0;$i<(($this->height-30)*$x)*$black_noise_density;$i++){
            imagesetpixel($this->img, mt_rand(0, $x-1), mt_rand(10, $this->height-15), $black);
        }
    }

}

//-------------------------------------------

	session_start();
    header("Content-type: image/gif");
    $num = intval($_GET['num']);

    $bgcolor = $_GET['bg'];
    $fgcolor = $_GET['fg'];

	$captcha = new captcha($num, $bgcolor, $fgcolor);

	if($_GET['f']==1) {
		$captcha->font_path = $_SERVER['DOCUMENT_ROOT'].'/test/captcha-font';
		$captcha->fonts = array();
		$captcha->fonts[] = 'aman.ttf';
		$captcha->fonts[] = 'sf.ttf';
		$captcha->fonts[] = 'wishful.ttf';
	}

	if($_GET['s']==1) {
		$captcha->width=120;
		$captcha->height=60;
		$captcha->font_size_min = 22;
		$captcha->font_size_max = 22;
	}

    if($_GET['s']==2) {
        $captcha->width=130;
        $captcha->height=60;
        $captcha->font_size_min = 22;
        $captcha->font_size_max = 30;
    }

	$nois = $_GET['n'];
	$type = $_GET['type'];
    $lines = $_GET['l'];


	switch($type) {
		case 1:
			break;
		case 2:
			break;
		case 3:
			$captcha->angle = 0;
			break;
		case 3:
			$captcha->angle = 4;
			break;
	}

    if($_GET['r']) $captcha->setnumber();
	imagegif($captcha->getImage());
?>
