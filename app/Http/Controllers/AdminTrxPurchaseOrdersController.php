<?php

namespace App\Http\Controllers;

use Session;
use Request;
use DB;
use CRUDBooster;
use Dompdf\Dompdf;

class AdminTrxPurchaseOrdersController extends \crocodicstudio\crudbooster\controllers\CBController
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
		$this->button_add = false;
		$this->button_edit = true;
		$this->button_delete = true;
		$this->button_detail = false;
		$this->button_show = false;
		$this->button_filter = true;
		$this->button_import = false;
		$this->button_export = false;
		$this->table = "trx_purchase_orders";
		$me = \CB::me();

		// Privilege definitions
		$createPrivileges = [1, 7];
		$viewPrivileges = [1, 6, 7, 8];
		$editPrivileges = [1, 7];
		$deletePrivileges = [1, 7];
		$approvePrivileges = [1, 7];

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

		# END CONFIGURATION DO NOT REMOVE THIS LINE

		# START COLUMNS DO NOT REMOVE THIS LINE
		$this->col = [];

		$this->col[] = ["label" => "Doc. No.", "name" => "U_SOL_SYNC_KEY"];
		$this->col[] = ["label" => "Doc. Date", "name" => "DocDate"];
		$this->col[] = ["label" => "Related PR", "name" => "trx_purchase_requests_id", "join" => "trx_purchase_requests,U_SOL_SYNC_KEY"];
		// $this->col[] = ["label"=>"Vendor","name"=>"CardCode"];
		$this->col[] = ["label" => "Warehouse", "name" => "WhsCode", "callback" => function ($r) {
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
		$this->col[] = ["label" => "Verification Status", "name" => "is_verified", "callback" => function ($r) {
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
		$this->col[] = ["label" => "Verified By", "name" => "verified_by_cms_users_id", "join" => "cms_users,name"];
		$this->col[] = ["label" => "Verified At", "name" => "verified_at"];

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

		$this->col[] = ["label" => "Sync Status", "name" => "sync_status", "callback" => function ($r) {
			$color = ['Failed' => 'danger', 'Synced' => 'success'];
			return '<div class="btn btn-xs btn-' . $color[$r->sync_status] . '">' . ucfirst($r->sync_status) . '</div>';
		}];
		$this->col[] = ["label" => "Sync At", "name" => "sync_at", "callback" => function ($r) {
			if ($r->sync_at) {
				return dateformat($r->sync_at);
			}
		}];
		# END COLUMNS DO NOT REMOVE THIS LINE

		# START FORM DO NOT REMOVE THIS LINE

		$createPrivileges = [1, 7];
		$viewPrivileges = [1, 6, 7, 8];
		$editPrivileges = [1, 7];
		$approvePrivileges = [1, 7];
		$myprivilage = \CB::me()->id_cms_privileges;

		// Check if user has view privileges
		if (!in_array($myprivilage, $viewPrivileges)) {
			$is_viewonly = true;
		} else {
			// For edit mode, check edit privileges and ownership/status
			$is_viewonly = false;
			if (!empty(Request::segment(4))) {
				$po = \App\TrxPurchaseOrders::find(Request::segment(4));
				if ($po) {
					// Make approved status view-only for all users
					if ($po->doc_status === 'approved') {
						$is_viewonly = true;
					}
					// Approvers can edit submitted records
					else if ($po->doc_status === 'submited' && in_array($myprivilage, $approvePrivileges)) {
						$is_viewonly = false;
					}
					// Users can only edit their own draft or rejected records
					else if (in_array($po->doc_status, ['draft', 'rejected']) && $po->created_by === $me->id) {
						$is_viewonly = false;
					}
					// All other cases are view-only
					else {
						$is_viewonly = true;
					}
				}
			}
		}

		$this->form = [];

		// Determine if fields should be readonly (all except items and status)
		$fields_readonly = false;
		if (!empty(Request::segment(4))) {
			$po = \App\TrxPurchaseOrders::find(Request::segment(4));
			if ($po && $po->doc_status === 'submited' && in_array($myprivilage, $approvePrivileges)) {
				$fields_readonly = true;
			}
		}

		$this->form[] = ['label' => 'Doc. No.', 'name' => 'U_SOL_SYNC_KEY', 'type' => 'text', 'validation' => 'required|min:1|max:255', 'width' => 'col-sm-10', 'readonly' => true];
		$this->form[] = ['label' => 'Related PR', 'name' => 'trx_purchase_requests_id', 'type' => 'select', 'validation' => 'required|integer|min:0', 'width' => 'col-sm-10', 'datatable' => 'trx_purchase_requests,U_SOL_SYNC_KEY', 'disabled' => true];
		$this->form[] = ['label' => 'Doc. Date', 'name' => 'DocDate', 'type' => 'date', 'validation' => 'required|date', 'width' => 'col-sm-10', 'readonly' => $fields_readonly ?: $is_viewonly];

		$store_option = '';
		$stores = \App\RefWarehouses::get();
		foreach ($stores as $v) {
			$store_option .= $v->code . '|' . $v->name . '[' . ($v->is_store ? 'Store' : 'Non-Store') . '];';
		}
		$this->form[] = ['label' => 'WhsCode', 'name' => 'WhsCode', 'type' => $fields_readonly ? 'select' : 'select2', 'validation' => 'required|min:1|max:255', 'width' => 'col-sm-10', 'dataenum' => $store_option, 'readonly' => $fields_readonly, 'disabled' => $fields_readonly ? false : $is_viewonly];
		$this->form[] = ['label' => 'Comments', 'name' => 'Comments', 'type' => 'textarea', 'validation' => 'required', 'width' => 'col-sm-10', 'readonly' => $fields_readonly ?: $is_viewonly];
		$itemmaster = \App\RefItemMasterData::selectRaw("id,name,sku,unit_of_measurement,vatcode")->whereRaw("(tags like '%NoN-Agrinesia%')")->get();
		$bpmaster = \App\RefBusinessPartner::get();
		$tax_groups = \App\RefTaxGroups::where('tax_type', 'tax_group')->get();
		$map_bpmaster = $bpmaster->keyBy('id');
		$map_itemmaster = [];
		foreach ($itemmaster as $v) {
			$map_itemmaster[$v->sku] = $v;
		}


		$data['itemmaster'] = $itemmaster;
		$data['map_itemmaster'] = $map_itemmaster;
		$data['bpmaster'] = $bpmaster;
		$data['map_bpmaster'] = $map_bpmaster;
		$data['disabled'] = $is_viewonly;
		$data['tax_groups'] = $tax_groups;
		$this->form[] = ['label' => 'Items', 'name' => 'items', 'type' => 'custom', 'validation' => 'required', 'width' => 'col-sm-10', 'html' => view('merchandiser/purchase_order/items_form', $data)->render(), 'disabled' => $is_viewonly];
		// $this->form[] = ['label'=>'Disc. Percent','name'=>'DiscPrcnt','type'=>'money','validation'=>'required|integer|min:0','width'=>'col-sm-10'];
		// $this->form[] = ['label'=>'Disc. Sum','name'=>'DiscSum','type'=>'money','validation'=>'required|integer|min:0','width'=>'col-sm-10'];
		// $this->form[] = ['label'=>'Doc. Total','name'=>'DocTotal','type'=>'money','validation'=>'required|integer|min:0','width'=>'col-sm-10'];
		$me = \CB::me();
		// Only users with approve privileges can approve/reject
		if (in_array($me->id_cms_privileges, $approvePrivileges)) {
			// For new records (add mode) or draft status, show creator options
			if (empty(Request::segment(4))) {
				// Add mode - show creator options
				$status_enum = 'draft|Save as Draft;submited|Submit & Request Approval';
			} else {
				// Edit mode - check document status
				$po = \App\TrxPurchaseOrders::find(Request::segment(4));
				if ($po && in_array($po->doc_status, ['submited', 'approved'])) {
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
		$this->form[] = ['label' => 'Doc. Status', 'name' => 'doc_status', 'type' => $fields_readonly ? 'select' : 'select2', 'validation' => 'required|min:1|max:255', 'width' => 'col-sm-10', 'dataenum' => $status_enum, 'disabled' => $is_viewonly];
		# END FORM DO NOT REMOVE THIS LINE

		# OLD START FORM
		//$this->form = [];
		//$this->form[] = ["label"=>"Trx Purchase Requests Id","name"=>"trx_purchase_requests_id","type"=>"select2","required"=>TRUE,"validation"=>"required|integer|min:0","datatable"=>"trx_purchase_requests,id"];
		//$this->form[] = ["label"=>"Doc Status","name"=>"doc_status","type"=>"text","required"=>TRUE,"validation"=>"required|min:1|max:255"];
		//$this->form[] = ["label"=>"U SOL SYNC KEY","name"=>"U_SOL_SYNC_KEY","type"=>"text","required"=>TRUE,"validation"=>"required|min:1|max:255"];
		//$this->form[] = ["label"=>"CardCode","name"=>"CardCode","type"=>"text","required"=>TRUE,"validation"=>"required|min:1|max:255"];
		//$this->form[] = ["label"=>"DocDate","name"=>"DocDate","type"=>"date","required"=>TRUE,"validation"=>"required|date"];
		//$this->form[] = ["label"=>"WhsCode","name"=>"WhsCode","type"=>"text","required"=>TRUE,"validation"=>"required|min:1|max:255"];
		//$this->form[] = ["label"=>"Comments","name"=>"Comments","type"=>"textarea","required"=>TRUE,"validation"=>"required|string|min:5|max:5000"];
		//$this->form[] = ["label"=>"DiscPrcnt","name"=>"DiscPrcnt","type"=>"money","required"=>TRUE,"validation"=>"required|integer|min:0"];
		//$this->form[] = ["label"=>"DiscSum","name"=>"DiscSum","type"=>"money","required"=>TRUE,"validation"=>"required|integer|min:0"];
		//$this->form[] = ["label"=>"DocTotal","name"=>"DocTotal","type"=>"money","required"=>TRUE,"validation"=>"required|integer|min:0"];
		//$this->form[] = ["label"=>"U SOL REF KEY","name"=>"U_SOL_REF_KEY","type"=>"text","required"=>TRUE,"validation"=>"required|min:1|max:255"];
		//$this->form[] = ["label"=>"U SOL RAV TRID","name"=>"U_SOL_RAV_TRID","type"=>"text","required"=>TRUE,"validation"=>"required|min:1|max:255"];
		//$this->form[] = ["label"=>"Is Verified","name"=>"is_verified","type"=>"radio","required"=>TRUE,"validation"=>"required|integer","dataenum"=>"Array"];
		//$this->form[] = ["label"=>"Verified By Cms Users Id","name"=>"verified_by_cms_users_id","type"=>"select2","required"=>TRUE,"validation"=>"required|integer|min:0","datatable"=>"verified_by_cms_users,id"];
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
		// $this->addaction[] = [
		//     'label' => 'Sync History',
		//     'url' => uri('admin/log_api_calls?related_module=trx_purchase_orders&related_reff_id=[id]'),
		//     'icon' => 'fa fa-refresh',
		//     'color' => 'danger',
		//     // 'showIf' => "[doc_status] == 'submited'"
		// ];
		// $this->addaction[] = [
		//     'label' => 'Try Sync',
		//     'url' => \CB::mainpath() . '/trysync/[id]',
		//     'icon' => 'fa fa-refresh',
		//     'color' => 'info',
		//     // 'showIf' => "[doc_status] == 'submited'"
		// ];

		if (in_array($me->id_cms_privileges, $approvePrivileges)) {
			$this->addaction[] = [
				'label' => 'Verification',
				'url' => \CB::mainpath() . '/verification/[id]',
				'icon' => 'fa fa-check',
				'color' => 'primary',
				'showIf' => "[doc_status] == 'submited'"
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
		// }
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



		$param = [];

		$param[] = ['label' => 'Doc. No.', 'name' => "U_SOL_SYNC_KEY", "type" => "input", "value" => get('U_SOL_SYNC_KEY')];
		$param[] = ['label' => 'Doc. Date', 'name' => "DocDate", "type" => "date", "value" => get('DocDate')];
		$param[] = ['label' => 'Rel. PR', 'name' => "RelPR", "type" => "input", "value" => get('RelPR')];
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
				['value' => 'approved', 'label' => 'Approved'],
			]
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

					$po = \App\TrxPurchaseOrders::find($id);

					if (!$po) {
						$failed_count++;
						$failed_messages[] = "Record ID $id not found";
						DB::rollback();
						continue;
					}

					// Only approve if status is 'submited'
					if ($po->doc_status !== 'submited') {
						$failed_count++;
						$failed_messages[] = "Record {$po->U_SOL_SYNC_KEY} is not in submitted status";
						DB::rollback();
						continue;
					}

					// Update purchase order
					$po->doc_status = 'approved';
					$po->is_verified = 1;
					$po->verified_by_cms_users_id = $me->id;
					$po->verified_at = date("Y-m-d H:i:s");
					$po->approved_by = $me->id;
					$po->save();

					// Handle items breakdown by business partner
					$po_childs = \App\TrxPurchaseOrders::where("parent_id", $po->id)->count();

					if ($po->is_verified == 1 && $po_childs == 0) {
						// Auto close related PR


						// Group items by business partner
						$item_group = [];
						foreach ($po->items as $v) {
							$item_group[$v->ref_business_partners_id][] = $v;
						}

						// Create separate POs for each business partner
						foreach ($item_group as $ref_business_partners_id => $pois) {
							$bp = \App\RefBusinessPartner::find($ref_business_partners_id);
							$newpo = $po->replicate();
							$newpo->push();

							/* generate new po */
							$po_code = 'PO' . date("Ymd") . six_random();
							$is_exist = \App\TrxPurchaseOrders::where("U_SOL_SYNC_KEY", $po_code)->exists();

							while ($is_exist) {
								/* generate new po */
								$po_code = 'PO' . date("Ymd") . six_random();
								$is_exist = \App\TrxPurchaseOrders::where("U_SOL_SYNC_KEY", $po_code)->exists();

							}
							$newpo->U_SOL_SYNC_KEY = $po_code;
							$newpo->U_SOL_REF_KEY = $po_code;
							$newpo->U_SOL_RAV_TRID = $po_code;
							$newpo->CardCode = $bp->code;
							$newpo->is_breaked = 1;
							$newpo->parent_id = $po->id;
							$newpo->save();

							$total = 0;
							foreach ($pois as $v) {
								if ((!isset($v->Quantity) || $v->Quantity == 0 || $v->Quantity == NULL)) {
									continue;
								}
								$po_item = new \App\TrxPurchaseOrdersItems;
								$po_item->trx_purchase_orders_id = $newpo->id;
								$po_item->ItemCode = $v->ItemCode;
								$po_item->Quantity = $v->Quantity;
								$po_item->UomCode = $v->UomCode;
								$po_item->VatGroup = $v->VatGroup;
								$po_item->PriceBefDi = $v->PriceBefDi;
								$po_item->ref_purchase_price_lists_id = $v->ref_purchase_price_lists_id;
								$po_item->ref_business_partners_id = $bp->id;
								$po_item->save();
								$total += $v->Quantity * $v->PriceBefDi;
							}

							$newpo->DocTotal = $total;
							$newpo->save();
							$this->poSync($newpo->id);
							$this->autoClosePR($po->trx_purchase_requests_id);
						}
					}

					$approved_count++;

					// Add delay between syncs (except for the last record)
					if ($index < $total_records - 1) {
						sleep($sync_interval);
					}

					DB::commit();
				} catch (\Exception $e) {
					DB::rollback();
					$failed_count++;
					$failed_messages[] = "Failed to approve {$po->U_SOL_SYNC_KEY}: " . $e->getMessage();
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
		$viewPrivileges = [1, 6, 7, 8];
		$approvePrivileges = [1, 7];

		// Only show records if user has view privileges
//		if (!in_array($me->id_cms_privileges, $viewPrivileges)) {
//			$query->where('1', '0'); // Show no records
//			return;
//		}

		if (in_array($me->id_cms_privileges, $approvePrivileges)) {
			// Approvers can see:
			// 1. All submitted records (for approval)
			// 2. Their own draft and rejected records
			$query->where(function ($q) use ($me) {
				$q->where("trx_purchase_orders.doc_status", "submited")
					->orWhere(function ($subQ) use ($me) {
						$subQ->whereIn("trx_purchase_orders.doc_status", ["draft", "rejected"])
							->where("trx_purchase_orders.created_by", $me->id);
					});
			});
		} else if ($me->id_cms_privileges == 1 || $me->id_cms_privileges == 7) {
			$query->whereIn("trx_purchase_orders.doc_status", ["submited", "draft", "rejected"]);
		} else {
			$query->where("trx_purchase_orders.doc_status", "!=", "approved");
		}


		$cms_users = \App\CmsUsers::with('branches.branch', 'stores.store')->find($me->id);
		if (count($cms_users->stores) > 0) {
			$listcode = [];
			foreach ($cms_users->stores as $v) {
				$listcode[] = $v->store->code;
			}
			$query->whereIn('trx_purchase_orders.WhsCode', $listcode);
		}

		if (get('U_SOL_SYNC_KEY')) {
			$U_SOL_SYNC_KEY = get('U_SOL_SYNC_KEY');
			$query->where('trx_purchase_orders.U_SOL_SYNC_KEY', $U_SOL_SYNC_KEY);
		}

		if (get('DocDate')) {
			$DocDate = get('DocDate');
			$query->where('trx_purchase_orders.DocDate', $DocDate);
		}
		if (get('RelPR')) {
			$RelPRs = get('RelPR');
			$PR = \App\TrxPurchaseRequests::where('U_SOL_SYNC_KEY', $RelPRs)->first();
			$RelPR = $PR->id;
			$query->where('trx_purchase_orders.trx_purchase_requests_id', $RelPR);
		}

		if (get('Branch')) {
			$Branch = get('Branch');
			$query->where('trx_purchase_orders.Branch',  $Branch);
		}

		if (get('WhsCode')) {
			$WhsCode = get('WhsCode');
			$query->where('trx_purchase_orders.WhsCode',  $WhsCode);
		}

		if (get('doc_status')) {
			$doc_status = get('doc_status');
			$query->where('trx_purchase_orders.doc_status',  $doc_status);
		}

		if (get('ver_status')) {
			$ver_status = get('ver_status');
			$query->where('trx_purchase_orders.is_verified',  $ver_status);
		}

        if (get('is_verified')) {
            $is_verified = get('is_verified');
            if ($is_verified == 'waiting') { $is_verified = 0; }
            elseif ($is_verified == 'verified') { $is_verified = 1; }
            elseif ($is_verified == 'rejected') { $is_verified = 2; }
            $query->where('trx_purchase_orders.is_verified', $is_verified);
        }

        if (get('sync_status')) {
            $sync_status = get('sync_status');
            if ($sync_status == 'not_yet') { $sync_status = null; }
            $query->where('trx_purchase_orders.sync_status', $sync_status);
        }



		$query->orderBy('trx_purchase_orders.id', 'desc');
	}

	/*
	    | ----------------------------------------------------------------------
	    | Hook for manipulate row of index table html
	    | ----------------------------------------------------------------------
	    |
	    */
	public function hook_row_index($column_index, &$column_value)
	{
		//Your code here
	}

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
	public function hook_after_add($id)
	{
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
	// public function hook_before_edit(&$postdata, $id)
	// {
	// 	//Your code here
	// 	// dd($postdata);

	// 	/* delete previous items */

	// 	// dd($postdata);
	// 	$items = \App\TrxPurchaseOrdersItems::where('trx_purchase_orders_id', $id)->get();
	// 	foreach ($items as $v) {
	// 		$v->delete();
	// 	}

	// 	if (!empty($postdata['items']['name'])) {

	// 		foreach ($postdata['items']['name'] as $k => $item_code) {
	// 			if ((!isset($postdata['items']['Quantity'][$k]) || $postdata['items']['Quantity'][$k] == 0 || $postdata['items']['Quantity'][$k] == NULL)) {
	// 				// if ((!isset($postdata['items']['Quantity'][$k]) || $postdata['items']['Quantity'][$k] == 0 || $postdata['items']['Quantity'][$k] == NULL) && $postdata->doc_status == 'submited') {
	// 				continue;
	// 			}
	// 			$po_item = new \App\TrxPurchaseOrdersItems;
	// 			$po_item->trx_purchase_orders_id = $id;
	// 			$po_item->ItemCode = $item_code;
	// 			$po_item->Quantity = $postdata['items']['Quantity'][$k];
	// 			$po_item->PriceBefDi = $postdata['items']['PriceBefDi'][$k];
	// 			$po_item->VatGroup = 'VATI11';
	// 			// hard code VATI11
	// 			// $po_item->VatGroup = $postdata['items']['VatGroup'][$k];
	// 			$po_item->ref_business_partners_id = $postdata['items']['ref_business_partners_id'][$k];
	// 			$po_item->save();
	// 		}
	// 	}

	// 	unset($postdata['items']);

	// 	if ($postdata['doc_status'] == 'approved') {
	// 		$me = \CB::me();
	// 		$postdata['is_verified'] = 1;
	// 		$postdata['verified_by_cms_users_id'] = $me->id;
	// 		$postdata['verified_at'] = date("Y-m-d H:i:s");
	// 	} else {
	// 		$postdata['is_verified'] = 0;
	// 	}
	// }


	public function hook_before_edit(&$postdata, $id)
	{
		/* delete previous items */
		$items = \App\TrxPurchaseOrdersItems::where('trx_purchase_orders_id', $id)->get();
		foreach ($items as $v) {
			$v->delete();
		}

		$total = 0;

		if (!empty($postdata['items']['name'])) {

			foreach ($postdata['items']['name'] as $k => $item_code) {
				if ((!isset($postdata['items']['Quantity'][$k]) || $postdata['items']['Quantity'][$k] == 0 || $postdata['items']['Quantity'][$k] == NULL)) {
					continue;
				}
				$po_item = new \App\TrxPurchaseOrdersItems;
				$po_item->trx_purchase_orders_id = $id;
				$po_item->ItemCode = $item_code;
				$po_item->Quantity = $postdata['items']['Quantity'][$k];
				$po_item->PriceBefDi = $postdata['items']['PriceBefDi'][$k];
				$po_item->VatGroup = 'VATI11';
				// hard code VATI11
				// $po_item->VatGroup = $postdata['items']['VatGroup'][$k];
				$po_item->ref_business_partners_id = $postdata['items']['ref_business_partners_id'][$k];
				$po_item->save();

				$total += $po_item->Quantity * $po_item->PriceBefDi;
			}
		}

		$postdata['DocTotal'] = $total;
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
			try {
			DB::beginTransaction();

			$po = \App\TrxPurchaseOrders::find($id);

			// Get the submitted items data using the global helper
			$items = request('items');

			// If items are submitted, process them
			if (isset($items['name'])) {
				// Delete existing items for this PO to replace them with the new submission
				\App\TrxPurchaseOrdersItems::where('trx_purchase_orders_id', $id)->delete();

				$total = 0;
				$itemCount = count($items['name']);
				for ($i = 0; $i < $itemCount; $i++) {
					// Skip if quantity is zero or null
					if (empty($items['Quantity'][$i])) {
						continue;
					}

					$po_item = new \App\TrxPurchaseOrdersItems;
					$po_item->trx_purchase_orders_id = $id;
					$po_item->ItemCode = $items['name'][$i];
					$po_item->ref_business_partners_id = $items['ref_business_partners_id'][$i];
					$po_item->Quantity = $items['Quantity'][$i];
					$po_item->PriceBefDi = $items['PriceBefDi'][$i];
					$po_item->VatGroup = $items['VatGroup'][$i] ?? null;

					$item_master = \App\RefItemMasterData::where('sku', $items['name'][$i])->first();
					if ($item_master) {
						$po_item->UomCode = $item_master->unit_of_measurement;
					}

					$po_item->save();

					$total += $items['Quantity'][$i] * $items['PriceBefDi'][$i];
				}

				// Update the PO's DocTotal
				$po->DocTotal = $total;
				$po->save();
			}

			// Reload the PO and its items relationship for the subsequent logic
			$po = \App\TrxPurchaseOrders::find($id);


			$po_childs = \App\TrxPurchaseOrders::where("parent_id", $po->id)->count();



			if ($po->is_verified == 1 && $po_childs == 0) {
				// Group items by business partner

				$item_group = [];
				foreach ($po->items as $v) {
					$item_group[$v->ref_business_partners_id][] = $v;
				}

				// Copy PO for each business partner
				foreach ($item_group as $ref_business_partners_id => $pois) {
					$bp = \App\RefBusinessPartner::find($ref_business_partners_id);
					$newpo = $po->replicate();
					$newpo->push();

					/* generate new po */
					$po_code = 'PO' . date("Ymd") . six_random();
					$is_exist = \App\TrxPurchaseOrders::where("U_SOL_SYNC_KEY", $po_code)->exists();

					while ($is_exist) {
						/* generate new po */
						$po_code = 'PO' . date("Ymd") . six_random();
						$is_exist = \App\TrxPurchaseOrders::where("U_SOL_SYNC_KEY", $po_code)->exists();

					}

					$newpo->U_SOL_SYNC_KEY = $po_code;
					$newpo->U_SOL_REF_KEY = $po_code;
					$newpo->U_SOL_RAV_TRID = $po_code;
					$newpo->CardCode = $bp->code;
					$newpo->is_breaked = 1;
					$newpo->parent_id = $po->id;
					$newpo->save();

					$total = 0;
					foreach ($pois as $v) {
						if ((!isset($v->Quantity) || $v->Quantity == 0 || $v->Quantity == NULL)) {
							continue;
						}
						$po_item = new \App\TrxPurchaseOrdersItems;
						$po_item->trx_purchase_orders_id = $newpo->id;
						$po_item->ItemCode = $v->ItemCode;
						$po_item->Quantity = $v->Quantity;
						$po_item->UomCode = $v->UomCode;
						$po_item->VatGroup = $v->VatGroup;
						$po_item->PriceBefDi = $v->PriceBefDi;
						$po_item->ref_purchase_price_lists_id = $v->ref_purchase_price_lists_id;
						$po_item->ref_business_partners_id = $bp->id;
						$po_item->save();
						$total += $v->Quantity * $v->PriceBefDi;
					}

					$newpo->DocTotal = $total;
					$newpo->save();
					$this->poSync($newpo->id);
				}

				// Move autoClosePR call here - after all child POs are created and synced
				$this->autoClosePR($po->trx_purchase_requests_id);
			}

			DB::commit();
		} catch (\Exception $e) {
			DB::rollback();

			// dd($e);

			return json([
				'code' => 500,
				'message' => 'Major transaction error, data rolled back',
				'error' => $e
			]);
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
		$deletePrivileges = [1, 7];

		// Check if user has delete privileges
		if (!in_array($me->id_cms_privileges, $deletePrivileges)) {
			\CB::redirect(CRUDBooster::mainpath(), "You don't have permission to delete this record!", "warning");
			exit;
		}

		// Check if user created this record
		$po = \App\TrxPurchaseOrders::find($id);
		if (!$po || $po->created_by !== $me->id) {
			\CB::redirect(CRUDBooster::mainpath(), "You can only delete records you created!", "warning");
			exit;
		}

		// Check if status allows deletion (only draft or rejected)
		if (!in_array($po->doc_status, ['draft', 'rejected'])) {
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




public function autoClosePR($pr_id)
{
    try {
        $pr = \App\TrxPurchaseRequests::find($pr_id);
        if (!$pr) {

            return false;
        }

        // Get PR items
        $pritems = \App\TrxPurchaseRequestsItems::where("trx_purchase_requests_id", $pr_id)->get();
        if ($pritems->isEmpty()) {

            return false;
        }




        // Log all PR items first
        foreach ($pritems as $pritem) {
            $itemMaster = \App\RefItemMasterData::find($pritem->ref_item_master_datas_id);
            $sku = $itemMaster ? trim($itemMaster->sku) : 'NO_SKU';

        }

        // Get verified POs for this PR
        $verifiedPOs = \App\TrxPurchaseOrders::where("trx_purchase_requests_id", $pr_id)
                ->where("is_verified", 1)
                ->get();



        // Separate parent and child POs using Collection filter methods
        $parentPOs = $verifiedPOs->filter(function($po) {
            return is_null($po->parent_id);
        });
        $childPOs = $verifiedPOs->filter(function($po) {
            return !is_null($po->parent_id);
        });



        // Log all POs for debugging
        foreach ($verifiedPOs as $po) {
            $type = is_null($po->parent_id) ? 'PARENT' : 'CHILD';

        }

        // Determine which POs to count items from
        $poIdsToCount = [];
        if ($childPOs->count() > 0) {
            // If there are child POs, count only child PO items
            $poIdsToCount = $childPOs->pluck('id')->toArray();

        } else {
            // If no child POs, count parent PO items (but only the latest version)
            $poIdsToCount = $parentPOs->pluck('id')->toArray();

        }

        if (empty($poIdsToCount)) {

            return false;
        }

        // Get items from the determined POs
        $poItems = \App\TrxPurchaseOrdersItems::whereIn('trx_purchase_orders_id', $poIdsToCount)->get();



        // Log all PO items
        foreach ($poItems as $item) {
            $poType = $verifiedPOs->firstWhere('id', $item->trx_purchase_orders_id);
            $type = $poType && is_null($poType->parent_id) ? 'PARENT' : 'CHILD';

        }

        // Build quantity map from PO items - FIXED CALCULATION
        $poQuantityMap = [];
        foreach ($poItems as $item) {
            $itemCode = trim($item->ItemCode);
            if (empty($itemCode)) {

                continue;
            }

            // Initialize if not exists
            if (!isset($poQuantityMap[$itemCode])) {
                $poQuantityMap[$itemCode] = 0;
            }

            // Add the quantity
            $itemQty = (float)$item->Quantity;
            $poQuantityMap[$itemCode] += $itemQty;


        }



        // Check if all PR items are fulfilled
        $allFulfilled = true;
        $fulfillmentDetails = [];

        foreach ($pritems as $pritem) {
            $itemMaster = \App\RefItemMasterData::find($pritem->ref_item_master_datas_id);
            if (!$itemMaster || empty(trim($itemMaster->sku))) {

                $allFulfilled = false;
                $fulfillmentDetails[] = "PR Item {$pritem->id}: NO VALID SKU - NOT FULFILLED";
                continue;
            }

            $sku = trim($itemMaster->sku);
            $prQty = (float)$pritem->qty;
            $poQty = isset($poQuantityMap[$sku]) ? (float)$poQuantityMap[$sku] : 0.0;
            $remaining = $prQty - $poQty;



            if ($remaining > 0.0001) { // Small epsilon for float comparison
                $allFulfilled = false;
                $fulfillmentDetails[] = "SKU {$sku}: REMAINING {$remaining} (PR: {$prQty}, PO: {$poQty})";

            } else {
                $fulfillmentDetails[] = "SKU {$sku}: FULFILLED (PR: {$prQty}, PO: {$poQty})";

            }
        }




        if ($allFulfilled) {
            $pr->is_closed = 1;
            $pr->closed_at = now();
            $pr->save();

            return true;
        } else {

            return false;
        }

    } catch (\Exception $e) {


        return false;
    }
}



	//By the way, you can still create your own method in here... :)
	public function getTrysync($id)
	{
		$this->poSync($id);

		return redirect(\CB::mainpath());
	}

	public function poSync($id)
	{
		$me = \CB::me();
		$po = \App\TrxPurchaseOrders::with('items')->find($id);
		$pr = \App\TrxPurchaseRequests::find($po->trx_purchase_requests_id);

		$url = env('SAP_HOST') . '/api/transaction/PostPurchaseOrder';
		$detail = [];
		$total = 0;
		foreach ($po->items as $v) {
			$prod = \App\RefItemMasterData::where("sku", $v->ItemCode)->first();
			$uom = \App\RefUoms::where("code", strtoupper($prod->unit_of_measurement))->first();
			$detail[] = [
				"ItemCode" => $v->ItemCode,
				"Quantity" => $v->Quantity,
				"PriceBefDi" => $v->PriceBefDi,
				"VatGroup" => $v->VatGroup,
				"UomCode" => $uom->abs_entry ? $uom->abs_entry : 1
			];

			$tax = \App\RefTaxGroups::where('code', $v->VatGroup)->first();
			$raiso_pajax = 0;
			if (!empty($tax)) {
			$raiso_pajax = $tax->rate / 100;
			}

			$total_item = ($v->Quantity * $v->PriceBefDi);
			$total_item_pajak = ($v->Quantity * $v->PriceBefDi) * $raiso_pajax;
			$total += ($total_item + $total_item_pajak);
		}

		$grand_total = $total - $po->DiscSum;
		$grand_total = round($grand_total, 2);

		$req = [
			"U_SOL_SYNC_KEY" => $po->U_SOL_SYNC_KEY,
			"CardCode" => $po->CardCode,
			"DocDate" => $po->DocDate,
			"WhsCode" => $po->WhsCode,
			"Comments" => $po->Comments,
			"DiscPrcnt" => $po->DiscPrcnt,
			"DiscSum" => $po->DiscSum,
			"DocTotal" => $grand_total,
			"U_SOL_REF_KEY" => $pr->U_SOL_SYNC_KEY,
			"U_SOL_RAV_TRID" => $po->U_SOL_SYNC_KEY,
			"detail" => $detail,
		];

		$body = json_encode($req);
		$res = \App\Service\SapService::post($url, $body, true);


		if ($po->sync_status != 'Synced') {
			if ($res['code'] == 200 && $res['data']['Data']['Data'] == "Success" && empty($res['data']['Error'])) {
				$po->sync_status = 'Synced';
				$po->api_try = $po->api_try + 1;
				$po->sync_at = date("Y-m-d H:i:s");
				$po->save();
			} else {
				if ($po->sync_status === null) {
					$po->sync_status = 'Failed';
					$po->api_try = $po->api_try + 1;
					$po->save();
				}
			}
		}

		$log = new \App\LogApiCalls;
		$log->related_module = 'trx_purchase_orders';
		$log->related_reff_id = $id;
		if (env('SAP_LIVE', false)) {
            $url .= 'Live';
        }
		$log->api_url = $url;
		$log->request_body = $body;
		$log->response_body = json_encode($res['data']);
		$log->response_code = $res['code'];
		$log->save();
	}

	public function getVerification($id)
	{
		return redirect(\CB::mainpath() . '/edit/' . $id);
	}

	public function getDownload($id)
	{
		$data['po'] = \App\TrxPurchaseOrders::with('items')->find($id);
		$data['store'] = \App\RefWarehouses::where('code', $data['po']->WhsCode)->first();
		$data['vendor'] = \App\RefBusinessPartner::where('code', $data['po']->CardCode)->first();
		$data['pr'] = \App\TrxPurchaseRequests::find($data['po']->trx_purchase_requests_id);
		$tax = \App\RefTaxGroups::where('code', $data['vendor']->vatcode)->first();

		$raiso_pajax = 1;
		$rate_percent = 0;
		if (!empty($tax)) {
			$raiso_pajax = ($tax->rate + 100) / 100;
			$rate_percent = $tax->rate / 100;
		}

		$total = 0;
		$total_pajak = 0;
		foreach ($data['po']->items as $v) {
			$v->item = \App\RefItemMasterData::where("sku", $v->ItemCode)->first();
			$total += $v->PriceBefDi * $v->Quantity * $raiso_pajax;
			$total += $v->PriceBefDi * $v->Quantity;
			$total_pajak += $v->PriceBefDi * $v->Quantity * $rate_percent;
		}
		if ($data['po']->DocTotal != $total) {
			$data['po']->DocTotal = $total;
			$data['po']->save();
			$data['po']->total_pajak = $total_pajak;
			$data['po']->total_pajak = 0;
		}
		// dd($data);
		// return view("merchandiser/purchase_order/download", $data);
		$dompdf = new Dompdf();
		$dompdf->loadHtml(view("merchandiser/purchase_order/download", $data)->render());
		$dompdf->setPaper('A3', 'potrait');
		$dompdf->render();
		$dompdf->stream("purchase-order-doc-" . $data['po']->U_SOL_SYNC_KEY . ".pdf");
	}
}
