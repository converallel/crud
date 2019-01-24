<?php

use Migrations\AbstractMigration;
use Phinx\Db\Adapter\MysqlAdapter;

class Initial extends AbstractMigration
{

    public $autoId = false;

    public function up()
    {

        $this->table('http_status_codes')
            ->addColumn('code', 'integer', [
                'default' => null,
                'limit' => MysqlAdapter::INT_SMALL,
                'null' => false,
                'signed' => false,
            ])
            ->addPrimaryKey(['code'])
            ->addColumn('definition', 'string', [
                'default' => null,
                'limit' => 40,
                'null' => false,
            ])
            ->create();

        $this->table('logs')
            ->addColumn('id', 'integer', [
                'autoIncrement' => true,
                'default' => null,
                'limit' => 11,
                'null' => false,
                'signed' => false,
            ])
            ->addPrimaryKey(['id'])
            ->addColumn('user_id', 'integer', [
                'default' => null,
                'limit' => 11,
                'null' => true,
                'signed' => false,
            ])
            ->addColumn('ip_address', 'string', [
                'default' => null,
                'limit' => 40,
                'null' => false,
            ])
            ->addColumn('request_method', 'enum', [
                'default' => null,
                'limit' => 10,
                'null' => false,
                'values' => ['GET', 'HEAD', 'POST', 'PUT', 'DELETE', 'CONNECT', 'OPTIONS', 'TRACE', 'PATCH']
            ])
            ->addColumn('request_url', 'string', [
                'default' => null,
                'limit' => 60,
                'null' => false,
            ])
            ->addColumn('request_headers', 'json', [
                'default' => null,
                'limit' => null,
                'null' => false,
            ])
            ->addColumn('request_body', 'json', [
                'default' => null,
                'limit' => null,
                'null' => true,
            ])
            ->addColumn('status_code', 'integer', [
                'default' => null,
                'limit' => MysqlAdapter::INT_SMALL,
                'null' => false,
                'signed' => false,
            ])
            ->addColumn('created_at', 'timestamp', [
                'default' => 'CURRENT_TIMESTAMP',
                'limit' => null,
                'null' => false,
            ])
            ->addIndex(
                [
                    'user_id',
                ]
            )
            ->addIndex(
                [
                    'status_code',
                ]
            )
            ->create();

        $this->table('logs')
            ->addForeignKey(
                'status_code',
                'http_status_codes',
                'code',
                [
                    'update' => 'CASCADE',
                    'delete' => 'NO_ACTION'
                ]
            )
            ->addForeignKey(
                'user_id',
                'users',
                'id',
                [
                    'update' => 'CASCADE',
                    'delete' => 'CASCADE'
                ]
            )
            ->update();
    }

    public function down()
    {

        $this->table('logs')
            ->dropForeignKey(
                'status_code'
            )
            ->dropForeignKey(
                'user_id'
            )->save();

        $this->table('http_status_codes')->drop()->save();
        $this->table('logs')->drop()->save();
    }
}
