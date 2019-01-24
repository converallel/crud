<?php

namespace Crud\Controller;

use Crud\Controller\AppController;

/**
 * Logs Controller
 *
 * @property \Crud\Model\Table\LogsTable $Logs
 *
 * @method \Crud\Model\Entity\Log[]
 */
class LogsController extends AppController
{
    public function index()
    {
        $this->load(['contain' => ['Users', 'HttpStatusCodes']]);
    }
}
