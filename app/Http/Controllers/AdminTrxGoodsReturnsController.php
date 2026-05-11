<?php namespace App\Http\Controllers;

	use Session;
	use Request;
	use DB;
	use CRUDBooster;
	use Dompdf\Dompdf;
	class AdminTrxGoodsReturnsController extends \crocodicstudio\crudbooster\controllers\CBController {

	    public function cbInit() {

			# START CONFIGURATION DO NOT REMOVE THIS LINE
			$this->title_field = "id";
			$this->limit = "20";
			$this->orderby = "id,desc";
			$this->global_privilege = false;
			$this->button_table_action = true;
			$this->button_bulk_action = true;
			$this->button_action_style = "button_icon";
			$this->button_add = false;
			$this->button_edit = true;
			$this->button_delete = true;
			$this->button_detail = false;
			$this->button_show = false;
			$this->button_filter = true;
			$this->button_import = false;
			$this->button_export = false;
			$me = \CB::me();

			// Privilege definitions
			$createPrivileges = [1, 6, 7, 8];
			$viewPrivileges = [1, 6, 7, 8];
			$editPrivileges = [1, 6, 7, 8];
			$deletePrivileges = [1, 6, 7, 8];
			$approvePrivileges = [1,6, 7];

			// Apply privilege restrictions
			if (!in_array($me->id_cms_privileges, $createPrivileges)) {
				$this->button_add = false;
			}

			if (!in_array($me->id_cms_privileges, $editPrivileges)) {
				$this->button_edit = false;
			}

			if (!in_array($me->id_cms_privileges, $deletePrivileges)) {
				$this->button_delete = false;
			}

			$this->table = "trx_goods_returns";
			# END CONFIGURATION DO NOT REMOVE THIS LINE

			# START COLUMNS DO NOT REMOVE THIS LINE
			$this->col = [];
			$this->col[] = ["label"=>"Doc. No.","name"=>"U_SOL_SYNC_KEY"];
			$this->col[] = ["label"=>"Doc. Date","name"=>"DocDate"];
			$this->col[] = ["label"=>"Related GRPO. Code","name"=>"trx_goods_receipts_id", "join" => "trx_goods_receipts,U_SOL_SYNC_KEY"];
			$this->col[] = ["label"=>"Related PR. Code","name"=>"U_SOL_REF_KEY"];
			$this->col[] = ["label"=>"Related PO. Code","name"=>"trx_purchase_orders_id", "join" => "trx_purchase_orders,U_SOL_SYNC_KEY"];
			$this->col[] = ["label" => "Branch", "name" => "trx_purchase_orders_id", "callback" => function ($r) {
				$po  = \App\TrxPurchaseOrders::find($r->trx_purchase_orders_id);
				$pr  = \App\TrxPurchaseRequests::find($po->trx_purchase_requests_id);
				$branch = \App\RefBranches::find($pr->Branch);
				return $branch->name . ' [' . ($branch->code) . ']';
			}];
			$this->col[] = ["label"=>"Vendor Code","name"=>"CardCode"];
			$this->col[] = ["label"=>"Vendor Ref. No","name"=>"NumAtCard"];
			$this->col[] = ["label"=>"Warehouse","name"=>"WhsCode", "callback" => function ($r) {
				$stores  = \App\RefWarehouses::where('code', $r->WhsCode)->first();
				return $stores->name . ' [' . ($stores->code) . ']';
			}];
			// $this->col[] = ["label"=>"Comments","name"=>"Comments"];
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
			$this->col[] = ["label"=>"Verification Status","name"=>"is_verified", "callback" => function($r){
                if ($r->is_verified == 1) {
                    return '<div class="btn btn-xs btn-success">Verified</div>';
                } else if ($r->is_verified == 2) {
					return '<div class="btn btn-xs btn-success">Rejected</div>';
				} else {
					if ($r->doc_status == "submited") {
						return '<div class="btn btn-xs btn-info">Waiting Verification</div>';
					}

                }
            }];
			$this->col[] = ["label"=>"Verified By","name"=>"verified_by_cms_users_id", "callback" => function ($r) {
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
			$this->col[] = ["label"=>"Verified At","name"=>"verified_at"];
			$this->col[] = ["label"=>"Created By","name"=>"created_by", "callback" => function ($r) {
				if ($r->created_by) {
					$user = \App\CmsUsers::find($r->created_by);
					if ($user) {
						$privilege = DB::table('cms_privileges')->where('id', $user->id_cms_privileges)->first();
						$roleName = $privilege ? $privilege->name : 'Unknown Role';
						return $user->name . ' (' . $roleName . ')';
					}
					return 'Unknown User';
				}
				return '-';
			}];
			$this->col[] = ["label"=>"Approved By","name"=>"approved_by", "callback" => function ($r) {
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
			$this->col[] = ["label"=>"Sync Status","name"=>"sync_status", "callback" => function($r){
                $color = ['Failed' => 'danger', 'Synced' => 'success'];
                return '<div class="btn btn-xs btn-'.$color[$r->sync_status].'">'.ucfirst($r->sync_status).'</div>';
            }];
			$this->col[] = ["label"=>"Sync At","name"=>"sync_at", "callback" => function($r){
				if ($r->sync_at) {
					return dateformat($r->sync_at);
				}
			}];
			$this->col[] = ["label" => "Try Sync", "name" => "api_try"];

			# END COLUMNS DO NOT REMOVE THIS LINE

			# START FORM DO NOT REMOVE THIS LINE

			$myprivilage = \CB::me()->id_cms_privileges;

			// Check if user has view privileges
			if (!in_array($myprivilage, $viewPrivileges)) {
				$is_viewonly = true;
			} else {
				// For edit mode, check edit privileges and ownership/status
				$is_viewonly = false;
				if (!empty(Request::segment(4))) {
					$gr = \App\TrxGoodsReturns::find(Request::segment(4));
					if ($gr) {
						// Make approved status view-only for all users
						if ($gr->doc_status === 'approved') {
							$is_viewonly = true;
						}
						// Approvers can edit submitted records
						else if ($gr->doc_status === 'submited' && in_array($myprivilage, $approvePrivileges)) {
							$is_viewonly = false;
						}
						// Users can only edit their own draft or rejected records
						else if (in_array($gr->doc_status, ['draft', 'rejected']) && $gr->created_by === $me->id) {
							$is_viewonly = false;
						}
						// All other cases are view-only
						else {
							$is_viewonly = true;
						}
					}
				}
			}

			// Determine if fields should be readonly (all except items and status)
			$fields_readonly = false;
			if (!empty(Request::segment(4))) {
				$gr = \App\TrxGoodsReturns::find(Request::segment(4));
				if ($gr && $gr->doc_status === 'submited' && in_array($myprivilage, $approvePrivileges)) {
					$fields_readonly = true;
				}
			}

			$this->form = [];
			$this->form = [];
			$this->form[] = ['label'=>'Doc. No.','name'=>'U_SOL_SYNC_KEY','type'=>'text','validation'=>'required|min:1|max:255','width'=>'col-sm-10', "readonly" => 1];
			$this->form[] = ['label'=>'Vendor Code','name'=>'CardCode','type'=>'text','validation'=>'required|min:1|max:255','width'=>'col-sm-10', "readonly" => 1];
            $this->form[] = ['label'=>'Vendor Name','name'=>'NumAtCard','type'=>'text','validation'=>'required|min:1|max:255','width'=>'col-sm-10', "readonly" => 1];

			$this->form[] = ['label'=>'DocDate','name'=>'DocDate','type'=>'date','validation'=>'required|min:1|max:255','width'=>'col-sm-10', 'readonly' => $fields_readonly ?: $is_viewonly];
			$store_option = '';
			$stores = \App\RefWarehouses::get();
			foreach ($stores as $v) {
				$store_option .= $v->code . '|' . $v->name . '['.( $v->is_store ? 'Store' : 'Non-Store' ).'];';
			}
			$this->form[] = ['label'=>'WhsCode','name'=>'WhsCode','type'=> $fields_readonly ? 'select' : 'select2','validation'=>'required|min:1|max:255','width'=>'col-sm-10', 'dataenum' => $store_option, 'readonly' => $fields_readonly, 'disabled' => $fields_readonly ? false : $is_viewonly];
			$this->form[] = ['label'=>'Comments','name'=>'Comments','type'=>'text','validation'=>'required|min:1|max:255','width'=>'col-sm-10', 'readonly' => $fields_readonly ?: $is_viewonly];
			$this->form[] = ['label'=>'Related PR. Code','name'=>'U_SOL_REF_KEY','type'=>'text','validation'=>'required|min:1|max:255','width'=>'col-sm-10', "readonly" => 1, 'disabled' => $is_viewonly];
            $itemmaster = \App\RefItemMasterData::selectRaw("id,name,sku,unit_of_measurement")->whereRaw("(tags like '%NoN-Agrinesia%')")
            ->orderBy("name")->get()->map(function($r){
                $r->name = slug($r->name);
                $r->name = str_replace("-", " ", $r->name);
                $r->name = strtoupper($r->name);
                return $r;
            });

            $map_itemmaster = [];
            foreach ($itemmaster as $v) {
                $map_itemmaster[$v->sku] = $v;
            }
			$data['itemmaster'] = $itemmaster;
			$data['map_itemmaster'] = $map_itemmaster;

            $data['readonly'] = true;
            $data['disabled'] = $is_viewonly;
            $this->form[] = ['label'=>'Items','name'=>'items','type'=>'custom','validation'=>'required','width'=>'col-sm-10' , 'html' => view('merchandiser/goods_return/items_form', $data)->render()];
			// $this->form[] = ['label'=>'U SOL RAV TRID','name'=>'U_SOL_RAV_TRID','type'=>'text','validation'=>'required|min:1|max:255','width'=>'col-sm-10'];
				$me = \CB::me();
				// Only users with approve privileges can approve/reject
				if (in_array($me->id_cms_privileges, $approvePrivileges)) {
					// For new records (add mode) or draft status, show creator options
					if (empty(Request::segment(4))) {
						// Add mode - show creator options
						$status_enum = 'draft|Save as Draft;submited|Submit & Request Approval';
					} else {
						// Edit mode - check document status
						$gr = \App\TrxGoodsReturns::find(Request::segment(4));
						if ($gr && in_array($gr->doc_status, ['submited', 'approved'])) {
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
				$this->form[] = ['label'=>'Doc. Status','name'=>'doc_status','type'=> $fields_readonly ? 'select' : 'select2','validation'=>'required|min:1|max:255','width'=>'col-sm-10', 'dataenum' => $status_enum, 'disabled' => $is_viewonly];
			# END FORM DO NOT REMOVE THIS LINE

			# OLD START FORM
			//$this->form = [];
			//$this->form[] = ["label"=>"U SOL SYNC KEY","name"=>"U_SOL_SYNC_KEY","type"=>"text","required"=>TRUE,"validation"=>"required|min:1|max:255"];
			//$this->form[] = ["label"=>"CardCode","name"=>"CardCode","type"=>"text","required"=>TRUE,"validation"=>"required|min:1|max:255"];
			//$this->form[] = ["label"=>"DocDate","name"=>"DocDate","type"=>"date","required"=>TRUE,"validation"=>"required|date"];
			//$this->form[] = ["label"=>"WhsCode","name"=>"WhsCode","type"=>"text","required"=>TRUE,"validation"=>"required|min:1|max:255"];
			//$this->form[] = ["label"=>"Comments","name"=>"Comments","type"=>"textarea","required"=>TRUE,"validation"=>"required|string|min:5|max:5000"];
			//$this->form[] = ["label"=>"U SOL REF KEY","name"=>"U_SOL_REF_KEY","type"=>"text","required"=>TRUE,"validation"=>"required|min:1|max:255"];
			//$this->form[] = ["label"=>"U SOL RAV TRID","name"=>"U_SOL_RAV_TRID","type"=>"text","required"=>TRUE,"validation"=>"required|min:1|max:255"];
			//$this->form[] = ["label"=>"Doc Status","name"=>"doc_status","type"=>"text","required"=>TRUE,"validation"=>"required|min:1|max:255"];
			//$this->form[] = ["label"=>"Sync Status","name"=>"sync_status","type"=>"text","required"=>TRUE,"validation"=>"required|min:1|max:255"];
			//$this->form[] = ["label"=>"Sync At","name"=>"sync_at","type"=>"datetime","required"=>TRUE,"validation"=>"required|date_format:Y-m-d H:i:s"];
			//$this->form[] = ["label"=>"Is Verified","name"=>"is_verified","type"=>"radio","required"=>TRUE,"validation"=>"required|integer","dataenum"=>"Array"];
			//$this->form[] = ["label"=>"Verified By Cms Users Id","name"=>"verified_by_cms_users_id","type"=>"select2","required"=>TRUE,"validation"=>"required|integer|min:0","datatable"=>"verified_by_cms_users,id"];
			//$this->form[] = ["label"=>"Verified At","name"=>"verified_at","type"=>"datetime","required"=>TRUE,"validation"=>"required|date_format:Y-m-d H:i:s"];
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
            $crudPrivileges = [1, 6, 7, 8];
            if (in_array($me->id_cms_privileges, $crudPrivileges)) {
                $this->addaction[] = [
                    'label' => 'Sync History',
                    'url' => uri('admin/log_api_calls?related_module=trx_goods_returns&related_reff_id=[id]'),
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
            if (in_array($me->id_cms_privileges, $approvePrivileges)) {
                $this->addaction[] = [
                    'label' => 'Verification',
                    'url' => CRUDBooster::mainpath() . '/edit/[id]',
                    'icon' => 'fa fa-files-o',
                    'color' => 'primary',
                    'showIf' => "[doc_status] == 'submited'"
                ];
            }

            $this->addaction[] = [
                'label' => 'Download PDF',
                'url' => CRUDBooster::mainpath() . '/download/[id]',
                'icon' => 'fa fa-files-o',
                'color' => 'primary',
                // 'showIf' => "[doc_status] == 'submited'"
            ];


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

	        // Add bulk approve button for users with approve privileges
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



	        /*
	        | ----------------------------------------------------------------------
	        | Include HTML Code after index table
	        | ----------------------------------------------------------------------
	        | html code to display it after index table
	        | $this->post_index_html = "<p>test</p>";
	        |
	        */
	        $this->post_index_html = null;

		$param = [];


            $param[] = ['label' => 'Doc. No.', 'name' => "U_SOL_SYNC_KEY", "type" => "input", "value" => get('U_SOL_SYNC_KEY')];
            // $param[] = ['label' => 'Parent Doc. No.', 'name' => "ParentDocNo", "type" => "input", "value" => get('ParentDocNo')];
            $param[] = ['label' => 'Doc. Date', 'name' => "DocDate", "type" => "date", "value" => get('DocDate')];
            $param[] = ['label' => 'Rel. PR', 'name' => "U_SOL_REF_KEY", "type" => "input", "value" => get('U_SOL_REF_KEY')];
            $param[] = ['label' => 'Rel. PO', 'name' => "RelPO", "type" => "input", "value" => get('RelPO')];
            $param[] = [
                'label' => 'Vendor',
                'name' => "CardCode",
                "type" => "select2",
                "value" => get('CardCode'),
                'options' => \App\RefBusinessPartner::selectRaw('code as value, CONCAT(name, \' [\', code, \']\') as label')->where('type','Vendor')->get()->toArray()
            ];
            $param[] = [
                'label' => 'Warehouse',
                'name' => "WhsCode",
                "type" => "select2",
                "value" => get('WhsCode'),
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
                    ['value' => 'approved', 'label' => 'Approved'],]
            ];
            $param[] = [
                'label' => 'Verification Status',
                'name' => "is_verified",
                "type" => "select",
                "value" => get('is_verified'),
                'options' => [
                    ['value' => 'waiting', 'label' => 'Waiting Verification'],
                    ['value' => 'rejected', 'label' => 'Rejected'],
                    ['value' => 'verified', 'label' => 'Verified'],
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



		$this->pre_index_html .= view("admin/elements/filter", ['param' => $param, 'with_export' => false])->render();



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
	    public function actionButtonSelected($id_selected,$button_name) {
	        //Your code here
	        if ($button_name == 'bulk_approve') {
	        	$me = \CB::me();
	        	$approvePrivileges = [1, 7];

	        	// Check if user has approve privileges
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
	        		try {
	        			DB::beginTransaction();

	        			$gr = \App\TrxGoodsReturns::find($id);

	        			if (!$gr) {
	        				$failed_count++;
	        				$failed_messages[] = "Record ID $id not found";
	        				DB::rollback();
	        				continue;
	        			}

	        			// Only approve if status is 'submited'
	        			if ($gr->doc_status !== 'submited') {
	        				$failed_count++;
	        				$failed_messages[] = "Record {$gr->U_SOL_SYNC_KEY} is not in submitted status";
	        				DB::rollback();
	        				continue;
	        			}

	        			// Update goods return
	        			$gr->doc_status = 'approved';
	        			$gr->is_verified = 1;
	        			$gr->verified_by_cms_users_id = $me->id;
	        			$gr->verified_at = date("Y-m-d H:i:s");
	        			$gr->approved_by = $me->id;
	        			$gr->save();

	        			// Sync to external systems
	        			$this->grpoSync($id);

	        			$approved_count++;

	        			// Add delay between syncs (except for the last record)
	        			if ($index < $total_records - 1) {
	        				sleep($sync_interval);
	        			}

	        			DB::commit();
	        		} catch (\Exception $e) {
	        			DB::rollback();
	        			$failed_count++;
	        			$failed_messages[] = "Failed to approve {$gr->U_SOL_SYNC_KEY}: " . $e->getMessage();
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
	    public function hook_query_index(&$query) {
	        //Your code here
			$me = \CB::me();
			$viewPrivileges = [1, 6, 7, 8];

			// Only show records if user has view privileges
//			if (!in_array($me->id_cms_privileges, $viewPrivileges)) {
//				$query->where('1', '0'); // Show no records
//				return;
//			}

			$cms_users = \App\CmsUsers::with('branches.branch', 'stores.store')->find($me->id);
			if (count($cms_users->stores) > 0) {
				$listcode = [];
				foreach($cms_users->stores as $v) {
					$listcode[] = $v->store->code;
				}
				$query->whereIn('trx_goods_returns.WhsCode', $listcode);
			}

			// Restrict draft records to only show to their creators
			$query->where(function($q) use ($me) {
				$q->where('trx_goods_returns.doc_status', '!=', 'draft')
				  ->orWhere('trx_goods_returns.created_by', $me->id);
			});

            $query->orderBy('trx_goods_returns.id', 'desc');




            if (get('U_SOL_SYNC_KEY')) {
                $U_SOL_SYNC_KEY = get('U_SOL_SYNC_KEY');
                $query->where('trx_goods_returns.U_SOL_SYNC_KEY', $U_SOL_SYNC_KEY);
            }

            if (get('CardCode')) {
                $CardCode = get('CardCode');
                $query->where('trx_goods_returns.CardCode', $CardCode);
            }

            if (get('DocDate')) {
                $DocDate = get('DocDate');
                $query->where('trx_goods_returns.DocDate', $DocDate);
            }


            if (get('RelPO')) {
                $RelPOs = get('RelPO');
                $PR = \App\TrxPurchaseOrders::where('U_SOL_SYNC_KEY', $RelPOs)->first();
                $RelPO = $PR->id;
                $query->where('trx_goods_returns.trx_purchase_orders_id', $RelPO);
            }


            if (get('U_SOL_REF_KEY')) {
                $U_SOL_REF_KEY = get('U_SOL_REF_KEY');
                $query->where('trx_goods_returns.U_SOL_REF_KEY', $U_SOL_REF_KEY);
            }

            if (get('Branch')) {
                $Branch = get('Branch');
                $query->where('trx_goods_returns.Branch',  $Branch);
            }

            if (get('WhsCode')) {
                $WhsCode = get('WhsCode');
                $query->where('trx_goods_returns.WhsCode',  $WhsCode);
            }

            if (get('doc_status')) {
                $doc_status = get('doc_status');
                $query->where('trx_goods_returns.doc_status',  $doc_status);
            }

            if (get('is_verified')) {
                $is_verified = get('is_verified');
                if ($is_verified == 'waiting') { $is_verified = 0; }
                elseif ($is_verified == 'verified') { $is_verified = 1; }
                elseif ($is_verified == 'rejected') { $is_verified = 2; }
                $query->where('trx_goods_returns.is_verified', $is_verified);
            }

            if (get('sync_status')) {
                $sync_status = get('sync_status');
                if ($sync_status == 'not_yet') { $sync_status = null; }
                $query->where('trx_goods_returns.sync_status', $sync_status);
            }


	    }

	    /*
	    | ----------------------------------------------------------------------
	    | Hook for manipulate row of index table html
	    | ----------------------------------------------------------------------
	    |
	    */
	    public function hook_row_index($column_index,&$column_value) {
	    	//Your code here
	    }

	    /*
	    | ----------------------------------------------------------------------
	    | Hook for manipulate data input before add data is execute
	    | ----------------------------------------------------------------------
	    | @arr
	    |
	    */
	    public function hook_before_add(&$postdata) {
	        //Your code here
	        $me = \CB::me();
	        $postdata['created_by'] = $me->id;
	    }

	    /*
	    | ----------------------------------------------------------------------
	    | Hook for execute command after add public static function called
	    | ----------------------------------------------------------------------
	    | @id = last insert id
	    |
	    */
	    public function hook_after_add($id) {
	        //Your code here

	    }

	    /*
	    | ----------------------------------------------------------------------
	    | Hook for manipulate data input before update data is execute
	    | ----------------------------------------------------------------------
	    | @postdata = input post data
	    | @id       = current id
	    |
	    */
	    public function hook_before_edit(&$postdata,$id) {
	        //Your code here

			/* delete all items */
			$items = \App\TrxGoodsReturnItems::where('trx_goods_returns_id', $id)->get();
			foreach ($items as $v) {
				$v->delete();
			}


			/* add new items */
			foreach ($postdata['items']['name'] as $k => $itemcode) {
				if ($postdata['doc_status'] == 'submited' && $postdata['items']['Quantity'][$k] == 0) {
					continue; // skip if quantity is 0
				}
				$item = new \App\TrxGoodsReturnItems;
				$item->trx_goods_returns_id = $id;
				$item->ItemCode = $itemcode;
				$item->Quantity = $postdata['items']['Quantity'][$k];
				$item->UomCode = $postdata['items']['UomCode'][$k];
				$item->save();
			}

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
	    public function hook_after_edit($id) {
	        //Your code here
			$grpo = \App\TrxGoodsReturns::find($id);
            if ($grpo->doc_status == 'approved') {
                $this->grpoSync($id);
            }

	    }

	    /*
	    | ----------------------------------------------------------------------
	    | Hook for execute command before delete public static function called
	    | ----------------------------------------------------------------------
	    | @id       = current id
	    |
	    */
	    public function hook_before_delete($id) {
	        //Your code here
			$me = \CB::me();
			$deletePrivileges = [1, 6, 7, 8];

			// Check if user has delete privileges
			if (!in_array($me->id_cms_privileges, $deletePrivileges)) {
				\CB::redirect(CRUDBooster::mainpath(), "You don't have permission to delete this record!", "warning");
				exit;
			}

			// Check if user created this record
			$gr = \App\TrxGoodsReturns::find($id);
			if (!$gr || $gr->created_by !== $me->id) {
				\CB::redirect(CRUDBooster::mainpath(), "You can only delete records you created!", "warning");
				exit;
			}

			// Check if status allows deletion (only draft or rejected)
			if (!in_array($gr->doc_status, ['draft', 'rejected'])) {
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
	    public function hook_after_delete($id) {
	        //Your code here

	    }



	    //By the way, you can still create your own method in here... :)
		public function getTrysync($id)
		{
			$this->grpoSync($id);

			return redirect( \CB::mainpath() );
		}

		public function grpoSync($id)
        {
            $me = \CB::me();
            $gr = \App\TrxGoodsReturns::with('items')->find($id);
			$po = \App\TrxPurchaseOrders::find($gr->trx_purchase_orders_id);
            $grpo = \App\TrxGoodsReceipts::find($gr->trx_goods_receipts_id);

            $url = env('SAP_HOST') . '/api/transaction/PostGoodsReturn';
            $req = $gr->toArray();

            unset($req['id']);
            unset($req['trx_purchase_orders_id']);
            unset($req['doc_status']);
            unset($req['sync_status']);
            unset($req['sync_at']);
            unset($req['is_verified']);
            unset($req['verified_by_cms_users_id']);
            unset($req['verified_at']);
            unset($req['deleted_at']);
            unset($req['created_at']);
            unset($req['updated_at']);

            foreach ($req['items'] as $k => $v) {
				if ($v['Quantity'] == 0) {
					unset($req['items'][$k]);
					continue;
				}
                unset($req['items'][$k]['id']);
                unset($req['items'][$k]['deleted_at']);
                unset($req['items'][$k]['created_at']);
                unset($req['items'][$k]['updated_at']);
                unset($req['items'][$k]['trx_goods_returns_id']);
                $req['items'][$k]['UomCode'] = getUomNumericCode($v['UomCode']);
            }

			$req['detail'] = $req['items'];
			unset($req['items']);

			$req['U_SOL_RAV_TRID'] = $req['U_SOL_SYNC_KEY'];
            // $req['U_SOL_RAV_TRID'] = $po->U_SOL_SYNC_KEY;
			$req['U_SOL_REF_KEY'] = $grpo->U_SOL_SYNC_KEY;

            $body = json_encode($req);
            $res = \App\Service\SapService::post($url, $body, true);

			if($gr->sync_status != 'Synced'){
				if (
					($res['data']['Data']['Data'] == "Success" || $res['data']['Success'] == true) 
					&& (empty($res['data']['Error']) && strpos($res['data']['Message'], 'failure') === false)
				) {
					$gr->sync_status = 'Synced';
					$gr->sync_at = date("Y-m-d H:i:s");
					$gr->api_try = $gr->api_try + 1;
					$gr->save();

					$this->greturnisellersync($id);
				} else {
					if ($gr->sync_status === null) {
						$gr->sync_status = 'Failed';
						$gr->api_try = $gr->api_try + 1;
						$gr->save();
					}
				}
			} else {
				$this->greturnisellersync($id);
			}

            $log = new \App\LogApiCalls;
            $log->related_module = 'trx_goods_returns';
            $log->related_reff_id = $id;
			if (env('SAP_LIVE', false)) {
				$url .= 'Live';
			}
            $log->api_url = $url;
            $log->request_body = $body;
            $log->response_body = json_encode($res['data']);
            $log->response_code = $res['code'];
            $log->save();

			return $log;

        }

        public function greturnisellersync($id)
        {
            $gr = \App\TrxGoodsReturns::with('items')->find($id);
			$po = \App\TrxPurchaseOrders::find($gr->trx_purchase_orders_id);
            $grpo = \App\TrxGoodsReceipts::where('trx_purchase_orders_id', $gr->trx_purchase_orders_id)->first();
			$pocode = $po->U_SOL_SYNC_KEY;

            $products = [];
            foreach ($gr->items as $v) {
				if ($v->Quantity == 0) {
					continue;
				}
                $products[] = [
                    'sku' => $v->ItemCode,
                    'quantity' => $v->Quantity,
                ];
            }

			$req = [
				"outlet_code" => $gr->WhsCode,
				"type" => "out",
                "out_type" => "returnsupplier",
				"status" => "completed",
				"supplier_name" =>	(string)$gr->NumAtCard,
				"reference_code" => (string)$gr->U_SOL_SYNC_KEY,
				"products" => $products,
				"tags" => [$gr->U_SOL_SYNC_KEY, $grpo->U_SOL_SYNC_KEY, $pocode]
			];

            $body = json_encode($req);
            $action_url = '/api/v3/CreateTransfer';
            $url = env('ISELLER_HOST') . $action_url;
            $res = \App\Service\IsellerService::iSellerPost($action_url, $body);

            $log = new \App\LogApiCalls;
            $log->related_module = 'trx_goods_returns';
            $log->related_reff_id = $id;
            $log->api_url = $url;
            $log->request_body = $body;
            $log->response_body = json_encode($res['data']);
            $log->response_code = $res['code'];
            $log->save();

			return $log;
        }

		public function getDownload($id)
		{
			$data['gr'] = \App\TrxGoodsReturns::with('items')->find($id);
			$data['store'] = \App\RefWarehouses::where('code', $data['gr']->WhsCode)->first();
			$data['vendor'] = \App\RefBusinessPartner::where('code', $data['gr']->CardCode)->first();

			foreach ($data['gr']->items as $v) {
				$v->item = \App\RefItemMasterData::where("sku", $v->ItemCode)->first();
			}


			// return view("merchandiser/goods_return/download", $data);
			$dompdf = new Dompdf();
			$dompdf->loadHtml(view("merchandiser/goods_return/download", $data)->render());
			$dompdf->setPaper('A3', 'potrait');
			$dompdf->render();
			$dompdf->stream("purchase-goods-return-doc-" . $data['gr']->U_SOL_SYNC_KEY  . ".pdf");

		}

	}
