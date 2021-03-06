<?php
namespace Crud\Test\TestCase\View\Helper;

use Crud\View\Helper\UtilsHelper;
use Cake\TestSuite\TestCase;
use Cake\View\View;

/**
 * Crud\View\Helper\UtilsHelper Test Case
 */
class UtilsHelperTest extends TestCase
{

    /**
     * Test subject
     *
     * @var \Crud\View\Helper\UtilsHelper
     */
    public $Utils;

    /**
     * setUp method
     *
     * @return void
     */
    public function setUp()
    {
        parent::setUp();
        $view = new View();
        $this->Utils = new UtilsHelper($view);
    }

    /**
     * tearDown method
     *
     * @return void
     */
    public function tearDown()
    {
        unset($this->Utils);

        parent::tearDown();
    }

    /**
     * Test initial setup
     *
     * @return void
     */
    public function testInitialization()
    {
        $this->markTestIncomplete('Not implemented yet.');
    }
}
