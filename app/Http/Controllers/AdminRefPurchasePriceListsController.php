<?php namespace App\Http\Controllers;

	use Session;
	use Request;
	use DB;
	use CRUDBooster;

	class AdminRefPurchasePriceListsController extends \crocodicstudio\crudbooster\controllers\CBController {

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
			$this->button_edit = false;
			$this->button_delete = false;
			$this->button_detail = false;
			$this->button_show = false;
			$this->button_filter = true;
			$this->button_import = false;
			$this->button_export = false;
			$this->table = "ref_purchase_price_lists";
			# END CONFIGURATION DO NOT REMOVE THIS LINE

			# START COLUMNS DO NOT REMOVE THIS LINE
			$this->col = [];
			// $this->col[] = ["label"=>"Item Master Datas","name"=>"ref_item_master_datas_id","join"=>"ref_item_master_datas,name"];
			$this->col[] = ["label"=>"Item Code","name"=>"item_code"];
			$this->col[] = ["label"=>"Price","name"=>"price"];
			$this->col[] = ["label"=>"Card Code","name"=>"card_code"];
			// $this->col[] = ["label"=>"Business Partners","name"=>"ref_business_partners_id","join"=>"ref_business_partners,name"];
			$this->col[] = ["label"=>"Var Group","name"=>"var_group"];
			$this->col[] = ["label"=>"Tax Rate","name"=>"tax_rate"];
			# END COLUMNS DO NOT REMOVE THIS LINE

			# START FORM DO NOT REMOVE THIS LINE
			$this->form = [];
			// $this->form[] = ['label'=>'Item Master Datas','name'=>'ref_item_master_datas_id','type'=>'select2','validation'=>'required|min:1|max:255','width'=>'col-sm-10','datatable'=>'ref_item_master_datas,name'];
			// $this->form[] = ['label'=>'Item Code','name'=>'item_code','type'=>'text','validation'=>'required|min:1|max:255','width'=>'col-sm-10'];
			$this->form[] = ['label'=>'Price','name'=>'price','type'=>'money','validation'=>'required|integer|min:0','width'=>'col-sm-10'];
			$this->form[] = ['label'=>'Card Code','name'=>'card_code','type'=>'text','validation'=>'required|min:1|max:255','width'=>'col-sm-10'];
			// $this->form[] = ['label'=>'Business Partners','name'=>'ref_business_partners_id','type'=>'select2','validation'=>'required|min:1|max:255','width'=>'col-sm-10','datatable'=>'ref_business_partners,name'];
			$this->form[] = ['label'=>'Var Group','name'=>'var_group','type'=>'text','validation'=>'required|min:1|max:255','width'=>'col-sm-10'];
			$this->form[] = ['label'=>'Tax Rate','name'=>'tax_rate','type'=>'number','validation'=>'required|integer|min:0','width'=>'col-sm-10'];
			# END FORM DO NOT REMOVE THIS LINE

			# OLD START FORM
			//$this->form = [];
			//$this->form[] = ["label"=>"Ref Item Master Datas Id","name"=>"ref_item_master_datas_id","type"=>"select2","required"=>TRUE,"validation"=>"required|min:1|max:255","datatable"=>"ref_item_master_datas,item_name"];
			//$this->form[] = ["label"=>"Item Code","name"=>"item_code","type"=>"text","required"=>TRUE,"validation"=>"required|min:1|max:255"];
			//$this->form[] = ["label"=>"Price","name"=>"price","type"=>"money","required"=>TRUE,"validation"=>"required|integer|min:0"];
			//$this->form[] = ["label"=>"Card Code","name"=>"card_code","type"=>"text","required"=>TRUE,"validation"=>"required|min:1|max:255"];
			//$this->form[] = ["label"=>"Ref Business Partners Id","name"=>"ref_business_partners_id","type"=>"select2","required"=>TRUE,"validation"=>"required|min:1|max:255","datatable"=>"ref_business_partners,name"];
			//$this->form[] = ["label"=>"Var Group","name"=>"var_group","type"=>"text","required"=>TRUE,"validation"=>"required|min:1|max:255"];
			//$this->form[] = ["label"=>"Tax Rate","name"=>"tax_rate","type"=>"number","required"=>TRUE,"validation"=>"required|integer|min:0"];
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
            $this->index_button[] = [
                'label' => 'Sync Purchase Price List',
                'url' => CRUDBooster::mainpath() . '/sync',
                'icon' => 'fa fa-refresh',
                'color' => 'info'
            ];
            $this->index_button[] = [
                'label' => 'Sync History',
                'url' => uri('admin/log_api_calls?related_module=ref_purchase_price_lists'),
                'icon' => 'fa fa-refresh',
                'color' => 'danger'
            ];


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
	    public function actionButtonSelected($id_selected,$button_name) {
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
        public function getSync()
        {
            $item_master = \App\RefItemMasterData::whereRaw("(tags like '%NoN-Agrinesia%' AND sku not like '%Topping%')")
                ->whereRaw('sku not in (SELECT item_code FROM ref_purchase_price_lists WHERE deleted_at is null)')
                ->orderBy('sku')
                ->limit(50)
                ->get()
                ->pluck('sku')
                ->toArray();

                foreach($item_master as $v) {
                    self::syncByItemCodeList([$v]);
                }

            return redirect( CRUDBooster::mainpath() );
        }

        public static function syncByItemCodeList($item_master)
        {
            $url = env('SAP_HOST') . "/api/master/getDataPurchasePriceList";

			if (env('SAP_LIVE', false)) {
				$url .= 'Live';
			}

            $body = [
                "ItemCodes" => $item_master
            ];
            
            $res = \App\Service\IsellerService::getWithBody($url, $body);

            if (!empty($res['data']['Data']['Data'])) {
                foreach ($res['data']['Data']['Data'] as $v) {
                    
                    $pricelist = \App\RefPurchasePriceList::where("item_code", $v['item_code'])->first();
                    if (empty($pricelist)) {
                        $pricelist = new \App\RefPurchasePriceList;
                    }

                    $pricelist->item_code = $v['ItemCode'];
                    $pricelist->price = $v['Price'];
                    $pricelist->card_code = $v['CardCode'];
                    $pricelist->var_group = $v['VatGroup'];
                    $pricelist->tax_rate = $v['TaxRate'];
                    $pricelist->save();

                }
            }

            $log = new \App\LogApiCalls;
            $log->related_module = 'ref_purchase_price_lists';
            $log->related_reff_id = null;
            $log->api_url = $url;
            $log->request_body = json_encode($body);
            $log->response_body = json_encode($res['data']);
            $log->response_code = $res['code'];
            $log->save();

            return $pricelist;
        }

	}