<?php

namespace Crud\Controller\Component;

use Cake\Controller\Component;
use Cake\Controller\ComponentRegistry;
use Cake\Core\InstanceConfigTrait;
use Cake\Datasource\QueryInterface;
use Cake\Datasource\RepositoryInterface;

/**
 * InfiniteScroll component
 */
class InfiniteScrollComponent extends Component
{
    use InstanceConfigTrait;

    /**
     * Default infiniteScroll settings.
     *
     * When calling scroll() these settings will be merged with the configuration
     * you provide.
     *
     * - `maxLimit` - The maximum limit users can choose to view. Defaults to 100
     * - `limit` - The initial number of items per scroll. Defaults to 20.
     * - `whitelist` - A list of parameters users are allowed to set using request
     *   parameters. Modifying this list will allow users to have more influence
     *   over infiniteScroll, be careful with what you permit.
     *
     * @var array
     */
    protected $_defaultConfig = [
        'limit' => 20,
        'maxLimit' => 100,
        'whitelist' => ['limit', 'sort', 'direction', 'min_position', 'max_position']
    ];

    /**
     * Scrolling params after infiniteScroll operation is done.
     *
     * @var array
     */
    protected $_scrollingParams = [];

    /**
     * Events supported by this component.
     *
     * @return array
     */
    public function implementedEvents()
    {
        return [];
    }

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

    public function __construct(ComponentRegistry $registry, array $config = [])
    {
        $this->_controller = $registry->getController();

        parent::__construct($registry, $config);
    }

    public function initialize(array $config)
    {
        parent::initialize($config);

        $this->_request = $this->_controller->getRequest();
    }

    /**
     * Handles infiniteScroll of records in Table objects.
     *
     * Will load the referenced Table object, and have the InfiniteScrollComponent scroll the query.
     *
     * @param \Cake\Datasource\RepositoryInterface|string|\Cake\Datasource\QueryInterface|null $object
     *        The table or query to infiniteScroll
     * (e.g: Table instance, 'TableName' or a Query object)
     * @param array $settings The settings/configuration used for infiniteScroll.
     * @return \Cake\ORM\ResultSet|\Cake\Datasource\ResultSetInterface Query results
     * @throws \RuntimeException When no compatible table object can be found.
     */
    public function scroll($object = null, array $settings = [])
    {
        if (is_string($object) || $object === null) {
            $try = [$object, $this->_controller->modelClass];
            foreach ($try as $tableName) {
                if (empty($tableName)) {
                    continue;
                }
                $object = $this->_controller->loadModel($tableName);
                break;
            }
        }

        if (empty($object)) {
            throw new \RuntimeException('Unable to locate an object compatible with infiniteScroll.');
        }

        $query = null;
        if ($object instanceof QueryInterface) {
            $query = $object;
            $object = $query->where()->getRepository();
        }

        $queryParams = $this->_request->getQueryParams();

        $alias = $object->getAlias();
        $defaults = $this->getDefaults($alias, $settings);
        $options = $this->mergeOptions($queryParams, $defaults);
        $options = $this->validateSort($object, $options);
        $options = $this->checkLimit($options);
        $options = $this->filterByPosition($object, $query, $options);

        $options += ['scope' => null];
        list($finder, $options) = $this->_extractFinder($options);

        if (empty($query)) {
            $query = $object->find($finder, $options);
        } else {
            $query->applyOptions($options);
        }

        $cleanQuery = clone $query;
        $results = $query->all();
        $numResults = count($results);
        $count = $cleanQuery->count();

        $limit = $options['limit'];
        $order = (array)$options['order'];
        $sortDefault = $directionDefault = false;
        if (!empty($defaults['order']) && count($defaults['order']) === 1) {
            $sortDefault = key($defaults['order']);
            $directionDefault = current($defaults['order']);
        }

        $scrolling = [
            'finder' => $finder,
            'current' => $numResults,
            'count' => $count,
            'perScroll' => $limit,
            'sort' => $options['sort'],
            'direction' => isset($options['sort']) ? current($order) : null,
            'limit' => $defaults['limit'] != $limit ? $limit : null,
            'sortDefault' => $sortDefault,
            'directionDefault' => $directionDefault,
            'scope' => $options['scope'],
            'completeSort' => $order,
        ];

        $this->_scrollingParams = [$alias => $scrolling];
        $this->_setScrollingParams();

        return $results;
    }

