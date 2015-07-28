<?php

require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/stdf.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/CFile.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/uploader/uploader.php");

//------------------------------------------------------------------------------

const STR_AUTH_REQ  = "Требуется авторизация.";
const STR_FRL_ONLY  = "Этот сервис доступен только для фрилансеров.";
const STR_FDELERR   = "Ошибка удаления файла.";
const STR_FMAX      = "Максимальное количество файлов: %d.";
const STR_FTOSMAL   = "Изображение слишком маленькое. Минимальные размеры %d на %d точек.";
const STR_FERR      = "Ошибка загрузки файла.";
const STR_WRNG_TYPE = "Недопустимый формат файла.";



//------------------------------------------------------------------------------

$_config = array(
    'solt' => '26bFRs2mgwuX_',
    'max_files' => 50,
    'max_file_size' => 10 * 1024 * 1024, // 10 мб
    'table' => 'file_tservices',
    'maxImageHeight' => 1000,//пока не используется поскольку изображение урезается max_image_size
    'maxImageWidth' => 1000,//пока не используется поскольку изображение урезается max_image_size
    'minImageHeight' => 600,
    'minImageWidth' => 600,
    'allowed_ext' => array('jpg', 'jpeg', 'gif', 'png'),
    'image_types' => array(IMAGETYPE_GIF, IMAGETYPE_JPEG, IMAGETYPE_PNG, IMAGETYPE_JPEG2000),//IMAGETYPE_BMP, IMAGETYPE_ICO
    'dir' => '%s/tservices',
    'server_root' => false,
    //Основной файл
    'max_image_size' => array('width' => 800, 'height' => 600, 'less' => 1, 'prevent_less' => 0),
    'resize' => true,
    'proportional' => true,
    'crop' => false,
    'topfill' => false,
    //Нужны миниатюры?
    'thumbs' => array(
        array(
            'return' => true, //ответ этой миниатюрой
            'width' => 60,
            'height' => 60,
            'prefix' => 'tiny_',
            'option' => 'cropthumbnail',
            'dir' => '', //??? пока не используется всех в одну папку фигачим
            'params' => array('small' => 1) //Идентификаторы для базы
        )/* ,
          array(
          'width' => 100,
          'height' => 75,
          'prefix' => 'small_',
          'option' => 'cropthumbnail',
          'params' => array('small' => 2)
          ) */,
        array(
            'width' => 128,
            'height' => 96,
            'prefix' => 'thumb_',
            'option' => 'cropthumbnail',
            'params' => array('small' => 3)
        ),
        array(
            //'preview' => true,
            'width' => 200,
            'height' => 150,
            'prefix' => 'med_',
            'option' => 'cropthumbnail',
            'params' => array('small' => 4)
        )
    )
);

//------------------------------------------------------------------------------

$name = __paramInit('string',NULL,'name',"files");

$is_preview = $name == 'preview';
    
// Если загружаем превью, а не фото, то меняем конфиги
if ($is_preview) {
    $_config['allowed_ext'] = array('jpg', 'jpeg', 'png');
    $_config['minImageWidth'] = 200;
    $_config['minImageHeight'] = 150;    
    $_config['params'] = array('preview' => true);
    $_config['thumbs'] = array(
        array(
            'return' => true, //ответ этой миниатюрой
            'width' => 60,
            'height' => 60,
            'prefix' => 'tiny_',
            'option' => 'cropthumbnail',
            'dir' => '', //??? пока не используется всех в одну папку фигачим
            'params' => array('small' => 1, 'preview' => true) //Идентификаторы для базы
        ),
        array(
            //'preview' => true,
            'width' => 200,
            'height' => 150,
            'prefix' => 'med_',
            'option' => 'cropthumbnail',
            'params' => array('small' => 4, 'preview' => true)
        )
    );
}

session_start();

$result = array();
$db = $GLOBALS['DB'];

$uid = get_uid(false);

