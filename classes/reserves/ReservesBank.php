<?php

require_once($_SERVER['DOCUMENT_ROOT'] . '/classes/events.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/classes/reserves/BaseModel.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/classes/num_to_word.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/classes/odt2pdf.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/classes/CFile.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/classes/DocGen/DocGenReserves.php');


/**
 * Class ReservesBank
 * Работа с разчетом по безналу для резерва средств
 */
class ReservesBank extends BaseModel
{
    const TEMPLATES_PATH        = '/templates/docs/';
    const INVOICE_TEMPLATE      = 'bank_invoice.odt';
    const TABLE_FILES           = 'file';
    const BILL_NUM_FORMAT       = 'БС-%d';
    const CHECK_ORG_FILENAME    = 'Счет №%s';
    const CHECK_TITLE           = 'Перечисление средств по договору № %s';
    
    private $TABLE         = 'reserves_bank';
    static public $_TABLE  = 'reserves_bank';

    protected $data = array();
    protected $options;



    /**
     * Конструктор получает настройки для класса
     * 
     * @param type $options
     */
    public function __construct($options) 
    {
        $this->options = array(
            'templates_path'        => static::TEMPLATES_PATH,
            'invoice_template'      => static::INVOICE_TEMPLATE,
            'table_files'           => static::TABLE_FILES,
            'src_id'                => 0,
            'bill_num_format'       => static::BILL_NUM_FORMAT,
            'check_org_filename'    => static::CHECK_ORG_FILENAME,
            'check_title'           => static::CHECK_TITLE
        );
        
        $this->options = array_merge($this->options, $options);
    }

    
   
   /**
    * Инициализировать данные счета
    * 
    * @param type $data
    */
   public function setData($data) 
   {
        $this->data = $data;
   }



