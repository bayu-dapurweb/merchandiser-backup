<?php namespace App\Http\Controllers;

use Session;
use Request;
use DB;
use CRUDbooster;
use Illuminate\Support\Facades\Validator;

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
		$this->form[] = array("label"=>"Password","name"=>"password","type"=>"password","help"=>"Leave empty to keep current password. For strong password: minimum 12 characters, include uppercase, lowercase, numbers, and special characters (!@#$%^&*)");
		$this->form[] = array("label"=>"Password Confirmation","name"=>"password_confirmation","type"=>"password","help"=>"Leave empty to keep current password. Must match the password field above");
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

		$isForceRedirectEnabled = filter_var(env('FORCE_MUST_RESET_PASSWORD_REDIRECT', false), FILTER_VALIDATE_BOOLEAN);
		$mustResetPassword = !empty($data['row']->is_must_reset_password) && empty($data['row']->has_changed_password);
		if ($isForceRedirectEnabled && $mustResetPassword) {
			// Only set warning if no validation error message exists
			if (!Session::has('message')) {
				Session::put('message_type', 'warning');
				Session::put('message', 'For security reasons, your account is required to reset password before continuing. Please set a new password now.');
			}
		}
		
		$this->cbView('crudbooster::default.form',$data);				
	}
	public function hook_before_edit(&$postdata,$id) { 
		// Validate password strength if password is being changed
		$isForceRedirectEnabled = filter_var(env('FORCE_MUST_RESET_PASSWORD_REDIRECT', false), FILTER_VALIDATE_BOOLEAN);
		$plainPassword = Request::input('password');
		if ($isForceRedirectEnabled && !empty($plainPassword)) {
			$validator = Validator::make(['password' => $plainPassword], [
				'password' => [
					'min:12',
					'regex:/[A-Z]/', // uppercase
					'regex:/[a-z]/', // lowercase
					'regex:/[0-9]/', // number
					'regex:/[!@#$%^&*()_+\-=\[\]{};:\'",.<>?\/\\|`~]/', // special char
				],
			], [
				'password.min' => 'Password must be at least 12 characters long',
				'password.regex' => 'Password must contain uppercase, lowercase, numbers, and special characters (!@#$%^&*)',
			]);

			if ($validator->fails()) {
				// Redirect to profile if editing own account, otherwise edit page
				$redirectPath = ($id == CRUDBooster::myId()) ? 'profile' : 'edit/' . $id;
				CRUDBooster::redirect(CRUDBooster::mainpath($redirectPath), implode(' ', $validator->errors()->all()), 'danger');
			}
		}

		unset($postdata['password_confirmation']);
	}
	public function hook_after_edit($id) {
		$isForceRedirectEnabled = filter_var(env('FORCE_MUST_RESET_PASSWORD_REDIRECT', false), FILTER_VALIDATE_BOOLEAN);
		if (!$isForceRedirectEnabled) {
			return;
		}

		$user = DB::table('cms_users')->find($id);
		$wasFlaggedForReset = !empty($user->is_must_reset_password) && empty($user->has_changed_password);
		$passwordSubmitted = !empty(request('password'));

		// Mark password as changed, but keep is_must_reset_password for audit trail
		if ($wasFlaggedForReset && $passwordSubmitted) {
			DB::table('cms_users')->where('id', $id)->update([
				'has_changed_password' => 1,
			]);
		}
	}
	public function hook_before_add(&$postdata) {
		// Validate password strength for new users
		$isForceRedirectEnabled = filter_var(env('FORCE_MUST_RESET_PASSWORD_REDIRECT', false), FILTER_VALIDATE_BOOLEAN);
		$plainPassword = Request::input('password');
		if ($isForceRedirectEnabled && !empty($plainPassword)) {
			$validator = Validator::make(['password' => $plainPassword], [
				'password' => [
					'min:12',
					'regex:/[A-Z]/', // uppercase
					'regex:/[a-z]/', // lowercase
					'regex:/[0-9]/', // number
					'regex:/[!@#$%^&*()_+\-=\[\]{};:\'",.<>?\/\\|`~]/', // special char
				],
			], [
				'password.min' => 'Password must be at least 12 characters long',
				'password.regex' => 'Password must contain uppercase, lowercase, numbers, and special characters (!@#$%^&*)',
			]);

			if ($validator->fails()) {
				CRUDBooster::redirect(CRUDBooster::mainpath('add'), implode(' ', $validator->errors()->all()), 'danger');
			}
		}
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
