<?php

namespace Crud\Model\Table;

use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * Logs Model
 *
 * @property \App\Model\Table\UsersTable|\Cake\ORM\Association\BelongsTo $Users
 * @property \Crud\Model\Table\HttpStatusCodesTable|\Cake\ORM\Association\BelongsTo $HttpStatusCodes
 *
 * @method \Crud\Model\Entity\Log get($primaryKey, $options = [])
 * @method \Crud\Model\Entity\Log newEntity($data = null, array $options = [])
 * @method \Crud\Model\Entity\Log[] newEntities(array $data, array $options = [])
 * @method \Crud\Model\Entity\Log|bool save(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \Crud\Model\Entity\Log|bool saveOrFail(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \Crud\Model\Entity\Log patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method \Crud\Model\Entity\Log[] patchEntities($entities, array $data, array $options = [])
 * @method \Crud\Model\Entity\Log findOrCreate($search, callable $callback = null, $options = [])
 */
class LogsTable extends Table
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

        $this->setTable('logs');
        $this->setDisplayField('id');
        $this->setPrimaryKey('id');

        $this->belongsTo('Users', [
            'foreignKey' => 'user_id'
        ]);

        $this->belongsTo('HttpStatusCodes', [
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
            ->nonNegativeInteger('id')
            ->allowEmptyString('id', 'create');

        $validator
            ->scalar('ip_address')
            ->maxLength('ip_address', 40)
            ->requirePresence('ip_address', 'create')
            ->allowEmptyString('ip_address', false);

        $validator
            ->scalar('request_method')
            ->requirePresence('request_method', 'create')
            ->allowEmptyString('request_method', false);

        $validator
            ->scalar('request_url')
            ->maxLength('request_url', 60)
            ->requirePresence('request_url', 'create')
            ->allowEmptyString('request_url', false);

        $validator
            ->requirePresence('request_headers', 'create')
            ->allowEmptyString('request_headers', false);

        $validator
            ->allowEmptyString('request_body');

        $validator
            ->requirePresence('status_code', 'create')
            ->allowEmptyString('status_code', false);

        $validator
            ->dateTime('created_at')
            ->requirePresence('created_at', 'create')
            ->allowEmptyDateTime('created_at', false);

        return $validator;
    }

    /**
     * Returns a rules checker object that will be used for validating
     * application integrity.
     *
     * @param \Cake\ORM\RulesChecker $rules The rules object to be modified.
     * @return \Cake\ORM\RulesChecker
     */
    public function buildRules(RulesChecker $rules)
    {
        $rules->add($rules->existsIn(['user_id'], 'Users'));

        return $rules;
    }
}
