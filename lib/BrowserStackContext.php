<?php

require 'vendor/autoload.php';


class BrowserStackContext implements Behat\Behat\Context\Context
{
    protected static $CONFIG;
    protected static $driver;

    public function __construct($parameters) {

        self::$CONFIG = $parameters;

        $GLOBALS['BROWSERSTACK_USERNAME'] = getenv('BROWSERSTACK_USERNAME');
        if(!$GLOBALS['BROWSERSTACK_USERNAME']) $GLOBALS['BROWSERSTACK_USERNAME'] = self::$CONFIG['user'];

        $GLOBALS['BROWSERSTACK_ACCESS_KEY'] = getenv('BROWSERSTACK_ACCESS_KEY');
        if(!$GLOBALS['BROWSERSTACK_ACCESS_KEY']) $GLOBALS['BROWSERSTACK_ACCESS_KEY'] = self::$CONFIG['key'];

        // Check if our driver has been created, if not create it
        if( !self::$driver ) {
            self::createDriver();
        }
    }

    public static function createDriver() {

        # Each parallel test we are running will contain  
        $test_run_id = getenv("TEST_RUN_ID") ? getenv("TEST_RUN_ID") : 0; 
        

        $url = "https://" . $GLOBALS["BROWSERSTACK_USERNAME"] . ":" . $GLOBALS["BROWSERSTACK_ACCESS_KEY"] . "@" . self::$CONFIG["server"] ."/wd/hub";

        # get the capabilities for this test_run_id 
        # caps contains the os, browser, and resolution
        $browserCaps = self::$CONFIG["browsers"][$test_run_id];
        # pull in capabilities that we want applied to all tests 
        foreach (self::$CONFIG["capabilities"] as $capName => $capValue) {
            if(!array_key_exists($capName, $browserCaps))
                $browserCaps[$capName] = $capValue;
        }

        self::$driver = RemoteWebDriver::create($url, $browserCaps, 120000, 120000);
    }

    /** @AfterSuite */
    public static function tearDown()
    {
        if(self::$driver)
            self::$driver->quit();
    }
}
?>
