<?php
namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
class HomeController extends Controller
{
   public function getAnalyticsSummary(Request $request){
        
        $from_date = date("Y-m-d", strtotime($request->get('from_date',"7 days ago")));
        
        $to_date = date("Y-m-d",strtotime($request->get('to_date',$request->get('from_date','today')))) ; 
        
        $gAData = $this->gASummary($from_date,$to_date) ;
        
        return $gAData;

    }
         //to get the summary of google analytics.
    private function gASummary($date_from,$date_to) {
        
        $service_account_email = 'laravel-google-analystic-repor@calm-magpie-284009.iam.gserviceaccount.com';       
        
        // Create and configure a new client object.
        
        $client = new \Google_Client();
        
        $client->setApplicationName("laravel-google-analystic-reports");
        
        $analytics = new \Google_Service_Analytics($client);
        
        $cred = new \Google_Auth_AssertionCredentials(
            $service_account_email,
            array(\Google_Service_Analytics::ANALYTICS_READONLY),
              "365fe106241e12b215155787b3dcc6edfa356459"
        );     
        
        $client->setAssertionCredentials($cred);
        
        if($client->getAuth()->isAccessTokenExpired()) {
            $client->getAuth()->refreshTokenWithAssertion($cred);
        }

        $optParams = [
            'dimensions' => 'ga:date',
            'sort'=>'-ga:date'
        ] ;   

        $results = $analytics->data_ga->get(
            'ga:247725653',
            $date_from,
            $date_to,
            'ga:sessions,ga:users,ga:pageviews,ga:bounceRate,ga:hits,ga:avgSessionDuration',
            $optParams
           );
            
            $rows = $results->getRows();
            $rows_re_align = [] ;
            foreach($rows as $key=>$row) {
                foreach($row as $k=>$d) {
                    $rows_re_align[$k][$key] = $d ;
                }
            }           
            $optParams = array(
                        'dimensions' => 'rt:medium'
                );
            try {
              $results1 = $analytics->data_realtime->get(
                  'ga:247725653',
                  'rt:activeUsers',
                  $optParams);
               
            } catch (apiServiceException $e) {
              
              $error = $e->getMessage();
            }
            $active_users = $results1->totalsForAllResults ;
            return [
                'data'=> $rows_re_align ,
                'summary'=>$results->getTotalsForAllResults(),
                'active_users'=>$active_users['rt:activeUsers']
                ] ;


    }
}
