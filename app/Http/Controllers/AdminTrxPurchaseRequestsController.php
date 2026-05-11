<?php

namespace App\Http\Controllers;

use Session;
use Request;
use DB;
use CRUDBooster;
use Dompdf\Dompdf;

class AdminTrxPurchaseRequestsController extends \crocodicstudio\crudbooster\controllers\CBController
{

	public function cbInit()
	{

		# START CONFIGURATION DO NOT REMOVE THIS LINE
		$this->title_field = "id";
		$this->limit = "20";
		$this->orderby = "id,desc";
		$this->global_privilege = false;
		$this->button_table_action = true;
		$this->button_bulk_action = true;
		$this->button_action_style = "button_icon";
		$this->button_add = true;
		$this->button_edit = true;
		$this->button_delete = true;
		$this->button_detail = false;
		$this->button_show = false;
		$this->button_filter = true;
		$this->button_import = false;
		$this->button_export = false;
		$me = \CB::me();

		// Create, View, Edit, Delete privileges: [1, 6, 7, 8]
		$crudPrivileges = [1, 6, 7, 8];

		if (!in_array($me->id_cms_privileges, $crudPrivileges)) {
			$this->button_delete = false;
			$this->button_add = false;
			$this->button_edit = false;
		}

		$this->table = "trx_purchase_requests";
		# END CONFIGURATION DO NOT REMOVE THIS LINE

		# START COLUMNS DO NOT REMOVE THIS LINE
		$this->col = [];
		$this->col[] = ["label" => "Doc. No.", "name" => "U_SOL_SYNC_KEY"];
		$this->col[] = ["label" => "Doc. Date", "name" => "DocDate", "callback" => function ($r) {
			return dateformatsimple($r->DocDate);
		}];
		$this->col[] = ["label" => "Requester Name", "name" => "ReqName"];
		$this->col[] = ["label" => "Branch", "name" => "Branch", "join" => "ref_branches,name"];
		$this->col[] = ["label" => "To Store", "name" => "U_VIT_ToStr", "callback" => function ($r) {
			$stores  = \App\RefWarehouses::where('code', $r->U_VIT_ToStr)->first();
			return $stores->name . ' [' . ($stores->code) . ']';
		}];
        $this->col[] = [
            "label" => "Doc. Status",
            "name" => "doc_status",
            "callback" => function ($r) {
                $statuses = [
                    'draft' => 'info',
                    'submitted' => 'primary',
                    'approved' => 'success',
                    'rejected' => 'danger'
                ];

                $value = strtolower(trim($r->doc_status));
                $typoFix = ['submited' => 'submitted'];
                $value = $typoFix[$value] ?? $value;

                $color = $statuses[$value] ?? 'secondary';
                $label = ucfirst($value);

                return "<div class='btn btn-xs btn-{$color}'>{$label}</div>";
            }
        ];

        $this->col[] = ["label" => "Sync Status", "name" => "sync_status", "callback" => function ($r) {
			$color = ['Failed' => 'danger', 'Synced' => 'success'];
			return '<div class="btn btn-xs btn-' . $color[$r->sync_status] . '">' . ucfirst($r->sync_status) . '</div>';
		}];
		$this->col[] = ["label" => "Sync At", "name" => "api_try"];
		$this->col[] = ["label" => "Try Sync", "name" => "sync_at", "callback" => function ($r) {
			if ($r->sync_at) {
				return dateformat($r->sync_at);
			}
		}];


		$this->col[] = ["label" => "Verified By", "name" => "verified_by_cms_users_id", "callback" => function ($r) {
			if ($r->verified_by_cms_users_id) {
				$user = \App\CmsUsers::find($r->verified_by_cms_users_id);
				if ($user) {
					$privilege = DB::table('cms_privileges')->where('id', $user->id_cms_privileges)->first();
					$roleName = $privilege ? $privilege->name : 'Unknown Role';
					return $user->name . ' (' . $roleName . ')';
				}
				return 'Unknown User';
			}
			return '-';
		}];
		$this->col[] = ["label" => "Verified At", "name" => "verified_at", "callback" => function ($r) {
			if ($r->verified_at) {
				return dateformat($r->verified_at);
			}
		}];

		$this->col[] = ["label" => "Created By", "name" => "created_by", "callback" => function ($r) {
			$user = \App\CmsUsers::find($r->created_by);
			if ($user) {
				$privilege = DB::table('cms_privileges')->where('id', $user->id_cms_privileges)->first();
				$roleName = $privilege ? $privilege->name : 'Unknown Role';
				return $user->name . ' (' . $roleName . ')';
			}
			return 'Unknown User';
		}];
		$this->col[] = ["label" => "Approved By", "name" => "approved_by", "callback" => function ($r) {
			if ($r->approved_by) {
				$user = \App\CmsUsers::find($r->approved_by);
				if ($user) {
					$privilege = DB::table('cms_privileges')->where('id', $user->id_cms_privileges)->first();
					$roleName = $privilege ? $privilege->name : 'Unknown Role';
					return $user->name . ' (' . $roleName . ')';
				}
				return 'Unknown User';
			}
			return '-';
		}];

		$this->col[] = ["label" => "Closure Status", "name" => "is_closed", "callback" => function ($r) {
			// Explicitly fetch the record to ensure we have all fields
			$pr = \App\TrxPurchaseRequests::find($r->id);
			if ($pr && $pr->is_closed) {
				return '<div class="btn btn-xs btn-danger">Closed at ' . ($pr->closed_at ? dateformat($pr->closed_at) : 'N/A') . '</div>';
			}
			return '<div class="btn btn-xs btn-success">Open</div>';
		}];
		# END COLUMNS DO NOT REMOVE THIS LINE

		# START FORM DO NOT REMOVE THIS LINE

		$me = \CB::me();
		$crudPrivileges = [1, 6, 7, 8];
        $approvePrivileges = [1, 7];
		$myprivilage = \CB::me()->id_cms_privileges;

		// Check if user has CRUD privileges
		if (!in_array($myprivilage, $crudPrivileges)) {
			$is_viewonly = true;
		} else {
			// For edit mode, check edit privileges and ownership/status
			$is_viewonly = false;
			if (!empty(Request::segment(4))) {
				$pr = \App\TrxPurchaseRequests::find(Request::segment(4));
				// dd($pr, $me);
				if ($pr) {
					// Make approved status view-only for all users
					if ($pr->doc_status === 'approved') {
						$is_viewonly = true;
					}
					// Approvers can edit submitted records
					else if ($pr->doc_status === 'submited' && in_array($myprivilage, $approvePrivileges)) {
						$is_viewonly = false;
					}
					// Users can only edit their own draft or rejected records
					else if (in_array($pr->doc_status, ['draft', 'rejected']) && $pr->created_by === $me->id) {
						$is_viewonly = false;
					}
					// All other cases are view-only
					else {
						$is_viewonly = true;
					}
				}
			}
		}

		$cms_users = \App\CmsUsers::with('branches.branch', 'stores.store')->find($me->id);
		$branch_id_available = [];
		if (count($cms_users->branches) > 0) {
			foreach ($cms_users->branches as $v) {
				$branch_id_available[] = $v->ref_branches_id;
			}
		}

		$store_code_available = [];
		if (count($cms_users->stores) > 0) {
			foreach ($cms_users->stores as $v) {
				$store_code_available[] = $v->store->code;
			}
		}



		$this->form = [];
		$this->form[] = ['label' => 'Doc. No.', 'name' => 'U_SOL_SYNC_KEY', 'type' => 'text', 'validation' => 'required|min:1|max:255', 'width' => 'col-sm-10', 'readonly' => true, "callback" => function ($r) {
			if (empty($r->U_SOL_SYNC_KEY)) {
				return 'PR' . date("Ymd") . six_random();
			} else {
				return $r->U_SOL_SYNC_KEY;
			}
		}];

		$branchs_options = '';
		if (!empty($branch_id_available)) {
			$branchs = \App\RefBranches::whereIn('id', $branch_id_available)->get();
		} else {
			$branchs = \App\RefBranches::get();
		}

		foreach ($branchs as $v) {
			$branchs_options .= $v->id . '|' . $v->name . ' [' . $v->code . '];';
		}

		$store_option = '';
		$stores = \App\RefWarehouses::get();
		foreach ($stores as $v) {
			$store_option .= $v->code . '|' . $v->name . '[' . ($v->is_store ? 'Store' : 'Non-Store') . '];';
		}

		// Determine if fields should be readonly (all except items and status)
		$fields_readonly = false;
		if (!empty(Request::segment(4))) {
			$pr = \App\TrxPurchaseRequests::find(Request::segment(4));
			if ($pr && $pr->doc_status === 'submited' && in_array($myprivilage, $approvePrivileges)) {
				$fields_readonly = true;
			}
		}

		$this->form[] = ['label' => 'Doc. Date', 'name' => 'DocDate', 'type' => 'date', 'validation' => 'required|date', 'width' => 'col-sm-10', 'readonly' => $fields_readonly ?: $is_viewonly];
		$this->form[] = ['label' => 'Branch', 'name' => 'Branch', 'type' => $fields_readonly ? 'select' : 'select2', 'validation' => 'required|min:1|max:255', 'width' => 'col-sm-10', 'dataenum' => $branchs_options, 'readonly' => $fields_readonly, 'disabled' => $fields_readonly ? false : $is_viewonly];
		// $this->form[] = ['label'=>'Requester Name','name'=>'ReqName','type'=>'text','validation'=>'required|min:1|max:255','width'=>'col-sm-10'];
		$this->form[] = ['label' => 'To Store', 'name' => 'U_VIT_ToStr', 'type' => $fields_readonly ? 'select' : 'select2', 'validation' => 'required|min:1|max:255', 'width' => 'col-sm-10',  'dataenum' => $store_option, 'readonly' => $fields_readonly, 'disabled' => $fields_readonly ? false : $is_viewonly];

		$this->form[] = ['label' => 'Comments', 'name' => 'Comments', 'type' => 'textarea', 'validation' => 'required', 'width' => 'col-sm-10', 'readonly' => $fields_readonly ?: $is_viewonly];
		$itemmaster = \App\RefItemMasterData::selectRaw("id,name,sku,unit_of_measurement")->whereRaw("(tags like '%NoN-Agrinesia%')")
			->orderBy("name")->get()->map(function ($r) {
				$r->name = slug($r->name);
				$r->name = str_replace("-", " ", $r->name);
				$r->name = strtoupper($r->name);
				return $r;
			});
		$data['itemmaster'] = $itemmaster;
		$data['disabled'] = $is_viewonly;
		$this->form[] = ['label' => 'Items', 'name' => 'items', 'type' => 'custom', 'validation' => 'required', 'width' => 'col-sm-10', 'html' => view('merchandiser/purchase_request/items_form', $data)->render()];

		$me = \CB::me();
		// Only users with approve privileges can approve/reject
		if (in_array($me->id_cms_privileges, $approvePrivileges)) {
			// For new records (add mode) or draft status, show creator options
			if (empty(Request::segment(4))) {
				// Add mode - show creator options
				$status_enum = 'draft|Save as Draft;submited|Submit & Request Approval';
			} else {
				// Edit mode - check document status
				$pr = \App\TrxPurchaseRequests::find(Request::segment(4));
				if ($pr && in_array($pr->doc_status, ['submited', 'approved'])) {
					// Show approval options only for submitted documents
					$status_enum = 'rejected|Rejected;approved|Approved';
				} else {
					// For draft or other statuses, show creator options
					$status_enum = 'draft|Save as Draft;submited|Submit & Request Approval';
				}
			}
		} else {
			$status_enum = 'draft|Save as Draft;submited|Submit & Request Approval';
		}
		$this->form[] = ['label' => 'Status', 'name' => 'doc_status', 'type' => 'select', 'validation' => 'required', 'width' => 'col-sm-10', 'dataenum' => $status_enum, 'disabled' => $is_viewonly];

		# END FORM DO NOT REMOVE THIS LINE

		# OLD START FORM
		//$this->form = [];
		//$this->form[] = ["label"=>"U SOL SYNC KEY","name"=>"U_SOL_SYNC_KEY","type"=>"text","required"=>TRUE,"validation"=>"required|min:1|max:255"];
		//$this->form[] = ["label"=>"ReqName","name"=>"ReqName","type"=>"text","required"=>TRUE,"validation"=>"required|min:1|max:255"];
		//$this->form[] = ["label"=>"DocDate","name"=>"DocDate","type"=>"date","required"=>TRUE,"validation"=>"required|date"];
		//$this->form[] = ["label"=>"Branch","name"=>"Branch","type"=>"text","required"=>TRUE,"validation"=>"required|min:1|max:255"];
		//$this->form[] = ["label"=>"U VIT ToStr","name"=>"U_VIT_ToStr","type"=>"text","required"=>TRUE,"validation"=>"required|min:1|max:255"];
		//$this->form[] = ["label"=>"Comments","name"=>"Comments","type"=>"textarea","required"=>TRUE,"validation"=>"required|string|min:5|max:5000"];
		//$this->form[] = ["label"=>"U SOL RAV TRID","name"=>"U_SOL_RAV_TRID","type"=>"text","required"=>TRUE,"validation"=>"required|min:1|max:255"];
		//$this->form[] = ["label"=>"Is Have Purchase Order","name"=>"is_have_purchase_order","type"=>"radio","required"=>TRUE,"validation"=>"required|integer","dataenum"=>"Array"];
		//$this->form[] = ["label"=>"Trx Purchase Orders Id","name"=>"trx_purchase_orders_id","type"=>"select2","required"=>TRUE,"validation"=>"required|integer|min:0","datatable"=>"trx_purchase_orders,id"];
		# OLD END FORM

		/*
	        | ----------------------------------------------------------------------
	        | Sub Module
	        | ----------------------------------------------------------------------
			| @label          = Label of action
			| @path           = Path of sub module
			| @foreign_key 	  = foreign key of sub table/module
			| @button_color   = Bootstrap Class (primary,success,warning,danger)
			| @button_icon    = Font Awesome Class
			| @parent_columns = Sparate with comma, e.g : name,created_at
	        |
	        */
		$this->sub_module = array();


		/*
	        | ----------------------------------------------------------------------
	        | Add More Action Button / Menu
	        | ----------------------------------------------------------------------
	        | @label       = Label of action
	        | @url         = Target URL, you can use field alias. e.g : [id], [name], [title], etc
	        | @icon        = Font awesome class icon. e.g : fa fa-bars
	        | @color 	   = Default is primary. (primary, warning, succecss, info)
	        | @showIf 	   = If condition when action show. Use field alias. e.g : [id] == 1
	        |
	        */
		$this->addaction = array();
        if (in_array($me->id_cms_privileges, $crudPrivileges)) {
            $this->addaction[] = [
                'label' => 'Sync History',
                'url' => uri('admin/log_api_calls?related_module=trx_purchase_requests&related_reff_id=[id]'),
                'icon' => 'fa fa-refresh',
                'color' => 'danger',
                'showIf' => "[doc_status] == 'approved'"
            ];
            $this->addaction[] = [
                'label' => 'Try Sync',
                'url' => \CB::mainpath() . '/trysync/[id]',
                'icon' => 'fa fa-refresh',
                'color' => 'info',
                'showIf' => "[doc_status] == 'approved'"
            ];
        }


        if ($me->id_cms_privileges == 1){
            $this->addaction[] = [
                'label' => 'Set As Synced',
                'url' => \CB::mainpath() . '/marksynced/[id]',
                'icon' => 'fa fa-check',
                'color' => 'success',
            ];
        }

        // if (in_array($me->id_cms_privileges, [1])) {
        $this->addaction[] = [
            'label' => 'Download PDF',
            'url' => CRUDBooster::mainpath() . '/download/[id]',
            'icon' => 'fa fa-files-o',
            'color' => 'primary',
            // 'showIf' => "[doc_status] == 'submited'"
        ];

		// Only show Create PO for approve privileges
		if (in_array($me->id_cms_privileges, $approvePrivileges)) {
            $this->addaction[] = [
                'label' => 'Verification',
                'url' => CRUDBooster::mainpath() . '/edit/[id]',
                'icon' => 'fa fa-files-o',
                'color' => 'primary',
                'showIf' => "[doc_status] == 'submited'"
            ];
			$this->addaction[] = [
				'label' => 'Create PO',
				'url' => CRUDBooster::mainpath() . '/createpo/[id]',
				'icon' => 'fa fa-files-o',
				'color' => 'info',
				'showIf' => "[doc_status] == 'approved' && [is_closed] == 0 && [sync_status] != 'Failed'"
			];
            $this->addaction[] = [
                'label' => 'Close PR',
                'url' => CRUDBooster::mainpath() . '/close/[id]',
                'icon' => 'fa fa-times',
                'color' => 'danger',
                'showIf' => "[is_closed] == 0 && [doc_status] == 'approved'",
                'confirmation' => true,
                'confirmation_title' => 'Close Purchase Request',
                'confirmation_text' => 'Are you sure want to close this PR?',
                'confirmation_type' => 'warning'
            ];
		}
		/*
	        | ----------------------------------------------------------------------
	        | Add More Button Selected
	        | ----------------------------------------------------------------------
	        | @label       = Label of action
	        | @icon 	   = Icon from fontawesome
	        | @name 	   = Name of button
	        | Then about the action, you should code at actionButtonSelected method
	        |
	        */
		$this->button_selected = array();

		// Only show bulk approve for users with approval privileges
		if (in_array($me->id_cms_privileges, $approvePrivileges)) {
			$this->button_selected[] = [
				'label' => 'Bulk Approve',
				'icon' => 'fa fa-check',
				'name' => 'bulk_approve'
			];
		}


		/*
	        | ----------------------------------------------------------------------
	        | Add alert message to this module at overheader
	        | ----------------------------------------------------------------------
	        | @message = Text of message
	        | @type    = warning,success,danger,info
	        |
	        */
		$this->alert        = array();



		/*
	        | ----------------------------------------------------------------------
	        | Add more button to header button
	        | ----------------------------------------------------------------------
	        | @label = Name of button
	        | @url   = URL Target
	        | @icon  = Icon from Awesome.
	        |
	        */
		$this->index_button = array();



		/*
	        | ----------------------------------------------------------------------
	        | Customize Table Row Color
	        | ----------------------------------------------------------------------
	        | @condition = If condition. You may use field alias. E.g : [id] == 1
	        | @color = Default is none. You can use bootstrap success,info,warning,danger,primary.
	        |
	        */
		$this->table_row_color = array();


		/*
	        | ----------------------------------------------------------------------
	        | You may use this bellow array to add statistic at dashboard
	        | ----------------------------------------------------------------------
	        | @label, @count, @icon, @color
	        |
	        */
		$this->index_statistic = array();



		/*
	        | ----------------------------------------------------------------------
	        | Add javascript at body
	        | ----------------------------------------------------------------------
	        | javascript code in the variable
	        | $this->script_js = "function() { ... }";
	        |
	        */
		$this->script_js = NULL;


		/*
	        | ----------------------------------------------------------------------
	        | Include HTML Code before index table
	        | ----------------------------------------------------------------------
	        | html code to display it before index table
	        | $this->pre_index_html = "<p>test</p>";
	        |
	        */
		$this->pre_index_html = null;


		$param = [];

		$param[] = ['label' => 'Doc. No.', 'name' => "U_SOL_SYNC_KEY", "type" => "input", "value" => get('U_SOL_SYNC_KEY')];
		$param[] = ['label' => 'Doc. Date', 'name' => "DocDate", "type" => "date", "value" => get('DocDate')];
		$param[] = ['label' => 'Req. Name', 'name' => "ReqName", "type" => "input", "value" => get('ReqName')];
		$param[] = [
			'label' => 'Branch',
			'name' => "Branch",
			"type" => "select2",
			"value" => get('Branch'),
			'options' => \App\RefBranches::selectRaw('id as value, CONCAT(name, \' [\', code, \']\') as label')->get()->toArray()
		];
		$param[] = [
			'label' => 'Store',
			'name' => "U_VIT_ToStr",
			"type" => "select2",
			"value" => get('U_VIT_ToStr'),
			'options' => \App\RefWarehouses::selectRaw('code as value, CONCAT(name, \' [\', code, \']\') as label')->get()->toArray()
		];
		$param[] = [
			'label' => 'Doc. Status',
			'name' => "doc_status",
			"type" => "select",
			"value" => get('doc_status'),
			'options' => [
				['value' => 'draft', 'label' => 'Draft'],
				['value' => 'submited', 'label' => 'Submitted'],
				['value' => 'rejected', 'label' => 'Rejected'],
				['value' => 'approved', 'label' => 'Approved'],
			]
		];

        $param[] = [
            'label' => 'Status Sync',
            'name' => "sync_status",
            "type" => "select",
            "value" => get('sync_status'),
            'options' => [
                ['value' => 'not_yet', 'label' => 'Not Synced Yet'],
                ['value' => 'Failed', 'label' => 'Failed'],
                ['value' => 'Synced', 'label' => 'Synced'],
            ]
        ];

        $param[] = [
            'label' => 'Closure Status',
            'name' => "is_closed",
            "type" => "select",
            "value" => get('is_closed'),
            'options' => [
                ['value' => "open", 'label' => 'Open'],
                ['value' => "closed", 'label' => 'Closed'],
            ]
        ];


		$this->pre_index_html .= view("admin/elements/filter", ['param' => $param, 'with_export' => false])->render();



		/*
	        | ----------------------------------------------------------------------
	        | Include HTML Code after index table
	        | ----------------------------------------------------------------------
	        | html code to display it after index table
	        | $this->post_index_html = "<p>test</p>";
	        |
	        */
		$this->post_index_html = null;



		/*
	        | ----------------------------------------------------------------------
	        | Include Javascript File
	        | ----------------------------------------------------------------------
	        | URL of your javascript each array
	        | $this->load_js[] = asset("myfile.js");
	        |
	        */
		$this->load_js = array();



		/*
	        | ----------------------------------------------------------------------
	        | Add css style at body
	        | ----------------------------------------------------------------------
	        | css code in the variable
	        | $this->style_css = ".style{....}";
	        |
	        */
		$this->style_css = NULL;



		/*
	        | ----------------------------------------------------------------------
	        | Include css File
	        | ----------------------------------------------------------------------
	        | URL of your css each array
	        | $this->load_css[] = asset("myfile.css");
	        |
	        */
		$this->load_css = array();
	}


