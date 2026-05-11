<?php

namespace App\Http\Controllers;

use Session;
use Request;
use DB;
use CRUDBooster;
use Dompdf\Dompdf;

class AdminTrxPurchaseOrdersApprovedController extends \crocodicstudio\crudbooster\controllers\CBController
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

		if ($me->id_cms_privileges != 1) {
			$this->button_add = false;
			$this->button_delete = false;
		}
		# END CONFIGURATION DO NOT REMOVE THIS LINE

		# START COLUMNS DO NOT REMOVE THIS LINE
		$this->col = [];

		$this->col[] = ["label" => "Doc. No.", "name" => "U_SOL_SYNC_KEY"];
		$this->col[] = ["label" => "Parent Doc. No.", "name" => "parent_id", "callback" => function ($r) {
			$po = \App\TrxPurchaseOrders::find($r->parent_id);
			return '<a class="btn btn-xs btn-default" href="' . uri('admin/trx_purchase_orders/edit/' . $po->id) . '">' . ($po->U_SOL_SYNC_KEY) . '</a>';
		}];
		$this->col[] = ["label" => "Doc. Date", "name" => "DocDate"];
		$this->col[] = ["label" => "Related PR", "name" => "trx_purchase_requests_id", "join" => "trx_purchase_requests,U_SOL_SYNC_KEY"];
		// $this->col[] = ["label"=>"Vendor","name"=>"CardCode"];
		$this->col[] = ["label" => "Branch", "name" => "trx_purchase_requests_id", "callback" => function ($r) {
			$pr  = \App\TrxPurchaseRequests::find($r->trx_purchase_requests_id);
			$branch = \App\RefBranches::find($pr->Branch);
			return $branch->name . ' [' . ($branch->code) . ']';
		}];
		$this->col[] = ["label" => "Vendor", "name" => "CardCode", "callback" => function ($r) {
			$stores  = \App\RefBusinessPartner::where('code', $r->CardCode)->first();
			return $stores->name . ' [' . ($stores->code) . ']';
		}];
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
		$this->col[] = ["label" => "Verified At", "name" => "verified_at"];

		$this->col[] = ["label" => "Created By", "name" => "created_by", "callback" => function ($r) {
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
		$this->col[] = ["label" => "Try Sync", "name" => "api_try"];
		// add col to show it the

		$this->col[] = ["label" => "Fulfill Status", "name" => "id", "callback" => function ($r) {
			$poitems = \App\TrxPurchaseOrdersItems::where('trx_purchase_orders_id', $r->id)->get();
			$grpos = \App\TrxGoodsReceipts::where('trx_purchase_orders_id', $r->id)->where('doc_status', 'approved')->get();
			$grpoitems = [];
			foreach ($grpos as $v) {
				$grpoitems = array_merge($grpoitems, \App\TrxGoodsReceiptItems::where('trx_goods_receipts_id', $v->id)->get()->toArray());
			}

			if ($poitems->isEmpty()) {
				return '<div class="btn btn-xs btn-warning">No Items</div>';
			}

			$totalItems = $poitems->count();
			$fulfilledItems = 0;
			$partiallyFulfilledItems = 0;

			foreach ($poitems as $v) {
				$fulfill = 0;
				foreach ($grpoitems as $gritem) {
					if ($v->ItemCode == $gritem['ItemCode']) {
						$fulfill += $gritem['Quantity'];
					}
				}

				if ($fulfill >= $v->Quantity) {
					$fulfilledItems++;
				} else if ($fulfill > 0) {
					$partiallyFulfilledItems++;
				}
			}

			if ($fulfilledItems == $totalItems) {
				return '<div class="btn btn-xs btn-success">Fulfilled</div>';
			} else if ($fulfilledItems > 0 || $partiallyFulfilledItems > 0) {
				return '<div class="btn btn-xs btn-warning">Partially Fulfilled</div>';
			} else {
				return '<div class="btn btn-xs btn-danger">Not Fulfilled</div>';
			}
		}];

		# END COLUMNS DO NOT REMOVE THIS LINE

		# START FORM DO NOT REMOVE THIS LINE
		$this->form = [];

		$this->form[] = ['label' => 'Doc. No.', 'name' => 'U_SOL_SYNC_KEY', 'type' => 'text', 'validation' => 'required|min:1|max:255', 'width' => 'col-sm-10', 'readonly' => true];
		$this->form[] = ['label' => 'Related PR', 'name' => 'trx_purchase_requests_id', 'type' => 'select', 'validation' => 'required|integer|min:0', 'width' => 'col-sm-10', 'datatable' => 'trx_purchase_requests,U_SOL_SYNC_KEY', 'readonly' => true];
		$this->form[] = ['label' => 'Doc. Date', 'name' => 'DocDate', 'type' => 'date', 'validation' => 'required|date', 'width' => 'col-sm-10'];


		$vendoroption = '';
		$bps = \App\RefBusinessPartner::where('type', 'vendor')->get()->map(function ($r) {
			return $r->code . '|' . $r->name . ' [' . $r->code . ']';
		})->toArray();
		$vendoroption = join(';', $bps);
		$this->form[] = ['label' => 'Vendor', 'name' => 'CardCode', 'type' => 'select', 'validation' => 'required|min:1|max:255', 'width' => 'col-sm-10', 'dataenum' => $vendoroption, "readonly" => true];

		$store_option = '';
		$stores = \App\RefWarehouses::get();
		foreach ($stores as $v) {
			$store_option .= $v->code . '|' . $v->name . '[' . ($v->is_store ? 'Store' : 'Non-Store') . '];';
		}
		$this->form[] = ['label' => 'WhsCode', 'name' => 'WhsCode', 'type' => 'select', 'validation' => 'required|min:1|max:255', 'width' => 'col-sm-10', 'dataenum' => $store_option, "readonly" => true];
		$this->form[] = ['label' => 'Comments', 'name' => 'Comments', 'type' => 'textarea', 'validation' => 'required|string|min:5|max:5000', 'width' => 'col-sm-10', "readonly" => true];
		$itemmaster = \App\RefItemMasterData::selectRaw("id,name,sku,unit_of_measurement")->whereRaw("(tags like '%NoN-Agrinesia%')")
			->orderBy("name")->get()->map(function ($r) {
				$r->name = slug($r->name);
				$r->name = str_replace("-", " ", $r->name);
				$r->name = strtoupper($r->name);
				return $r;
			});
		$bpmaster = \App\RefBusinessPartner::get();
		$tax_groups = \App\RefTaxGroups::where('tax_type', 'tax_group')->get();
		$map_itemmaster = [];
		foreach ($itemmaster as $v) {
			$map_itemmaster[$v->sku] = $v;
		}
		$data['itemmaster'] = $itemmaster;
		$data['map_itemmaster'] = $map_itemmaster;
		$data['bpmaster'] = $bpmaster;
		$data['readonly'] = true;
		$data['disabled'] = true;
		$data['hidepoed'] = true;
		$data['tax_groups'] = $tax_groups;
		$this->form[] = ['label' => 'Items', 'name' => 'items', 'type' => 'custom', 'validation' => 'required', 'width' => 'col-sm-10', 'html' => view('merchandiser/purchase_order/items_form', $data)->render()];
		// $this->form[] = ['label'=>'Disc. Percent','name'=>'DiscPrcnt','type'=>'money','validation'=>'required|integer|min:0','width'=>'col-sm-10'];
		// $this->form[] = ['label'=>'Disc. Sum','name'=>'DiscSum','type'=>'money','validation'=>'required|integer|min:0','width'=>'col-sm-10'];
		// $this->form[] = ['label'=>'Doc. Total','name'=>'DocTotal','type'=>'money','validation'=>'required|integer|min:0','width'=>'col-sm-10'];
		$me = \CB::me();
		if ($me->id_cms_privileges == 5) {
			$status_enum = 'rejected|Rejected;approved|Approved';
		} else {
			$status_enum = 'draft|Save as Draft;submited|Submit and Sync';
		}
		// $this->form[] = ['label'=>'Doc. Status','name'=>'doc_status','type'=>'select2','validation'=>'required|min:1|max:255','width'=>'col-sm-10', 'dataenum' => $status_enum];
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
		//$this->form[] = ["label"=>"Verified At","name"=>"verified_at","type"=>"datetime","required"=>TRUE,"validation"=>"required|date_format:Y-m-d H:i:s"];
		//$this->form[] = ["label"=>"Is Breaked","name"=>"is_breaked","type"=>"radio","required"=>TRUE,"validation"=>"required|integer","dataenum"=>"Array"];
		//$this->form[] = ["label"=>"Parent Id","name"=>"parent_id","type"=>"select2","required"=>TRUE,"validation"=>"required|integer|min:0","datatable"=>"parent,id"];
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

        $crudPrivileges = [1, 6, 7, 8];
        $approvePrivileges = [1, 7];
		$this->addaction = array();
        if (in_array($me->id_cms_privileges, $crudPrivileges)) {
            $this->addaction[] = [
                'label' => 'Sync History',
                'url' => uri('admin/log_api_calls?related_module=trx_purchase_orders&related_reff_id=[id]'),
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
            $this->addaction[] = [
                'label' => 'Good Receipt (Accept)',
                'url' => \CB::mainpath() . '/greceipt/[id]',
                'icon' => 'fa fa-files-o',
                'color' => 'primary',
                'showIf' => "[doc_status] == 'approved' && [sync_status] != 'Failed'"
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

		// $this->addaction[] = [
		//     'label' => 'Good Return (Reject)',
		//     'url' => \CB::mainpath() . '/greturn/[id]',
		//     'icon' => 'fa fa-files-o',
		//     'color' => 'warning',
		//     // 'showIf' => "[doc_status] == 'submited'"
		// ];

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
		$param[] = ['label' => 'Parent Doc. No.', 'name' => "ParentDocNo", "type" => "input", "value" => get('ParentDocNo')];
		$param[] = ['label' => 'Doc. Date', 'name' => "DocDate", "type" => "date", "value" => get('DocDate')];
		$param[] = ['label' => 'Rel. PR', 'name' => "RelPR", "type" => "input", "value" => get('RelPR')];
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

        $param[] = [
            'label' => 'Fulfill Status',
            'name' => "fulfill_status",
            "type" => "select",
            "value" => get('fulfill_status'),
            'options' => [
                ['value' => 'not_fulfilled', 'label' => 'Not Fulfilled'],
                ['value' => 'partially_fulfilled', 'label' => 'Partially Fulfilled'],
                ['value' => 'fulfilled', 'label' => 'Fulfilled'],
                ['value' => 'no_items', 'label' => 'No Items'],
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
			$cms_users = \App\CmsUsers::with('branches.branch', 'stores.store')->find($me->id);
			if (count($cms_users->stores) > 0) {
				$listcode = [];
				foreach($cms_users->stores as $v) {
					$listcode[] = $v->store->code;
				}
				$query->whereIn('trx_purchase_orders.WhsCode', $listcode);
			}

	        $query->whereRaw("(parent_id is not null)")->orderBy('trx_purchase_orders.id', 'desc');

			if (get('U_SOL_SYNC_KEY')) {
				$U_SOL_SYNC_KEY = get('U_SOL_SYNC_KEY');
				$query->where('trx_purchase_orders.U_SOL_SYNC_KEY', $U_SOL_SYNC_KEY);
			}

			if (get('CardCode')) {
				$CardCode = get('CardCode');
				$query->where('trx_purchase_orders.CardCode', $CardCode);
			}

			if (get('DocDate')) {
				$DocDate = get('DocDate');
				$query->where('trx_purchase_orders.DocDate', $DocDate);
			}

			if (get('ParentDocNo')) {
				$ParentDocNos = get('ParentDocNo');
				$PR = \App\TrxPurchaseOrders::where('U_SOL_SYNC_KEY', $ParentDocNos)->first();
				$ParentDocNo = $PR->id;
				$query->where('trx_purchase_orders.parent_id', $ParentDocNo);
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

            if (get('fulfill_status')) {
                $fulfill_status = get('fulfill_status');

                $filtered_po_ids = [];

                $all_pos = \App\TrxPurchaseOrders::select('id')->get();

                foreach ($all_pos as $po) {
                    $poitems = \App\TrxPurchaseOrdersItems::where('trx_purchase_orders_id', $po->id)->get();
                    $grpos = \App\TrxGoodsReceipts::where('trx_purchase_orders_id', $po->id)->where('doc_status', 'approved')->get();
                    $grpoitems = [];
                    foreach ($grpos as $v) {
                        $grpoitems = array_merge($grpoitems, \App\TrxGoodsReceiptItems::where('trx_goods_receipts_id', $v->id)->get()->toArray());
                    }

                    if ($poitems->isEmpty()) {
                        $current_status = 'no_items';
                    } else {
                        $totalItems = $poitems->count();
                        $fulfilledItems = 0;
                        $partiallyFulfilledItems = 0;

                        foreach ($poitems as $v) {
                            $fulfill = 0;
                            foreach ($grpoitems as $gritem) {
                                if ($v->ItemCode == $gritem['ItemCode']) {
                                    $fulfill += $gritem['Quantity'];
                                }
                            }

                            if ($fulfill >= $v->Quantity) {
                                $fulfilledItems++;
                            } else if ($fulfill > 0) {
                                $partiallyFulfilledItems++;
                            }
                        }

                        if ($fulfilledItems == $totalItems) {
                            $current_status = 'fulfilled';
                        } else if ($fulfilledItems > 0 || $partiallyFulfilledItems > 0) {
                            $current_status = 'partially_fulfilled';
                        } else {
                            $current_status = 'not_fulfilled';
                        }
                    }

                    // Check if this PO matches the filter criteria
                    if ($current_status === $fulfill_status) {
                        $filtered_po_ids[] = $po->id;
                    }
                }

                // Apply the filter to the query
                if (!empty($filtered_po_ids)) {
                    $query->whereIn('trx_purchase_orders.id', $filtered_po_ids);
                } else {
                    // If no records match the filter, show no results
                    $query->where('trx_purchase_orders.id', 0);
                }
            }



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
	public function hook_before_edit(&$postdata, $id)
	{
		//Your code here

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



	//By the way, you can still create your own method in here... :)
    public function getMarksynced($id)
    {
        try {
            $data = \App\TrxPurchaseOrders::find($id);

            if ($data) {
                $data->sync_status = 'Synced';
                $data->sync_at = date('Y-m-d H:i:s');
                $data->save();

                \CB::redirect(\CB::mainpath(), "Data berhasil disinkronkan!", "success");
            } else {
                \CB::redirect(\CB::mainpath(), "Data tidak ditemukan!", "warning");
            }
        } catch (\Exception $e) {
            \Log::error("Error syncing purchase order: " . $e->getMessage());

            \CB::redirect(\CB::mainpath(), "Terjadi kesalahan saat menyinkronkan data.", "danger");
        }
    }

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

	public function getGreceipt($id)
	{
		$po = \App\TrxPurchaseOrders::with("items")->find($id);
		$pr = \App\TrxPurchaseRequests::find($po->trx_purchase_requests_id);
		$bp = \App\RefBusinessPartner::find($po->items[0]->ref_business_partners_id);
		$gr = \App\TrxGoodsReceipts::where("trx_purchase_orders_id", $po->id)->where("doc_status", "approved")->get();

		// Get all GR items for this PO
		$gr_ids = $gr->pluck('id')->toArray();
		$gr_items = collect();
		if (!empty($gr_ids)) {
			$gr_items = \App\TrxGoodsReceiptItems::whereIn('trx_goods_receipts_id', $gr_ids)->get();
		}

		// Sum GR items qty by ItemCode
		$gr_qty_by_item = [];
		foreach ($gr_items as $item) {
			if (!isset($gr_qty_by_item[$item->ItemCode])) {
				$gr_qty_by_item[$item->ItemCode] = 0;
			}
			$gr_qty_by_item[$item->ItemCode] += $item->Quantity;
		}

		// Sum PO items qty by ItemCode
		$po_qty_by_item = [];
		foreach ($po->items as $item) {
			if (!isset($po_qty_by_item[$item->ItemCode])) {
				$po_qty_by_item[$item->ItemCode] = 0;
			}
			$po_qty_by_item[$item->ItemCode] += $item->Quantity;
		}

		// Check if all PO item qtys are fulfilled by GRs
		$all_fulfilled = true;
		foreach ($po_qty_by_item as $itemCode => $qty) {
			if (!isset($gr_qty_by_item[$itemCode]) || $gr_qty_by_item[$itemCode] < $qty) {
				$all_fulfilled = false;
				break;
			}
		}

		if ($all_fulfilled && count($gr) > 0) {
			echo '<script>alert("Goods Receipt already fulfilled");  setTimeout(() => history.back(), 1500); </script>';
			exit();
		}

		$grpo_exist = \App\TrxGoodsReceipts::where('trx_purchase_orders_id', $id)->whereNotIn('doc_status', ['approved', 'rejected'])->first();


		if (!empty($grpo_exist)) {
			return redirect(uri('admin/trx_goods_receipts/edit/' . $grpo_exist->id));
		}




		try {
			DB::beginTransaction();
			// $grpo = \App\TrxGoodsReceipts::where("trx_purchase_orders_id", $po->id)->first();
			// if (empty($grpo)) {
			// }
			$grpo = new \App\TrxGoodsReceipts;
			$grpo->trx_purchase_orders_id = $po->id;
			$grpo->U_SOL_SYNC_KEY = 'GRPO' . date('Ymd') . six_random();
			$grpo->CardCode = $bp->code;
			$grpo->DocDate = date("Y-m-d");
			$grpo->NumAtCard = $bp->name;
			$grpo->WhsCode = $po->WhsCode;
			$grpo->Comments = $po->Comments;
			$grpo->U_SOL_REF_KEY = $pr->U_SOL_SYNC_KEY;
			$grpo->U_SOL_RAV_TRID = $grpo->U_SOL_SYNC_KEY;
			$grpo->doc_status = 'draft';
			$grpo->created_by = \CB::me()->id;
			$grpo->save();

			$items = \App\TrxGoodsReceiptItems::where('trx_goods_receipts_id', $grpo->id)->get();

			foreach ($items as $v) {
				$v->delete();
			}

			foreach ($po->items as $v) {
				$item = new \App\TrxGoodsReceiptItems;
				$item->trx_goods_receipts_id = $grpo->id;
				$item->ItemCode = $v->ItemCode;
				$item->Quantity = $v->Quantity;
				$item->UomCode = $v->UomCode;
				$item->save();
			}
			//code here
			DB::commit();
		} catch (\Exception $e) {
			DB::rollback();

			return json([
				'code' => 500,
				'message' => 'Major transaction error, data rolled back',
				'error' => $e
			]);
		}

		return redirect(uri('admin/trx_goods_receipts/edit/' . $grpo->id));
	}

	public function getGreturn($id)
	{
		$po = \App\TrxPurchaseOrders::with("items")->find($id);
		$pr = \App\TrxPurchaseRequests::find($po->trx_purchase_requests_id);
		$bp = \App\RefBusinessPartner::find($po->items[0]->ref_business_partners_id);
		$grpo = \App\TrxGoodsReceipts::where("trx_purchase_orders_id", $po->id)->first();

		$gr = \App\TrxGoodsReturns::where("trx_purchase_orders_id", $po->id)->first();

		if (empty($grpo)) {
			echo '<script>alert("You need to make a Goods Receipt first");  setTimeout(() => history.back(), 1500); </script>';
			exit();
		}

		if (!empty($gr)) {
			echo '<script>alert("Goods Return already created");  setTimeout(() => history.back(), 1500); </script>';
			exit();
		}

		try {
			DB::beginTransaction();
			$grpo = \App\TrxGoodsReturns::where("trx_purchase_orders_id", $po->id)->first();
			if (empty($grpo)) {
				$grpo = new \App\TrxGoodsReturns;
			}
			$grpo->trx_purchase_orders_id = $po->id;
			$grpo->U_SOL_SYNC_KEY = 'GR' . date('Ymd') . six_random();
			$grpo->CardCode = $bp->code;
			$grpo->NumAtCard = $bp->name;
			$grpo->DocDate = date("Y-m-d");
			$grpo->WhsCode = $po->WhsCode;
			$grpo->Comments = $po->Comments;
			$grpo->U_SOL_REF_KEY = $pr->U_SOL_SYNC_KEY;
			$grpo->U_SOL_RAV_TRID = $grpo->U_SOL_SYNC_KEY;
			$grpo->doc_status = 'draft';
			$grpo->save();

			$items = \App\TrxGoodsReturnItems::where('trx_goods_returns_id', $grpo->id)->get();
			foreach ($items as $v) {
				$v->delete();
			}


			foreach ($po->items as $v) {
				$item = new \App\TrxGoodsReturnItems;
				$item->trx_goods_returns_id = $grpo->id;
				$item->ItemCode = $v->ItemCode;
				$item->Quantity = $v->Quantity;
				$item->UomCode = $v->UomCode ?? 'PCS';
				$item->save();
			}
			//code here
			DB::commit();
		} catch (\Exception $e) {
			DB::rollback();

			return json([
				'code' => 500,
				'message' => 'Major transaction error, data rolled back',
				'error' => $e
			]);
		}

		return redirect(uri('admin/trx_goods_returns/edit/' . $grpo->id));
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
			// $total += $v->PriceBefDi * $v->Quantity;
			$total_pajak += $v->PriceBefDi * $v->Quantity * $rate_percent;
		}
		if ($data['po']->DocTotal != $total) {
			$data['po']->DocTotal = $total;
			$data['po']->save();
			$data['po']->total_pajak = $total_pajak;
			// $data['po']->total_pajak = 0;
		}
		// return view("merchandiser/purchase_order/download", $data);
		$dompdf = new Dompdf();
		$dompdf->loadHtml(view("merchandiser/purchase_order/download", $data)->render());
		$dompdf->setPaper('A3', 'potrait');
		$dompdf->render();
		$dompdf->stream($data['vendor']->name . ' - ' . $data['store']->code . ' - ' . $data['po']->U_SOL_SYNC_KEY.  ".pdf");
	}
}
