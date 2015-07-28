<?php
$EXTERNAL_REQ = array (
  'type' => 'xml',
  'protocol-version' => 1.0,
  'data' => file_get_contents('php://input')
);

include('../index.php');
