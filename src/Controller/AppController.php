<?php

namespace Crud\Controller;

use Cake\Controller\Controller;

/**
 * Class AppController
 * @package Crud\Controller
 *
 * @property \Crud\Controller\Component\CrudComponent $Crud
 * @property \Crud\Controller\Component\InfiniteScrollComponent $InfiniteScroll
 */
class AppController extends Controller
{
    public function initialize()
    {
        parent::initialize();

        $this->loadComponent('RequestHandler', [
            'enableBeforeRedirect' => false,
        ]);
        $this->loadComponent('Flash');
        $this->loadComponent('Crud.Crud', [
//            'infiniteScroll' => true
        ]);
    }
}