    /**
     * Extracts the finder name and options out of the provided infiniteScroll options.
     *
     * @param array $options the infiniteScroll options.
     * @return array An array containing in the first position the finder name
     *   and in the second the options to be passed to it.
     */
    protected function _extractFinder($options)
    {
        $type = !empty($options['finder']) ? $options['finder'] : 'all';
        unset($options['finder'], $options['maxLimit']);

        if (is_array($type)) {
            $options = (array)current($type) + $options;
            $type = key($type);
        }

        return [$type, $options];
    }

    /**
     * @param \Cake\Datasource\RepositoryInterface $object Repository object.
     * @param QueryInterface $query
     * @param array $options The infiniteScroll options being used for this request.
     * @return array An array of options
     */
    public function filterByPosition(RepositoryInterface $object, QueryInterface $query, array $options)
    {
        $options['min_position'] = isset($options['min_position']) ? (int)$options['min_position'] : null;
        $options['max_position'] = isset($options['max_position']) ? (int)$options['max_position'] : null;
        if (!$options['min_position'] && !$options['max_position']) {
            return $options;
        }

        $scrollingUp = isset($options['min_position']);
        $position = $scrollingUp ? $options['min_position'] : $options['max_position'];

        $order = [];
        if (!empty($options['order'])) {
            $order = $options['order'];
        } elseif (!is_null($query)) {
            $query->clause('order')->iterateParts(function ($direction, $field) use (&$order) {
                $order[$field] = strtolower($direction);
                return $direction;
            });
        }

        if (empty($order)) {
            return $options;
        }

        $generateConditions = function ($order) use (&$generateConditions, $object, $scrollingUp, $position) {
            $field = key($order);
            $direction = $order[$field];
            $operation = $direction === 'asc' ? ($scrollingUp ? '<' : '>') : ($scrollingUp ? '>' : '<');
            $value = $object->find()->select($field)->where(['id' => $position]);

            if (count($order) <= 1) {
                $conditions["$field $operation"] = $value;
                return $conditions;
            }

            $conditions['OR']["$field $operation"] = $value;
            unset($order[$field]);
            $conditions['OR']['AND'] = array_merge([$field => $value], $generateConditions($order));

            return $conditions;
        };

        $options['conditions'] = $generateConditions($order);
        return $options;
    }

    /**
     * Get scrolling params after infiniteScroll operation.
     *
     * @return array
     */
    public function getScrollingParams()
    {
        return $this->_scrollingParams;
    }

    /**
     * Set scrolling params to request instance.
     *
     * @return void
     */
    protected function _setScrollingParams()
    {
        $controller = $this->getController();
        $request = $controller->getRequest();
        $scrolling = $this->_scrollingParams + (array)$request->getParam('scrolling', []);

        $controller->setRequest($request->withParam('scrolling', $scrolling));
    }

    /**
     * Merges the various options that InfiniteScroll uses.
     * Pulls settings together from the following places:
     *
     * - General infiniteScroll settings
     * - Model specific settings.
     * - Request parameters
     *
     * The result of this method is the aggregate of all the option sets
     * combined together. You can change config value `whitelist` to modify
     * which options/values can be set using request parameters.
     *
     * @param array $params Request params.
     * @param array $settings The settings to merge with the request data.
     * @return array Array of merged options.
     */
    public function mergeOptions($params, $settings)
    {
        if (!empty($settings['scope'])) {
            $scope = $settings['scope'];
            $params = !empty($params[$scope]) ? (array)$params[$scope] : [];
        }
        $params = array_intersect_key($params, array_flip($this->getConfig('whitelist')));

        return array_merge($settings, $params);
    }

    /**
     * Get the settings for a $model. If there are no settings for a specific
     * repository, the general settings will be used.
     *
     * @param string $alias Model name to get settings for.
     * @param array $settings The settings which is used for combining.
     * @return array An array of infiniteScroll settings for a model,
     *   or the general settings.
     */
    public function getDefaults($alias, $settings)
    {
        if (isset($settings[$alias])) {
            $settings = $settings[$alias];
        }

        $defaults = $this->getConfig();
        $maxLimit = isset($settings['maxLimit']) ? $settings['maxLimit'] : $defaults['maxLimit'];
        $limit = isset($settings['limit']) ? $settings['limit'] : $defaults['limit'];

        if ($limit > $maxLimit) {
            $limit = $maxLimit;
        }

        $settings['maxLimit'] = $maxLimit;
        $settings['limit'] = $limit;

        return $settings + $defaults;
    }