	/*
	    | ----------------------------------------------------------------------
	    | Hook for button selected
	    | ----------------------------------------------------------------------
	    | @id_selected = the id selected
	    | @button_name = the name of button
	    |
	    */
	public function actionButtonSelected($id_selected, $button_name)
	{
		//Your code here
		if ($button_name == 'bulk_approve') {
			$me = \CB::me();
			$approvePrivileges = [1, 7];

			// Check if user has approval privileges
			if (!in_array($me->id_cms_privileges, $approvePrivileges)) {
				\CB::redirect(CRUDBooster::mainpath(), "You don't have permission to approve records!", "warning");
				return;
			}

			$approved_count = 0;
			$failed_count = 0;
			$failed_messages = [];
			$total_records = count($id_selected);

			// Add interval configuration (in seconds)
			$sync_interval = 2; // 2 seconds between each sync

			foreach ($id_selected as $index => $id) {
				$pr = \App\TrxPurchaseRequests::find($id);

				if (!$pr) {
					$failed_count++;
					$failed_messages[] = "Record ID $id not found";
					continue;
				}

				// Only approve submitted records
				if ($pr->doc_status !== 'submited') {
					$failed_count++;
					$failed_messages[] = "Record {$pr->U_SOL_SYNC_KEY} is not in submitted status";
					continue;
				}

				try {
					// Update to approved status
					$pr->doc_status = 'approved';
					$pr->is_verified = 1;
					$pr->verified_by_cms_users_id = $me->id;
					$pr->verified_at = date("Y-m-d H:i:s");
					$pr->approved_by = $me->id;
					$pr->save();

					// Sync to SAP with interval
					$this->prSync($id);

					$approved_count++;

					// Add delay between syncs (except for the last record)
					if ($index < $total_records - 1) {
						sleep($sync_interval);
					}

				} catch (\Exception $e) {
					$failed_count++;
					$failed_messages[] = "Failed to approve {$pr->U_SOL_SYNC_KEY}: " . $e->getMessage();
				}
			}

			// Prepare success/error messages
			$messages = [];
			if ($approved_count > 0) {
				$messages[] = "$approved_count record(s) approved successfully";
			}
			if ($failed_count > 0) {
				$messages[] = "$failed_count record(s) failed to approve";
				if (!empty($failed_messages)) {
					$messages = array_merge($messages, array_slice($failed_messages, 0, 3)); // Show first 3 error messages
					if (count($failed_messages) > 3) {
						$messages[] = "... and " . (count($failed_messages) - 3) . " more errors";
					}
				}
			}

			$message_type = ($failed_count > 0) ? "warning" : "success";
			$message = implode(". ", $messages);

			\CB::redirect(CRUDBooster::mainpath(), $message, $message_type);
		}
	}

