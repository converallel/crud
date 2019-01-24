<?php

namespace Crud\Test\TestCase\Controller\Component;

use Cake\Controller\ComponentRegistry;
use Cake\TestSuite\TestCase;
use Crud\Controller\Component\InfiniteScrollComponent;

/**
 * Crud\Controller\Component\InfiniteScrollComponent Test Case
 */
class InfiniteScrollComponentTest extends TestCase
{

    /**
     * Test subject
     *
     * @var \Crud\Controller\Component\InfiniteScrollComponent
     */
    public $InfiniteScroll;

    /**
     * setUp method
     *
     * @return void
     */
    public function setUp()
    {
        parent::setUp();
        $registry = new ComponentRegistry();
        $this->InfiniteScroll = new InfiniteScrollComponent($registry);
    }

    /**
     * tearDown method
     *
     * @return void
     */
    public function tearDown()
    {
        unset($this->InfiniteScroll);

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
