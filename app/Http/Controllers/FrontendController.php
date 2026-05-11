<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class FrontendController extends Controller
{
    public function index(){
        return view('frontend/index');
    }
    public function splash(){
        return view('frontend/pages/splash');
    }
    public function confirmation($any){
        $data['type'] = $any;
        return view('frontend/pages/auth/confirmation',$data);
    }
    public function login(){
        return view('frontend/pages/auth/login');
    }
    public function register(){
        return view('frontend/pages/auth/register');
    }
    public function agreement(){
        return view('frontend/pages/auth/agreement');
    }
    public function my_store(){
        return view('frontend/pages/profile/my_store');
    }
    public function personal_data(){
        return view('frontend/pages/profile/personal_data');
    }
    

    public function accounting_management_systems(){
        $data['breadcrumbs'] = array(
            [ 
                'label'=>'Home',
                'link'=>'frontend/home'
            ],
            [ 
                'label'=>'Solutions',
                'link'=>'frontend/home'
            ],
            [ 
                'label'=>'Accounting & Tax Service',
                'link'=>'#'
            ],
            
        );
        return view('frontend/pages/solutions/accounting_management_systems',$data);
    }

    public function i5_trading(){
        $data['breadcrumbs'] = array(
            [ 
                'label'=>'Home',
                'link'=>'frontend/home'
            ],
            [ 
                'label'=>'Solutions',
                'link'=>'frontend/home'
            ],
            [ 
                'label'=>'Sales / Inventory / Consigning',
                'link'=>'#'
            ],
            
        );
        return view('frontend/pages/solutions/i5_trading',$data);
    }

    public function i7_manufacture(){
        $data['breadcrumbs'] = array(
            [ 
                'label'=>'Home',
                'link'=>'frontend/home'
            ],
            [ 
                'label'=>'Solutions',
                'link'=>'frontend/home'
            ],
            [ 
                'label'=>'Integrated Manufacturing',
                'link'=>'#'
            ],
            
        );
        return view('frontend/pages/solutions/i7_manufacture',$data);
    }
   
    public function indonesia_branch(){
        $data['breadcrumbs'] = array(
            [ 
                'label'=>'Home',
                'link'=>'frontend/home'
            ],
            [ 
                'label'=>'About',
                'link'=>'#'
            ], 
            [ 
                'label'=>'Indonesia Branch',
                'link'=>'#'
            ], 
        );
        return view('frontend/pages/abouts/indonesia_branch',$data);
    }
    public function system_ever(){
        $data['breadcrumbs'] = array(
            [ 
                'label'=>'Home',
                'link'=>'frontend/home'
            ],
            [ 
                'label'=>'About',
                'link'=>'#'
            ], 
            [ 
                'label'=>'SystemEver',
                'link'=>'#'
            ], 
        );
        return view('frontend/pages/abouts/system_ever',$data);
    }
    public function history(){
        $data['breadcrumbs'] = array(
            [ 
                'label'=>'Home',
                'link'=>'frontend/home'
            ],
            [ 
                'label'=>'About',
                'link'=>'#'
            ], 
            [ 
                'label'=>'SystemEver',
                'link'=>'#'
            ], 
        );
        return view('frontend/pages/abouts/history',$data);
    }

    public function support(){
        $data['breadcrumbs'] = array(
            [ 
                'label'=>'Home',
                'link'=>'frontend/home'
            ],
            [ 
                'label'=>'Support',
                'link'=>'#'
            ], 
            [ 
                'label'=>'FAQ',
                'link'=>'#'
            ], 
        );
        return view('frontend/pages/support/support',$data);
    }
    public function support_guide(){
        $data['breadcrumbs'] = array(
            [ 
                'label'=>'Home',
                'link'=>'frontend/home'
            ],
            [ 
                'label'=>'Support',
                'link'=>'#'
            ], 
            [ 
                'label'=>'Support Guide',
                'link'=>'#'
            ], 
        );
        return view('frontend/pages/support/support_guide',$data);
    }
    public function video(){
        $data['breadcrumbs'] = array(
            [ 
                'label'=>'Home',
                'link'=>'frontend/home'
            ],
            [ 
                'label'=>'Support',
                'link'=>'#'
            ], 
            [ 
                'label'=>'Video',
                'link'=>'#'
            ], 
        );
        return view('frontend/pages/support/video',$data);
    }
   
    public function article(){
        $data['breadcrumbs'] = array(
            [ 
                'label'=>'Home',
                'link'=>'frontend/home'
            ],
            [ 
                'label'=>'Business Insights',
                'link'=>'#'
            ],
            [ 
                'label'=>'Article',
                'link'=>'#'
            ],
            
        );
        return view('frontend/pages/article/article',$data);
    }
    public function article_list_category(){
        $data['breadcrumbs'] = array(
            [ 
                'label'=>'Home',
                'link'=>'frontend/home'
            ],
            [ 
                'label'=>'Business Insight',
                'link'=>'#'
            ],
            [ 
                'label'=>'Article',
                'link'=>'#'
            ],
            
        );
        return view('frontend/pages/article/article_list_category',$data);
    }
    public function article_detail(){
        $data['breadcrumbs'] = array(
            [ 
                'label'=>'Home',
                'link'=>'frontend/home'
            ],
            [ 
                'label'=>'Business Insight',
                'link'=>'#'
            ],
            [ 
                'label'=>'Article',
                'link'=>'#'
            ], 
        );
        return view('frontend/pages/article/article_detail',$data);
    }
    public function news_detail(){
        $data['breadcrumbs'] = array(
            [ 
                'label'=>'Home',
                'link'=>'frontend/home'
            ],
            [ 
                'label'=>'Business Insight',
                'link'=>'#'
            ],
            [ 
                'label'=>'News',
                'link'=>'#'
            ], 
        );
        return view('frontend/pages/article/news_detail',$data);
    }
    public function news(){
        $data['breadcrumbs'] = array(
            [ 
                'label'=>'Home',
                'link'=>'frontend/home'
            ],
            [ 
                'label'=>'Business Insight',
                'link'=>'frontend/article'
            ], 
            [ 
                'label'=>'News',
                'link'=>'#'
            ], 
        );
        return view('frontend/pages/article/news',$data);
    }

    public function accounting_tax(){
        $data['breadcrumbs'] = array(
            [ 
                'label'=>'Home',
                'link'=>'frontend/home'
            ],
            [ 
                'label'=>'Pricing',
                'link'=>'frontend/pricing'
            ], 
            [ 
                'label'=>'Accounting & Tax Services',
                'link'=>'#'
            ], 
        );
        return view('frontend/pages/pricing/accounting_tax',$data);
    }

    public function integrated_erp(){
        $data['breadcrumbs'] = array(
            [ 
                'label'=>'Home',
                'link'=>'frontend/home'
            ],
            [ 
                'label'=>'Pricing',
                'link'=>'frontend/pricing'
            ], 
            [ 
                'label'=>'Integrated ERP',
                'link'=>'#'
            ], 
        );
        return view('frontend/pages/pricing/integrated_erp',$data);
    }

    public function service_feature(){
        $data['breadcrumbs'] = array(
            [ 
                'label'=>'Home',
                'link'=>'frontend/home'
            ],
            [ 
                'label'=>'Service',
                'link'=>'#'
            ], 
            [ 
                'label'=>'SystemEver Feature',
                'link'=>'#'
            ], 
        );
        return view('frontend/pages/services/feature',$data);
    }

    public function service_quality_security(){
        $data['breadcrumbs'] = array(
            [ 
                'label'=>'Home',
                'link'=>'frontend/home'
            ],
            [ 
                'label'=>'Service',
                'link'=>'#'
            ], 
            [ 
                'label'=>'Quality/Security',
                'link'=>'#'
            ], 
        );
        return view('frontend/pages/services/quality_security',$data);
    }
    public function customer_industry(){
        $data['breadcrumbs'] = array(
            [ 
                'label'=>'Home',
                'link'=>'frontend/home'
            ],
            [ 
                'label'=>'Service',
                'link'=>'#'
            ], 
            [ 
                'label'=>'Customer',
                'link'=>'#'
            ], 
        );
        return view('frontend/pages/services/customer_industry',$data);
    }
    public function join_partnership(){
        $data['breadcrumbs'] = array(
            [ 
                'label'=>'Home',
                'link'=>'frontend/home'
            ],
            [ 
                'label'=>'Join',
                'link'=>'#'
            ], 
            [ 
                'label'=>'Partnership',
                'link'=>'#'
            ], 
        );
        return view('frontend/pages/join_partnership',$data);
    }
    
}