	/*
	    | ----------------------------------------------------------------------
	    | Hook for manipulate query of index result
	    | ----------------------------------------------------------------------
	    | @query = current sql query
	    |
	    */
	public function hook_query_index(&$query)
	{
		//Your code here
		$me = \CB::me();
		$crudPrivileges = [1, 6, 7, 8];

		// Explicitly select all needed fields including closed_at
		$query->select('trx_purchase_requests.*');

		// Only show records if user has CRUD privileges
//		if (!in_array($me->id_cms_privileges, $crudPrivileges)) {
//			$query->where('1', '0'); // Show no records
//			return;
//		}

		if ($me->id_cms_privileges == 5) {
			$query->where("trx_purchase_requests.doc_status", "submited");
			$query->whereRaw('(trx_purchase_requests.verified_at is null)');
		}

		$cms_users = \App\CmsUsers::with('branches.branch', 'stores.store')->find($me->id);
		if (count($cms_users->branches) > 0) {
			$listcode = [];
			foreach ($cms_users->branches as $v) {
				$listcode[] = $v->ref_branches_id;
			}
			$query->whereIn('trx_purchase_requests.Branch', $listcode);
		}
		if (count($cms_users->stores) > 0) {
			$listcode = [];
			foreach ($cms_users->stores as $v) {
				$listcode[] = $v->store->code;
			}
			$query->whereIn('trx_purchase_requests.U_VIT_ToStr', $listcode);
		}
		if (get('U_SOL_SYNC_KEY')) {
			$U_SOL_SYNC_KEY = get('U_SOL_SYNC_KEY');
			$query->where('trx_purchase_requests.U_SOL_SYNC_KEY', $U_SOL_SYNC_KEY);
		}

		if (get('DocDate')) {
			$DocDate = get('DocDate');
			$query->where('trx_purchase_requests.DocDate', $DocDate);
		}
		if (get('ReqName')) {
			$ReqName = get('ReqName');
			$query->where('trx_purchase_requests.ReqName', 'LIKE', "%{$ReqName}%");
		}

		if (get('Branch')) {
			$Branch = get('Branch');
			$query->where('trx_purchase_requests.Branch', 'LIKE', "%{$Branch}%");
		}

		if (get('U_VIT_ToStr')) {
			$U_VIT_ToStr = get('U_VIT_ToStr');
			$query->where('trx_purchase_requests.U_VIT_ToStr', $U_VIT_ToStr);
		}

		if (get('doc_status')) {
			$doc_status = get('doc_status');
			$query->where('trx_purchase_requests.doc_status', 'LIKE', "%{$doc_status}%");
		}

        if (get('sync_status')) {
            $sync_status = get('sync_status');
            if ($sync_status == 'not_yet') { $sync_status = null; }
            $query->where('trx_purchase_requests.sync_status', $sync_status);
        }

        if (get('is_closed')) {
            $is_closed = get('is_closed');
            if ($is_closed == 'open') {$is_closed = 0;} elseif($is_closed == 'closed') {$is_closed = 1;}
            $query->where('trx_purchase_requests.is_closed', $is_closed);
        }

		$query->orderBy('trx_purchase_requests.id', 'desc');
	}

