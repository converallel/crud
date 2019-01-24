<?php

namespace Crud\Model\Table;

use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * HttpStatusCodes Model
 *
 * @property \Crud\Model\Table\LogsTable|\Cake\ORM\Association\HasMany $Logs
 *
 * @method \Crud\Model\Entity\HttpStatusCode get($primaryKey, $options = [])
 * @method \Crud\Model\Entity\HttpStatusCode newEntity($data = null, array $options = [])
 * @method \Crud\Model\Entity\HttpStatusCode[] newEntities(array $data, array $options = [])
 * @method \Crud\Model\Entity\HttpStatusCode|bool save(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \Crud\Model\Entity\HttpStatusCode|bool saveOrFail(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \Crud\Model\Entity\HttpStatusCode patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method \Crud\Model\Entity\HttpStatusCode[] patchEntities($entities, array $data, array $options = [])
 * @method \Crud\Model\Entity\HttpStatusCode findOrCreate($search, callable $callback = null, $options = [])
 */
class HttpStatusCodesTable extends Table
{

    /**
     * Initialize method
     *
     * @param array $config The configuration for the Table.
     * @return void
     */
    public function initialize(array $config)
    {
        parent::initialize($config);

        $this->setTable('http_status_codes');
        $this->setDisplayField('definition');
        $this->setPrimaryKey('code');

        $this->hasMany('Logs', [
            'foreignKey' => 'status_code'
        ]);
    }

    /**
     * Default validation rules.
     *
     * @param \Cake\Validation\Validator $validator Validator instance.
     * @return \Cake\Validation\Validator
     */
    public function validationDefault(Validator $validator)
    {
        $validator
            ->allowEmptyString('code', 'create');

        $validator
            ->scalar('definition')
            ->maxLength('definition', 40)
            ->requirePresence('definition', 'create')
            ->allowEmptyString('definition', false);

        return $validator;
    }
}
