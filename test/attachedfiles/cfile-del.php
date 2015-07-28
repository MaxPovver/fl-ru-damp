<?php      

chdir(dirname(__FILE__));                                                                                                           
require_once('../../classes/stdf.php');                                                                                             
                                                                                                                                    
//$filename = 'users/er/ericafor/tmpproj/f_4d6d828df0cfc.pdf';                                                                      
                                                                                                                                    
//$filename = 'users/os/ost56/contacts/f_94853908f3702609.zip';                                                                     
                                                                                                                                    
//$filename = 'users/en/enhard/reserves/27959/f_96054211b8451003.pdf';                                                              
                                                                                                                                    
                                                                                                                                    
$filenames = array(                                                                                                                 
'users/st/stihami/resume/f_874516f3e49eb4d2.pdf',                                                                                   
'users/DL/DLK/resume/f_534523a08de43224.pdf',                                                                                       
'users/vk/vkulpina/resume/f_507d8cc4345be.pdf'                                                                                      
);                                                                                                                                  
                                                                                                                                    
                                                                                                                                    
                                                                                                                                                                                                                                                                    
$cfile = new CFile();                                                                                                               
                                                                                                                                    
if(count($filenames)) {                                                                                                             
    foreach($filenames as $filename) {                                                                                              
        $res = $cfile->Delete(NULL, dirname($filename).'/', basename($filename));                                                   
        var_dump($res);                                                                                                             
    }                                                                                                                               
}                                                                                                                                   
                                                                                                                                    
exit;