	/*
	    | ----------------------------------------------------------------------
	    | Hook for manipulate row of index table html
	    | ----------------------------------------------------------------------
	    |
	    */

	/*
	    | ----------------------------------------------------------------------
	    | Hook for manipulate data input before add data is execute
	    | ----------------------------------------------------------------------
	    | @arr
	    |
	    */
	public function hook_before_add(&$postdata)
	{
		//Your code here
		unset($postdata['items']);

		$me = \CB::me();
		$postdata['ReqName'] = username($me);
		$postdata['created_by'] = $me->id;

		// Ensure U_SOL_SYNC_KEY is unique
		do {
			$syncKey = 'PR' . date("Ymd") . six_random();
			$exists = \App\TrxPurchaseRequests::where('U_SOL_SYNC_KEY', $syncKey)->exists();
		} while ($exists);

		$postdata['U_SOL_SYNC_KEY'] = $syncKey;
	}

	/*
	    | ----------------------------------------------------------------------
	    | Hook for execute command after add public static function called
	    | ----------------------------------------------------------------------
	    | @id = last insert id
	    |
	    */
	public function hook_after_add($id)
	{
		//Your code here
		foreach ($_POST['items']['name'] as $k => $v) {
			$item = new \App\TrxPurchaseRequestsItems;
			$item->trx_purchase_requests_id = $id;
			$item->ref_item_master_datas_id = $v;
			$item->qty = $_POST['items']['qty'][$k];
			$item->save();
		}
	}

