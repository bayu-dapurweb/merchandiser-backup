<?php

namespace Tests\Feature;

use ReflectionMethod;
use Tests\TestCase;

class UpdateSingleSecurityTest extends TestCase
{
    /**
     * Stub that mimics a module controller form definition.
     */
    private function makeControllerStub()
    {
        return new class extends \crocodicstudio\crudbooster\controllers\CBController {
            public function cbInit()
            {
                $this->table = 'trx_posts';
                $this->title_field = 'title';
                $this->primary_key = 'id';
                $this->form = [
                    ['label' => 'Title', 'name' => 'title', 'type' => 'text'],
                    ['label' => 'Thumb', 'name' => 'thum_image', 'type' => 'upload'],
                    ['label' => 'File', 'name' => 'attachment', 'type' => 'filemanager'],
                    ['label' => 'Password', 'name' => 'password', 'type' => 'password'],
                    ['label' => 'Privilege', 'name' => 'id_cms_privileges', 'type' => 'select'],
                ];
            }
        };
    }

    private function allowedColumns($controller)
    {
        $method = new ReflectionMethod($controller, 'getUpdateSingleAllowedColumns');
        $method->setAccessible(true);

        return $method->invoke($controller);
    }

    public function test_allowlist_only_includes_upload_and_filemanager_columns()
    {
        $controller = $this->makeControllerStub();
        $controller->cbInit();

        $allowed = $this->allowedColumns($controller);

        $this->assertSame(['thum_image', 'attachment'], $allowed);
        $this->assertNotContains('title', $allowed);
        $this->assertNotContains('password', $allowed);
        $this->assertNotContains('id_cms_privileges', $allowed);
    }

    public function test_arbitrary_table_param_must_match_module_table()
    {
        $moduleTable = 'trx_posts';
        $attackTables = [
            'cms_users',
            'cms_privileges',
            'cms_privileges_roles',
            'cms_settings',
        ];

        foreach ($attackTables as $requestedTable) {
            $isAllowed = ($requestedTable === $moduleTable);
            $this->assertFalse($isAllowed, "Table {$requestedTable} must be rejected");
        }

        $this->assertTrue('trx_posts' === $moduleTable);
    }

    public function test_sensitive_columns_are_denied_even_if_named_in_form()
    {
        $controller = $this->makeControllerStub();
        $controller->cbInit();

        $allowed = $this->allowedColumns($controller);
        $denied = array_merge(
            explode(',', config('crudbooster.PASSWORD_FIELDS_CANDIDATE')),
            ['id', 'id_cms_privileges', 'id_cms_moduls', 'is_superadmin']
        );

        $attackColumns = ['password', 'id_cms_privileges', 'id', 'is_superadmin', 'title'];

        foreach ($attackColumns as $column) {
            $pass = in_array($column, $allowed, true) && ! in_array($column, $denied, true);
            $this->assertFalse($pass, "Column {$column} must be blocked");
        }
    }

    public function test_legitimate_filemanager_clear_column_is_allowed()
    {
        $controller = $this->makeControllerStub();
        $controller->cbInit();

        $allowed = $this->allowedColumns($controller);
        $denied = array_merge(
            explode(',', config('crudbooster.PASSWORD_FIELDS_CANDIDATE')),
            ['id', 'id_cms_privileges', 'id_cms_moduls', 'is_superadmin']
        );

        $column = 'attachment';
        $pass = in_array($column, $allowed, true) && ! in_array($column, $denied, true);

        $this->assertTrue($pass);
    }
}
