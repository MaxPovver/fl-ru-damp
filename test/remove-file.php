<?php                                                                                                                                                                        
                                                                                                                                                                             
/*                                                                                                                                                                           
require_once("../classes/config.php");                                                                                                                                       
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/CFile.php");                                                                                                              
*/                                                                                                                                                                           
                                                                                                                                                                             
chdir(dirname(__FILE__));                                                                                                                                                    
require_once('../classes/stdf.php');                                                                                                                                         
                                                                                                                                                                             
$results = array();                                                                                                                                                          
                                                                                                                                                                             
/*                                                                                                                                                                           
Удалить файлы из базы                                                                                                                                                        
https://st.fl.ru/projects/upload/201404/f_573533a61827a616.png                                                                                                               
http://st.free-lance.ru/users/Matrix-333/resume/f_4fe6e710ec167.pdf                                                                                                          
*/                                                                                                                                                                           
                                                                                                                                                                             
                                                                                                                                                                             
$files = array(                                                                                                                                                              
'projects/upload/201404/f_573533a61827a616.png',                                                                                                                             
'users/Ma/Matrix-333/resume/f_4fe6e710ec167.pdf'                                                                                                                                
);                                                                                                                                                                           
                                                                                                                                                                             
foreach($files as $filename)                                                                                                                                                 
{                                                                                                                                                                            
    $cfile = new CFile($filename);                                                                                                                                           
    $results[$filename] = $cfile->id;                                                                                                                                        
    //$cfile->Delete(NULL, dirname($filename).'/', basename($filename));                                                                                                     
}                                                                                                                                                                            
                                                                                                                                                                             
                                                                                                                                                                             
/*                                                                                                                                                                           
$filename = 'users/fr/freelancer7/resume/f_1325346524ccece7.pdf';                                                                                                            
$file = new CFile($filename);                                                                                                                                                
$results['id'] = $file->id;                                                                                                                                                  
*/                                                                                                                                                                           
                                                                                                                                                                             
                                                                                                                                                                             
                                                                                                                                                                             
                                                                                                                                                                             
//------------------------------------------------------------------------------                                                                                             
                                                                                                                                                                             
array_walk($results, function(&$value, $key){                                                                                                                                
    $value = sprintf('%s = %s'.PHP_EOL, $key, $value);                                                                                                                       
});                                                                                                                                                                          
                                                                                                                                                                             
print_r(implode('', $results));                                                                                                                                              
                                                                                                                                                                             
exit;