	/*
	    | ----------------------------------------------------------------------
	    | Hook for manipulate data input before update data is execute
	    | ----------------------------------------------------------------------
	    | @postdata = input post data
	    | @id       = current id
	    |
	    */
	public function hook_before_edit(&$postdata, $id)
	{
		//Your code here

		$olditems = \App\TrxPurchaseRequestsItems::where('trx_purchase_requests_id', $id)->get();
		foreach ($olditems as $v) {
			$v->delete();
		}

		foreach ($postdata['items']['name'] as $k => $v) {
			$item = new \App\TrxPurchaseRequestsItems;
			$item->trx_purchase_requests_id = $id;
			$item->ref_item_master_datas_id = $v;
			$item->qty = $postdata['items']['qty'][$k];
			$item->save();
		}

		// $me = \CB::me();
		// if ($me->id_cms_privileges != 5) {
		// 	$postdata['ReqName'] = username($me);
		// }


		unset($postdata['items']);

		if ($postdata['doc_status'] == 'approved') {
			$me = \CB::me();
			$postdata['is_verified'] = 1;
			$postdata['verified_by_cms_users_id'] = $me->id;
			$postdata['verified_at'] = date("Y-m-d H:i:s");
			$postdata['approved_by'] = $me->id;
		} else {
			$postdata['is_verified'] = 0;
		}
	}

