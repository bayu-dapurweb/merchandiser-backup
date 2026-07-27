<?php

namespace App\Http\Controllers;

use App\Support\ModulePrivilege;
use crocodicstudio\crudbooster\controllers\PrivilegesController as CBPrivilegesController;
use CRUDBooster;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Session;

class AdminPrivilegesController extends CBPrivilegesController
{
    public function getAdd()
    {
        $this->cbLoader();

        if (!CRUDBooster::isCreate() && $this->global_privilege == false) {
            CRUDBooster::insertLog(trans('crudbooster.log_try_add', ['module' => CRUDBooster::getCurrentModule()->name]));
            CRUDBooster::redirect(CRUDBooster::adminPath(), trans('crudbooster.denied_access'));
        }

        $row = null;
        $page_title = 'Add Data';
        $moduls = DB::table('cms_moduls')
            ->where('is_protected', 0)
            ->whereNull('deleted_at')
            ->orderby('name', 'asc')
            ->get();
        $page_menu = Route::getCurrentRoute()->getActionName();
        $approve_module_paths = ModulePrivilege::approveModulePaths();

        return view('admin.privileges', compact('row', 'page_title', 'moduls', 'page_menu', 'approve_module_paths'));
    }

    public function getEdit($id)
    {
        $this->cbLoader();

        $row = DB::table($this->table)->where('id', $id)->first();

        if (!CRUDBooster::isRead() && $this->global_privilege == false) {
            CRUDBooster::insertLog(trans('crudbooster.log_try_edit', [
                'name' => $row->{$this->title_field},
                'module' => CRUDBooster::getCurrentModule()->name,
            ]));
            CRUDBooster::redirect(CRUDBooster::adminPath(), trans('crudbooster.denied_access'));
        }

        $page_title = trans('crudbooster.edit_data_page_title', ['module' => 'Privilege', 'name' => $row->name]);
        $moduls = DB::table('cms_moduls')
            ->where('is_protected', 0)
            ->whereNull('deleted_at')
            ->orderby('name', 'asc')
            ->get();
        $page_menu = Route::getCurrentRoute()->getActionName();
        $approve_module_paths = ModulePrivilege::approveModulePaths();

        return view('admin.privileges', compact('row', 'page_title', 'moduls', 'page_menu', 'approve_module_paths'));
    }

    public function postAddSave()
    {
        $this->cbLoader();

        if (!CRUDBooster::isCreate() && $this->global_privilege == false) {
            CRUDBooster::insertLog(trans('crudbooster.log_try_add_save', [
                'name' => Request::input($this->title_field),
                'module' => CRUDBooster::getCurrentModule()->name,
            ]));
            CRUDBooster::redirect(CRUDBooster::adminPath(), trans('crudbooster.denied_access'));
        }

        $this->validation($request);
        $this->input_assignment($request);

        $this->arr[$this->primary_key] = DB::table($this->table)->max($this->primary_key) + 1;

        DB::table($this->table)->insert($this->arr);
        $id = $this->arr[$this->primary_key];

        Session::put('theme_color', $this->arr['theme_color']);

        $this->saveModulePrivileges($id);
        $this->refreshSessionRoles();

        CRUDBooster::redirect(CRUDBooster::mainpath(), trans('crudbooster.alert_add_data_success'), 'success');
    }

    public function postEditSave($id)
    {
        $this->cbLoader();

        $row = CRUDBooster::first($this->table, $id);

        if (!CRUDBooster::isUpdate() && $this->global_privilege == false) {
            CRUDBooster::insertLog(trans('crudbooster.log_try_add', [
                'name' => $row->{$this->title_field},
                'module' => CRUDBooster::getCurrentModule()->name,
            ]));
            CRUDBooster::redirect(CRUDBooster::adminPath(), trans('crudbooster.denied_access'));
        }

        $this->validation($id);
        $this->input_assignment($id);

        DB::table($this->table)->where($this->primary_key, $id)->update($this->arr);

        $this->saveModulePrivileges($id);

        if ($id == CRUDBooster::myPrivilegeId()) {
            $this->refreshSessionRoles();
            Session::put('theme_color', $this->arr['theme_color']);
        }

        CRUDBooster::redirect(CRUDBooster::mainpath(), trans('crudbooster.alert_update_data_success', [
            'module' => 'Privilege',
            'title' => $row->name,
        ]), 'success');
    }

    public function getDelete($id)
    {
        $this->cbLoader();

        $row = DB::table($this->table)->where($this->primary_key, $id)->first();

        if (!CRUDBooster::isDelete() && $this->global_privilege == false) {
            CRUDBooster::insertLog(trans('crudbooster.log_try_delete', [
                'name' => $row->{$this->title_field},
                'module' => CRUDBooster::getCurrentModule()->name,
            ]));
            CRUDBooster::redirect(CRUDBooster::adminPath(), trans('crudbooster.denied_access'));
        }

        DB::table($this->table)->where($this->primary_key, $id)->delete();
        DB::table('cms_privileges_roles')->where('id_cms_privileges', $row->id)->delete();

        CRUDBooster::redirect(CRUDBooster::mainpath(), trans('crudbooster.alert_delete_data_success'), 'success');
    }

    private function saveModulePrivileges($privilegeId): void
    {
        $priv = Request::input('privileges');

        DB::table('cms_privileges_roles')->where('id_cms_privileges', $privilegeId)->delete();

        if (!$priv) {
            return;
        }

        foreach ($priv as $id_modul => $data) {
            $arrs = [];
            $arrs['id'] = DB::table('cms_privileges_roles')->max('id') + 1;
            $arrs['is_visible'] = @$data['is_visible'] ?: 0;
            $arrs['is_create'] = @$data['is_create'] ?: 0;
            $arrs['is_read'] = @$data['is_read'] ?: 0;
            $arrs['is_edit'] = @$data['is_edit'] ?: 0;
            $arrs['is_delete'] = @$data['is_delete'] ?: 0;
            $arrs['is_approve'] = @$data['is_approve'] ?: 0;
            $arrs['id_cms_privileges'] = $privilegeId;
            $arrs['id_cms_moduls'] = $id_modul;
            DB::table('cms_privileges_roles')->insert($arrs);
        }
    }

    private function refreshSessionRoles(): void
    {
        $roles = DB::table('cms_privileges_roles')
            ->where('id_cms_privileges', CRUDBooster::myPrivilegeId())
            ->join('cms_moduls', 'cms_moduls.id', '=', 'id_cms_moduls')
            ->select(ModulePrivilege::sessionRoleColumns())
            ->get();
        Session::put('admin_privileges_roles', $roles);
    }
}
