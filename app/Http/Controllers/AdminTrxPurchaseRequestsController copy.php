<?php

namespace App\Http\Controllers;

use Session;
use Request;
use DB;
use CRUDBooster;

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
		if ($me->id_cms_privileges == 5) {
			$this->button_add = false;
			$this->button_delete = false;
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
		$this->col[] = ["label" => "To Store", "name" => "U_VIT_ToStr"];
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
		$this->col[] = ["label" => "Sync At", "name" => "sync_at", "callback" => function ($r) {
			if ($r->sync_at) {
				return dateformat($r->sync_at);
			}
		}];


		$this->col[] = ["label" => "Verified By", "name" => "verified_by_cms_users_id", "join" => "cms_users,name"];
		$this->col[] = ["label" => "Verified At", "name" => "verified_at", "callback" => function ($r) {
			if ($r->verified_at) {
				return dateformat($r->verified_at);
			}
		}];
		# END COLUMNS DO NOT REMOVE THIS LINE

		# START FORM DO NOT REMOVE THIS LINE
		$this->form = [];
		$this->form[] = ['label' => 'Doc. No.', 'name' => 'U_SOL_SYNC_KEY', 'type' => 'text', 'validation' => 'required|min:1|max:255', 'width' => 'col-sm-10', 'readonly' => true, "callback" => function ($r) {
			if (empty($r->U_SOL_SYNC_KEY)) {
				return 'PR' . date("Ymd") . six_random();
			} else {
				return $r->U_SOL_SYNC_KEY;
			}
		}];
		$store_option = '';
		$stores = \App\RefWarehouses::get();
		foreach ($stores as $v) {
			$store_option .= $v->code . '|' . $v->name . '[' . ($v->is_store ? 'Store' : 'Non-Store') . '];';
		}
		$this->form[] = ['label' => 'Doc. Date', 'name' => 'DocDate', 'type' => 'date', 'validation' => 'required|date', 'width' => 'col-sm-10'];
		$this->form[] = ['label' => 'Branch', 'name' => 'Branch', 'type' => 'select2', 'validation' => 'required|min:1|max:255', 'width' => 'col-sm-10', 'datatable' => 'ref_branches,name'];
		// $this->form[] = ['label'=>'Requester Name','name'=>'ReqName','type'=>'text','validation'=>'required|min:1|max:255','width'=>'col-sm-10'];
		// $this->form[] = ['label' => 'To Store', 'name' => 'U_VIT_ToStr', 'type' => 'select2', 'validation' => 'required|min:1|max:255', 'width' => 'col-sm-10',  'dataenum' => $store_option];
		$this->form[] = ['label' => 'To Store', 'name' => 'U_VIT_ToStr', 'type' => 'select2', 'validation' => 'required|min:1|max:255', 'width' => 'col-sm-10',  'dataenum' => $store_option];
		$this->form[] = ['label' => 'Comments', 'name' => 'Comments', 'type' => 'textarea', 'validation' => 'required', 'width' => 'col-sm-10'];
		$itemmaster = \App\RefItemMasterData::selectRaw("id,name,sku,unit_of_measurement")->whereRaw("(tags like '%NoN-Agrinesia%')")
			->orderBy("name")->get()->map(function ($r) {
				$r->name = slug($r->name);
				$r->name = str_replace("-", " ", $r->name);
				$r->name = strtoupper($r->name);
				return $r;
			});
		$data['itemmaster'] = $itemmaster;
		$this->form[] = ['label' => 'Items', 'name' => 'items', 'type' => 'custom', 'validation' => 'required', 'width' => 'col-sm-10', 'html' => view('merchandiser/purchase_request/items_form', $data)->render()];

		$me = \CB::me();
		if ($me->id_cms_privileges == 5) {
			$status_enum = 'rejected|Rejected;approved|Approved';
		} else {
			$status_enum = 'draft|Save as Draft;submited|Submit & Request Approval';
		}
		$this->form[] = ['label' => 'Status', 'name' => 'doc_status', 'type' => 'select', 'validation' => 'required', 'width' => 'col-sm-10', 'dataenum' => $status_enum];

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
		$this->addaction[] = [
			'label' => 'Sync History',
			'url' => uri('admin/log_api_calls?related_module=trx_purchase_requests&related_reff_id=[id]'),
			'icon' => 'fa fa-refresh',
			'color' => 'danger',
			// 'showIf' => "[doc_status] == 'submited'"
		];
		$this->addaction[] = [
			'label' => 'Try Sync',
			'url' => \CB::mainpath() . '/trysync/[id]',
			'icon' => 'fa fa-refresh',
			'color' => 'info',
			// 'showIf' => "[doc_status] == 'submited'"
		];
		if (in_array($me->id_cms_privileges, [5])) {
			$this->addaction[] = [
				'label' => 'Verification',
				'url' => CRUDBooster::mainpath() . '/edit/[id]',
				'icon' => 'fa fa-files-o',
				'color' => 'primary',
				'showIf' => "[doc_status] == 'submited'"
			];
		}
		if (in_array($me->id_cms_privileges, [1])) {
			$this->addaction[] = [
				'label' => 'Create PO',
				'url' => CRUDBooster::mainpath() . '/createpo/[id]',
				'icon' => 'fa fa-files-o',
				'color' => 'info',
				'showIf' => "[doc_status] == 'approved'"
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
	public function hook_query_index(&$query)
	{
		//Your code here
		$me = \CB::me();
		if ($me->id_cms_privileges == 5) {
			$query->where("trx_purchase_requests.doc_status", "submited");
			$query->whereRaw('(trx_purchase_requests.verified_at is null)');
		}

		$query->orderBy('trx_purchase_requests.id', 'desc');
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
		unset($postdata['items']);

		$me = \CB::me();
		$postdata['ReqName'] = username($me);
		$postdata['U_SOL_SYNC_KEY'] = 'PR' . date("Ymd") . six_random();
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

		$me = \CB::me();
		if ($me->id_cms_privileges != 5) {
			$postdata['ReqName'] = username($me);
		}


		unset($postdata['items']);

		if ($postdata['doc_status'] == 'approved') {
			$me = \CB::me();
			$postdata['is_verified'] = 1;
			$postdata['verified_by_cms_users_id'] = $me->id;
			$postdata['verified_at'] = date("Y-m-d H:i:s");
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
			"ReqName" => username($me),
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

		if ($res['code'] == 200 && $res['data']['Data']['Data'] == "Success" && empty($res['data']['Error'])) {
			$pr->sync_status = 'Synced';
			$pr->sync_at = date("Y-m-d H:i:s");
			$pr->save();
		} else {
			if ($pr->sync_status === null) {
				$pr->sync_status = 'Failed';
				$pr->save();
			}
		}
	}

    public function getStores($id){

        $store_option = '';
		$stores = \App\RefWarehouses::where('ref_branches_id', $id)->get();
		foreach ($stores as $v) {
			$store_option .= [
                'value' => $v->id, 'text' => $v->code . '|' . $v->name . '[' . ($v->is_store ? 'Store' : 'Non-Store') . '];'
            ];
		}

        return $store_option;
    }

	public function getCreatepo($id)
	{
		$po_exist = \App\TrxPurchaseOrders::where('trx_purchase_requests_id', $id)->first();
		if (!empty($po_exist)) {
			return redirect(uri('admin/trx_purchase_orders/edit/' . $po_exist->id));
		}


		$pr = \App\TrxPurchaseRequests::with('items.item')->find($id);

		try {
			DB::beginTransaction();

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
			$po->save();

			$total = 0;
			foreach ($pr->items as $v) {
				$prod = \App\RefItemMasterData::where("sku", $v->item->sku)->first();
				$uom = \App\RefUoms::where("code", strtoupper($prod->unit_of_measurement))->first();
				$pricelist = \App\RefPurchasePriceList::where("item_code", $v->item->sku)->first();
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
}
