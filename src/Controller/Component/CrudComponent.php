<?php

namespace Crud\Controller\Component;

use Cake\Controller\Component;
use Cake\Controller\ComponentRegistry;
use Cake\Controller\Exception\MissingActionException;
use Cake\Datasource\EntityInterface;
use Cake\Datasource\ResultSetInterface;
use Cake\Event\Event;
use Cake\Http\Exception\BadRequestException;
use Cake\Http\Exception\InternalErrorException;
use Cake\Http\Exception\NotFoundException;
use Cake\Http\Response;
use Cake\ORM\Query;
use Cake\ORM\ResultSet;
use Cake\Utility\Hash;
use Cake\Utility\Inflector;

/**
 * Crud component
 */
class CrudComponent extends Component
{

    /**
     * Reference to the current controller.
     *
     * @var \Cake\Controller\Controller
     */
    protected $_controller;

    /**
     * The table instance associated with the current controller.
     *
     * @var \Cake\ORM\Table
     */
    protected $_table;

    /**
     * Reference to the current request.
     *
     * @var \Cake\Http\ServerRequest
     */
    protected $_request;

    /**
     * The class name of the entity associated with the current table.
     *
     * @var string
     */
    protected $_entityName;

    /**
     * The current controller action.
     *
     * @var string
     */
    protected $_action;

    /**
     * Whether the response should be serialized, i.e. JSON or XML.
     * @var bool
     */
    protected $_serialized;

    /**
     * Whether the current action effects many data entries, i.e. saveMany, updateAll, deleteAll
     * @var bool
     */
    protected $_bulkAction;

    /**
     * Actions that are handled by Crud component.
     * @var array
     */
    protected $_crudActions = ['index', 'view', 'add', 'edit', 'delete'];

    /**
     * Default configuration.
     *
     * @var array
     */
    protected $_defaultConfig = [
        'bulkAdd' => true,
        'bulkEdit' => true,
        'bulkDelete' => true,
        'infiniteScroll' => null
    ];

    public function __construct(ComponentRegistry $registry, array $config = [])
    {
        $this->_controller = $registry->getController();
        $this->_table = $this->_controller->{$this->_controller->modelClass};

        parent::__construct($registry, $config);
    }

    public function initialize(array $config)
    {
        parent::initialize($config);

        $this->_request = $this->_controller->getRequest();
        $this->_action = $this->_request->getParam('action');

        $acceptsContentTypes = $this->_request->accepts();
        $this->_serialized = !empty(array_intersect(['application/json', 'application/xml'], $acceptsContentTypes))
            && !in_array('text/html', $acceptsContentTypes);
        $this->_entityName = Inflector::singularize(lcfirst($this->_controller->modelClass));
    }

    /**
     * Called after the Controller::beforeFilter() and before the controller action.
     *
     * @param \Cake\Event\Event $event Event instance
     * @return \Cake\Http\Response|null
     * @throws \Exception
     */
    public function startup(Event $event)
    {
        if (!$this->_controller->isAction($this->_action)) {
            if (!$this->_request) {
                throw new \LogicException('No Request object configured. Cannot invoke action');
            }
            if (!in_array($this->_action, $this->_crudActions)) {
                throw new MissingActionException([
                    'controller' => $this->_controller->getName() . 'Controller',
                    'action' => $this->_request->getParam('action'),
                    'prefix' => $this->_request->getParam('prefix') ?: '',
                    'plugin' => $this->_request->getParam('plugin'),
                ]);
            }
            $response = $this->load();

            if (!$response && $this->_controller->isAutoRenderEnabled()) {
                $this->_controller->render();
            }

            $result = $this->_controller->shutdownProcess();
            if ($result instanceof Response) {
                return $result;
            }
            if (!$response) {
                $response = $this->_controller->getResponse();
            }

            return $response;
        }
    }

    public function getTable()
    {
        return $this->_table;
    }