	/*
	    | ----------------------------------------------------------------------
	    | Hook for execute command after edit public static function called
	    | ----------------------------------------------------------------------
	    | @id       = current id
	    |
	    */
	public function hook_after_edit($id)
	{
		//Your code here
		$pr = \App\TrxPurchaseRequests::find($id);
		if ($pr->doc_status == 'approved') {
			$this->prSync($id);
		}
	}

	/*
	    | ----------------------------------------------------------------------
	    | Hook for execute command before delete public static function called
	    | ----------------------------------------------------------------------
	    | @id       = current id
	    |
	    */
	public function hook_before_delete($id)
	{
		//Your code here
		$me = \CB::me();
		$crudPrivileges = [1, 6, 7, 8];

		// Check if user has delete privileges
		if (!in_array($me->id_cms_privileges, $crudPrivileges)) {
			\CB::redirect(CRUDBooster::mainpath(), "You don't have permission to delete this record!", "warning");
			exit;
		}

		// Check if user created this record and status allows deletion
		$pr = \App\TrxPurchaseRequests::find($id);
		if (!$pr || $pr->created_by !== $me->id) {
			\CB::redirect(CRUDBooster::mainpath(), "You can only delete records you created!", "warning");
			exit;
		}

		// Check if status allows deletion (only draft or rejected)
		if (!in_array($pr->doc_status, ['draft', 'rejected'])) {
			\CB::redirect(CRUDBooster::mainpath(), "You can only delete records with 'Draft' or 'Rejected' status!", "warning");
			exit;
		}
	}

	/*
	    | ----------------------------------------------------------------------
	    | Hook for execute command after delete public static function called
	    | ----------------------------------------------------------------------
	    | @id       = current id
	    |
	    */
	public function hook_after_delete($id)
	{
		//Your code here

	}



