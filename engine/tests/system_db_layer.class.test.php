<?php
//new system_db_layer();

class TestCase extends UnitTestCase {
    function TestCase() {
//        /$mock = &new Mocksystem_db_layer();
        //$this->assertIsA($mock, 'SimpleMock');
    }
    
    function testSettings() {
       try {
            $e = system_db_layer::getInstance()->select("SELECT * FROM users LIMIT 1;")->fetchAll();
       } catch(Exception $v) {
       
       }
       $this->assertTrue(sizeof($e) == 1, "Select from users error");
       
       $e = false;
       try {
            $e = system_db_layer::getInstance()->select("SELECT * FROM users LIMIT 1;")->fetchRow();
       } catch(Exception $v) {
       
       }
       $this->assertTrue(sizeof($e) > 1, "Select Row from users error");
       
       $e = false;
       try {
            $e = system_db_layer::getInstance()->select("SELECT * FROM users LIMIT 1;")->fetchOne();
       } catch(Exception $v) {
       
       }
       $this->assertTrue(sizeof($e) == 1, "Select One Cell from users error");
       
       $e = false;
       try {
            $e = system_db_layer::getInstance()->select("SELECT * FROM users WHERE uid > ? LIMIT 1;", 10000)->fetchRow();
       } catch(Exception $v) {
       
       }
       $this->assertTrue(sizeof($e) > 1, "Select Row with placeholder from users error");
       $this->assertTrue(false, "Ошибка функции биллинга");
       $this->assertTrue(false, "Нет класса");
       $this->assertTrue(false, "Не правильно возвращен результат пользователя");
       
       
    }
}


$test->addTestCase(new TestCase());
?>
