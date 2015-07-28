<?

if (! defined('SIMPLE_TEST')) {
    define('SIMPLE_TEST', getcwd().'/engine/libs/simpletest/');
}


require_once(SIMPLE_TEST . 'autorun.php');
        // die();
      //vardump(new SimpleTestCase());

class TestOfLogging extends UnitTestCase {
    function testLogCreatesNewFileOnFirstMessage() {
        $this->assertFalse(file_exists('/temp/test.log'));
        $this->assertTrue(file_exists('/temp/test.log'));
    }
}

?>