	//By the way, you can still ceate your own method in here... :)
    public function getMarksynced($id)
    {
        try {
            $data = \App\TrxPurchaseRequests::find($id);

            if ($data) {
                $data->sync_status = 'Synced';
                $data->sync_at = date('Y-m-d H:i:s');
                $data->save();

                \CB::redirect(\CB::mainpath(), "Data berhasil disinkronkan!", "success");
            } else {
                \CB::redirect(\CB::mainpath(), "Data tidak ditemukan!", "warning");
            }
        } catch (\Exception $e) {
            \Log::error("Error syncing purchase request: " . $e->getMessage());

            \CB::redirect(\CB::mainpath(), "Terjadi kesalahan saat menyinkronkan data.", "danger");
        }
    }


    public function getTrysync($id)
	{
		$this->prSync($id);

		return redirect(\CB::mainpath());
	}

	public function prSync($id)
	{
		$me = \CB::me();
		$pr = \App\TrxPurchaseRequests::with('items.item')->find($id);
		$branch = \App\RefBranches::find($pr->Branch);
		$url = env('SAP_HOST') . '/api/transaction/PostPurchaseRequest';
		$detail = [];
		$pricelist_update = [];
		foreach ($pr->items as $v) {
			$detail[] = [
				"ItemCode" => $v->item->sku,
				"Quantity" => $v->qty,
				"UomCode" => "1",
			];

			$pricelist_update[] = \App\Http\Controllers\AdminRefPurchasePriceListsController::syncByItemCodeList([$v->item->sku]);
		}
		$req = [
			"U_SOL_SYNC_KEY" => $pr->U_SOL_SYNC_KEY,
			"ReqName" => $pr->ReqName,
			"DocDate" => $pr->DocDate,
			"Branch" => $branch->code,
			"U_VIT_ToStr" => $pr->U_VIT_ToStr,
			"Comments" => $pr->Comments,
			"U_SOL_RAV_TRID" => $pr->U_SOL_SYNC_KEY,
			"detail" => $detail,
		];

		$body = json_encode($req);
		$res = \App\Service\SapService::post($url, $body, true);

		$log = new \App\LogApiCalls;
		$log->related_module = 'trx_purchase_requests';
		$log->related_reff_id = $id;
		if (env('SAP_LIVE', false)) {
            $url .= 'Live';
        }
		$log->api_url = $url;
		$log->request_body = $body;
		$log->response_body = json_encode($res['data']);
		$log->response_code = $res['code'];
		$log->save();

		/* update price to iseller */
		if (false) {
			foreach ($pricelist_update as $v) {

				$action_url = '/api/v3/UpdateProducts';
				$url = env('ISELLER_HOST') . $action_url;
				$body = [
					'sku' => $v->item_code,
					'type' => 'standard',
					'price' => $v->price,
				];
				$body = json_encode($body);

				$sub_res = \App\Service\IsellerService::iSellerPost($action_url, $body);

				$log = new \App\LogApiCalls;
				$log->related_module = 'product_update_to_iseller';
				$log->related_reff_id = $v->item_code;
				$log->api_url = $url;
				$log->request_body = $body;
				$log->response_body = json_encode($sub_res['data']);
				$log->response_code = $sub_res['code'];
				$log->save();
			}
		}

		if ($pr->sync_status != 'Synced') {

			if ($res['code'] == 200 && $res['data']['Data']['Data'] == "Success" && empty($res['data']['Error'])) {
				$pr->sync_status = 'Synced';
				$pr->sync_at = date("Y-m-d H:i:s");
				$pr->api_try = $pr->api_try + 1;
				$pr->save();
			} else {
				if ($pr->sync_status === null) {
					$pr->sync_status = 'Failed';
					$pr->api_try = $pr->api_try + 1;
					$pr->save();
				}
			}
		}
	}

	public function getStores($id)
	{
		$store_option = [];
		$stores = \App\RefWarehouses::where('ref_branches_id', $id)->get();
		foreach ($stores as $v) {
			$store_option[] = [
				'id' => $v->code,
				'text' => $v->name . '[' . ($v->is_store ? 'Store' : 'Non-Store') . ']'
			];
		}

		return json_encode($store_option);
	}

	// public function getProductItems($id)
	// {
	//     $products = \App\ProductBranches::where('ref_branches_id', $id)
	//         ->join('ref_item_master_datas', 'product_branches.ref_item_master_datas_id', '=', 'ref_item_master_datas.id')
	//         ->select(
	//             'ref_item_master_datas.id',
	//             'ref_item_master_datas.name',
	//             'ref_item_master_datas.sku',
	//             'ref_item_master_datas.unit_of_measurement'
	//         )
	//         ->get()
	//         ->map(function($item) {
	//             return [
	//                 'key' => $item->id,
	//                 'label' => $item->name . ' [' . $item->sku . '] [' . $item->unit_of_measurement . ']'
	//             };
	//         });

	//     return json([
	//         'code' => 200,
	//         'message' => 'success',
	//         'data' => $products
	//     ]);
	// }