    /**
     * Load the query/object(s), carry out the request action and set the view.
     *
     * By default, `$options` will recognize the following keys:
     *
     * - `action` - The action to execute this query with. One of (index, view, add, edit, delete)
     * - `allowMethods` - The HTTP methods allowed for the current action.
     * - `updateMethods` - The HTTP methods that will carry out an update action in the database.
     *   Usually one of (post, patch, put, delete)
     * - `successMessage`
     * - `errorMessage`
     * - `pagination` - Settings for pagination.
     * - `infiniteScroll` - Settings for infiniteScroll.
     * - `objectHydration` - A list of options for the objects hydration.
     * - `viewVars` - Extra view variables to load into the view, e.g., ['tags', 'files' => ['contain' => 'Users'], ...].
     * - `view` - View to use for rendering.
     *
     * @param array|Query|EntityInterface|ResultSetInterface|ResultSet|null $query
     * @param array $options
     * @return \Cake\Http\Response|null
     */
    public function load($query = [], array $options = [])
    {
        $this->_setBulkAction();
        $options = $this->_validateOptions($options);
        $entity = $this->_getEntity($query, $options);
        $options = $this->_getActionConfig($options);

        $action = $options['allowMethods'] ?? $this->_action;
        $allowMethods = $options['allowMethods'];
        $updateMethods = $options['updateMethods'];
        $method = $options['method'];
        $successMessage = $options['successMessage'];
        $errorMessage = $options['errorMessage'];
        $viewVars = $options['viewVars'] ?? [];
        $view = $options['view'] ?? '';

        if (!empty($allowedMethods)) {
            $this->_request->allowMethod($allowMethods);
        }

        if ($this->_request->is($updateMethods)) {
            $success = $this->_table->{$method}($entity);
            $flashMethod = $success ? 'success' : 'error';
            $statusCode = $success ? 200 : 500;
            $message = $success ? $successMessage : $errorMessage;

            if ($this->_serialized) {
                switch ($action) {
                    case 'index':
                        $serializeData = $entity;
                        break;
                    case 'add':
                        if ($this->_bulkAction) {
                            $ids = array_map(function ($entity) {
                                return ['id' => $entity->id];
                            }, $entity);
                            if (count($ids) === 1) {
                                $ids = $ids[0];
                            }
                            $serializeData = $ids;
                            break;
                        }
                        $serializeData = ['id' => $entity->id];
                        break;
                    default:
                        $serializeData = $message;
                        break;
                }
                return $this->serialize($serializeData, $statusCode);
            }
            $this->_controller->Flash->{$flashMethod}($message);

            // refresh page on success
            if ($success) {
                return $this->_controller->redirect(['action' => 'index']);
            }
        }

        if ($this->_serialized) {
            return $this->serialize($entity);
        }

        if ($action !== 'delete') {
            $name = $entity instanceof \Countable ? lcfirst($this->_table->getAlias()) : $this->_entityName;
            $this->_controller->set($name, $entity);
            $this->setExtraViews($viewVars);
        }

        if ($view) {
            $this->_controller->render($view);
        }
    }

    protected function _validateOptions($options)
    {
        $allowedOptions = [
            'action',
            'allowMethods',
            'updateMethods',
            'successMessage',
            'errorMessage',
            'pagination',
            'infiniteScroll',
            'objectHydration',
            'viewVars',
            'view'
        ];

        foreach ($options as $key => $value) {
            if (!in_array($key, $allowedOptions)) {
                unset($options[$key]);
            }
        }

        if (isset($options['action']) && !in_array($options['action'], $this->_crudActions)) {
            throw new \InvalidArgumentException(
                "Action should be one of (" . implode(', ', $this->_crudActions) . ').');
        }

        return $options;
    }

    /**
     * Get entity/entities used for the current action.
     * If query is empty, returns the default entity/entities for the current action.
     *
     * @param array|Query|EntityInterface|ResultSetInterface|ResultSet|null $query
     * @param array $options
     * @return EntityInterface|ResultSetInterface|ResultSet|null
     */
    protected function _getEntity($query = [], $options = [])
    {
        if ($query instanceof EntityInterface || $query instanceof ResultSetInterface || $query instanceof ResultSet) {
            return $query;
        }

        if (is_null($query)) {
            $query = [];
        }

        if (!is_array($query) && !($query instanceof Query)) {
            throw new InternalErrorException('Invalid type for the first parameter');
        }

        $requestData = $this->_request->getData();
        $id = $this->_request->getParam('id') ?: ($this->_request->getParam('pass')[0] ?? null);
        $objectHydration = $options['objectHydration'] ?? [];
        $action = $options['action'] ?? $this->_action;

        if ($this->_bulkAction && !$this->getConfig('bulk' . ucfirst($action))) {
            throw new BadRequestException("Cannot bulk $action $this->_table");
        }

        switch ($action) {
            case 'index':
                if (is_array($query)) {
                    $query = $this->_table->find('all', $query);
                }
                $entity = is_null($this->getConfig('infiniteScroll')) ?
                    $this->_controller->paginate($query, $options['infiniteScroll'] ?? []) :
                    $this->infiniteScroll($query, $options['pagination'] ?? []);
                break;
            case 'view':
                if ($query instanceof Query) {
                    $entity = $query->where(["{$query->getRepository()->getAlias()}.id" => $id])->first();
                    if (is_null($entity)) {
                        throw new NotFoundException();
                    }
                } else {
                    $entity = $this->_table->get($id, $query);
                }
                break;
            case 'add':
                $method = $this->_bulkAction ? 'newEntities' : 'newEntity';
                $entity = $this->_table->{$method}($requestData ?: null, $objectHydration);
                break;
            case 'edit':
                if ($this->_bulkAction) {
                    $entity = [];
                    $ids = Hash::extract($requestData, '{n}.id');
                    if (!empty($ids)) {
                        $entity = $this->_table->find()->whereInList('id', $ids)->all();
                    }
                } else {
                    $entity = $this->_table->get($id);
                }
                $method = $this->_bulkAction ? 'patchEntities' : 'patchEntity';
                $entity = $this->_table->{$method}($entity, $requestData, $objectHydration);
                break;
            case 'delete':
                if ($this->_bulkAction) {
                    $ids = Hash::extract($requestData, '{n}.id');
                    if (empty($ids) || !is_array($requestData) || count($ids) !== count($requestData)) {
                        throw new BadRequestException();
                    }
                    $entity = $this->_table->find()->whereInList('id', $ids)->all();
                } else {
                    $entity = $this->_table->get($id);
                }
                break;
            default:
                if ($query instanceof Query) {
                    $result = $query->all();
                } elseif (is_array($query) && !empty($query)) {
                    $result = $this->_table->find('all', $query)->all();
                } else {
                    return null;
                }
                switch ($result->count()) {
                    case 0:
                        return null;
                    case 1:
                        return $result->first();
                    default:
                        return $result;
                }
        }

        return $entity;
    }