    /**
     * Получить счет
     * 
     * @param type $id
     * @return type
     */
    public function getCheck($id)
    {
        $data = $this->db()->row("
            SELECT *
            FROM {$this->TABLE}
            WHERE id = ?i
        ",$id);
        return $data; 
    }

    
    /**
     * Получить счет по ID резерва
     * 
     * @param type $reserve_id
     * @return type
     */
    public function getCheckByReserveId($reserve_id)
    {
        $data = $this->db()->row("
            SELECT *
            FROM {$this->TABLE}
            WHERE reserve_id = ?i
        ",$reserve_id);
        return $data;        
    }

    
    /**
     * Обновить данные по счету
     * 
     * @param type $id
     * @param type $data
     * @return type
     */
    public function updateCheck($id, $data)
    {
        return $this->db->update($this->TABLE,$data,'id = ?i',$id);
    }

    
    /**
     * Получение папки для сохранения файлов
     * 
     * @return boolean
     */
    public function getFilePath()
    {
        $login = @$_SESSION['login'];
        if(@$_SESSION['uid'] != $this->data['user_id']) 
        {
            $emp = new employer();
            $ret = $emp->GetName($this->data['user_id'], $error);
            if(!$ret) return false;
            $login = $ret['login'];
        }
        
        if(empty($login)) return false;
        return sprintf('users/%s/%s/upload/', substr($login, 0, 2), $login);
    }

    

    /**
     * Добавление/обновление информации о счете
     * если счет уже оплачен то обновить неполучится
     * 
     * @param type $data
     * @return boolean
     */
    public function addCheck2($data = array())
    {
        if(empty($data)) $data = $this->data;
        
        $reserve_id = @$data['reserve_id'];
        if(!$reserve_id) return false;
        
        if(!isset($data['date'])) 
            $data['date'] = date('Y-m-d H:i:s');
        
        $ret = $this->db()->row("
            SELECT 
                id, 
                payed_date,
                check_file_id
            FROM {$this->TABLE}
            WHERE reserve_id = ?i
        ",$reserve_id);
  
        if(!$ret)
        {
            $id = $this->db()->insert(
                    $this->TABLE,
                    $data,
                    'id');
            if($id) {
                $data['id'] = $id;
                $this->data = $data;
                return true;
            }
        }
        elseif(empty($ret['payed_date']))
        {
            $ok = $this->db()->update(
                    $this->TABLE,
                    $data,
                    'reserve_id = ?i',
                    $reserve_id);
            if($ok) {
                $this->data = $data;
                return true;
            }
        }
        
        return false;
    }

    

    
    /**
     * Добавление/обновление информации о счете
     * 
     * @param type $data
     * @return boolean
     */
    public function addCheck($data = array())
    {
        if(empty($data)) $data = $this->data;
        
        $id = (isset($data['id']))?$data['id']:false;
        
        if($id)
        {
            $ok = $this->db()->update(
                    $this->TABLE,
                    $data,
                    'id = ?i',
                    $id);
            if(!$ok) return false;
        }
        else
        {
            $reserve_id = @$data['reserve_id'];
            if(!$reserve_id) return false;
            
            $id = $this->db()->insert(
                    $this->TABLE,
                    $data,
                    'id');
            if(!$id) return false;
            $data['id'] = $id;
            $this->setData($data);
        }
        
        return true;
    }

    


    
    /**
     * Аналог generateInvoice
     * использующий DocGenReserves класс для генерации дока
     * 
     * @param type $user_info
     * @return boolean
     */
    public function generateInvoice2($user_info, $generate_now = false)
    {
        $user_info['form_type'] = sbr::FT_JURI;
        $file = new CFile();
        $reserve_id = @$this->data['reserve_id'];
        $data = $this->getCheckByReserveId($reserve_id);
        if(isset($data['payed_date']) && !empty($data['payed_date'])) return false;
        if(isset($data['check_file_id']) && $data['check_file_id']>0) $file->Delete($data['check_file_id']);
        if(!$data) $data = array();
        $this->data = array_merge($data, $this->data, $user_info);
        
        $login = @$_SESSION['login'];
        if(@$_SESSION['uid'] != $this->data['user_id']) 
        {
            $emp = new employer();
            $ret = $emp->GetName($this->data['user_id'], $error);
            if(!$ret) return false;
            $login = $ret['login'];
        }
        
        try
        {
            $doc = new DocGenReserves(array(
                'id' => $this->options['src_id'],
                'employer' => array('login' => $login)
            ));    
            $doc->setField('date_offer', $this->data['date_offer']);
            unset($this->data['date_offer']);
            if(!isset($this->data['date'])) {
                $this->data['date'] = date('Y-m-d H:i:s');
            }
            $doc->setField('datetext_1', $this->data['date']);
            $doc->setField('num_bs', $this->options['src_id']);
            $doc->setField('fio_emp', $this->data);
            if(empty($user_info['phone'])) $user_info['phone'] = $user_info['mob_phone'];
            $doc->setField('phone', $user_info['phone']);
            
            $doc->setField('price_price', $this->data['price']);
            $doc->setField('nonds_commision', $this->data['tax_price']);
            $doc->setField('nondstotal_price', $this->data);
            $doc->setField('pricends_commision', $this->data['tax_price']);
            $doc->setField('price_reserve_price', $this->data['reserve_price']);
            $doc->setField('pricelong_reserve_price', $this->data['reserve_price']);
            $doc->setField('price_commision', $this->data['tax_price']);
            
            if ($generate_now) {
                $doc->disableQueue();
            }
            
            $file = $doc->generateBankInvoice();
        }
        catch(Exception $e){ return false; }
        
        $this->data['check_file_id'] = $file->id;
        if(!$this->addCheck()) return false;

        if ($generate_now) {
            Events::trigger('generateInvoice2');
        }
        
        return $file;
    }
    
    


    /**
     * НЕ ИСПОЛЬЗОВАТЬ - УСТАРЕЛ СМ generateInvoice2.
     * 
     * Генерация счета
     * @todo: может перенести генерацию в очереди?
     * @todo: но тогда нужно организовать обратную связь.
     * 
     * @param type $user_info
     * @return boolean|\CFile
     */
    public function generateInvoice($user_info)
    {
        extract($this->options);
        $file = new CFile();
        
        $file_path = $this->getFilePath();
        $reserve_id = @$this->data['reserve_id'];
        if(!$file_path || 
           !$reserve_id || 
           empty($this->data)) return false;
        
        $data = $this->getCheckByReserveId($reserve_id);
        if(isset($data['payed_date']) && !empty($data['payed_date'])) return false;
        if(isset($data['check_file_id']) && $data['check_file_id']>0) $file->Delete($data['check_file_id']);
        if(!$data) $data = array();
        $this->data = array_merge($data, $this->data, $user_info);
        $data = $this->data;
        
        $bill_num = sprintf($bill_num_format,$data['reserve_id']);
        $data['bill_num'] = $bill_num;
        
        $data['date'] = date_text($data['date'],'d');
        $data['price_txt'] = num2str(intval($data['price']));
        $data['price'] = number_format($data['price'], 2, ',', '');
        $data['title'] = sprintf($check_title,$bill_num);
        
        foreach($data as $key => $value)
        {
            $data['$'.$key] = $value;
            unset($data[$key]);
        }

        $pdf = new odt2pdf($invoice_template);
        $pdf->setFolder(ABS_PATH . $templates_path);
        $pdf->convert($data);
        $content = $pdf->output(NULL, 'S');
        $len = strlen($content);
        if(!$len) return false;
        
        
        $file->path = $file_path;
        $file->table = $table_files;
        $file->size = $len;
        $file->src_id = $src_id;
        $file->name = basename($file->secure_tmpname($file->path,'.pdf'));
        $file->original_name = change_q_x(sprintf($check_org_filename,$bill_num));
        if(!$file->putContent($file->path . $file->name, $content)) return false;
        
        $this->data['check_file_id'] = $file->id;
        return ($this->addCheck())?$file:false;
    }
    
    
}