	public function getCreatepo($id)
	{

		$po_exist = \App\TrxPurchaseOrders::where('trx_purchase_requests_id', $id)->where('doc_status', '==', 'draft')->first();
		// $po_exist = \App\TrxPurchaseOrders::where('trx_purchase_requests_id', $id)->first();

		if (!empty($po_exist)) {
			return redirect(uri('admin/trx_purchase_orders/edit/' . $po_exist->id));
		}

		$pr = \App\TrxPurchaseRequests::with('items.item')->find($id);

		try {
			DB::beginTransaction();

			$me = \CB::me();
			$po = new \App\TrxPurchaseOrders;
			$po->trx_purchase_requests_id = $pr->id;
			$po->doc_status = "draft";
			$po->U_SOL_SYNC_KEY = 'PO' . date("Ymd") . six_random();
			$po->DocDate = date("Y-m-d");
			$po->WhsCode = $pr->U_VIT_ToStr;
			$po->Comments = $pr->Comments;
			$po->DiscPrcnt = 0;
			$po->DiscSum = 0;
			$po->U_SOL_REF_KEY = $pr->U_SOL_SYNC_KEY;
			$po->U_SOL_RAV_TRID = $po->U_SOL_SYNC_KEY;
			$po->created_by = $me->id;
			$po->save();

			$total = 0;
			foreach ($pr->items as $v) {
				$prod = \App\RefItemMasterData::where("sku", $v->item->sku)->first();
				$uom = \App\RefUoms::where("code", strtoupper($prod->unit_of_measurement))->first();
                $pricelist = \App\RefPurchasePriceList::where("item_code", $v->item->sku)
                    ->orderBy('created_at', 'desc')
                    ->first();
				$bp = \App\RefBusinessPartner::where('code', $pricelist->card_code)->first();

				$total = $v->qty * $pricelist->price;
				$po_item = new \App\TrxPurchaseOrdersItems;
				$po_item->trx_purchase_orders_id = $po->id;
				$po_item->ItemCode = $v->item->sku;
				$po_item->Quantity = $v->qty;
				$po_item->UomCode = $uom->abs_entry;
				$po_item->VatGroup = $pricelist->var_group;
				$po_item->PriceBefDi = $pricelist->price;
				$po_item->ref_purchase_price_lists_id = $v->id;
				$po_item->ref_business_partners_id = $bp->id;
				$po_item->save();
			}

			$po->DocTotal = $total;
			$po->save();

			$pr->is_have_purchase_order = 1;
			$pr->trx_purchase_orders_id = $po->id;
			$pr->save();

			DB::commit();

			return redirect(uri('admin/trx_purchase_orders/edit/' . $po->id));
		} catch (\Exception $e) {
			DB::rollback();

			return json([
				'code' => 500,
				'message' => 'Major transaction error, data rolled back',
				'error' => $e
			]);
		}
	}

	public function getItems()
	{
		$branch_id = get('branch_id');
		if (empty($branch_id)) {
			return json([
				'code' => 422,
				'message' => 'Branch ID is mandatory',
			]);
		}
		$itemmaster = \App\RefItemMasterData::selectRaw("ref_item_master_datas.id, ref_item_master_datas.name, ref_item_master_datas.sku, ref_item_master_datas.unit_of_measurement")
			->join('product_branches', 'product_branches.ref_item_master_datas_id', '=', 'ref_item_master_datas.id')
			->where('product_branches.ref_branches_id', $branch_id)
			->whereRaw("(tags like '%NoN-Agrinesia%')")
			->whereNull('product_branches.deleted_at')
			->orderBy("ref_item_master_datas.name")
			->get()
			->map(function ($r) {
				$r->name = slug($r->name);
				$r->name = str_replace("-", " ", $r->name);
				$r->name = strtoupper($r->name);
				return [
					'id' => $r->id,
					'name' => $r->name,
					'sku' => $r->sku,
					'unit_of_measurement' => $r->unit_of_measurement,
				];
			});

		return json([
			'code' => 200,
			'message' => 'success',
			'data' => $itemmaster
		]);
	}

	public function getStore()
	{
		$me = \CB::me();
		$cms_users = \App\CmsUsers::with('branches.branch', 'stores.store')->find($me->id);
		$store_code_available = [];
		if (count($cms_users->stores) > 0) {
			foreach ($cms_users->stores as $v) {
				$store_code_available[] = $v->store->code;
			}
		}

		$branch_id = get('branch_id');
		if (!empty($store_code_available)) {
			$stores = \App\RefWarehouses::where("ref_branches_id", $branch_id)->whereIn('code', $store_code_available)->get();
		} else {
			$stores = \App\RefWarehouses::where("ref_branches_id", $branch_id)->get();
		}

		$options = [];
		foreach ($stores as $v) {
			$options[] = [
				'key' => $v->code,
				'label' => $v->name . ' [' . ($v->is_store ? 'Store' : 'Non-Store') . '] [' . $v->code . ']'
			];
		}

		return json([
			'code' => 200,
			'message' => 'success',
			'data' => $options
		]);
	}

	public function getDownload($id)
	{
		$data['pr'] = \App\TrxPurchaseRequests::with('items.item')->find($id);
		$data['branch'] = \App\RefBranches::find($data['pr']->Branch);
		$data['store'] = \App\RefWarehouses::where('code', $data['pr']->U_VIT_ToStr)->first();
		// return view("merchandiser/purchase_request/download", $data);
		$dompdf = new Dompdf();
		$dompdf->loadHtml(view("merchandiser/purchase_request/download", $data)->render());
		$dompdf->setPaper('A3', 'potrait');
		$dompdf->render();
		$dompdf->stream("purchase-request-doc-" . $data['pr']->U_SOL_SYNC_KEY  . ".pdf");
	}

	public function getClose($id)
	{
		$pr = \App\TrxPurchaseRequests::find($id);
		$pr->is_closed = true;
		$pr->closed_at = date('Y-m-d H:i:s');
		$pr->save();

		\CB::redirect(CRUDBooster::mainpath(), "PR has been closed successfully!", "success");
	}
}
