<?php
namespace App\Test\TestCase\Controller\Component;

use App\Controller\Component\CrudComponent;
use Cake\Controller\ComponentRegistry;
use Cake\TestSuite\TestCase;

/**
 * App\Controller\Component\CrudComponent Test Case
 */
class CrudComponentTest extends TestCase
{

    /**
     * Test subject
     *
     * @var \App\Controller\Component\CrudComponent
     */
    public $Crud;

    /**
     * setUp method
     *
     * @return void
     */
    public function setUp()
    {
        parent::setUp();
        $registry = new ComponentRegistry();
        $this->Crud = new CrudComponent($registry);
    }

    /**
     * tearDown method
     *
     * @return void
     */
    public function tearDown()
    {
        unset($this->Crud);

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
