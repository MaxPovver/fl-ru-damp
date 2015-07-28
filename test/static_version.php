<?php
define('IS_OPENED', 1);
chdir(dirname(__FILE__));
require_once ( '../classes/stdf.php' );
require_once ( '../classes/static_compress.php' );

$stc = new static_compress();
if($argv[1] == '--only-bem') {
   exit( +!$stc->createBemBatchFiles() );
}

exit( $stc->updateBatchesVersion() );

