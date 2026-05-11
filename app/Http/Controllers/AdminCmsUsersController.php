<?php namespace App\Http\Controllers;

use Session;
use Request;
use DB;
use CRUDbooster;

class AdminCmsUsersController extends \crocodicstudio\crudbooster\controllers\CBController {


	public function cbInit() {
		# START CONFIGURATION DO NOT REMOVE THIS LINE
		$this->table               = 'cms_users';
		$this->primary_key         = 'id';
		$this->title_field         = "name";
		$this->button_action_style = 'button_icon';	
		$this->button_import 	   = FALSE;	
		$this->button_export 	   = FALSE;	
		$this->button_detail = false;
		$this->button_show = false;
		# END CONFIGURATION DO NOT REMOVE THIS LINE
	
		# START COLUMNS DO NOT REMOVE THIS LINE
		$this->col = array();
		$this->col[] = array("label"=>"Name","name"=>"name");
		$this->col[] = array("label"=>"Email","name"=>"email");
		$this->col[] = array("label"=>"Privilege","name"=>"id_cms_privileges","callback" => function($data){
			return \DB::table('cms_privileges')->find($data->id_cms_privileges)->name;
		});
		// $this->col[] = array("label"=>"Ticket Category","name"=>"tiket_categories_id", "join" => "tiket_categories,title");
		$this->col[] = array("label"=>"Photo","name"=>"photo","image"=>1);		
		$this->col[] = array("label"=>"Branch","name"=>"id","callback"=> function($r){
			$cms_users = \App\CmsUsers::with('branches.branch', 'stores.store')->find($r->id);
			if (count($cms_users->branches) > 0) {
				$html = '';
				foreach($cms_users->branches as $v) {
					$html .= '<div class="btn btn-xs btn-info" style="margin-right:3px">'.$v->branch->name.'</div>';
				}
			}

			return $html;
		});		
		$this->col[] = array("label"=>"Store","name"=>"id","callback"=> function($r){
			$cms_users = \App\CmsUsers::with('branches.branch', 'stores.store')->find($r->id);
			if (count($cms_users->stores) > 0) {
				$html = '';
				foreach($cms_users->stores as $v) {
					$html .= '<div class="btn btn-xs btn-info" style="margin-right:3px">'.$v->store->name.'</div>';
				}
			}

			return $html;
		});
		# END COLUMNS DO NOT REMOVE THIS LINE

		# START FORM DO NOT REMOVE THIS LINE
		$this->form = array(); 		
		$this->form[] = array("label"=>"Name","name"=>"name",'required'=>true,'validation'=>'required|alpha_spaces|min:3');
		$this->form[] = array("label"=>"Email","name"=>"email",'required'=>true,'type'=>'email','validation'=>'required|email|unique:cms_users,email,'.CRUDBooster::getCurrentId());		
		$this->form[] = array("label"=>"Photo","name"=>"photo","type"=>"upload","help"=>"Recommended resolution is 200x200px",'validation'=>'','resize_width'=>90,'resize_height'=>90);
		$this->form[] = array("label"=>"Privilege","name"=>"id_cms_privileges","type"=>"select","datatable"=>"cms_privileges,name",'required'=>true);
		$this->form[] = [
			"label" 				=> "Branch",
			"name" 					=> "map_cms_users_ref_branches",
			"type" 					=> "select2",
			"select2_multiple" 		=> true,
			"datatable" 			=> "ref_branches,name",
			"relationship_table" 	=> "map_cms_users_ref_branches",
		];
		$this->form[] = [
			"label" 				=> "Store",
			"name" 					=> "map_cms_users_ref_warehouses",
			"type" 					=> "select2",
			"select2_multiple" 		=> true,
			"datatable" 			=> "ref_warehouses,name",
			"relationship_table" 	=> "map_cms_users_ref_warehouses",
		];
		$this->form[] = array("label"=>"Password","name"=>"password","type"=>"password","help"=>"Please leave empty if not change");
		$this->form[] = array("label"=>"Password Confirmation","name"=>"password_confirmation","type"=>"password","help"=>"Please leave empty if not change");
		# END FORM DO NOT REMOVE THIS LINE
				
	}

	public function getProfile() {			

		$this->button_addmore = FALSE;
		$this->button_cancel  = FALSE;
		$this->button_show    = FALSE;			
		$this->button_add     = FALSE;
		$this->button_delete  = FALSE;	
		$this->hide_form 	  = ['id_cms_privileges'];

		$data['page_title'] = trans("crudbooster.label_button_profile");
		$data['row']        = CRUDBooster::first('cms_users',CRUDBooster::myId());		
		$this->cbView('crudbooster::default.form',$data);				
	}
	public function hook_before_edit(&$postdata,$id) { 
		unset($postdata['password_confirmation']);
	}
	public function hook_before_add(&$postdata) {      
	    unset($postdata['password_confirmation']);
	}

	public function hook_after_add($id) {
		// Save branches
		$branches = request('map_cms_users_ref_branches');
		if ($branches && is_array($branches)) {
			foreach ($branches as $branch_id) {
				DB::table('map_cms_users_ref_branches')->insert([
					'cms_users_id' => $id,
					'ref_branches_id' => $branch_id
				]);
			}
		}
		// Save stores
		$stores = request('map_cms_users_ref_warehouses');
		if ($stores && is_array($stores)) {
			foreach ($stores as $store_id) {
				DB::table('map_cms_users_ref_warehouses')->insert([
					'cms_users_id' => $id,
					'ref_warehouses_id' => $store_id
				]);
			}
		}
	}
}
