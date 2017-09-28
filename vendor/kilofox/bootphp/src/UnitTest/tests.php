<?php

if (!class_exists('Bootphp')) {
    exit('Please include the bootphp bootstrap file (see README.markdown)');
}

if ($file = Core::find_file('classes', 'Unittest/Tests')) {
    require_once $file;
    // PHPUnit requires a test suite class to be in this file,
    // so we create a faux one that uses the bootphp base
    class TestSuite extends Unittest_Tests
    {

    }

} else {
    exit('Could not include the test suite');
}
