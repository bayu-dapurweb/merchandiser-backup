<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use ReflectionMethod;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Tests\TestCase;

class DatatableWhereSecurityTest extends TestCase
{
    private function controller()
    {
        return new class extends \crocodicstudio\crudbooster\controllers\CBController {
            public function cbInit()
            {
                $this->table = 'cms_privileges';
            }
        };
    }

    private function invokePrivate($object, $method, array $args = [])
    {
        $reflection = new ReflectionMethod($object, $method);
        $reflection->setAccessible(true);

        return $reflection->invokeArgs($object, $args);
    }

    public function test_safe_sql_identifier_rejects_injection_characters()
    {
        $controller = $this->controller();

        $this->assertTrue($this->invokePrivate($controller, 'isSafeSqlIdentifier', ['cms_privileges']));
        $this->assertTrue($this->invokePrivate($controller, 'isSafeSqlIdentifier', ['name']));
        $this->assertFalse($this->invokePrivate($controller, 'isSafeSqlIdentifier', ['cms_users; drop']));
        $this->assertFalse($this->invokePrivate($controller, 'isSafeSqlIdentifier', ['id) union']));
    }

    public function test_parse_datatable_where_literal_accepts_safe_values()
    {
        $controller = $this->controller();

        $this->assertSame(0, $this->invokePrivate($controller, 'parseDatatableWhereLiteral', ['0']));
        $this->assertSame(3, $this->invokePrivate($controller, 'parseDatatableWhereLiteral', ['3']));
        $this->assertSame('active', $this->invokePrivate($controller, 'parseDatatableWhereLiteral', ["'active'"]));
        $this->assertSame('%foo%', $this->invokePrivate($controller, 'parseDatatableWhereLiteral', ["'%foo%'"]));
        $this->assertNull($this->invokePrivate($controller, 'parseDatatableWhereLiteral', ['admin']));
        $this->assertNull($this->invokePrivate($controller, 'parseDatatableWhereLiteral', ['1; drop']));
    }

    public function test_apply_safe_datatable_where_blocks_union_injection()
    {
        if (! Schema::hasTable('cms_privileges')) {
            $this->markTestSkipped('cms_privileges table not available');
        }

        $controller = $this->controller();
        $query = DB::table('cms_privileges');

        try {
            $this->invokePrivate($controller, 'applySafeDatatableWhere', [
                $query,
                '1=1) UNION SELECT id, email FROM cms_users -- ',
                'cms_privileges',
            ]);
            $this->fail('Expected HttpException for UNION injection payload');
        } catch (HttpException $e) {
            $this->assertSame(400, $e->getStatusCode());
        }
    }

    public function test_apply_safe_datatable_where_blocks_or_tautology()
    {
        if (! Schema::hasTable('cms_privileges')) {
            $this->markTestSkipped('cms_privileges table not available');
        }

        $controller = $this->controller();
        $query = DB::table('cms_privileges');

        try {
            $this->invokePrivate($controller, 'applySafeDatatableWhere', [
                $query,
                '1=0 OR 1=1',
                'cms_privileges',
            ]);
            $this->fail('Expected HttpException for OR tautology payload');
        } catch (HttpException $e) {
            $this->assertSame(400, $e->getStatusCode());
        }
    }

    public function test_apply_safe_datatable_where_allows_legitimate_filter()
    {
        if (! Schema::hasTable('cms_privileges') || ! Schema::hasColumn('cms_privileges', 'id')) {
            $this->markTestSkipped('cms_privileges.id column not available');
        }

        $controller = $this->controller();
        $query = DB::table('cms_privileges');

        $this->invokePrivate($controller, 'applySafeDatatableWhere', [
            $query,
            'id > 0',
            'cms_privileges',
        ]);

        $sql = $query->toSql();
        $this->assertContains('where', strtolower($sql));
        $this->assertNotContains('union', strtolower($sql));
        $this->assertSame(['id', '>', 0], array_slice($query->getBindings(), 0, 3));
    }

    public function test_get_data_table_endpoint_blocks_union_injection_when_hardened()
    {
        if (! Schema::hasTable('cms_privileges')) {
            $this->markTestSkipped('cms_privileges table not available');
        }

        Config::set('crudbooster.DATATABLE_WHERE_HARDENING_ENABLED', true);

        $response = $this->withoutMiddleware()->get('/admin/users/data-table?'.http_build_query([
            'table' => 'cms_privileges',
            'label' => 'name',
            'fk_name' => 'id',
            'fk_value' => '1',
            'datatable_where' => '1=1) UNION SELECT id, email FROM cms_users -- ',
        ]));

        $this->assertSame(400, $response->getStatusCode());
    }

    public function test_get_data_table_endpoint_allows_legitimate_filter_when_hardened()
    {
        if (! Schema::hasTable('cms_privileges') || ! Schema::hasColumn('cms_privileges', 'id')) {
            $this->markTestSkipped('cms_privileges table not available');
        }

        Config::set('crudbooster.DATATABLE_WHERE_HARDENING_ENABLED', true);

        $response = $this->withoutMiddleware()->get('/admin/users/data-table?'.http_build_query([
            'table' => 'cms_privileges',
            'label' => 'name',
            'fk_name' => 'id',
            'fk_value' => '1',
            'datatable_where' => 'id > 0',
        ]));

        $this->assertSame(200, $response->getStatusCode());
        $this->assertTrue(is_array($response->json()));
    }
}