    /**
     * Check if the current action effects many data entries
     * @return bool
     */
    protected function _setBulkAction()
    {
        $requestData = $this->_request->getData();

        if (!is_array($requestData) || !is_assoc($requestData)) {
            return false;
        }

        foreach ($requestData as $key => $value) {
            if (!is_array($value)) {
                return false;
            }
        }

        return $this->_bulkAction = true;
    }

    /**
     * Get the configuration for the current action.
     *
     * @param $options
     * @return array $config
     */
    protected function _getActionConfig($options)
    {
        $config = [
            'method' => 'save',
            'successMessage' => 'Success!',
            'errorMessage' => 'Request failed. Please, try again.',
            'allowMethods' => [],
            'updateMethods' => []
        ];

        $action = $options['action'] ?? $this->_action;
        $override = [];
        $entity = null;
        switch ($action) {
            case 'index':
                $override = [];
                break;
            case 'view':
                break;
            case 'add':
                if ($this->_bulkAction) {
                    $override['method'] = 'saveMany';
                }
                $override['updateMethods'] = ['post'];
                break;
            case 'edit':
                $override = [
                    'updateMethods' => ['patch', 'post', 'put'],
                ];
                if ($this->_bulkAction) {
                    $override['method'] = 'updateAll';
                }
                break;
            case 'delete':
                $override = [
                    'allowMethods' => ['post', 'delete'],
                    'updateMethods' => ['post', 'delete'],
                    'method' => $this->_bulkAction ? 'deleteAll' : 'delete'
                ];
                break;
        }

        $config = array_merge($config, $override);

        return array_merge($config, $options);
    }

    /**
     * `find()` the fields of the table and `set()` them.
     * @param array $viewVars e.g. ['tags', 'files' => ['contain' => 'Users'], ...]
     */
    public function setExtraViews($viewVars = [])
    {
        foreach ($viewVars as $field => $options) {
            if (is_int($field)) {
                $field = $options;
                $options = ['limit' => 10];
            }
            $this->_controller->set(lcfirst($field), $this->_table->{ucfirst($field)}->find('list', $options));
        }
    }

    /**
     * Serializes the response body, i.e. json/xml
     * @param array|int|string|EntityInterface|ResultSetInterface $data
     * @param int $status
     */
    public function serialize($data = [], $status = 200)
    {
        if (is_int($data) && 100 <= $data && $data < 600) {
            $status = $data;
            $data = [];
        } elseif (is_string($data)) {
            $data = ['message' => $data];
        } elseif ($data instanceof EntityInterface || $data instanceof ResultSetInterface) {
            $data = $data->toArray();
        } elseif (!$data) {
            $data = [];
        }

        $this->_controller->setResponse($this->_controller->getResponse()->withStatus($status));
        $this->_controller->set(array_merge($data, ['_serialize' => array_keys($data)]));
    }

    /**
     * Handles infiniteScroll of records in Table objects.
     *
     * Will load the referenced Table object, and have the InfiniteScrollComponent
     * scroll the query using the request data and settings defined in
     * `$this->loadComponent('Crud.Crud, ['infiniteScroll' => $settings])`.
     *
     * @param \Cake\ORM\Table|string|\Cake\ORM\Query|null $object Table to infiniteScroll
     * (e.g: Table instance, 'TableName' or a Query object)
     * @param array $settings The settings/configuration used for infiniteScroll.
     * @return \Cake\ORM\ResultSet|\Cake\Datasource\ResultSetInterface Query results
     * @throws \RuntimeException When no compatible table object can be found.
     */
    public function infiniteScroll($object = null, array $settings = [])
    {
        $this->_controller->loadComponent('Crud.InfiniteScroll');
        $settings += $this->getConfig('infiniteScroll', []);

        return $this->_controller->InfiniteScroll->scroll($object, $settings);
    }
}