    /**
     * Validate that the desired sorting can be performed on the $object.
     *
     * Only fields or virtualFields can be sorted on. The direction param will
     * also be sanitized. Lastly sort + direction keys will be converted into
     * the model friendly order key.
     *
     * You can use the whitelist parameter to control which columns/fields are
     * available for sorting via URL parameters. This helps prevent users from ordering large
     * result sets on un-indexed values.
     *
     * If you need to sort on associated columns or synthetic properties you
     * will need to use a whitelist.
     *
     * Any columns listed in the sort whitelist will be implicitly trusted.
     * You can use this to sort on synthetic columns, or columns added in custom
     * find operations that may not exist in the schema.
     *
     * The default order options provided to scroll() will be merged with the user's
     * requested sorting field/direction.
     *
     * @param \Cake\Datasource\RepositoryInterface $object Repository object.
     * @param array $options The infiniteScroll options being used for this request.
     * @return array An array of options with sort + direction removed and
     *   replaced with order if possible.
     */
    public function validateSort(RepositoryInterface $object, array $options)
    {
        if (isset($options['sort'])) {
            $direction = null;
            if (isset($options['direction'])) {
                $direction = strtolower($options['direction']);
            }
            if (!in_array($direction, ['asc', 'desc'])) {
                $direction = 'asc';
            }

            $order = (isset($options['order']) && is_array($options['order'])) ? $options['order'] : [];
            if ($order && $options['sort'] && strpos($options['sort'], '.') === false) {
                $order = $this->_removeAliases($order, $object->getAlias());
            }

            $options['order'] = [$options['sort'] => $direction] + $order;
        } else {
            $options['sort'] = null;
        }
        unset($options['direction']);

        if (empty($options['order'])) {
            $options['order'] = [];
        }
        if (!is_array($options['order'])) {
            return $options;
        }

        $inWhitelist = false;
        if (isset($options['sortWhitelist'])) {
            $field = key($options['order']);
            $inWhitelist = in_array($field, $options['sortWhitelist'], true);
            if (!$inWhitelist) {
                $options['order'] = [];
                $options['sort'] = null;

                return $options;
            }
        }

        if ($options['sort'] === null
            && count($options['order']) === 1
            && !is_numeric(key($options['order']))
        ) {
            $options['sort'] = key($options['order']);
        }

        $options['order'] = $this->_prefix($object, $options['order'], $inWhitelist);

        return $options;
    }

    /**
     * Remove alias if needed.
     *
     * @param array $fields Current fields
     * @param string $model Current model alias
     * @return array $fields Unaliased fields where applicable
     */
    protected function _removeAliases($fields, $model)
    {
        $result = [];
        foreach ($fields as $field => $sort) {
            if (strpos($field, '.') === false) {
                $result[$field] = $sort;
                continue;
            }

            list ($alias, $currentField) = explode('.', $field);

            if ($alias === $model) {
                $result[$currentField] = $sort;
                continue;
            }

            $result[$field] = $sort;
        }

        return $result;
    }

    /**
     * Prefixes the field with the table alias if possible.
     *
     * @param \Cake\Datasource\RepositoryInterface $object Repository object.
     * @param array $order Order array.
     * @param bool $whitelisted Whether or not the field was whitelisted.
     * @return array Final order array.
     */
    protected function _prefix(RepositoryInterface $object, $order, $whitelisted = false)
    {
        $tableAlias = $object->getAlias();
        $tableOrder = [];
        foreach ($order as $key => $value) {
            if (is_numeric($key)) {
                $tableOrder[] = $value;
                continue;
            }
            $field = $key;
            $alias = $tableAlias;

            if (strpos($key, '.') !== false) {
                list($alias, $field) = explode('.', $key);
            }
            $correctAlias = ($tableAlias === $alias);

            if ($correctAlias && $whitelisted) {
                // Disambiguate fields in schema. As id is quite common.
                if ($object->hasField($field)) {
                    $field = $alias . '.' . $field;
                }
                $tableOrder[$field] = $value;
            } elseif ($correctAlias && $object->hasField($field)) {
                $tableOrder[$tableAlias . '.' . $field] = $value;
            } elseif (!$correctAlias && $whitelisted) {
                $tableOrder[$alias . '.' . $field] = $value;
            }
        }

        return $tableOrder;
    }

    /**
     * Check the limit parameter and ensure it's within the maxLimit bounds.
     *
     * @param array $options An array of options with a limit key to be checked.
     * @return array An array of options for infiniteScroll.
     */
    public function checkLimit(array $options)
    {
        $options['limit'] = (int)$options['limit'];
        if (empty($options['limit']) || $options['limit'] < 1) {
            $options['limit'] = 1;
        }
        $options['limit'] = max(min($options['limit'], $options['maxLimit']), 1);

        return $options;
    }
}
