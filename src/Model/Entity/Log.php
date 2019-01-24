<?php

namespace Crud\Model\Entity;

use Cake\ORM\Entity;

/**
 * Log Entity
 *
 * @property int $id
 * @property int|null $user_id
 * @property string $ip_address
 * @property string $request_method
 * @property string $request_url
 * @property array $request_headers
 * @property array|null $request_body
 * @property int $status_code
 * @property \Cake\I18n\FrozenTime $created_at
 *
 * @property \App\Model\Entity\User $user
 * @property \Crud\Model\Entity\HttpStatusCode $http_status_code
 */
class Log extends Entity
{

    /**
     * Fields that can be mass assigned using newEntity() or patchEntity().
     *
     * Note that when '*' is set to true, this allows all unspecified fields to
     * be mass assigned. For security purposes, it is advised to set '*' to false
     * (or remove it), and explicitly make individual fields accessible as needed.
     *
     * @var array
     */
    protected $_accessible = [
        'user_id' => true,
        'ip_address' => true,
        'request_method' => true,
        'request_url' => true,
        'request_headers' => true,
        'request_body' => true,
        'status_code' => true
    ];
}
