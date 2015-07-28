<?php

require_once('Abstract.php');

use WindowsAzure\Common\ServicesBuilder;
use WindowsAzure\Blob\Models\CreateContainerOptions;
use WindowsAzure\Blob\Models\CreateBlobOptions;
use WindowsAzure\Blob\Models\PublicAccessType;
use WindowsAzure\Common\ServiceException;

class Backup_Service_AzureBlob extends Backup_Service_Abstract
{
    const DELETED_CONTAINER = 'deleted';
    
    //Параметры соединения
    protected $connectionString;
    
    //текущий контейнер
    protected $containerName;
    //текушее имя данных
    protected $blobName;
    
    //проверять и создвать контейнер
    protected $createContainerName = true;

    //список контейнеров которые уже были созданы и существуют
    protected $existContainers = array();

    //префикс пути или url к файлу
    protected $filePrefix;


    //обьект взаимодействия с сервисом
    protected $blobRestProxy;
    
    /**
     * Конструктор обьекта
     * 
     * @param type $options
     * @throws Exception
     */
    public function __construct($options) 
    {
        $connectionOption = array();
        
        if(isset($options['СreateContainerName'])) {
            $this->createContainerName = $options['СreateContainerName'] === true;
        }
        
        if(!isset($options['FilePrefix'])){
            throw new Exception('Required option "FilePrefix" is missing.');
        }

        $this->filePrefix = rtrim($options['FilePrefix'],'/');        
        
        
        if(!isset($options['UseDevelopmentStorage'])){
        
            $connectionOption['DefaultEndpointsProtocol'] = 
                    isset($options['DefaultEndpointsProtocol'])?
                        $options['DefaultEndpointsProtocol']:
                        'https';


            if(!isset($options['AccountName'])){
                throw new Exception('Required option "AccountName" is missing.');
            }

            $connectionOption['AccountName'] = $options['AccountName'];


            if(!isset($options['AccountKey'])){
                throw new Exception('Required option "AccountKey" is missing.');
            }

            $connectionOption['AccountKey'] = $options['AccountKey'];   
        
        } else {
            $connectionOption['UseDevelopmentStorage'] = 'true';
        }
        
        $this->connectionString = urldecode(http_build_query($connectionOption,'',';'));
        $this->blobRestProxy = ServicesBuilder::getInstance()->createBlobService($this->connectionString);
    }
    

    /**
     * Установить относительный путь к файлу
     * первая компонента пути является контейнером Blob хранилища
     * 
     * @param type $filepath
     */
    public function setFilePath($filepath) 
    {
        $this->filepath = ltrim($filepath, '/');
        if(!$this->filepath) {
            throw new Exception("Filepath is empty.");
        }        
        
        $this->containerName = current(explode('/', $this->filepath));
        if(!$this->containerName) {
            throw new Exception("Not found container name: {$this->filepath}");
        }
        
        $this->blobName  = ltrim($this->filepath, "{$this->containerName}/");
        if(!$this->blobName){
            throw new Exception("Not found blob name: {$this->filepath}");
        }
    }

    
    /**
     * Создать произвольный контейнер
     * 
     * @param type $name
     * @throws \WindowsAzure\Common\ServiceException
     */
    public function createContainer($name)
    {
        $createContainerOptions = new CreateContainerOptions(); 
        $createContainerOptions->setPublicAccess(PublicAccessType::CONTAINER_AND_BLOBS);
        
        try
        {
            // Создание контейнера.
            $this->blobRestProxy->createContainer($name, $createContainerOptions);
        }
        catch(ServiceException $e)
        {
            //@todo: ничего не делаем если вдруг контейнер уже создан
            //http://msdn.microsoft.com/ru-ru/library/windowsazure/dd179439.aspx
            if($e->getCode() != 409) {
                //Значит другая ошибка и посему бросаем эксепшен
                throw $e;
            }
        }        
    }
    
   
    /**
     * Создаем контейнер если есть возможность
     * 
     * @return boolean
     * @throws \WindowsAzure\Common\ServiceException
     */
    public function createContainerIfPossible()
    {
        //Если контейнер уже создавался 
        //или установлена опция не проверять наличие то выходим
        if(in_array($this->containerName, $this->existContainers) || 
           !$this->createContainerName) {
            return false;
        }

        $this->createContainer($this->containerName);
        
        //коллекционируем созданные контейнеры
        $this->existContainers[] = $this->containerName;
        return true;
    }



    /**
     * Загрузка Blob в контейнер
     * 
     * @param type $filepath
     * @return boolean
     * @throws Exception
     */
    public function create($filepath) 
    {
        $this->setFilePath($filepath);
        $this->createContainerIfPossible();

        $fullpath = "{$this->filePrefix}/{$this->filepath}";
        
        // возвращает mime-тип
        $finfo = finfo_open(FILEINFO_MIME_TYPE); 
        $mime = finfo_file($finfo, $fullpath);
        finfo_close($finfo);

        if(!$mime) {
            throw new Exception("Not found mime-type: {$fullpath}");
        }
        
        $content = fopen($fullpath, "r");

        if(!$content) {
            throw new Exception("Cant open file: {$fullpath}");
        }
        
        $options = new CreateBlobOptions();
        $options->setBlobContentType($mime);
        
        //Передача blob-объекта
        $this->blobRestProxy->createBlockBlob(
               $this->containerName, 
               $this->blobName, 
               $content, 
               $options);
       
        return true;
    }
    
    
    /**
     * Удаление Blob из контейнера
     * 
     * @param type $filepath
     * @return boolean
     */
    public function delete($filepath) 
    {
        $this->setFilePath($filepath);
        
        //Копируем в удаленные
        $this->blobRestProxy->copyBlob(
                self::DELETED_CONTAINER, 
                $this->filepath, 
                $this->containerName, 
                $this->blobName);
        
        //Удаление Blob
        $this->blobRestProxy->deleteBlob(
                $this->containerName, 
                $this->blobName);
        
        return true;
    }
    
    
    /**
     * Копирование Blob
     * 
     * @param type $from
     * @param type $to
     */
    public function copy($from, $to)
    {
        //получаем источник
        $this->setFilePath($from);
        $fromContainerName = $this->containerName;
        $fromBlobName = $this->blobName;
        
        //получаем получатель
        $this->setFilePath($to);
        $toContainerName = $this->containerName;
        $toBlobName = $this->blobName; 
        //пробуем создать контейнер получатель
        $this->createContainerIfPossible();
        
        //Копируем
        $this->blobRestProxy->copyBlob(
                $toContainerName, 
                $toBlobName, 
                $fromContainerName, 
                $fromBlobName);        
        
        return true;
    }
    
}