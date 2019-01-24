<?php

namespace Crud\Shell\Task;

/**
 * Model shell task.
 */
class ModelTask extends \Bake\Shell\Task\ModelTask
{

    public function getTableContext($tableObject, $table, $name)
    {
        $data = parent::getTableContext($tableObject, $table, $name);

        $tableUseStatements = [];
        $tableTraits = [];
        if ($tableObject->hasField('deleted_at')) {
            $tableUseStatements[] = 'Crud\\Model\\Table\\SoftDeleteTrait';
            $tableTraits[] = 'SoftDeleteTrait';
        }

        return $data + compact('tableUseStatements', 'tableTraits');
    }
}