$owner_id = __paramInit('int',NULL,'owner_id',0);
$user_obj = new users();
$user_obj->GetUserByUID($owner_id);

$is_owner = ($uid > 0 && ($user_obj->uid == $uid));
$is_adm = hasPermissions('tservices');
$is_allow = ($is_owner || $is_adm);


if(!$is_allow)
{
    $result['error'] = STR_AUTH_REQ;//'Требуется авторизация.';
}
else if(is_emp() && !$is_adm)
{
    $result['error'] = STR_FRL_ONLY;//'Этот сервис доступен только для фрилансеров.';
}
else
{
    $_method = strtoupper(__paramInit('string',NULL,'_method',''));
    
    switch($_method)
    {
        case 'DELETE':
            
            $file_id = __paramInit('string',NULL,'qquuid','');
            $sess = __paramInit('string',NULL,'sess','');
            $hash = __paramInit('string',NULL,'hash','');
            $post_id = __paramInit('string',NULL,'id','');
            
            $is_hash = ((intval($post_id) > 0) && !empty($hash) && ($hash == md5( $_config['solt'] .  $file_id . $post_id . $uid )));
            $is_sess = (!$post_id && (strlen($sess) > 0));
            
            if(!$is_hash && !$is_sess)
            {
                $result['error'] = STR_FDELERR;//"Ошибка удаления файла.";        
            } 
            else
            {
                if($is_hash)
                {
                    //Получаем существующие
                    $files = $db->rows("
                        SELECT id, fname, path FROM {$_config['table']} 
                        WHERE src_id = ?i",
                        intval($post_id)
                    );
                }
                else
                {
                    $files = uploader::sgetFiles($sess);
                }
            
                
                $original = array_filter($files,function ($file) use ($file_id) { 
                    return ($file['id'] == $file_id); 
                });
                
                
                if(count($original))
                {
                    //Ищем миниатюры и удаляем усе
                    $original = current($original);
                
                    $cnt = count($_config['thumbs']);
                    if($cnt)
                    {
                        $_regex = '';
                        $_prefs = array();
                        foreach($_config['thumbs'] as $key => $thumb)
                        {
                            if(!isset($thumb['prefix'])) continue;
                        
                            $_regex .= $thumb['prefix'];
                            if($key < $cnt-1) $_regex .= '|';
                            else $_regex = '('.$_regex.')?';
                    
                            $_prefs[] = $thumb['prefix'];
                        }
                    }
            
                    $clear_name = str_replace($_prefs, '', $original['fname']);
            
                    $all_files = array_filter($files,function ($file) use ($clear_name, $_regex) { 
                        return preg_match('/^' . $_regex . $clear_name . '$/', $file['fname']);
                    });

                    $cfile = new CFile();
                    $cfile->table = $_config['table'];

                    $file_ids = array();
                    foreach($all_files as $file)
                    {
                        //Удаляем из таблицы file_tservices
                        $cfile->Delete($file['id']);
                        $file_ids[] = $file['id'];
                    }

                    //Помечаем как удаленные в таблице attachedfiles
                    if($is_sess) 
                    {
                        uploader::sremoveFiles($sess, $file_ids);
                    }
                    
                    $result = array('success' => true);
                }

            }
            break;
        
            
//------------------------------------------------------------------------------        
            
            
        default:
            
            $sess = __paramInit('string',NULL,'sess','');
            if(!strlen($sess))
            {
                $result['error'] = STR_FERR;//'Ошибка загрузки файла.';
            }
            else
            {
                $uploader = new uploader($sess);
                
                $files_info = $uploader->getCountResource();
                $img_cnt = count($_config['thumbs']) + 1;
                $files_count = ($files_info['count'] > 0)?
                        round($files_info['count']/$img_cnt):
                        $files_info['count'];
           
                if(($files_count + 1) > $_config['max_files']) 
                {          
                    $result['error'] = sprintf(STR_FMAX,$_config['max_files']);//"Максимальное количество файлов: {$_config['max_files']}.";
                }
                else
                {
                    $cfile = new CFile($_FILES['qqfile']);
                    $cfile->table = $_config['table'];
                    $cfile->max_size = $_config['max_file_size'];
                    $cfile->server_root = $_config['server_root'];
                    $cfile->max_image_size = $_config['max_image_size'];
                    $cfile->resize = $_config['resize'];
                    $cfile->proportional = $_config['proportional'];
                    $cfile->crop = $_config['crop'];
                    $cfile->topfill = $_config['topfill'];
                    $cfile->allowed_ext = $_config['allowed_ext'];
                    $dir = sprintf($_config['dir'],$user_obj->login);
                    
                    $cfile->_getImageSize($cfile->tmp_name);
                    
                    if($cfile->image_size && in_array($cfile->image_size['type'], $_config['image_types']))
                    {
                        if (($_config['minImageHeight'] && $cfile->image_size['height'] < $_config['minImageHeight']) || 
                            ($_config['minImageWidth'] && $cfile->image_size['width'] < $_config['minImageWidth'])) 
                        {
                            $result['error'] = sprintf(STR_FTOSMAL,$_config['minImageWidth'],$_config['minImageHeight']);
                            //sprintf('Изображение слишком маленькое. Минимальные размеры %d на %d точек.',$_config['minImageWidth'],$_config['minImageHeight']);
                        }
                        else
                        {
                            $filename = $cfile->MoveUploadedFile($dir); 
                            
                            $error = $cfile->error;
                            $error = (is_array($error))?$error:array($error);
                            $cnt = count($error);
                            
                            if(!$filename || $cnt > 0)
                            {
                                $result['error'] = STR_FERR . ' ' . implode(' ', $error);//'Ошибка загрузки файла. '
                            }
                            else
                            {
                                $fileinfo = $uploader->createFile($cfile);
                                
                                if (count($_config['params'])) {
                                    $err = $cfile->updateFileParams($_config['params']);
                                }
                                    
                                if(count($_config['thumbs']))
                                {
                                    foreach($_config['thumbs'] as $thumb)
                                    {
                                        $thumb_cfile = $cfile->resizeImage(
                                                $cfile->path . $thumb['prefix'] . $cfile->name, 
                                                $thumb['width'], 
                                                $thumb['height'], 
                                                $thumb['option'], 
                                                true);                                        
                                        
                                        if(!$thumb_cfile)
                                        {
                                            $result['error'] = STR_FERR;//"Ошибка загрузки файла.";
                                            break;
                                        }
                                        
                                        $uploader->createFile($thumb_cfile);
                                        
                                        if(count($thumb['params']))
                                        {
                                            $thumb_cfile->updateFileParams($thumb['params']);
                                        }
                                        
                                        if(isset($thumb['return']) && $thumb['return'] == true)
                                        {
                                            $result += array(
                                                'thumbnailUrl' => WDCPREFIX . '/' . $thumb_cfile->path . $thumb_cfile->name,
                                            );
                                        }
                                        
                                        if(isset($thumb['preview']) && $thumb['preview'] == true)
                                        {
                                            $result += array(
                                                'previewUrl' => WDCPREFIX . '/' . $thumb_cfile->path . $thumb_cfile->name,
                                            );
                                        }
                                    }
                                }

                                if(!isset($result['error']))
                                {
                                    $result += array(
                                        'newUuid' => $fileinfo['id'],
                                        'success' => true,
                                        'is_preview' => $is_preview
                                    );    
                                }
                            }
                        }
                    }
                    else
                    {
                        $result['error'] = STR_WRNG_TYPE;//"Недопустимый формат файла.";
                    }
                    
                    if(isset($result['error']) && $cfile->id > 0)
                    {
                        $cfile->Delete($cfile->id);
                    }
                }
            }
    }
}
 

if(isset($result['error'])) $result['error'] = iconv('cp1251', 'utf-8', $result['error']);
header('Content-type: text/html; charset=windows-1251');
echo json_encode($result);