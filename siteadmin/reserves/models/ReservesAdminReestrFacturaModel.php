<?php

require_once($_SERVER['DOCUMENT_ROOT'] . '/classes/reserves/ReservesAdmin.php');

class ReservesAdminReestrFacturaModel extends ReservesAdmin
{
    
    /**
     * Таблица файлов загруженный реестров фактур
     */
    protected $TABLE = 'file_reserves_factura';
    
    /**
     * Директория хранилище файлов реестров фактур
     */
    public $path = '/reserves/factura/';


    /**
     * Конструктор
     * Отслеживает загружаемые файлы
     */
    public function __construct()
    {
        $filename = $this->saveUploadedFile('file','csv');
        
        if ($filename) {
            $this->parseFile($filename);
        }
    }
    
    
    /**
     * Парсим реестр и генерируем счет-фактуры
     * 
     * @param type $filename
     */
    public function parseFile($filename) 
    {
        //@todo: это не красиво :(
        ini_set('max_execution_time', 300);
        //ini_set('memory_limit', '512M');
        
        $uri = WDCPREFIX_LOCAL . $this->path . $filename;
        
        $list = array();
        $ids = array();
        $handle = fopen($uri, 'r');
		while (($data = fgetcsv($handle, 1000, ";")) !== FALSE) {
			if ($data[0] == 'order_id' || count($data) != 7) {
				continue;
			} 
            
            //order_id;sf_num;sf_date;sf_summa;pp_num;pp_date;pp_type
            $res = array(
                'id' => $this->getOrderId($data[0]), //номер сделки,
                'sf_num' => $data[1], //Номер счета-фактуры
                'sf_date' => $data[2], //Дата счета фактуры
                'sf_summa' => $data[3], //Сумма счета фактуры
                'pp_num' => $data[4], //Номер платежного документа
                'pp_date' => $data[5], //Дата дата платежного документа
                'pp_type' => $data[6] //тип платежного документа (Якасса или банк)
            );
            $ids[] = $res['id'];
            $list[] = $res;
        }        
        fclose($handle);

        if ($list) {
           
           $reserveModel = ReservesModelFactory::getInstance(
                   ReservesModelFactory::TYPE_TSERVICE_ORDER); 
           
           $empData = $reserveModel->getEmpByReserveIds($ids);

           foreach ($list as $key => $data) {

                if (!isset($empData[$data['id']])) {
                    continue;
                }

                $data['employer']['login'] = $empData[$data['id']]['login'];
                $data['employer']['uid'] = $empData[$data['id']]['uid'];
                
                $reserveModel->getReserve($ids[$key]);
                $data['employer']['reqv'] = $reserveModel->getEmpReqv();

                try {
                    $doc = new DocGenReserves($data);
                    $doc->generateFactura();
                } catch (Exception $e) {
                    require_once($_SERVER['DOCUMENT_ROOT'] . '/classes/log.php');
                    $log = new log('reserves_docs/' . SERVER . '-%d%m%Y.log', 'a', "%d.%m.%Y %H:%M:%S: ");
                    $log->writeln(sprintf("Order Id = %s: %s", $data['id'], iconv('CP1251','UTF-8',$e->getMessage())));
                }
           } 
        }
    }
    
    
    
    public function getReestrs() 
    {
        $sql = "SELECT * FROM {$this->TABLE} ORDER BY id DESC";
        $sql = $this->_limit($sql);
        $files = $this->db()->rows($sql);
        return $files;
    }
    
    public function getReestrsCount() 
    {
        $sql = "SELECT reltuples FROM pg_class WHERE oid = 'public.{$this->TABLE}'::regclass;";
        return $this->db()->val($sql);
    }
    
    
}