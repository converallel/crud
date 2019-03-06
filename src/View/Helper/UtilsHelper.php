<?php

namespace Crud\View\Helper;

use Cake\View\Helper;

/**
 * Utils helper
 */
class UtilsHelper extends Helper
{

    /**
     * Default configuration.
     *
     * @var array
     */
    protected $_defaultConfig = [];

    public $helpers = ['Html', 'Number'];

    /**
     * @param mixed $value
     * @return string Parsed string.
     */
    public function display($value)
    {
        if (is_numeric($value)) {
            $value = $this->Number->format($value);
        } elseif (is_bool($value)) {
            $value = $value ? __('Yes') : __('No');
        } elseif ($value instanceof \Cake\Datasource\EntityInterface) {
            $value = $this->Html->link($value->id,
                ['controller' => $value->getSource(), 'action' => 'view', $value->id]);
        } else {
            $value = h($value);
        }
        return "<td>$value</td>";
    }
}
