<?php
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/stdf.php");


use WindowsAzure\Common\ServicesBuilder;
use WindowsAzure\Blob\Models\CreateBlobOptions;

$connectionString = "DefaultEndpointsProtocol=https;AccountName=portalvhdscs9w1rhddm7rf;AccountKey=O7zHtOhoGGLxpoZbgQ01bEiQvrFQwoefrwCBHTYnGv9pqNYOIN4636VygfCU9aRaXWO388R9Vuhj1yKYA/GNMg==";

$blobRestProxy = ServicesBuilder::getInstance()->createBlobService($connectionString);

// 1. Отображение списка BLOB-объектов.
try {
    
    $listContainers = $blobRestProxy->listContainers();
    $containers = $listContainers->getContainers();
    
    if($containers) {
        foreach($containers as $container){
            if(in_array($container->getName(), array('vhds'))) {
                continue;
            }
            $blob_list = $blobRestProxy->listBlobs($container->getName());
            $blobs = $blob_list->getBlobs();
            
            if($blobs) {
                echo "<h4>Контейнер: {$container->getName()}</h4>";
                foreach($blobs as $blob){
                    echo "<a href=\"{$blob->getUrl()}\">{$blob->getName()}</a><br/>";
                }
                echo '<br/>';
            }
        }
    }
    
    
    
    /*
    echo '<h2>Загруженные</h2>';
    
    $blob_list = $blobRestProxy->listBlobs("users");
    $blobs = $blob_list->getBlobs();

    foreach($blobs as $blob) {
        //echo '<a href="'.$blob->getUrl().'">'.$blob->getName().": ".$blob->getUrl()."<br />";
        echo "<a href=\"{$blob->getUrl()}\">{$blob->getName()}</a><br />";
    }
    
    
    echo '<h2>Удаленные</h2>';
    
    $blob_list = $blobRestProxy->listBlobs("deleted");
    $blobs = $blob_list->getBlobs();

    foreach($blobs as $blob) {
        //echo '<a href="'.$blob->getUrl().'">'.$blob->getName().": ".$blob->getUrl()."<br />";
        echo "<a href=\"{$blob->getUrl()}\">{$blob->getName()}</a><br />";
    }
     */
    
    
}
catch (ServiceException $e){
    // Обработка исключений на основе кодов ошибок и сообщений об ошибках.
    // // Коды ошибок и сообщения об ошибках приведены здесь: 
    // http://msdn.microsoft.com/ru-ru/library/windowsazure/dd179439.aspx
    $code = $e->getCode();
    $error_message = $e->getMessage();
    echo $code.": ".$error_message."<br />";
}

// 2. Отправка BLOB-объекта в контейнер
/*
$content = fopen("images/logo_50x50.png", "r");
$blob_name = "images/logo_50x50.png";

$options = new CreateBlobOptions();
$options->setBlobContentType("image/png");

try {
    //Передача blob-объекта
    $blob = $blobRestProxy->createBlockBlob("users", $blob_name, $content, $options);
}
catch(ServiceException $e){
    // Обработка исключений на основе кодов ошибок и сообщений об ошибках.
    // // Коды ошибок и сообщения об ошибках приведены здесь: 
    // http://msdn.microsoft.com/ru-ru/library/windowsazure/dd179439.aspx
    $code = $e->getCode();
    $error_message = $e->getMessage();
    echo $code.": ".$error_message."<br />";
}*/