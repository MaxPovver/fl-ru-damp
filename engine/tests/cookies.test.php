<?php
class TestOfLogging extends UnitTestCase {
    function testLogCreatesNewFileOnFirstMessage() {
        $this->assertFalse(file_exists('/temp/test.log'));
    }
}

$test->addTestCase(new TestOfLogging());

?>