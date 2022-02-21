<?php

namespace App\Http\Controllers;
use App\Http\Controllers\Controller;
use App\models\Categories;
use App\models\Country;
use App\models\Customers;
use App\models\Inventory;
use App\models\Products;
use App\models\Productsattributes;
use App\models\Productsimages;
use App\models\Productsoptions;
use App\models\Smsverificationcode;
use App\models\Brand;
use App\models\Manufacturers;
use App\models\Customersbasket;
use App\models\Review;
use App\models\Flashsale;
use App\models\Reviewdesc;
use App\models\Branddescription;
use App\models\Productsoptionsvalues;
use App\models\Productstocategories;
use App\models\Sliderimages;
use App\models\Verificationcode;
use App\models\Order;
use App\models\Likedproducts;
use App\models\Addressbook;
use App\models\Ordersstatushistory;
use App\models\Ordersproducts;
use App\models\Notifications;
use App\models\Devices;
use App\models\Language;
use Hash;
use DNS1D;
use DNS2D;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Lang;
use URL;

use DB;
use Carbon\Carbon;
//require_once 'dompdf/autoload.inc.php';
// require_once 'dompdf/include/autoload.inc.php';
//require_once 'TCPDF/examples/tcpdf_include.php';
//use TCPDF;
// use Dompdf\DOMPDF;
//use Dompdf\Dompdf;


//use Validator;
use Session;
use DateTime;
use App;
use Response;
use File;
use Storage;
class ApiController extends Controller {

    public function socialLogin()   // property  -- login
    {

        $input = file_get_contents('php://input');
        $post = json_decode($input, true);
        $urlnew = url('');
        $new = str_replace('index.php', '', $urlnew);

        if(empty($post['issocial']))
        {
            $post['issocial']='0';
        }

        $language='en';
        if(!empty($post['language'])){
            $language=$post['language'];
        }
        App::setLocale($language);

        try
        {

         if($post['issocial']=="1")
            {
                if((empty($post['deviceType'])) || (empty($post['deviceId'])) || (empty($post['email']))  )
                {
                    $response = array('success' => 0, 'message' => trans('labels.pleasefillallrequired'));
                    echo json_encode($response,JSON_HEX_TAG|JSON_HEX_APOS|JSON_HEX_QUOT|JSON_UNESCAPED_UNICODE|JSON_HEX_AMP);exit;
                }

            $firstName              = $post['firstName'];
			$lastName               = $post['lastName'];
			$email                  = $post['email'];
			$deviceId               = $post['deviceId'];
			$deviceType             = $post['deviceType'];
			$loginWith              = $post['loginWith'];
			$socialId               = $post['socialId'];
			$image                  = $post['image'];
		    $date_created           = strtotime("now");
			$manufacturer           = $deviceType == "1" ? "Apple" : "Android";
            $checkEmail = Customers::where('email', $email)->first();
			if (!empty($checkEmail)) {
		      $usercreate = Customers::where('email', $email)->first();
		   }else{
			$usercreate= new Customers;
			$usercreate->customers_firstname = $firstName;
			$usercreate->customers_lastname = $lastName;
			$usercreate->email = $email;
			$usercreate->isActive = 1;
			$usercreate->isSocial = 1;

			if(isset($image)){
			  $usercreate->customers_picture = $image;
			}
			if($loginWith == "facebook"){
			 $usercreate->fb_id = $socialId;
			}else{
			   $usercreate->google_id = $socialId;
			}


			$usercreate->save();
			}



// 		 $usercreate = Customers::create([
// 				'customers_firstname' => $firstName,
// 				'customers_lastname' => $lastName,
// 				'email' => $email
// 			]);

			$code = rand(1000, 9999);
		    $customersId = $usercreate['customers_id'];
			$getdata['device_id']           = $deviceId;
			$getdata['customers_id']        = $customersId;
			$getdata['device_type']         = $deviceType;
			$getdata['register_date']       = $date_created;
			$getdata['update_date']         = $date_created;
			$getdata['status']              = "1";
			$getdata['manufacturer']        = $manufacturer;
			$getdata['device_model']        = $manufacturer;

			$devicecreate = Devices::insertGetId($getdata);

			$data=[];
		    $result=[];
			$countrylist=Country::where('countries_id',$usercreate->country_id)->first();
			$result['customerId']           = $usercreate->customers_id;
			$result['gender']               = $usercreate->customers_gender;
			$result['firstName']            = $usercreate->customers_firstname;
			$result['lastName']             = $usercreate->customers_lastname;
			$result['dob']                  = $usercreate->customers_dob;
			$result['email']                = $usercreate->email;
			$result['userName']             = $usercreate->user_name;
			$result['mobile']               = $usercreate->customers_telephone;
		    $result['countryCode']          = isset($countrylist->phone_code) ? $countrylist->phone_code : "";
	        $result['countryId']            = isset($usercreate->country_id) ? $usercreate->country_id :"";
			$result['defaultAddressId']     = $usercreate->customers_default_address_id !==NULL ? $user->customers_default_address_id : '0';
			$result['isActive']             = $usercreate->isActive;
			if ($usercreate->customers_picture == '') {
				$result['image'] = str_replace("/index.php/", "/", url('resources/assets/images/user_profile/default_user.png'));
			} else {
				$result['image'] = starts_with($usercreate->customers_picture,"http") ?  $usercreate->customers_picture : str_replace("/index.php/", "/", url($usercreate->customers_picture));
			}

	        $response = array('success' => 1, 'message' => Lang::get('labels.User Registered Succeessfully'), 'result' => $result);
			echo json_encode($response, JSON_UNESCAPED_UNICODE);exit;
           } else{
              $response = array('success' => 0, 'message' => trans('labels.pleasefillallrequired'));
              echo json_encode($response, JSON_UNESCAPED_UNICODE);exit;
           }
           }
        catch(Exception $e)
        {
            $arr = array('success' => 0, 'message' => trans('labels.unknown_error_occured'));
            echo json_encode($arr,JSON_HEX_TAG|JSON_HEX_APOS|JSON_HEX_QUOT|JSON_HEX_AMP);exit;
        }
    }


	public function register(Request $request) {
		//$post = $request->all();
		$input = file_get_contents('php://input');
		$post = json_decode($input, true);
		$urlnew = url('');
		$new = str_replace('index.php', '', $urlnew);

		$language="en";
	    if(!empty($post['language'])){ $language=$post['language']; }
	    App::setLocale($language);

		try
		{
			// if ((!isset($post['firstName'])) || (!isset($post['lastName'])) || (!isset($post['mobilePhone'])) || (!isset($post['gender'])) || (!isset($post['email'])) || (!isset($post['password'])) || (!isset($post['dob'])) || (!isset($post['countryId']))  || (!isset($post['deviceId'])) || (!isset($post['deviceType'])) || (empty($post['firstName'])) || (empty($post['lastName'])) || (empty($post['mobilePhone'])) || (empty($post['gender'])) || (empty($post['email'])) || (empty($post['password'])) || (empty($post['dob'])) || (empty($post['countryId'])) || (empty($post['deviceId'])) || (empty($post['deviceType'])) || (empty($post['deviceType']))) {
				if ((!isset($post['firstName'])) || (!isset($post['lastName'])) || (!isset($post['mobilePhone'])) || (!isset($post['gender'])) || (!isset($post['email'])) || (!isset($post['password'])) || (!isset($post['countryId']))  || (!isset($post['deviceId'])) || (!isset($post['deviceType'])) || (empty($post['firstName'])) || (empty($post['lastName'])) || (empty($post['mobilePhone'])) || (empty($post['gender'])) || (empty($post['email'])) || (empty($post['password'])) || (empty($post['countryId'])) || (empty($post['deviceId'])) || (empty($post['deviceType'])) || (empty($post['deviceType']))) {

				$response = array('success' => 0, 'message' => Lang::get('labels.All Fields Are Required'));
				echo json_encode($response, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_UNESCAPED_UNICODE | JSON_HEX_AMP);exit;
			}

			$validator = Validator::make($post, [

				'password' => 'min:8|regex:/[0-9]/ | regex:/[A-Z]/',
			]);
			if ($validator->fails()) {

				$messages = Lang::get("labels.password must be at least 8 character and password format is invalid");
				$response = array('success' => 0, 'message' => $messages);
				echo json_encode($response, JSON_UNESCAPED_UNICODE);exit;

			}

			$firstName              = $post['firstName'];
			$lastName               = $post['lastName'];
			$mobilePhone            = $post['mobilePhone'];
			$gender                 = $post['gender'];
			$email                  = $post['email'];
			$password               = $post['password'];
			$dob                    = isset($post['dob']) ? $post['dob'] : '';
			$countryId              = $post['countryId'];
			$deviceId               = $post['deviceId'];
			$deviceType             = $post['deviceType'];
			$date_created           = strtotime("now");
			$manufacturer           = $deviceType == "1" ? "Apple" : "Android";

			$checkEmail = Customers::where('email', $email)->first();
			if (!empty($checkEmail)) {
				$response = array('success' => 0, 'message' => Lang::get('labels.This Email Already Exists'));
				echo json_encode($response, JSON_UNESCAPED_UNICODE);exit;
			}

			$checkEmail1 = Customers::where('customers_telephone', $mobilePhone)->first();
			if (!empty($checkEmail1)) {
				$response = array('success' => 0, 'message' => Lang::get('labels.This Phone Already Exists'));
				echo json_encode($response, JSON_UNESCAPED_UNICODE);exit;
			}

			$usercreate = Customers::create([
				'customers_firstname' => $firstName,
				'customers_lastname' => $lastName,
				'customers_telephone' => $mobilePhone,
				'customers_gender' => $gender,
				'email' => $email,
				'country_id'=>$countryId,
				'password' => Hash::make($password),
				'customers_dob' => $dob,
			]);

			$code = rand(1000, 9999);
		/*	$verificationcodes = Verificationcode::create([
				'code' => $code,
				'customers_id' =>$usercreate['customers_id']

			]);*/
			$customersId = $usercreate['customers_id'];
			$getdata['device_id']           = $deviceId;
			$getdata['customers_id']        = $customersId;
			$getdata['device_type']         = $deviceType;
			$getdata['register_date']       = $date_created;
			$getdata['update_date']         = $date_created;
			$getdata['status']              = "1";
			$getdata['manufacturer']        = $manufacturer;
			$getdata['device_model']        = $manufacturer;

			$devicecreate = Devices::insertGetId($getdata);
		//	$otp=Verificationcode::select('code')->where('customers_id',$customersId)->first();
			$data=[];
			/*$data['customersId']            = $usercreate['customers_id'];
                  $id                       = $data['customersId'];
                 // $data['name'] = $results->customers_firstname;
                  $data['email'] = $usercreate->email;
                  $subject = "Verificationcode";
                  $header="";
                  $header .= "MIME-Version: 1.0\r\n";
                  $header .= "Content-type: text/html\r\n";
                 //   $header .= 'From: Aqark - عقارك' ;
                  $header .= "From:harmis\r\n";
                  $header .= "Reply-To: email.aqark.co\r\n";
                  $message ="";
                  $message .= "Hi <br>";
                  $message .= "Your verificationcode is<br>";
                  $message .= $otp->code;

                mail($data['email'],$subject,$message,$header);*/

			$result=[];
			$countrylist=Country::where('countries_id',$usercreate->country_id)->first();
			$result['customerId']           = $usercreate->customers_id;
			$result['gender']               = $usercreate->customers_gender;
			$result['firstName']            = $usercreate->customers_firstname;
			$result['lastName']             = $usercreate->customers_lastname;
			$result['dob']                  = $usercreate->customers_dob;
			$result['email']                = $usercreate->email;
			$result['userName']             = $usercreate->user_name;
			$result['mobile']               = $usercreate->customers_telephone;
			$result['countryCode']          = $countrylist->phone_code;
	        $result['countryId']            = $usercreate->country_id;
			$result['defaultAddressId']     = $usercreate->customers_default_address_id !==NULL ? $user->customers_default_address_id : '0';
			$result['isActive']             = $usercreate->isActive;
			if ($usercreate->customers_picture == '') {
				$result['image'] = str_replace("/index.php/", "/", url('resources/assets/images/user_profile/default_user.png'));
			} else {
				$result['image'] = str_replace("/index.php/", "/", url($user->customers_picture));
			}


			$result['password']             = $usercreate->password;

		//	$result['otp']                  = $otp->code;
			//$results[]=$result;
			$response = array('success' => 1, 'message' => Lang::get('labels.User Registered Succeessfully'), 'result' => $result);
			echo json_encode($response, JSON_UNESCAPED_UNICODE);exit;

		} catch (Exception $e) {
			$response = array('success' => 0, 'message' => $e->getMessage());
			echo json_encode($response, JSON_UNESCAPED_UNICODE);exit;

		}
	}

	public function countrylist(Request $request) {

	//	$posts = $request->all();
		$input = file_get_contents('php://input');
		$post = json_decode($input, true);
		$urlnew = url('');
		$new = str_replace('index.php', '', $urlnew);

		$language="en";
	    if(!empty($post['language'])){ $language=$post['language']; }
	    App::setLocale($language);

		try
		{

			//$getCountrylist = Country::where('countries_id','102')->get();
			$getCountrylist = Country::get();
			$countryData = [];
			if (!empty($getCountrylist)) {
				foreach ($getCountrylist as $key => $value) {
					$countryData['countryId']       = $value->countries_id;
					$countryData['countryName']     =$post['language']=='en' ? $value->countries_name :$value->countries_ar_name ;
					$countryData['countryIsoCode2'] = $value->countries_iso_code_2;
					$countryData['countryIsoCode3'] = $value->countries_iso_code_3;

					if ($value->countries_img == '') {
						$countryData['countryImg'] = str_replace("/index.php/", "/", url('resources/assets/images/user_profile/default_user.png'));
					} else {
						$countryData['countryImg'] = $new.$value->countries_img;
					}
					$countrylist[] = $countryData;
				}
				$response = array('success' => 1, 'message' => Lang::get('labels.Countrydata Loaded Successfully'), 'result' => $countrylist);
				echo json_encode($response, JSON_UNESCAPED_UNICODE);exit;
			} else {
				$response = array('success' => 0, 'message' => Lang::get('labels.No Data found'));
				echo json_encode($response);exit;
			}
		} catch (Exception $e) {
			$response = array('success' => 0, 'message' => $e->getMessage());
			echo json_encode($response, JSON_UNESCAPED_UNICODE);exit;
		}
	}

	public function loginuser(Request $request) {

		$input = file_get_contents('php://input');
		$post = json_decode($input, true);
		$urlnew = url('');
		$new = str_replace('index.php', '', $urlnew);

		$language="en";
	    if(!empty($post['language'])){ $language=$post['language']; }
	    App::setLocale($language);

		try
		{
			if ((!isset($post['email'])) || (!isset($post['password'])) || (!isset($post['deviceId'])) || (!isset($post['deviceType'])) || (empty($post['deviceId'])) || (empty($post['deviceType']))) {
				$response = array('success' => 0, 'message' => Lang::get('labels.All Fields Are Required'));
				echo json_encode($response, JSON_UNESCAPED_UNICODE);exit;
			}
			if ($post['email'] == '' || $post['password'] == '') {
				$response = array('success' => 0, 'message' => Lang::get('labels.All Fields Are Required'));
				echo json_encode($response, JSON_UNESCAPED_UNICODE);exit;
			}

			$user = Customers::where('email', $post['email'])->orWhere('customers_telephone', $post['email'])->first();


			if (empty($user)) {
				$arr = array('success' => 0, 'message' => Lang::get('labels.Invalid email or password'));
				echo json_encode($arr, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP);exit;
			}
			else if (!Hash::check($post["password"], $user->password)) {
				$arr = array('success' => 0, 'message' => Lang::get('labels.Invalid email or password'));
				echo json_encode($arr, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP);exit;
			}

			$checkToken = Devices::where('customers_id',$user->customers_id)->first();
			//$manufacturer = $post['deviceType'] == "1" ? "Apple" : "Android";

			if(!empty($checkToken)){

				$checkToken->update_date    = strtotime("now");
				$checkToken->device_id      = $post['deviceId'];
				$checkToken->device_type    = $post['deviceType'];
				//$checkToken->manufacturer   = $manufacturer;
				//$checkToken->device_model   = $manufacturer;
				$checkToken->save();

			}

			else
			{

				$getdata['device_id']       = $post['deviceId'];
				$getdata['customers_id']    = $user->customers_id;
				$getdata['device_type']     = $post['deviceType'];
				$getdata['register_date']   = strtotime("now");
				$getdata['update_date']     = strtotime("now");
				$getdata['status']          = "1";
				//$getdata['manufacturer']    = $manufacturer;
				//$getdata['device_model']    = $manufacturer;
				$devicecreate = Devices::insertGetId($getdata);

			}

				Customersbasket::where('device_id',$post['deviceId'])->update(
            				[
            					'customers_id'                  => $user->customers_id,

            				]);

            $countrylist=Country::where('countries_id',$user->country_id)->first();
			$userData = array();
			$userData['customerId']         = $user->customers_id;
			$userData['gender']             = $user->customers_gender;
			$userData['firstName']          = $user->customers_firstname;
			$userData['lastName']           = $user->customers_lastname;
			$userData['dob']                = $user->customers_dob;
			$userData['email']              = $user->email;
			$userData['userName']           = $user->user_name;
			$userData['mobile']             = $user->customers_telephone;
			$userData['countryCode']        = $countrylist->phone_code;
	        $userData['countryId']          = $user->country_id;
			$userData['defaultAddressId']   = $user->customers_default_address_id !==NULL ? $user->customers_default_address_id : '0';
			$userData['isActive'] = $user->isActive;
			if ($user->customers_picture == '') {
				$userData['image'] = str_replace("/index.php/", "/", url('resources/assets/images/user_profile/default_user.png'));
			} else {
				$userData['image'] = str_replace("/index.php/", "/", url($user->customers_picture));
			}
			$response = array('success' => 1, 'message' => Lang::get('labels.Login Successfully'), 'result' => $userData);
			echo json_encode($response, JSON_UNESCAPED_UNICODE);exit;
		} catch (Exception $e) {
			$response = array('success' => 0, 'message' => $e->getMessage());
			echo json_encode($response, JSON_UNESCAPED_UNICODE);exit;
		}

	}

	public function sendCode(Request $request) {

		$input = file_get_contents('php://input');
		$post = json_decode($input, true);
		$urlnew = url('');
		$new = str_replace('index.php', '', $urlnew);

		$language="en";
	    if(!empty($post['language'])){ $language=$post['language']; }
	    App::setLocale($language);

		try
		{
			if ((!isset($post['email'])) || empty($post['email']))
			 {
				$response = array('success' => 0, 'message' => Lang::get('labels.All Fields Are Required'));
				echo json_encode($response, JSON_UNESCAPED_UNICODE);exit;
			}
			$email = $post['email'];
			$checkEmail = Customers::where('email', $email)->first();

			if (!empty($checkEmail)) {
				$response = array('success' => 0, 'message' => Lang::get('labels.This Email Already Exists'));
				echo json_encode($response, JSON_UNESCAPED_UNICODE);exit;
			}

		/*	$code = rand(1000, 9999);
			$verificationcodes = Verificationcode::create([
				'code' => $code,

			]);*/
			$data = [];
			$data['email'] = $post['email'];
		//	$data['verificationcode'] = $code;

		/*	\Mail::send('mail.sendVerificationCode', ['data' => $data],
				function ($message) use ($data) {
					$message
						->from('harmistest@gmail.com')
						->to($data['email'])->subject(Lang::get('labels.Verification code'));
				});*/

		//	$data=array();
                  $data['customersId'] = $post->customers_id;
                  $id= $data['customersId'];
                 // $data['name'] = $results->customers_firstname;
                  $data['email'] = $results->email;
                  $subject = "Verificationcode";
                  $header="";
                  $header .= "MIME-Version: 1.0\r\n";
                  $header .= "Content-type: text/html\r\n";
                 //   $header .= 'From: Aqark - عقارك' ;
                  $header .= "From:harmis\r\n";
                  $header .= "Reply-To:".$results->email."\r\n";
                  $message ="";
                  $message .= "Hi <br>";
                  $message .= "Your verificationcode is<br>";
                  $message .= "$post->code";


                 mail($sendmail['email'],$subject,$message,$header);

			$response = array('success' => 1, 'message' => Lang::get('labels.Code send Successfully'));
			echo json_encode($response, JSON_UNESCAPED_UNICODE);exit;

		} catch (Exception $e) {
			$response = array('success' => 0, 'message' => $e->getMessage());
			echo json_encode($response, JSON_UNESCAPED_UNICODE);exit;
		}

	}

	public function verificationCode(Request $request) {
		$input = file_get_contents('php://input');
		$post = json_decode($input, true);
		$urlnew = url('');
		$new = str_replace('index.php', '', $urlnew);

		$language="en";
	    if(!empty($post['language'])){ $language=$post['language']; }
	    App::setLocale($language);

		try
		{
			if ((!isset($post['code'])) || (!isset($post['id'])) || (empty($post['code'])) || (empty($post['id']))) {
			$response = array('success' => 0, 'message' => Lang::get('labels.All Fields Are Required'));
			echo json_encode($response, JSON_UNESCAPED_UNICODE);exit;
		}
		$verificationCode = $post['code'];
		$id = $post['id'];
		$checkverificationcode = Verificationcode::where('customers_id', $id)->where('code',$verificationCode)->first();
		if (empty($checkverificationcode)) {
			$response = array('success' => 0, 'message' => Lang::get('labels.Your verification code is wrong !'));
			echo json_encode($response, JSON_UNESCAPED_UNICODE);exit;
		}
		$user = Customers::where('customers_id', '=', $post['id'])->first();
		$countrylist=Country::where('countries_id',$user->country_id)->first();
		    $userData                           = array();
			$userData['customerId']             = $user['customers_id'];
			$userData['gender']                 = $user['customers_gender'];
			$userData['firstName']              = $user['customers_firstname'];
			$userData['lastName']               = $user['customers_lastname'];
			$userData['dob']                    = $user['customers_dob'];
			$userData['email']                  = $user['email'];
			$userData['userName']               = $user['user_name'];
			$userData['mobile']                 = $user['customers_telephone'];
			$userData['countryCode']            = $countrylist['phone_code'];
	        $userData['countryId']              = $user['country_id'];
			$userData['defaultAddressId']       = $user['customers_default_address_id'] !==NULL ? $user['customers_default_address_id'] : '0';
			$userData['isActive'] = $user['isActive'];
			if ($user['customers_picture'] == '') {
				$userData['image'] = str_replace("/index.php/", "/", url('resources/assets/images/user_profile/default_user.png'));
			} else {
				$userData['image'] = str_replace("/index.php/", "/", url($user['customers_picture']));
			}
		$response = array('success' => 1, 'message' => Lang::get('labels.You Verify Successfully'), 'result' => $userData);
		echo json_encode($response, JSON_UNESCAPED_UNICODE);exit;
		}
		catch (Exception $e) {
			$response = array('success' => 0, 'message' => $e->getMessage());
			echo json_encode($response, JSON_UNESCAPED_UNICODE);exit;
		}

	}

	public function forgotPassword() {

		$data = Customers::get()->all();
		$input = file_get_contents('php://input');
		$post = json_decode($input, true);

		$urlnew = url('');
		$new = str_replace('index.php', '', $urlnew);

		$language="en";
	    if(!empty($post['language'])){ $language=$post['language']; }
	    App::setLocale($language);

		try
		{
			$email = $post['email'];

			if (!isset($email) || empty($email)) {
				$response = array();
				$response['success'] = 0;
				$response['message'] = Lang::get('labels.All Fields Are Required');
				echo json_encode($response);exit;
			}
			$email = $email;

			$results = Customers::where('email', $email)->first();

			 if(!empty($results))
              {

    			$data = array('results'=>$results,'id'=>$results->customers_id);
                  \Mail::send('admin.mail', $data, function($message) use ($results)  {
                     $message->to($results->email, $results->customers_firstname)->subject(Lang::get("labels.fogotPasswordEmailTitle"));
                     $message->from('vivek.harmistechnology@gmail.com','basratimes-shops.com');
                  });

              	$response = array();
				$response['success'] = 1;
				$response['message'] = Lang::get('labels.Password Reset Link Sent To  E-mail Successfully');
				echo json_encode($response, JSON_UNESCAPED_UNICODE);exit;
              }
              else {
				$response = array();
				$response['success'] = 0;
				$response['message'] = Lang::get('labels.Email does not exist.Please try with registered email');
				echo json_encode($response);exit;
			}

		} catch (Exception $e) {
			$response = array();
			$response['success'] = 0;
			$response['message'] = $e->getMessage();
			echo json_encode($response);exit;

		}

	}

	public function getCategoryList() {

		$input = file_get_contents('php://input');
		$post = json_decode($input, true);
		$urlnew = url('');
		$new = str_replace('index.php', '', $urlnew.'/');

		$language="en";
	    if(!empty($post['language'])){ $language=$post['language']; }
	    App::setLocale($language);

		try
		{
            $getLangid = $this->get_language_code($language);
			$getCategorylist = Categories::leftjoin('categories_description','categories_description.categories_id','categories.categories_id')->where('parent_id',0)->where('categories_description.language_id',$getLangid)->get();
			$countryData = [];
			if (!empty($getCategorylist)) {
				foreach ($getCategorylist as $key => $value) {
					$categoryData['categoriesId'] = $value->categories_id;
					$categoryData['categoriesImg'] = $new.$value->categories_image;
					$categoryData['categoriesIcon'] = $new.$value->categories_icon;
					$categoryData['categoriesSortOrder'] = $value->sort_order !==Null ? $value->sort_order : "";
					$categoryData['categoriesName'] = $value->categories_name;
					$categoryData['color'] = $value->color;
					$categorylist[] = $categoryData;
				}
				$response = array('success' => 1, 'message' => Lang::get('labels.categories Loaded Successfully'), 'result' => $categorylist);
				echo json_encode($response, JSON_UNESCAPED_UNICODE);exit;
			} else {
				$response = array('success' => 0, 'message' => Lang::get('labels.No Data found'));
				echo json_encode($response);exit;
			}
		} catch (Exception $e) {
			$response = array('success' => 0, 'message' => $e->getMessage());
			echo json_encode($response, JSON_UNESCAPED_UNICODE);exit;
		}
	}

	public function subcategoryList() {
		$input = file_get_contents('php://input');
		$post = json_decode($input, true);
		$urlnew = url('');
		$new = str_replace('index.php', '', $urlnew.'/');

		$language="en";
	    if(!empty($post['language'])){ $language=$post['language']; }
	    App::setLocale($language);

		try
		{


			if ((!isset($post['categoriesId'])) || empty($post['categoriesId']))
			 {
				$response = array('success' => 0, 'message' => Lang::get('labels.All Fields Are Required'));
				echo json_encode($response, JSON_UNESCAPED_UNICODE);exit;
			}
			$getLangid = $this->get_language_code($language);
            $categoriesId = $post['categoriesId'];
			$subCategorylist = Categories::leftjoin('categories_description','categories_description.categories_id','categories.categories_id')->where('parent_id', $categoriesId)->where('language_id',$getLangid)->get();




			$subcountryData = [];
			if (count($subCategorylist) > 0)  {
				foreach ($subCategorylist as $key => $value) {
					$subcountryData['categoriesId'] = $value->categories_id;
					$subcountryData['categoriesImg'] = $new.$value->categories_image;
					$subcountryData['categoriesIcon'] = $new.$value->categories_icon;
					$subcountryData['categoriesSortOrder'] = $value->sort_order;
					$subcountryData['categoriesName'] = $value->categories_name;
					$subcountryData['color'] = $value->color;
					$subcategorylist[] = $subcountryData;
				}
				$response = array('success' => 1, 'message' => Lang::get('labels.subcategories Loaded Successfully'), 'result' => $subcategorylist);
				echo json_encode($response, JSON_UNESCAPED_UNICODE);exit;
			} else {
				$response = array('success' => 0, 'message' => Lang::get('labels.No Data found'));
				echo json_encode($response);exit;
			}
		} catch (Exception $e) {
			$response = array('success' => 0, 'message' => $e->getMessage());
			echo json_encode($response, JSON_UNESCAPED_UNICODE);exit;
		}
	}

	public function bannerList() {
		$input = file_get_contents('php://input');
		$post = json_decode($input, true);
		$urlnew = url('');
		$new = str_replace('index.php', '', $urlnew);

		$language="en";
	    if(!empty($post['language'])){ $language=$post['language']; }
	    App::setLocale($language);

		try
		{
            $getLangid = $this->get_language_code($language);
			$bannerList = Sliderimages::where('languages_id',$getLangid)->get();

			$bannerListData = [];
			if (!empty($bannerList)) {
				foreach ($bannerList as $key => $value) {
					$bannerListData['slidersId'] = $value->sliders_id;
					$bannerListData['slidersTitle'] = $value->sliders_title;
					$bannerListData['slidersUrl'] = $value->sliders_url;
					$bannerListData['sliders_Img'] = $new.$value->sliders_image;
					$bannerListData['slidersGroup'] = $value->sliders_group;
					$bannerListData['slidersHtmlText'] = $value->sliders_html_text;
					$bannerListData['Status'] = $value->status;
					$bannerListData['type'] = $value->type;
					$BannerListData[] = $bannerListData;
				}
				$response = array('success' => 1, 'message' => Lang::get('labels.Bannerlist Loaded Successfully'), 'result' => $BannerListData);
				echo json_encode($response, JSON_UNESCAPED_UNICODE);exit;
			} else {
				$response = array('success' => 0, 'message' => Lang::get('labels.No Data found'));
				echo json_encode($response);exit;
			}
		} catch (Exception $e) {
			$response = array('success' => 0, 'message' => $e->getMessage());
			echo json_encode($response, JSON_UNESCAPED_UNICODE);exit;
		}
	}

    public function popularProducts()
    {
        $input = file_get_contents('php://input');
		$post = json_decode($input, true);
		$urlnew = url('');
		$new = str_replace('index.php', '', $urlnew);

    	$language="en";
	    if(!empty($post['language'])){ $language=$post['language']; }
	    App::setLocale($language);

        $limit = 10;
        $min_price = 0;
        $max_price = 0;

        try{
                $data = array('page_number' => '0', 'type' => 'mostliked', 'limit' => $limit, 'min_price' => $min_price, 'max_price' => $max_price);

                if (empty($data['page_number']) or $data['page_number'] == 0) {
                    $skip = $data['page_number'] . '0';
                } else {
                    $skip = $data['limit'] * $data['page_number'];
                }

                $min_price = $data['min_price'];
                $max_price = $data['max_price'];
                $take = $data['limit'];
                $currentDate = time();
                $type = $data['type'];


                if ($type == "mostliked") {
                    $sortby = "products_liked";
                    $order = "DESC";

                }else {
                    $sortby = "products.products_liked";
                    $order = "desc";
                }

                $filterProducts = array();
                $eliminateRecord = array();
                $getLangid = $this->get_language_code($language);
                $categories = Products::join('manufacturers', 'manufacturers.manufacturers_id', '=', 'products.manufacturers_id', 'left')
                    ->join('manufacturers_info', 'manufacturers.manufacturers_id', '=', 'manufacturers_info.manufacturers_id', 'left')
                    ->join('products_description', 'products_description.products_id', '=', 'products.products_id', 'left')->where('language_id',$getLangid);


                $categories->LeftJoin('specials', function ($join) use ($currentDate) {
                    $join->on('specials.products_id', '=', 'products.products_id')->where('status', '=', '1')->where('expires_date', '>', $currentDate);
                })->select('products.*', 'products_description.*', 'manufacturers.*', 'manufacturers_info.manufacturers_url', 'specials.specials_new_products_price as discount_price');

                $categories->whereNotIn('products.products_id', function ($query) use ($currentDate) {
                    $query->select('flash_sale.products_id')->from('flash_sale')->where('flash_sale.flash_status', '=', '1');
                });

                if (!empty($data['products_id']) && $data['products_id'] != "") {
                    $categories->where('products.products_id', '=', $data['products_id']);
                }

                //for min and maximum price
                if (!empty($max_price)) {
                    $categories->whereBetween('products.products_price', [$min_price, $max_price]);
                }

                //wishlist customer id
                if ($type == "is_feature") {
                    $categories->where('products.is_feature', '=', 1);
                }

                $categories->where('products_status', '=', 1);


                $categories->orderBy($sortby, $order)->groupBy('products.products_id');

                //count
                $total_record = $categories->limit(50)->get();

                $products = $categories->limit(50)->get();

                $result = array();
                $result2 = array();

                //check if record exist
                if (count($products) > 0) {

                    $index = 0;
                    foreach ($products as $products_data) {
                        $products_id = $products_data->products_id;

                        $products_images = Productsimages::select('image')->where('products_id', '=', $products_id)->orderBy('sort_order', 'ASC')->get();

                        $img=[];

                        foreach ($products as $key => $value) {

                        	$products[$key]->products_image= url($value->products_image);

                        }

                        $categories = Productstocategories::leftjoin('categories', 'categories.categories_id', 'products_to_categories.categories_id')
                            ->leftjoin('categories_description', 'categories_description.categories_id', 'products_to_categories.categories_id')
                            ->select('categories.categories_id', 'categories_description.categories_name', 'categories.categories_image', 'categories.categories_icon', 'categories.parent_id')
                            ->where('products_id', '=', $products_id)
                            ->get();

                            $dasss=[];
                            foreach ($categories as $key => $value) {


                            	$categories[$key]->categories_image=url($value->categories_image);

                            }

                        $products_data->categories = $categories;
                        array_push($result, $products_data);

                        $options = array();
                        $attr = array();

                        $stocks = 0;
                        $stockOut = 0;
                        if ($products_data->products_type == '0') {
                            $stocks = Inventory::where('products_id', $products_data->products_id)->where('stock_type', 'in')->sum('stock');
                            $stockOut = Inventory::where('products_id', $products_data->products_id)->where('stock_type', 'out')->sum('stock');

                        }

                        $result[$index]->defaultStock = $stocks - $stockOut;

                        if (count($categories) > 0) {
                            $result[$index]->isLiked = '1';
                        } else {
                            $result[$index]->isLiked = '0';
                        }

                        $result[$index]->isLiked = '0';

                        $products_attribute = Productsattributes::where('products_id', '=', $products_id)->groupBy('options_id')->get();

                        if (count($products_attribute)) {
                            $index2 = 0;
                            foreach ($products_attribute as $attribute_data) {

                                $option_name = Productsoptions::leftJoin('products_options_descriptions', 'products_options_descriptions.products_options_id', '=', 'products_options.products_options_id')->select('products_options.products_options_id', 'products_options_descriptions.options_name as products_options_name', 'products_options_descriptions.language_id')->where('language_id', '=', Session::get('language_id'))->where('products_options.products_options_id', '=', $attribute_data->options_id)->get();

                                if (count($option_name) > 0) {

                                    $temp = array();
                                    $temp_option['id'] = $attribute_data->options_id;
                                    $temp_option['name'] = $option_name[0]->products_options_name;
                                    $temp_option['is_default'] = $attribute_data->is_default;
                                    $attr[$index2]['option'] = $temp_option;

                                    $attributes_value_query = Productsattributes::where('products_id', '=', $products_id)->where('options_id', '=', $attribute_data->options_id)->get();
                                    $k = 0;
                                    foreach ($attributes_value_query as $products_option_value) {

                                        $option_value = Productsoptionsvalues::leftJoin('products_options_values_descriptions', 'products_options_values_descriptions.products_options_values_id', '=', 'products_options_values.products_options_values_id')->select('products_options_values.products_options_values_id', 'products_options_values_descriptions.options_values_name as products_options_values_name')->where('products_options_values_descriptions.language_id', '=', Session::get('language_id'))->where('products_options_values.products_options_values_id', '=', $products_option_value->options_values_id)->get();

                                        $attributes = Productsattributes::where([['products_id', '=', $products_id], ['options_id', '=', $attribute_data->options_id], ['options_values_id', '=', $products_option_value->options_values_id]])->get();

                                        $temp_i['products_attributes_id'] = $attributes[0]->products_attributes_id;
                                        $temp_i['id'] = $products_option_value->options_values_id;
                                        $temp_i['value'] = $option_value[0]->products_options_values_name;
                                        $temp_i['price'] = $products_option_value->options_values_price;
                                        $temp_i['price_prefix'] = $products_option_value->price_prefix;
                                        array_push($temp, $temp_i);

                                    }
                                    $attr[$index2]['values'] = $temp;
                                    $result[$index]->attributes = $attr;
                                    $index2++;
                                }
                            }
                        } else {
                            $result[$index]->attributes = array();
                        }
                        $index++;
                    }

                    foreach ($result as $key => $products_data) {

                        if(!empty($post['customerId']))
                        {

                            $cusmorelikedId=Likedproducts::where('liked_customers_id',$post['customerId'])->where('liked_products_id',$products_data['products_id'])->get();
                        }
                        else
                        {
                             $cusmorelikedId=array();
                        }
                    	$productsData=[];

                    	$productsData['prouductId']                 = $products_data['products_id'];
                    	$productsData['productName']                = $products_data['products_name'];

                    	if (str_contains($products_data['products_price'], ',')) {
						    $priceproduct = substr($products_data['products_price'], 0, -1);
						}
						else {

							$ppp1 = number_format($products_data['products_price'], 2, '.', ',');
							$priceproduct = $ppp1;
						}


                    	$productsData['productPrice']               = $products_data['discount_price']== ! NULL ? $products_data['discount_price'] : $priceproduct ;
                    	$productsData['productOriginalPrice']       = $priceproduct;
                    	$products_image = str_replace('index.php', '',$products_data['products_image']);
                    	$productsData['productsImage']              = $products_image;
                    	$productsData['productOfferPercentage']     = $products_data['is_offer'];
                    	if(count($cusmorelikedId) > 0)
                   		{
                   			$customersId=$cusmorelikedId[0]->liked_customers_id;
                   			$productsData['productLiked']=  true;
                   		}
                   		else
                   		{
                   				$productsData['productLiked']= false;
                   		}

	                	$productsDatas[] = $productsData;
                    }

                    $responseData = array('success' => 1, 'total_record' => count($total_record), 'product_data' => $productsDatas, 'message' => Lang::get('labels.Popular product list'));
                    	echo json_encode($responseData, JSON_UNESCAPED_UNICODE);exit;
                } else {
                    $responseData = array('success' => 0, 'product_data' => $productsDatas, 'message' => Lang::get('labels.Empty record'), 'total_record' => count($total_record));
                    echo json_encode($responseData, JSON_UNESCAPED_UNICODE);exit;
                }

    	}catch (Exception $e) {
			$responseData = array('success' => 0, 'message' => $e->getMessage());
			echo json_encode($responseData, JSON_UNESCAPED_UNICODE);exit;
		}

    }

	public function orderList(){

		$input = file_get_contents('php://input');
		$post = json_decode($input, true);
		$urlnew = url('');
		$new = str_replace('index.php', '', $urlnew);

		$language="en";
	    if(!empty($post['language'])){ $language=$post['language']; }
	    App::setLocale($language);

		try
		{

			if ((!isset($post['customerId'])) || (empty($post['customerId']))) {

				$response = array('success' => 0, 'message' => Lang::get('labels.customers id is Required'));
				echo json_encode($response, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_UNESCAPED_UNICODE | JSON_HEX_AMP);exit;
			}
		    $getLangid = $this->get_language_code($language);
			$orderList = Order::join('orders_products', 'orders_products.orders_id', '=', 'orders.orders_id', 'left')
                    ->join('orders_status_history', 'orders_status_history.orders_id', '=', 'orders_products.orders_id', 'left')
                    ->join('products_description', 'products_description.products_id', '=', 'orders_products.products_id', 'left')
                    ->join('orders_status', 'orders_status.orders_status_id', '=', 'orders_status_history.orders_status_id', 'left')
                    ->where('orders.customers_id',$post['customerId'])
                    ->where('orders_status_history.orders_status_id',1)
                    ->where('products_description.language_id',$getLangid)
                    ->orderBy('date_purchased',"DESC")->get();



			if (count($orderList) > 0) {
                $checkOrderArray=[];
                $orderListing=[];

				foreach ($orderList as $key => $value) {

			 //   echo "<pre>";print_r($value);exit;
				    $orderListData1 = array();
				    $orderListData2 = array();
				    $order = array();

					if (!array_key_exists($value->orders_id,$orderListing))
                      {
                        $order['orderId'] = $value->orders_id;
                        $order['status'] = $value->orders_status_id;
                        $order['purchaseDate']     = date("F j, Y",strtotime($value->date_purchased));
    					$img = Productsimages::where('products_id', $value->products_id)->orderBy('sort_order', 'ASC')->first();
    					if(!empty($img)){
    						$im = str_replace("/index.php/", "/", url($img->image));
    					}else{
    						$im = str_replace("/index.php/", "/", url('resources/assets/images/user_profile/default_user.png'));
    					}

    					$orderListData1['productId']        = $value->products_id;
    					$orderListData1['productName']      = $value->products_name;
    					$orderListData1['productPrice']     = $value->products_price;
    					$orderListData1['productQty']       = $value->products_quantity;
    					$orderListData1['image']            = $im;
    				 	$order['product'][]=$orderListData1;
    				    $orderListing[$value->orders_id]=$order;
                      }
					else{
    					$img = Productsimages::where('products_id', $value->products_id)->orderBy('sort_order', 'ASC')->first();
    					if(!empty($img)){
    						$im = str_replace("/index.php/", "/", url($img->image));
    					}else{
    						$im = str_replace("/index.php/", "/", url('resources/assets/images/user_profile/default_user.png'));
    					}

    					$orderListData1['productId']        = $value->products_id;
    					$orderListData1['productName']      = $value->products_name;
    					$orderListData1['productPrice']     = $value->products_price;
    					$orderListData1['productQty']       = $value->products_quantity;
    					$orderListData1['image']            = $im;
    				 	$orderListing[$value->orders_id]['product'][]=$orderListData1;
					}
				}
// 			echo "<pre>";print_r($orderListing);exit;
				foreach($orderListing as $orderListings){
				    $checkOrderArray[] = $orderListings;
				}
				$response = array('success' => 1, 'message' => Lang::get('labels.Orderlist Successfully'),'result'=> $checkOrderArray);
				echo json_encode($response, JSON_UNESCAPED_UNICODE);exit;
			} else {
				$response = array('success' => 0, 'message' => Lang::get('labels.No Data found'));
				echo json_encode($response);exit;
			}
		} catch (Exception $e) {
			$response = array('success' => 0, 'message' => $e->getMessage());
			echo json_encode($response, JSON_UNESCAPED_UNICODE);exit;
		}
	}

	public function orderDetail(){

		$input = file_get_contents('php://input');
		$post = json_decode($input, true);
		$urlnew = url('');
		$new = str_replace('index.php', '', $urlnew);

		$language="en";
	    if(!empty($post['language'])){ $language=$post['language']; }
	    App::setLocale($language);

		try
		{

			if ((!isset($post['orderId'])) || (empty($post['orderId']))) {

				$response = array('success' => 0, 'message' => Lang::get('labels.Order id is Required'));
				echo json_encode($response, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_UNESCAPED_UNICODE | JSON_HEX_AMP);exit;
			}
		    $getLangid = $this->get_language_code($language);
			$orderList = Order::join('orders_products', 'orders_products.orders_id', '=', 'orders.orders_id', 'left')
                    ->join('orders_status_history', 'orders_status_history.orders_id', '=', 'orders_products.orders_id', 'left')
                    ->join('products_description', 'products_description.products_id', '=', 'orders_products.products_id', 'left')
                    ->join('orders_status', 'orders_status.orders_status_id', '=', 'orders_status_history.orders_status_id', 'left')->where('orders.orders_id',$post['orderId'])->groupBy('orders_products.products_id')->where('products_description.language_id',$getLangid)->get();

			$orderListData = array();
			$productData=array();
			if (count($orderList) > 0) {
				foreach ($orderList as $key => $value) {


					$img = Productsimages::where('products_id', $value->products_id)->orderBy('sort_order', 'ASC')->first();
					if(!empty($img)){
						$im = str_replace("/index.php/", "/", url($img->image));
					}else{
						$im = str_replace("/index.php/", "/", url('resources/assets/images/user_profile/default_user.png'));
					}

					$orderListData1['orderId']          = $value->orders_id;


					$orderListData1['shipTo']           = $value->billing_name;
					$orderListData1['orderDate']        = date("d M, Y",strtotime($value->date_purchased));
					$orderListData1['email']            = $value->email;
					$orderListData1['phoneNo']          = $value->billing_phone;
					$orderListData1['deliveredTo']      = $value->delivery_street_address;
					$orderListData1['invoice']          = "";
					$orderListData1['status']           = $value->orders_status_id;


                    $productData['productId']        = $value->products_id;
                    $productData['productName']      = $value->products_name;
                    $productData['productPrice']     = $value->products_price;
                    $productData['productQty']       = $value->products_quantity;
                    $productData['image']            = $im;
                    $productdata1[]=$productData;
				    $orderListData1['product']=$productdata1;


				}

				$response = array('success' => 1, 'message' => Lang::get('labels.Order Detail Succeessfully'), 'result' => $orderListData1);
				echo json_encode($response, JSON_UNESCAPED_UNICODE);exit;
			} else {
				$response = array('success' => 0, 'message' => Lang::get('labels.No Data found'));
				echo json_encode($response);exit;
			}
		} catch (Exception $e) {
			$response = array('success' => 0, 'message' => $e->getMessage());
			echo json_encode($response, JSON_UNESCAPED_UNICODE);exit;
		}
	}


	public function topSelledProduct()
    {


        $input = file_get_contents('php://input');
		$post = json_decode($input, true);
		$urlnew = url('');
		$new = str_replace('index.php', '', $urlnew);

		$language="en";
	    if(!empty($post['language'])){ $language=$post['language']; }
	    App::setLocale($language);
        $limit = 10;
        $min_price = 0;
        $max_price = 0;

        try{
                $data = array('page_number' => '0', 'type' => 'topseller', 'limit' => $limit, 'min_price' => $min_price, 'max_price' => $max_price);

                if (empty($data['page_number']) or $data['page_number'] == 0) {
                    $skip = $data['page_number'] . '0';
                } else {
                    $skip = $data['limit'] * $data['page_number'];
                }

                $min_price = $data['min_price'];
                $max_price = $data['max_price'];
                $take = $data['limit'];
                $currentDate = time();
                $type = $data['type'];

                if ($type == "topseller") {
                    $sortby = "count(orders_products.orders_id)";
                    $order = "DESC";

                }else {
                    $sortby = "COUNT(orders_products.orders_id)";
                    $order = "desc";
                }

                $filterProducts = array();
                $eliminateRecord = array();
                 $getLangid = $this->get_language_code($language);
                $categories = Products::join('orders_products','orders_products.products_id','=', 'products.products_id', 'left')->join('manufacturers', 'manufacturers.manufacturers_id', '=', 'products.manufacturers_id', 'left')
                    ->join('manufacturers_info', 'manufacturers.manufacturers_id', '=', 'manufacturers_info.manufacturers_id', 'left')
                    ->join('products_description', 'products_description.products_id', '=', 'products.products_id', 'left')->select('products_description.*','products.*',DB::raw('count(orders_products.orders_id) AS count_link'))->where('products_description.language_id',$getLangid);

// SELECT p.products_id, COUNT(o.orders_id)
// FROM
//     products p LEFT JOIN
//     orders_products o ON o.products_id = p.products_id
//  GROUP BY p.products_id
//  ORDER BY COUNT(o.orders_id) DESC LIMIT 10
// SELECT p.products_id, COUNT(o.orders_id)
// FROM
//     products p LEFT JOIN
//     orders_products o ON o.products_id = p.products_id
//  GROUP BY p.products_id
//  ORDER BY COUNT(o.orders_id) DESC LIMIT 10
// DB::raw('(SELECT count(products_id) FROM orders_products ) as count_links')

 // DB::raw(COUNT('orders_products.orders_id') as   	'total_count'))

                //wishlist customer id
                if ($type == "is_feature") {
                    $categories->where('products.is_feature', '=', 1);
                }

                $categories->where('products_status', '=', 1);

                $categories->orderBy("count_link","DESC")->groupBy('products.products_id');

                //count
                $total_record = $categories->limit(50)->get();

                $products = $categories->limit(50)->get();

                $result = array();
                $result2 = array();

                //check if record exist
                if (count($products) > 0) {

                    $index = 0;
                    foreach ($products as $products_data) {
                        $products_id = $products_data->products_id;

                        $products_images = Productsimages::select('image')->where('products_id', '=', $products_id)->orderBy('sort_order', 'ASC')->get();

                        $img=[];

                        foreach ($products as $key => $value) {

                        	$products[$key]->products_image= url($value->products_image);

                        }

                        $categories = Productstocategories::leftjoin('categories', 'categories.categories_id', 'products_to_categories.categories_id')
                            ->leftjoin('categories_description', 'categories_description.categories_id', 'products_to_categories.categories_id')
                            ->select('categories.categories_id', 'categories_description.categories_name', 'categories.categories_image', 'categories.categories_icon', 'categories.parent_id')
                            ->where('products_id', '=', $products_id)
                            ->get();

                            $dasss=[];
                            foreach ($categories as $key => $value) {


                            	$categories[$key]->categories_image=url($value->categories_image);

                            }

                        $products_data->categories = $categories;
                        array_push($result, $products_data);

                        $options = array();
                        $attr = array();

                        $stocks = 0;
                        $stockOut = 0;
                        if ($products_data->products_type == '0') {
                            $stocks = Inventory::where('products_id', $products_data->products_id)->where('stock_type', 'in')->sum('stock');
                            $stockOut = Inventory::where('products_id', $products_data->products_id)->where('stock_type', 'out')->sum('stock');

                        }

                        $result[$index]->defaultStock = $stocks - $stockOut;

                        if (count($categories) > 0) {
                            $result[$index]->isLiked = '1';
                        } else {
                            $result[$index]->isLiked = '0';
                        }

                        $result[$index]->isLiked = '0';

                        $products_attribute = Productsattributes::where('products_id', '=', $products_id)->groupBy('options_id')->get();

                        if (count($products_attribute)) {
                            $index2 = 0;
                            foreach ($products_attribute as $attribute_data) {

                                $option_name = Productsoptions::leftJoin('products_options_descriptions', 'products_options_descriptions.products_options_id', '=', 'products_options.products_options_id')->select('products_options.products_options_id', 'products_options_descriptions.options_name as products_options_name', 'products_options_descriptions.language_id')->where('language_id', '=', Session::get('language_id'))->where('products_options.products_options_id', '=', $attribute_data->options_id)->get();

                                if (count($option_name) > 0) {

                                    $temp = array();
                                    $temp_option['id'] = $attribute_data->options_id;
                                    $temp_option['name'] = $option_name[0]->products_options_name;
                                    $temp_option['is_default'] = $attribute_data->is_default;
                                    $attr[$index2]['option'] = $temp_option;

                                    $attributes_value_query = Productsattributes::where('products_id', '=', $products_id)->where('options_id', '=', $attribute_data->options_id)->get();
                                    $k = 0;
                                    foreach ($attributes_value_query as $products_option_value) {

                                        $option_value = Productsoptionsvalues::leftJoin('products_options_values_descriptions', 'products_options_values_descriptions.products_options_values_id', '=', 'products_options_values.products_options_values_id')->select('products_options_values.products_options_values_id', 'products_options_values_descriptions.options_values_name as products_options_values_name')->where('products_options_values_descriptions.language_id', '=', Session::get('language_id'))->where('products_options_values.products_options_values_id', '=', $products_option_value->options_values_id)->get();

                                        $attributes = Productsattributes::where([['products_id', '=', $products_id], ['options_id', '=', $attribute_data->options_id], ['options_values_id', '=', $products_option_value->options_values_id]])->get();

                                        $temp_i['products_attributes_id'] = $attributes[0]->products_attributes_id;
                                        $temp_i['id'] = $products_option_value->options_values_id;
                                        $temp_i['value'] = $option_value[0]->products_options_values_name;

                                        if (str_contains($products_option_value->options_values_price, ',')) {
										    $priceproduct = substr($products_option_value->options_values_price, 0, -1);
										}
										else {

											$ppp1 = number_format($products_option_value->options_values_price, 2, '.', ',');
											$priceproduct = $ppp1;
										}


                                        $temp_i['price'] = $priceproduct;
                                        $temp_i['price_prefix'] = $products_option_value->price_prefix;
                                        array_push($temp, $temp_i);

                                    }
                                    $attr[$index2]['values'] = $temp;
                                    $result[$index]->attributes = $attr;
                                    $index2++;
                                }
                            }
                        } else {
                            $result[$index]->attributes = array();
                        }
                        $index++;
                    }

                   // echo "<pre>"; print_r($result); exit;

                    foreach ($result as $key => $products_data) {

                        if(!empty($post['customerId']))
                        {
                            $cusmorelikedId=Likedproducts::where('liked_customers_id',$post['customerId'])->where('liked_products_id',$products_data['products_id'])->get();
                        }
                        else
                        {
                              $cusmorelikedId=array();
                        }
                    	$productsData=[];

                    	$productsData['prouductId']             = $products_data['products_id'];
                    	$productsData['productName']            = $products_data['products_name'];


                    		if (str_contains($products_data['products_price'], ',')) {
						        $priceproduct = substr($products_data['products_price'], 0, -1);
							}
							else {

								$ppp1 = number_format($products_data['products_price'], 2, '.', ',');
								$priceproduct = $ppp1;
							}

                    	$productsData['productPrice']           = $products_data['discount_price']== ! NULL ? $products_data['discount_price'] : $priceproduct ;
                    	$productsData['productOriginalPrice']   = $priceproduct;
                    	$products_image = str_replace('index.php', '', $products_data['products_image']);
                    	$productsData['productsImage']          =$products_image;
                    	$productsData['productOfferPercentage'] = $products_data['is_offer'];
                        if(count($cusmorelikedId) > 0)
                   		{
                   			$customersId=$cusmorelikedId[0]->liked_customers_id;
                   			$productsData['productLiked']= true;
                   		}
                   		else
                   		{
                   				$productsData['productLiked']= false;
                   		}

	                	$productsDatas[] = $productsData;
                    }
                    $responseData = array('success' => 1, 'message' => Lang::get('labels.Topselled product list'), 'total_record' => count($total_record),'product_data' => $productsDatas);
                    	echo json_encode($responseData, JSON_UNESCAPED_UNICODE);exit;
                } else {
                    $responseData = array('success' => 0, 'product_data' => $productsDatas, 'message' => Lang::get('labels.Empty record'), 'total_record' => count($total_record));
                    echo json_encode($responseData, JSON_UNESCAPED_UNICODE);exit;
                }

    	}catch (Exception $e) {
			$responseData = array('success' => 0, 'message' => $e->getMessage());
			echo json_encode($responseData, JSON_UNESCAPED_UNICODE);exit;
		}

    }

    public function Productlist(Request $request)
    {

    	$input = file_get_contents('php://input');
		$post = json_decode($input, true);
		$post = $request->all();
		$urlnew = url('');
		$new = str_replace('index.php', '', $urlnew.'/');

		$language="en";
	    if(!empty($post['language'])){ $language=$post['language']; }
	    App::setLocale($language);

    	$limit = 10;
        $min_price = 0;
        $max_price = 0;


        try
        {
            $data = array('page_number' => '0', 'limit' => $limit, 'min_price' => $min_price, 'max_price' => $max_price);
            if (empty($post['pagination']) or $post['pagination'] == 0) {
                $skip = $post['pagination'] . '0';
            } else {
                $skip = $data['limit'] *$post['pagination'];
            }
            $post['sortbyname']=0;
            $post['customerID']=0;
            $sortbyname=0;
            $post['sortbyprice']=0;
            $post['filter']="";
            $sortbyname=$post['sortbyname'];
            $sortbyprice=$post['sortbyprice'];
            $filter=$post['filter'];


            $min_price = $data['min_price'];
            $max_price = $data['max_price'];
            $take = $data['limit'];
            $currentDate = time();
            $eliminateRecord = array();
            $getLangid = $this->get_language_code($post['language']);
            $categories = Products::join('manufacturers', 'manufacturers.manufacturers_id', '=', 'products.manufacturers_id', 'left')
                    ->join('manufacturers_info', 'manufacturers.manufacturers_id', '=', 'manufacturers_info.manufacturers_id', 'left')
                    ->join('products_description', 'products_description.products_id', '=', 'products.products_id', 'left')
                    ->where('language_id',$getLangid);


            if (!empty($data['products_id']) && $data['products_id'] != "") {
                $categories = $categories->where('products.products_id', '=', $data['products_id']);
            }

            if(!empty($post['categories'])){
            $categoryId=explode(",",$post['categories']);
            $categoryId = Categories::leftjoin('categories_description','categories_description.categories_id','categories.categories_id')->whereIn('parent_id', $categoryId)->where('language_id',$getLangid)->get();

            $categoriesid=[];
            if(count($categoryId) > 0)
            {
            	foreach ($categoryId as $key => $value) {

            			$categoriesid[]=$value->categories_id;

            		# code...
            	}
            	$categories = $categories->LeftJoin('products_to_categories', 'products.products_id', '=', 'products_to_categories.products_id');
				$categories = $categories->where(function ($query) use ($categoriesid) {
		        $query->whereIn('products_to_categories.categories_id', $categoriesid);
		        });

            }
     		else{

            $categoryId=explode(",",$post['categories']);
            $categories = $categories->LeftJoin('products_to_categories', 'products.products_id', '=', 'products_to_categories.products_id');
			$categories = $categories->where(function ($query) use ($categoryId) {
		        $query->whereIn('products_to_categories.categories_id', $categoryId);


			});
		}

		//	$categorylist=$categories->get();
            }
            	//$categorylist=$categories->get();
          //  echo "<pre>"; print_r($categorylist); exit;



			if(!empty( $post['brands'])){
                $brandId=explode(",",$post['brands']);
			$categories = $categories->orwhere(function ($query) use ($brandId) {
			    $query->whereIn('products.brand_id', $brandId);
					});
			}

			if(!empty($filter))
			{
				$categories = $categories->where(function ($query) use ($filter) {
				$query->where('products_description','LIKE', '%'.$filter.'%')
				     ->orWhere('products_name','LIKE', '%'.$filter.'%');
				});
			}

            $categories = $categories->groupBy('products.products_id');
            $total_record = $categories->get();
            $products = $categories->skip($skip)->take($take)->get();

            $result = array();
            $result2 = array();

                //check if record exist
                if (count($products) > 0) {

                    $index = 0;
                    foreach ($products as $products_data) {
                        $products_id = $products_data->products_id;

                        $products_images = Productsimages::select('image')->where('products_id', '=', $products_id)->orderBy('sort_order', 'ASC')->get();

                        $img=[];

                        foreach ($products as $key => $value) {

                        	$products[$key]->products_image= url($value->products_image);

                        }

                        $categories = Productstocategories::leftjoin('categories', 'categories.categories_id', 'products_to_categories.categories_id')
                            ->leftjoin('categories_description', 'categories_description.categories_id', 'products_to_categories.categories_id')
                            ->select('categories.categories_id', 'categories_description.categories_name', 'categories.categories_image', 'categories.categories_icon', 'categories.parent_id')
                            ->where('products_id', '=', $products_id)
                            ->get();

                            foreach ($categories as $key => $value) {

                            	$categories[$key]->categories_image=url($value->categories_image);

                            }

                        $products_data->categories = $categories;
                        array_push($result, $products_data);

                        $options = array();
                        $attr = array();

                        $stocks = 0;
                        $stockOut = 0;
                        if ($products_data->products_type == '0') {
                            $stocks = Inventory::where('products_id', $products_data->products_id)->where('stock_type', 'in')->sum('stock');
                            $stockOut = Inventory::where('products_id', $products_data->products_id)->where('stock_type', 'out')->sum('stock');

                        }

                        $result[$index]->defaultStock = $stocks - $stockOut;

                        if (count($categories) > 0) {
                            $result[$index]->isLiked = '1';
                        } else {
                            $result[$index]->isLiked = '0';
                        }

                        $result[$index]->isLiked = '0';

                        $products_attribute = Productsattributes::where('products_id', '=', $products_id)->groupBy('options_id')->get();

                        if (count($products_attribute)) {
                            $index2 = 0;
                            foreach ($products_attribute as $attribute_data) {

                                $option_name = Productsoptions::leftJoin('products_options_descriptions', 'products_options_descriptions.products_options_id', '=', 'products_options.products_options_id')->select('products_options.products_options_id', 'products_options_descriptions.options_name as products_options_name', 'products_options_descriptions.language_id')->where('language_id', '=', Session::get('language_id'))->where('products_options.products_options_id', '=', $attribute_data->options_id)->get();

                                if (count($option_name) > 0) {

                                    $temp = array();
                                    $temp_option['id'] = $attribute_data->options_id;
                                    $temp_option['name'] = $option_name[0]->products_options_name;
                                    $temp_option['is_default'] = $attribute_data->is_default;
                                    $attr[$index2]['option'] = $temp_option;
                                    $attributes_value_query = Productsattributes::where('products_id', '=', $products_id)->where('options_id', '=', $attribute_data->options_id)->get();
                                    $k = 0;
                                    foreach ($attributes_value_query as $products_option_value) {

                                        $option_value = Productsoptionsvalues::leftJoin('products_options_values_descriptions', 'products_options_values_descriptions.products_options_values_id', '=', 'products_options_values.products_options_values_id')->select('products_options_values.products_options_values_id', 'products_options_values_descriptions.options_values_name as products_options_values_name')->where('products_options_values_descriptions.language_id', '=', Session::get('language_id'))->where('products_options_values.products_options_values_id', '=', $products_option_value->options_values_id)->get();

                                        $attributes = Productsattributes::where([['products_id', '=', $products_id], ['options_id', '=', $attribute_data->options_id], ['options_values_id', '=', $products_option_value->options_values_id]])->get();

                                        $temp_i['products_attributes_id'] = $attributes[0]->products_attributes_id;
                                        $temp_i['id'] = $products_option_value->options_values_id;
                                        $temp_i['value'] = $option_value[0]->products_options_values_name;
                                        $temp_i['price'] = $products_option_value->options_values_price;
                                        $temp_i['price_prefix'] = $products_option_value->price_prefix;
                                        array_push($temp, $temp_i);

                                    }
                                    $attr[$index2]['values'] = $temp;
                                    $result[$index]->attributes = $attr;
                                    $index2++;
                                }
                            }
                        } else {
                            $result[$index]->attributes = array();
                        }
                        $index++;
                    }

                    foreach ($result as $key => $products_data) {


                    	if(!empty($post['customerId']) && !empty($post['customerId']))
                    	{
                    		$cusmorelikedId=Likedproducts::where('liked_customers_id',$post['customerId'])->where('liked_products_id',$products_data['products_id'])->get();
                    	}
                    	else
                    	{
                    		$cusmorelikedId= array();
                    	}

                    	$productsData=[];

                    	if (str_contains($products_data['products_price'], ',')) {
						    $priceproduct = substr($products_data['products_price'], 0, -1);
						}
						else {

							$ppp1 = number_format($products_data['products_price'], 2, '.', ',');
							$priceproduct = $ppp1;
						}
                    	$productsData['prouductId']             = $products_data['products_id'];
                    	$productsData['productName']            = $products_data['products_name'];
                    	$productsData['productPrice']           = $products_data['discount_price']== ! NULL ? $products_data['discount_price'] : $priceproduct;
                    	$productsData['productOriginalPrice']   = $priceproduct;

                    	//echo "<pre>"; print_r($new); exit;

                    	// $abs_path = '/var/www/html/'.$products_data['products_image'];
						// $string='what i want is you';
						// $string = explode(" ", $string);

						// $omit_words = array('the','i','we','you','what','is');
						// $result=array_diff($string,$omit_words);

					// echo "<pre>";	print_r($result); exit;
 //
                    	// echo public_path($products_data['products_image']); exit;
						// if (file_exists($abs_path)) {
					if($products_data['products_image']!="" ){
                    	// if($products_data['products_image']==""){
                    		$image = str_replace('index.php', '', $products_data['products_image']);
        						//$image = str_replace("/index.php/", "/", url('resources/views/admin/images/admin_logo/logo.png'));

        				}else{
        						//$image = str_replace('index.php', '', $products_data['products_image']);
        						$image = str_replace("/index.php/", "/", url('resources/views/admin/images/admin_logo/logo.png'));
        				}
                    	$productsData['productsImage']          = $image;
                    	$productsData['productOfferPercentage'] = $products_data['is_offer'];
                    	$productsData['ScheduledDelivery']      = $products_data['delivery_days'];


                    	if(count($cusmorelikedId) > 0)
                   		{
                   			$customersId=$cusmorelikedId[0]->liked_customers_id;
                   			$productsData['productLiked']=true;
                   		}
                   		else
                   		{
                   			$productsData['productLiked']=false;
                   		}

	                	$productsDatas[] = $productsData;

                    }

                    $responseData = array('success' => 1, 'total_record' => count($total_record), 'product_data' => $productsDatas, 'message' => Lang::get('labels.Product list'));
                    	echo json_encode($responseData, JSON_UNESCAPED_UNICODE);exit;
                } else {
                    $responseData = array('success' => 0,'message' => Lang::get('labels.Empty record'), 'total_record' => count($total_record));
                    echo json_encode($responseData, JSON_UNESCAPED_UNICODE);exit;
                }

    	}catch (Exception $e) {
			$responseData = array('success' => 0, 'message' => $e->getMessage());
			echo json_encode($responseData, JSON_UNESCAPED_UNICODE);exit;
		}

    }

  public function invoiceSummary(Request $request)
	{
		$input = file_get_contents('php://input');
		$post = json_decode($input, true);
		$urlnew = url('');
		$new = str_replace('index.php', '', $urlnew);

		$language="en";
	    if(!empty($post['language'])){ $language=$post['language']; }
	    App::setLocale($language);

		try
		{
			if ((!isset($post['orderId'])) || (empty($post['orderId']))) {

				$response = array('success' => 0, 'message' => Lang::get('labels.Order id is Required'));
				echo json_encode($response, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_UNESCAPED_UNICODE | JSON_HEX_AMP);exit;
			}
		  //  DB::table('table_names')->where('id',$id)->first('column_name');
		    $getLangid = $this->get_language_code($post['language']);
			$orderList = Order::join('orders_products', 'orders_products.orders_id', '=', 'orders.orders_id', 'left')
			        ->join('products_description', 'products_description.products_id', '=', 'orders_products.products_id', 'left')
                    ->join('orders_status_history', 'orders_status_history.orders_id', '=', 'orders_products.orders_id', 'left')
                    ->join('orders_status', 'orders_status.orders_status_id', '=', 'orders_status_history.orders_status_id', 'left')
                     ->where('products_description.language_id',$getLangid)
                    ->where('orders.orders_id',$post['orderId'])->get();
            //echo "<pre>"; print_r($orderList); exit;
                    	$subTotal = 0;
			$orderListData = array();
			if (count($orderList) > 0) {

				foreach ($orderList as $key => $value) {
					$orderListData1 = array();

					$orderListData1['productName'] = $value->products_name;
					$orderListData1['productPrice'] = $value->products_price;
					$orderListData1['productQty'] = $value->products_quantity;
					$subTotal += $value->products_price * $value->products_quantity;

					$orderListData['orderId'] = $value->orders_id;
					$orderListData['orderPrice'] = $value->order_price;
					$orderListData['shippingCost'] = $value->shipping_cost;
					$orderListData['totalTax'] = $value->total_tax;
					$orderListData['orderDate'] = date("d-m-Y",strtotime($value->date_purchased));
					$orderListData['shipTo'] = $value->billing_name;
					$orderListData['email'] = $value->email;
					$orderListData['phoneNo'] = $value->billing_phone;
					$orderListData['deliveredTo'] = $value->delivery_street_address;
					$orderListData['status'] =$post['language']=="en" ? $value->orders_status_name : $value->orders_ar_status_name ;
					$orderListData['subTotal'] = $subTotal;

					$orderListData2[] = $orderListData1;
					$orderListData['product'] = $orderListData2;
				}
				$response = array('success' => 1, 'message' => Lang::get('labels.Invoice Summary Successfully'), 'result' => $orderListData);
				echo json_encode($response, JSON_UNESCAPED_UNICODE);exit;
			} else {
				$response = array('success' => 0, 'message' => Lang::get('labels.No Data found'));
				echo json_encode($response);exit;
			}
		} catch (Exception $e) {
			$response = array('success' => 0, 'message' => $e->getMessage());
			echo json_encode($response, JSON_UNESCAPED_UNICODE);exit;
		}
	}

    public function sendInvoice(Request $request)
	{
		$input = file_get_contents('php://input');
		$post = json_decode($input, true);
		$urlnew = url('');
		$new = str_replace('index.php', '', $urlnew);

		$language="en";
	    if(!empty($post['language'])){ $language=$post['language']; }
	    App::setLocale($language);

		try
		{
			if ((!isset($post['orderId'])) || (empty($post['orderId']))) {

				$response = array('success' => 0, 'message' => Lang::get('labels.Order id is Required'));
				echo json_encode($response, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_UNESCAPED_UNICODE | JSON_HEX_AMP);exit;
			}
		     $getLangid = $this->get_language_code($post['language']);
			$invoiceorderList = Order::join('orders_products', 'orders_products.orders_id', '=', 'orders.orders_id', 'left')
					->select('orders_products.*','orders.*', 'products.products_image as image','products.products_price as pro_price','orders_status_history.*','orders_status.*','products_description.*')
			        ->join('products_description', 'products_description.products_id', '=', 'orders_products.products_id', 'left')
                    ->join('orders_status_history', 'orders_status_history.orders_id', '=', 'orders_products.orders_id', 'left')
                    ->join('orders_status', 'orders_status.orders_status_id', '=', 'orders_status_history.orders_status_id', 'left')
                    ->join('products', 'products.products_id','=', 'orders_products.products_id')
                    ->where('products_description.language_id',$getLangid)
                    ->where('orders.orders_id',$post['orderId'])->get();


	//	echo "<pre>";print_r($orderList[0]->products_name);exit;
			if (count($invoiceorderList) > 0) {

			    $orderListData = array();
				$subTotal = 0;
				foreach ($invoiceorderList as $key => $value) {
					$orderListData1 = array();
					$orderListData1['productId']        = $value->products_id;
					$orderListData1['productName']      = $value->products_name;
					$orderListData1['productPrice']     = $value->products_price;
					$orderListData1['productQty']       = $value->products_quantity;
					$img = Productsimages::where('products_id', $value->products_id)->orderBy('sort_order', 'ASC')->first();
					if(!empty($img)){
						$im = str_replace("/index.php/", "/", url($img->image));
					}else{
						$im = str_replace("/index.php/", "/", url('resources/assets/images/user_profile/default_user.png'));
					}
					$orderListData1['image']            = $im;
					$subTotal += $value->final_price;
					$orderListData['orderId']           = $value->orders_id;
					$orderListData['orderPrice']        = $value->order_price;
					$orderListData['shippingCost']      = $value->shipping_cost;
					$orderListData['totalTax']          = $value->total_tax;
					$orderListData['orderDate']         = date("d-m-Y",strtotime($value->date_purchased));
					$orderListData['shipTo']            = $value->billing_name;
					$orderListData['email']             = $value->email;
					$orderListData['phoneNo']           = $value->billing_phone;
					$orderListData['deliveredTo']       = $value->delivery_street_address;
					$orderListData['status']            = $value->orders_status_id;
					$orderListData['subTotal']          = $subTotal;

					$orderListData2[]                   = $orderListData1;
					$orderListData['product']           = $orderListData2;
				}



 $html = '';

                $html .= '<!DOCTYPE html><html><head>';
            $html .= '<meta charset="utf-8">
                	<meta http-equiv="Content-type" content="text/html; charset=UTF-8">
                	<meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no, minimum-scale=1, maximum-scale=1, viewport-fit=cover, shrink-to-fit=no">

                	</head>';

            $html.='<body style="margin:0;font-family: DejaVu Sans, sans-serif; text-align:left" >
            <div class="wrapper wrapper2" style="padding: 20px;">

	            <section class="invoice" bgcolor="#fff" style="margin: 15px; position: relative; background: #fff; border: 1px solid #f4f4f4; padding: 20px; display: inline-block; font-size: 14px;
    line-height: 1.42857143; color: #333;">';

               $html.='<div class="row">
                    <div class="col-xs-12">
                    <table style="width:100%; padding-bottom: 25px; table-layout: fixed; margin: 10px 0 20px 0; border-bottom: 1px solid #eee; font-size: 14px; line-height: 1.42857143;
          color: #333;">

                    <tr>
                        <td style="font-size: 22px; line-height: 1; padding: 0;"><i class="fa fa-globe"></i> '.trans('labels.OrderID').'# '.$invoiceorderList[0]['orders_id'].' </td>
                         <td align="center" style="text-align:center;"><img src="'.asset('resources/views/admin/images/admin_logo/logo.png').'" style="width: 100px;" width="100px" /></td>
                        <td style="text-align:right; line-height: 1; padding: 0;"><small style="text-align:right; color: #666; display: block; margin-top: 5px;">'.trans('labels.OrderedDate').': '.date('m/d/Y', strtotime($invoiceorderList[0]['date_purchased'])).'</small></td>
                    </tr>
                   </table>
                    </div>
                    <!-- /.col -->
                  </div>
                  <!-- info row -->

                  <table style="width:100%">
                   <tr>
                     <td style="padding: 0 15px 0 0; vertical-align: top; text-align:left;" align="left">
                      <label>'.trans('labels.CustomerInfo').':</label>
                      <div style="margin-bottom: 20px; font-style: normal; line-height: 1.42857143;">
                        <span><strong>'.$invoiceorderList[0]['customers_name'].'</strong></span><br><span>'.$invoiceorderList[0]['customers_street_address'].'</span><br><span>'.$invoiceorderList[0]['customers_city'].',</span><span>'.$invoiceorderList[0]['customers_state'].'</span><span>'.$invoiceorderList[0]['customers_postcode'].',</span><span>'.$invoiceorderList[0]['customers_country'].'</span><br><span>'.trans('labels.Phone').': '.$invoiceorderList[0]['customers_telephone'].'</span><br><span>'.trans('labels.Email').': '.$invoiceorderList[0]['email'].'</span>
                      </div>
                    </td>
                    <!-- /.col -->
                   <td style="padding: 0 15px 0 0; vertical-align: top; text-align:left;" align="left">
                      <label>'.trans('labels.ShippingInfo').':</label>
                      <div style="margin-bottom: 20px; font-style: normal; line-height: 1.42857143;">
                        <strong>'.$invoiceorderList[0]['delivery_name'].'</strong><br><span>'.trans('labels.Phone').'</span><span>'.$invoiceorderList[0]['delivery_phone'].'</span><br>'.$invoiceorderList[0]['delivery_street_address'].'<br>'.$invoiceorderList[0]['delivery_city'].','.$invoiceorderList[0]['delivery_state'].''.$invoiceorderList[0]['delivery_postcode'].','.$invoiceorderList[0]['delivery_country'].'<br><strong>'.trans('labels.ShippingMethod').':</strong> '.$invoiceorderList[0]->shipping_method.'<br><strong>'.trans('labels.ShippingCost').':</strong>';
                      if (!empty($invoiceorderList[0]['shipping_cost']))
                      {

                       $invoiceorderList[0]['currency'] ;
                       $invoiceorderList[0]['shipping_cost'];

                      }


                      $html.='<br>
                    </div>
                    </td>
                    <!-- /.col -->
                    <td style="padding: 0 15px 0 0; vertical-align: top; text-align:left;" align="left">
                      '.trans('labels.BillingInfo').'
                      <div style="margin-bottom: 20px; font-style: normal; line-height: 1.42857143;">
                        <strong>'.$invoiceorderList[0]['billing_name'].'</strong><br><span>'.trans('labels.Phone').':</span><span>'.$invoiceorderList[0]['billing_phone'].'</span><br><span>'.$invoiceorderList[0]['billing_street_address'].'</span><br><span>'.$invoiceorderList[0]['billing_city'].',<span></span>'.$invoiceorderList[0]['billing_state'].'</span><span>'.$invoiceorderList[0]['billing_postcode'].',</span><span>'.$invoiceorderList[0]['billing_country'].'</span><br>
                      </div>
                    </td>
                  </tr>
                  </table>
                  <!-- Table row -->
                  <div class="row">
                    <div class="col-xs-12 table-responsive">
                      <table style="width: 100%; max-width: 100%; margin-bottom: 20px; border-spacing: 0; border-collapse: collapse;">
                        <thead>
                        <tr>
                          <th style="border-bottom: 2px solid #f4f4f4; vertical-align: bottom; padding: 8px; line-height: 1.42857143;"> '.trans('labels.Qty').'</th>
                          <th style="border-bottom: 2px solid #f4f4f4; vertical-align: bottom; padding: 8px; line-height: 1.42857143;"> '.trans('labels.ProductName').'</th>
                          <th style="border-bottom: 2px solid #f4f4f4; vertical-align: bottom; padding: 8px; line-height: 1.42857143;"> '.trans('labels.ProductModal').'</th>
                          <th style="border-bottom: 2px solid #f4f4f4; vertical-align: bottom; padding: 8px; line-height: 1.42857143;">'.trans('labels.Options').'</th>
                          <th style="border-bottom: 2px solid #f4f4f4; vertical-align: bottom; padding: 8px; line-height: 1.42857143;"> '.trans('labels.Price').'</th>
                        </tr>
                        </thead>
                        <tbody>';
                          $subtotals = 0;
                        foreach ($invoiceorderList as $key => $value)
                       {


                        $html.=' <tr style="background-color: #f9f9f9;">
                           <td style="border-bottom: 2px solid #f4f4f4; vertical-align: bottom; padding: 8px; line-height: 1.42857143;">'.$value->products_quantity.'</td>
                           <td dir="rtl" style="border-bottom: 2px solid #f4f4f4; vertical-align: bottom; padding: 8px; line-height: 1.42857143;">
                                  '.$value->products_name.'
                            </td>
                             <td style="border-bottom: 2px solid #f4f4f4; vertical-align: bottom; padding: 8px; line-height: 1.42857143;">
                                '.$value->products_model.'
                            </td>

                            <td style="border-bottom: 2px solid #f4f4f4; vertical-align: bottom; padding: 8px; line-height: 1.42857143;"></td>
                            <td style="border-bottom: 2px solid #f4f4f4; vertical-align: bottom; padding: 8px; line-height: 1.42857143;">IQ '. $value->pro_price.'</td>
                         </tr>';
                           $subtotals +=(float)number_format(str_replace(",", "", $value->pro_price), 3, ".", "");

                       }

                       $html.='</tbody>
                      </table>
                    </div>


                  </div>';



            $html.='<table style="width: 100%; table-layout: fixed;">
            <tr>
                <td style="width:58.33333333%; vertical-align: top; padding: 0 15px 0 0;">
                  <p class="lead" style="margin-bottom:10px; font-size: 21px; font-weight: 300; line-height: 1.4;">'.trans('labels.PaymentMethods').':</p>
                  <p class="text-muted well well-sm no-shadow" style="text-transform:capitalize; min-height: 20px; padding: 9px; border-radius: 3px; margin-bottom: 20px; background-color: #f5f5f5;
                        border: 1px solid #e3e3e3;">
                   	'.str_replace('_',' ',  $invoiceorderList[0]->payment_method).'
                  </p>

                </td>
                <td style="width:41.66666667%; vertical-align: top; padding: 0 0 0 15px;">

                  <div class="table-responsive ">
                    <table class="table order-table" align="left" style="text-align: left; width: 100%; max-width: 100%; margin-bottom: 20px; border-spacing: 0; border-collapse: collapse;">
                      <tr>
                        <th align="left" style="width:50%" style="text-align: left; padding: 8px; line-height: 1.42857143; vertical-align: top; border-top: 1px solid #f4f4f4;">'.trans('labels.Subtotal').':</th>
                        <td align="left" style="padding: 8px; line-height: 1.42857143; vertical-align: top; border-top: 1px solid #f4f4f4; text-align: center; text-transform: capitalize;">IQ '.$subtotals.'</td>
                      </tr>
                      <tr>
                        <th align="left" style="text-align: left;padding: 8px; line-height: 1.42857143; vertical-align: top; border-top: 1px solid #f4f4f4;">'.trans('labels.Tax').':</th>
                        <td align="left" style="padding: 8px; line-height: 1.42857143; vertical-align: top; border-top: 1px solid #f4f4f4; text-align: center; text-transform: capitalize;">IQ '.$invoiceorderList[0]['total_tax'].'</td>
                      </tr>
                      <tr>
                        <th align="left" style="text-align: left;padding: 8px; line-height: 1.42857143; vertical-align: top; border-top: 1px solid #f4f4f4;">'.trans('labels.ShippingCost').':</th>
                        <td align="left" style="padding: 8px; line-height: 1.42857143; vertical-align: top; border-top: 1px solid #f4f4f4; text-align: center; text-transform: capitalize;">IQ '.$invoiceorderList[0]['shipping_cost'].'</td>
                      </tr>';
                      if(!empty( '.$invoiceorderList[0]->coupon_code.')){
                      $html.='<tr>
                        <th align="left" style="text-align: left;padding: 8px; line-height: 1.42857143; vertical-align: top; border-top: 1px solid #f4f4f4;">'.trans('labels.DicountCoupon').':</th>
                        <td align="left" style="padding: 8px; line-height: 1.42857143; vertical-align: top; border-top: 1px solid #f4f4f4; text-align: center; text-transform: capitalize;">IQ '.$invoiceorderList[0]['coupon_amount'].'</td>
                      </tr>';
                      }


                      // echo "<pre>"; print_r($invoiceorderList[0]['pro_price']); die;

                      // $totalaaa = '3000' + $invoiceorderList[0]['total_tax'] + $invoiceorderList[0]['products_price'];
                      // echo $totalaaa; die;


                      //echo "<pre>"; print_r($data['orders_data'][0]->order_price); die;
		                // $prrr = $data['orders_data'][0]->data[0]->pro_price;
		                // echo $prrr + 3,000.000; die;
		              if ($invoiceorderList[0]['pro_price'] != '0.00' && $invoiceorderList[0]['pro_price'] != '') {
		                  $proprice = (float)number_format(str_replace(",", "",$invoiceorderList[0]['pro_price']), 3, ".", "");
		                  // $proprice = number_format((float)$data['orders_data'][0]->data[0]->pro_price);
		                  $ttt = $proprice + 3000 + $invoiceorderList[0]['total_tax'] + $subtotals;
		              } else {
		                $ttt = 3000 + $invoiceorderList[0]['total_tax'] + $subtotals;
		              }


                      $html.='<tr>
                        <th align="left" style="text-align: left;padding: 8px; line-height: 1.42857143; vertical-align: top; border-top: 1px solid #f4f4f4;">'.trans('labels.Total').':</th>
                        <td align="left" style="padding: 8px; line-height: 1.42857143; vertical-align: top; border-top: 1px solid #f4f4f4; text-align: center; text-transform: capitalize;">IQ '.number_format($ttt).'.000</td>
                      </tr>
                    </table>
                  </div>

                </td>
          </tr>
      </table>';
        $html.='<table style="width: 100%; table-layout: fixed;">
                <tr>
                    <td style="width:100%;">
                    	<p class="lead" style="margin-bottom:10px font-weight: 300; line-height: 1.4; font-size: 21px;">'.trans('labels.Orderinformation').':</p>
                    	<p class="text-muted well well-sm no-shadow" style="text-transform:capitalize; word-break:break-all; padding: 9px; border-radius: 3px; min-height: 20px;
                	    margin-bottom: 20px; background-color: #f5f5f5; border: 1px solid #e3e3e3;">';
                        if(trim($invoiceorderList[0]['order_information']) != [] and !empty($invoiceorderList[0]['order_information'])){
                      		$invoiceorderList[0]['order_information'];
                        }
                        else
                        {
                            "---";
                        }

                        $html.='</p>

                    </td>
                </tr>
        </table>
                </section>
              <!-- /.content -->
            </div>
            <!-- ./wrapper -->
            </body></html>';

            $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

            // set header and footer fonts
            $pdf->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
            $pdf->setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));

            // set default monospaced font
            $pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);

            // set margins
            $pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
            $pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
            $pdf->SetFooterMargin(PDF_MARGIN_FOOTER);

            // set auto page breaks
            $pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);

            // set image scale factor
            $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);

            // set some language dependent data:
            $lg = Array();
            $lg['a_meta_charset'] = 'UTF-8';
            // $lg['a_meta_dir'] = 'rtl';
            $lg['a_meta_language'] = 'ar';
            $lg['w_page'] = 'page';

            // set some language-dependent strings (optional)
            $pdf->setLanguageArray($lg);

            // $pdf->setCellPaddings(2,2,2,2);
            // $pdf->setCellMargins(0, 0, 0, 0);
            // $pdf->SetFillColor(0,0,0);

            $pdf->SetFont('dejavusans', '', 8);
            // $pdf->SetFont('dejavusans', '', 8, '', true);

            // add a page
            $pdf->AddPage('P','A4');

            // Persian and English content
            $htmlpersian = $html;
            // $pdf->WriteHTML($htmlpersian, true, 0, true, 0);
            $pdf->WriteHTML($htmlpersian, true, false, true, false);
            // $pdf->writeHTMLCell(0, 0, '', '', $html, 0, 1, 0, true, '', true);

            // set LTR direction for english translation
            // $pdf->setRTL(false);

            $pdf->SetFontSize(2);

            // print newline
            $pdf->Ln();

            // echo "<pre>"; print_r($pdf);
            ob_end_clean();
            // $pdf->Output('Receipt.pdf', 'D');
            // $pdf->Output('Receipt.pdf', 'I');


            $fileatt = $pdf->Output('Receipt.pdf','S');
            // $data = chunk_split($fileatt);



            	\Mail::send('admin.invoicepdf', ['orderDetailData' => $invoiceorderList],
				function ($message) use ($invoiceorderList,$fileatt) {
					$message
						->from('online@bigmartauc.com')
						  ->to($invoiceorderList[0]['email'])->subject(Lang::get('labels.order invoice detail'))
						 ->to('online@bigmartauc.com')->subject(Lang::get('labels.order invoice detail'))
						// ->to('harmistest@gmail.com')->subject(Lang::get('labels.order invoice detail'))
						// ->attachData($dompdf->output(), "order detail.pdf");;
						// ->attachData($pdf->Output('Receipt.pdf','E'), "order detail.pdf");;
						->attachData($fileatt, "order detail.pdf");;


				});

				$response = array('success' => 1, 'message' => Lang::get('labels.Send Order Invoice Detail Successfully'), 'result' => $orderListData);
				echo json_encode($response, JSON_UNESCAPED_UNICODE);exit;
			} else {
				$response = array('success' => 0, 'message' => Lang::get('labels.No Data found'));
				echo json_encode($response);exit;
			}
		} catch (Exception $e) {
			$response = array('success' => 0, 'message' => $e->getMessage());
			echo json_encode($response, JSON_UNESCAPED_UNICODE);exit;
		}
	}
public function curl($url){
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL,$url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
    $return = curl_exec($ch);
    curl_close ($ch);
    return $return;
}
	public function favouriteList()
	{
		$input = file_get_contents('php://input');
		$post = json_decode($input, true);
		$urlnew = url('');
		$new = str_replace('index.php', '', $urlnew);

		$language="en";
	    if(!empty($post['language'])){ $language=$post['language']; }
	    App::setLocale($language);


		try
		{

			if ((!isset($post['customerId'])) || (empty($post['customerId']))) {

				$response = array('success' => 0, 'message' => Lang::get('labels.customers id is Required'));
				echo json_encode($response, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_UNESCAPED_UNICODE | JSON_HEX_AMP);exit;
			}
		    $getLangid = $this->get_language_code($post['language']);
			$posts = Products::join('products_description', 'products_description.products_id', '=', 'products.products_id', 'inner')
						 ->join('liked_products', 'liked_products.liked_products_id', '=', 'products_description.products_id', 'inner')
                         ->where('liked_products.liked_customers_id',$post['customerId'])->where('language_id',$getLangid)->get();

			$data = array();
			if (count($posts) > 0) {
				foreach ($posts as $key => $value) {

					$data1 = array();
				//	$img = Productsimages::where('products_id', $value->products_id)->orderBy('sort_order', 'ASC')->first();
				        $img=$value->products_image;
					    $cusmorelikedId=Likedproducts::where('liked_customers_id',$post['customerId'])->where('liked_products_id',$value['products_id'])->get();
						$productsData=[];
                    	$productsData['prouductId']             = $value->products_id;
                    	$productsData['productName']            = $value->products_name;
                    	$productsData['productPrice']           = $value->discount_price !==NULL ? $value->discount_price :$value->products_price;
                    	$productsData['productOriginalPrice']   = $value->products_price;
                    	$productsData['productOfferPercentage'] = $value->is_offer;
                    	if(!empty($img)){
        					    $productsData['productsImage'] = str_replace("/index.php/", "/", url($img));
        				}else{
        						$productsData['productsImage'] = str_replace("/index.php/", "/", url('resources/assets/images/user_profile/default_user.png'));
        					}
                    	if(count($cusmorelikedId) > 0)
                   		{
                   			$customersId=$cusmorelikedId[0]->liked_customers_id;
                   			$productsData['productLiked']= true;
                   		}
                   		else
                   		{
                   				$productsData['productLiked']= false;
                   		}

					$data[] = $productsData;
				}
				$response = array('success' => 1, 'message' => trans('labels.Favourite List Successfully'), 'result' => $data);
				echo json_encode($response, JSON_UNESCAPED_UNICODE);exit;
			} else {
				$response = array('success' => 0, 'message' => Lang::get('labels.No Data found'));
				echo json_encode($response);exit;
			}
		} catch (Exception $e) {
			$response = array('success' => 0, 'message' => $e->getMessage());
			echo json_encode($response, JSON_UNESCAPED_UNICODE);exit;
		}
	}

	public function addtofavouriteList()
	{
		$input = file_get_contents('php://input');
		$post = json_decode($input, true);
		$urlnew = url('');
		$new = str_replace('index.php', '', $urlnew);

		$language="en";
	    if(!empty($post['language'])){ $language=$post['language']; }
	    App::setLocale($language);

		try
		{
			if ((!isset($post['customerId'])) || (!isset($post['productId'])) || (!isset($post['isType'])) || (empty($post['customerId'])) || (empty($post['productId'])))
			{
				$response = array('success' => 0, 'message' => Lang::get('labels.All Fields Are Required'));
				echo json_encode($response, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_UNESCAPED_UNICODE | JSON_HEX_AMP);exit;
			}

			if($post['isType'] == 1)
			{

				$check = Likedproducts::where('liked_customers_id',$post['customerId'])->where('liked_products_id',$post['productId'])->count();
				if($check != 0)
				{
					$response = array('success' => 0, 'message' => Lang::get('labels.This product already exists'));
					echo json_encode($response, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_UNESCAPED_UNICODE | JSON_HEX_AMP);exit;
				}

				$add = new Likedproducts;
				$add->liked_customers_id = $post['customerId'];
	    		$add->liked_products_id = $post['productId'];
	    		$add->date_liked = date('Y-m-d H:i:s');
	    		$add->save();

	    		$insertedId = $add->like_id;
	    		$data1 = Likedproducts::where('like_id',$insertedId)->first();
	    		$data['id'] = $data1->like_id;
	    		$data['customerId'] = $data1->liked_customers_id;
	    		$data['productId'] = $data1->liked_products_id;
	    		$data['date'] = $data1->date_liked;

	    		$getcount2 = Products::where('products_id',$post['productId'])->first();
	    		$getcount1 = $getcount2->products_liked;
	    		$getcount = $getcount1 + 1;
	    		$getcount2->products_liked = $getcount;
	    		$getcount2->save();

	    		$data['likeCount'] = $getcount;

				$response = array('success' => 1, 'message' => Lang::get('labels.Added Favourite Successfully.'), 'result' => $data);
				echo json_encode($response, JSON_UNESCAPED_UNICODE);exit;

			}else{

				$delete = Likedproducts::where('liked_customers_id',$post['customerId'])->where('liked_products_id',$post['productId'])->delete();

				if($delete){

					$getcount2 = Products::where('products_id',$post['productId'])->first();
		    		$getcount1 = $getcount2->products_liked;
		    		$getcount = $getcount1 - 1;

		    		$getcount2->products_liked = $getcount;
		    		$getcount2->save();

		    		$data['likeCount'] = $getcount;

					$response = array('success' => 1, 'message' => Lang::get('labels.Remove Favourite Successfully'), 'result' => $data);
					echo json_encode($response, JSON_UNESCAPED_UNICODE);exit;
				}else{
					$response = array('success' => 0, 'message' => Lang::get('labels.No Data found'));
					echo json_encode($response);exit;
				}
			}
		} catch (Exception $e) {
			$response = array('success' => 0, 'message' => $e->getMessage());
			echo json_encode($response, JSON_UNESCAPED_UNICODE);exit;
		}
	}

	public function addAddress(Request $request)
	{
		$input = file_get_contents('php://input');
		$post = json_decode($input, true);
		$urlnew = url('');
		$new = str_replace('index.php', '', $urlnew);

		$language="en";
	    if(!empty($post['language'])){ $language=$post['language']; }
	    App::setLocale($language);

		try
		{
		   // echo "<pre>"; print_r($post);exit;

			if ((!isset($post['lat'])) || (!isset($post['lng'])) || (!isset($post['deviceType'])) ||  (!isset($post['customerId'])) || (!isset($post['firstName'])) || (!isset($post['city'])) || (!isset($post['address'])) || (!isset($post['mobile'])) || (!isset($post['blokNumber'])) || (empty($post['city']))  || (empty($post['firstName'])) || (empty($post['deviceType'])) || (empty($post['customerId'])) || (empty($post['address'])) || (empty($post['mobile'])) || (empty($post['lat'])) || (empty($post['lng'])) || (empty($post['blokNumber'])))
			{

				$response = array('success' => 0, 'message' => Lang::get('labels.All Fields Are Required'));
				echo json_encode($response, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_UNESCAPED_UNICODE | JSON_HEX_AMP);exit;
			}

			if((isset($post['addressId'])) && $post['addressId'] != 0 && $post['addressId'] != "")
			{
				$edit = Addressbook::where('address_book_id',$post['addressId'])->first();
				if(!empty($edit))
				{
					$edit->customers_id = $post['customerId'];
		    		$edit->entry_firstname = $post['firstName'];
		    		$edit->entry_street_address = $post['address'];
		    		//$edit->entry_postcode = $post['postCode'];
		    		$edit->entry_city = $post['city'];
		    		$edit->entry_mobile = $post['mobile'];
		    		$edit->latitude = $post['lat'];
		    		$edit->longitude = $post['lng'];
		    		$edit->block_number = $post['blokNumber'];
		    		$edit->device_type = $post['deviceType'];
		    		$edit->save();

		    		$data['addressId'] = $post['addressId'];
		    		$data['customerId'] = $post['customerId'];
		    		$data['firstName'] = $post['firstName'];
		    		$data['address'] = $post['address'];
		    		//$data['postCode'] = $post['postCode'];
		    		$data['city'] = $post['city'];
		    		$data['mobile'] = $post['mobile'];
		    		$data['latitude'] = $post['lat'];
		    		$data['longitude'] = $post['lng'];
		    		$data['block_number'] = $post['blokNumber'];
		    		$data['device_type'] = $post['deviceType'];

					$response = array('success' => 1, 'message' => Lang::get('labels.Edit Address Successfully'), 'result' => $data);
					echo json_encode($response, JSON_UNESCAPED_UNICODE);exit;
				}else{
					$response = array('success' => 0, 'message' => Lang::get('labels.No Data found'));
					echo json_encode($response, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_UNESCAPED_UNICODE | JSON_HEX_AMP);exit;
				}
			}else{

				$add = new Addressbook;
				$add->customers_id = $post['customerId'];
	    		$add->entry_firstname = $post['firstName'];
	    		$add->entry_street_address = $post['address'];
	    		//$add->entry_postcode = $post['postCode'];
	    		$add->entry_city = $post['city'];
	    		$add->entry_mobile = $post['mobile'];
	    		$add->device_type = $post['deviceType'];
	    		$add->latitude = $post['lat'];
	    		$add->longitude = $post['lng'];
	    		$add->block_number = $post['blokNumber'];

	    	//	$add->entry_area = $post['area'];
	    	//	$add->entry_landmark = $post['landmark'];
	    		$add->save();

	    		$insertedId = $add->address_book_id;
	    		$data1 = Addressbook::where('address_book_id',$insertedId)->first();
	    		$data['addressId'] = $data1->address_book_id;
	    		$data['customerId'] = $data1->customers_id;
	    		$data['firstName'] = $data1->entry_firstname;
	    		$data['address'] = $data1->entry_street_address;
	    		$data['postCode'] = $data1->entry_postcode;
	    		$data['city'] = $data1->entry_city;
	    		$data['mobile'] = $data1->entry_mobile;
	    		$data['area'] = $data1->entry_area;
	    		$data['landmark'] = $data1->entry_landmark;
    			$data['landmark'] = $data1->block_number;
    			$data['lat'] = $data1->latitude;
    			$data['lng'] = $data1->longitude;
    			$data['deviceType'] = $data1->device_type;

				$response = array('success' => 1, 'message' => Lang::get('labels.Added Address Successfully'), 'result' => $data);
				echo json_encode($response, JSON_UNESCAPED_UNICODE);exit;

			}
		} catch (Exception $e) {
			$response = array('success' => 0, 'message' => $e->getMessage());
			echo json_encode($response, JSON_UNESCAPED_UNICODE);exit;
		}
	}

    public function deleteAddress(Request $request)
	{
		$input = file_get_contents('php://input');
		$post = json_decode($input, true);
		$urlnew = url('');
		$new = str_replace('index.php', '', $urlnew);

		$language="en";
	    if(!empty($post['language'])){ $language=$post['language']; }
	    App::setLocale($language);

		try
		{
			if ((!isset($post['addressId'])) || (!isset($post['customerId'])) || (empty($post['addressId'])) || (empty($post['customerId']))) {
				$response = array('success' => 0, 'message' => Lang::get('labels.All Fields Are Required'));
				echo json_encode($response, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_UNESCAPED_UNICODE | JSON_HEX_AMP);exit;
			}

			$check = Customers::where('customers_id',$post['customerId'])->where('customers_default_address_id',$post['addressId'])->first();
			if(!empty($check)){
				$check->customers_default_address_id = NULL;
				$check->save();
			}

			$delete = Addressbook::where('customers_id',$post['customerId'])->where('address_book_id',$post['addressId'])->delete();
			if($delete){
				$response = array('success' => 1, 'message' => Lang::get('labels.Remove Address Successfully'));
				echo json_encode($response, JSON_UNESCAPED_UNICODE);exit;
			}else{
				$response = array('success' => 0, 'message' => Lang::get('labels.No Data found'));
				echo json_encode($response);exit;
			}
		} catch (Exception $e) {
			$response = array('success' => 0, 'message' => $e->getMessage());
			echo json_encode($response, JSON_UNESCAPED_UNICODE);exit;
		}
	}

	public function addressList(Request $request) {

		$posts = $request->all();
		$input = file_get_contents('php://input');
		$post = json_decode($input, true);
		$urlnew = url('');
		$new = str_replace('index.php', '', $urlnew);

		$language="en";
	    if(!empty($post['language'])){ $language=$post['language']; }
	    App::setLocale($language);

		try
		{
			if ((!isset($post['customerId'])) || (empty($post['customerId']))) {
				$response = array('success' => 0, 'message' => Lang::get('labels.All Fields Are Required'));
				echo json_encode($response, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_UNESCAPED_UNICODE | JSON_HEX_AMP);exit;
			}

			$posts = Addressbook::where('customers_id',$post['customerId'])->get();
		//	echo "<pre>";print_r($posts);exit;
			$data = [];
			if (count($posts)>0) {
				foreach ($posts as $key => $post) {
					$data1['addressId']     = $post->address_book_id;
		    		$data1['customerId']    = $post->customers_id;
		    		$data1['firstName']     = $post->entry_firstname;
		    		$data1['address']       = $post->entry_street_address;
		    		$data1['postCode']      = $post->entry_postcode;
		    		$data1['city']          = $post->entry_city;
		    		$data1['mobile']        = $post->entry_mobile;
		    		$data1['area']          = $post->entry_area;
		    		$data1['lat']           = $post->latitude;
		    		$data1['lng']           = $post->longitude;
		    		$data1['blokNumber']    = $post->block_number;
		    		$data1['deviceType']    = $post->device_type;
		    		$data1['landmark']      = $post->entry_landmark;
					$data[] = $data1;
				}
				$response = array('success' => 1, 'message' => Lang::get('labels.Address List Loaded Successfully'), 'result' => $data);
				echo json_encode($response, JSON_UNESCAPED_UNICODE);exit;
			} else {
				$response = array('success' => 0, 'message' => Lang::get('labels.No Data found'));
				echo json_encode($response);exit;
			}
		} catch (Exception $e) {
			$response = array('success' => 0, 'message' => $e->getMessage());
			echo json_encode($response, JSON_UNESCAPED_UNICODE);exit;
		}
	}

	public function addOrder(Request $request){


		$input = file_get_contents('php://input');
		$post = json_decode($input, true);
		$urlnew = url('');
		$new = str_replace('index.php', '', $urlnew);
		// echo "<pre>"; print_r($post); die;
		$language="en";
	    if(!empty($post['language'])){ $language=$post['language']; }
	    App::setLocale($language);

		try
		{

			if ((!isset($post['customerId']))  || (!isset($post['addressId']))  || (!isset($post['paymentMethod'])) || (empty($post['customerId']))  || (empty($post['addressId'])) || (empty($post['paymentMethod'])))
			{
				$response = array('success' => 0, 'message' => Lang::get('labels.All Fields Are Required'));
				echo json_encode($response, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_UNESCAPED_UNICODE | JSON_HEX_AMP);exit;
			}

            $getLangid = $this->get_language_code($post['language']);

			$customerData = Customers::where('customers_id',$post['customerId'])->first();
			$customerBasketData=Customersbasket::leftjoin('products','products.products_id','customers_basket.products_id')
			->leftjoin('products_description','products_description.products_id','products.products_id')
			->where('customers_id',$post['customerId'])->where('language_id',$getLangid)->get();

			$firstname=$customerData->customers_firstname;
			$lastname=$customerData->customers_lastname;
			$name=$firstname.$lastname;
			if(empty($customerData)){
				$response = array('success' => 0, 'message' => Lang::get('labels.No Data found'));
				echo json_encode($response, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_UNESCAPED_UNICODE | JSON_HEX_AMP);exit;
			}
			$addressData = Addressbook::leftjoin('countries','countries.countries_id','=','address_book.entry_country_id')->where('address_book_id',$post['addressId'])->first();
			if(empty($addressData)){
				$response = array('success' => 0, 'message' => Lang::get('labels.No Data found'));
				echo json_encode($response, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_UNESCAPED_UNICODE | JSON_HEX_AMP);exit;
			}
			$subTotal1=0;
			if(count($customerBasketData) > 0) {
		    foreach ($customerBasketData as $key => $product1) {
		        		$subTotal1 += $product1['final_price'];
		    }
			}

        			$getdata['customers_id']              = $post['customerId'];
        			$getdata['total_tax']                 = "";
        			$getdata['customers_name']            = $name;
        			$getdata['customers_street_address']  = $addressData->entry_street_address;
        			$getdata['customers_city']            = $addressData->entry_city;
        			$getdata['customers_postcode']        = $addressData->entry_postcode;
        			$getdata['customers_country']         = $addressData->countries_name != NULL ? $addressData->countries_name :"0";
        			$getdata['customers_telephone']       = $addressData->entry_mobile != NULL ? $addressData->entry_mobile : "0";
        			$getdata['customers_suburb']          = "";
        			$getdata['customers_state']           = "";
        			$getdata['email']                     = $customerData->email;
        			$getdata['delivery_name']             = $firstname;
        			$getdata['delivery_street_address']   = $addressData->entry_street_address;
        			$getdata['delivery_city']             = $addressData->entry_city;
        			$getdata['delivery_postcode']         = $addressData->entry_postcode;
        			$getdata['delivery_country']          = $addressData->countries_name != NULL ? $addressData->countries_name :"0";
        			$getdata['delivery_phone']            = $addressData->entry_mobile != "" ? $addressData->entry_mobile : "0";
        			$getdata['delivery_suburb']           = "";
        			$getdata['delivery_state']            = "";
        			$getdata['billing_name']              = $name;
        			$getdata['billing_street_address']    = $addressData->entry_street_address;
        			$getdata['billing_city']              = $addressData->entry_city;
        			$getdata['billing_postcode']          = $addressData->entry_postcode;
        			$getdata['billing_country']           = $addressData->countries_name != NULL ? $addressData->countries_name :"0";
        			$getdata['billing_phone']             = $addressData->entry_mobile != "" ? $addressData->entry_mobile : "0";
        			$getdata['billing_suburb']            = "";
        			$getdata['billing_state']             = "";
        			$getdata['order_price']               = $subTotal1;
        			$getdata['shipping_cost']             = "0.00";
        			$getdata['shipping_method']           = "Flat Rate";
        			$getdata['ordered_source']            = "2";
        			$getdata['order_information']         = "[]";
        			$getdata['currency']                  = "$";
        			$getdata['last_modified']             = date("Y-m-d H:i:s");
        			$getdata['date_purchased']            = date("Y-m-d H:i:s");
        			$getdata['payment_method']            = $post['paymentMethod'];
        			$getdata['cc_type']                   = "";
        			$getdata['cc_owner']                  = "";
        			$getdata['cc_number']                 = "";
        			$getdata['cc_expires']                = "";

        			$insertId = Order::insertGetId($getdata);

        			$newgetdata['orders_id'] = $insertId;
        			$newgetdata['orders_status_id'] = "1";
        			$newgetdata['date_added'] = date("Y-m-d H:i:s");
        			$newgetdata['customer_notified'] = "1";
        			$newgetdata['comments'] = "";
        			$statusId = Ordersstatushistory::insertGetId($newgetdata);

        		if(count($customerBasketData) > 0) {
            		    foreach ($customerBasketData as $key => $product) {

            		    	//echo "<pre>"; print_r($product); exit;
        					$data = array();
        					$data['orders_id'] = $insertId;
        					$data['products_id'] = $product['products_id'];
        					$data['products_model'] = NULL;
        					$data['products_name'] = $product['products_name'];
        					//$data['products_price'] = $product['products_price'];
        					$data['products_price'] = (float)number_format(str_replace(",","",$product['products_price']),3,".","");
        					$data['final_price'] = $product['final_price'];
        					$data['products_tax'] = "1";
        					$data['products_quantity'] = $product['customers_basket_quantity'];
        					$Ordersproductsid = Ordersproducts::insertGetId($data);

            			}
	        	}

		    $removeProductFromCart=Customersbasket::where('customers_id',$post['customerId'])->delete();

            $invoiceorderList = Order::join('orders_products', 'orders_products.orders_id', '=', 'orders.orders_id', 'left')
            		->select('orders_products.*','orders.*', 'products.products_image as image','products.products_price as pro_price','orders_status_history.*','orders_status.*')
                    ->join('orders_status_history', 'orders_status_history.orders_id', '=', 'orders_products.orders_id', 'left')
                    ->join('orders_status', 'orders_status.orders_status_id', '=', 'orders_status_history.orders_status_id', 'left')
                    ->join('products', 'products.products_id','=', 'orders_products.products_id')
                    ->where('orders.orders_id',$insertId)->get();
                    // echo "<pre>"; print_r($invoiceorderList); die;


		 /*   $html = '';

                $html .= '<!DOCTYPE html><html><head>';
            $html .= '<meta charset="utf-8">
                	<meta http-equiv="Content-type" content="text/html; charset=UTF-8">
                	<meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no, minimum-scale=1, maximum-scale=1, viewport-fit=cover, shrink-to-fit=no">

                	</head>';

            $html.='<body style="margin:0;font-family: DejaVu Sans, sans-serif; text-align:left" >
            <div class="wrapper wrapper2" style="padding: 20px;">

	            <section class="invoice" bgcolor="#fff" style="margin: 15px; position: relative; background: #fff; border: 1px solid #f4f4f4; padding: 20px; display: inline-block; font-size: 14px;
    line-height: 1.42857143; color: #333;">';

               $html.='<div class="row">
                    <div class="col-xs-12">
                    <table style="width:100%; padding-bottom: 25px; table-layout: fixed; margin: 10px 0 20px 0; border-bottom: 1px solid #eee; font-size: 14px; line-height: 1.42857143;
          color: #333;">

                    <tr>
                        <td style="font-size: 22px; line-height: 1; padding: 0;"><i class="fa fa-globe"></i> '.trans('labels.OrderID').'# '.$invoiceorderList[0]['orders_id'].' </td>
                         <td align="center" style="text-align:center;"><img src="'.asset('resources/views/admin/images/admin_logo/logo.png').'" style="width: 100px;" width="100px" /></td>
                        <td style="text-align:right; line-height: 1; padding: 0;"><small style="text-align:right; color: #666; display: block; margin-top: 5px;">'.trans('labels.OrderedDate').': '.date('m/d/Y', strtotime($invoiceorderList[0]['date_purchased'])).'</small></td>
                    </tr>
                   </table>
                    </div>
                    <!-- /.col -->
                  </div>
                  <!-- info row -->

                  <table style="width:100%">
                   <tr>
                     <td style="padding: 0 15px 0 0; vertical-align: top; text-align:left;" align="left">
                      <label>'.trans('labels.CustomerInfo').':</label>
                      <div style="margin-bottom: 20px; font-style: normal; line-height: 1.42857143;">
                        <span><strong>'.$invoiceorderList[0]['customers_name'].'</strong></span><br><span>'.$invoiceorderList[0]['customers_street_address'].'</span><br><span>'.$invoiceorderList[0]['customers_city'].',</span><span>'.$invoiceorderList[0]['customers_state'].'</span><span>'.$invoiceorderList[0]['customers_postcode'].',</span><span>'.$invoiceorderList[0]['customers_country'].'</span><br><span>'.trans('labels.Phone').': '.$invoiceorderList[0]['customers_telephone'].'</span><br><span>'.trans('labels.Email').': '.$invoiceorderList[0]['email'].'</span>
                      </div>
                    </td>
                    <!-- /.col -->
                   <td style="padding: 0 15px 0 0; vertical-align: top; text-align:left;" align="left">
                      <label>'.trans('labels.ShippingInfo').':</label>
                      <div style="margin-bottom: 20px; font-style: normal; line-height: 1.42857143;">
                        <strong>'.$invoiceorderList[0]['delivery_name'].'</strong><br><span>'.trans('labels.Phone').'</span><span>'.$invoiceorderList[0]['delivery_phone'].'</span><br>'.$invoiceorderList[0]['delivery_street_address'].'<br>'.$invoiceorderList[0]['delivery_city'].','.$invoiceorderList[0]['delivery_state'].''.$invoiceorderList[0]['delivery_postcode'].','.$invoiceorderList[0]['delivery_country'].'<br><strong>'.trans('labels.ShippingMethod').':</strong> '.$invoiceorderList[0]->shipping_method.'<br><strong>'.trans('labels.ShippingCost').':</strong>';
                      if (!empty($invoiceorderList[0]['shipping_cost']))
                      {

                       $invoiceorderList[0]['currency'] ;
                       $invoiceorderList[0]['shipping_cost'];

                      }


                      $html.='<br>
                    </div>
                    </td>
                    <!-- /.col -->
                    <td style="padding: 0 15px 0 0; vertical-align: top; text-align:left;" align="left">
                      '.trans('labels.BillingInfo').'
                      <div style="margin-bottom: 20px; font-style: normal; line-height: 1.42857143;">
                        <strong>'.$invoiceorderList[0]['billing_name'].'</strong><br><span>'.trans('labels.Phone').':</span><span>'.$invoiceorderList[0]['billing_phone'].'</span><br><span>'.$invoiceorderList[0]['billing_street_address'].'</span><br><span>'.$invoiceorderList[0]['billing_city'].',<span></span>'.$invoiceorderList[0]['billing_state'].'</span><span>'.$invoiceorderList[0]['billing_postcode'].',</span><span>'.$invoiceorderList[0]['billing_country'].'</span><br>
                      </div>
                    </td>
                  </tr>
                  </table>
                  <!-- Table row -->
                  <div class="row">
                    <div class="col-xs-12 table-responsive">
                      <table style="width: 100%; max-width: 100%; margin-bottom: 20px; border-spacing: 0; border-collapse: collapse;">
                        <thead>
                        <tr>
                          <th style="border-bottom: 2px solid #f4f4f4; vertical-align: bottom; padding: 8px; line-height: 1.42857143;"> '.trans('labels.Qty').'</th>
                          <th style="border-bottom: 2px solid #f4f4f4; vertical-align: bottom; padding: 8px; line-height: 1.42857143;"> '.trans('labels.ProductName').'</th>
                          <th style="border-bottom: 2px solid #f4f4f4; vertical-align: bottom; padding: 8px; line-height: 1.42857143;"> '.trans('labels.ProductModal').'</th>
                          <th style="border-bottom: 2px solid #f4f4f4; vertical-align: bottom; padding: 8px; line-height: 1.42857143;">'.trans('labels.Options').'</th>
                          <th style="border-bottom: 2px solid #f4f4f4; vertical-align: bottom; padding: 8px; line-height: 1.42857143;"> '.trans('labels.Price').'</th>
                        </tr>
                        </thead>
                        <tbody>';

                       $subtotals = 0;

                        foreach ($invoiceorderList as $key => $value)
                       {


                        $html.=' <tr style="background-color: #f9f9f9;">
                           <td style="border-bottom: 2px solid #f4f4f4; vertical-align: bottom; padding: 8px; line-height: 1.42857143;">'.$value->products_quantity.'</td>
                           <td dir="rtl" style="border-bottom: 2px solid #f4f4f4; vertical-align: bottom; padding: 8px; line-height: 1.42857143;">
                                  '.$value->products_name.'
                            </td>
                             <td style="border-bottom: 2px solid #f4f4f4; vertical-align: bottom; padding: 8px; line-height: 1.42857143;">
                                '.$value->products_model.'
                            </td>

                            <td style="border-bottom: 2px solid #f4f4f4; vertical-align: bottom; padding: 8px; line-height: 1.42857143;"></td>
                            <td style="border-bottom: 2px solid #f4f4f4; vertical-align: bottom; padding: 8px; line-height: 1.42857143;">IQ '.$value->pro_price.'</td>
                         </tr>';
                          $subtotals +=(float)number_format(str_replace(",", "", $value->pro_price), 3, ".", "") * $value->products_quantity;

                       }

                       $html.='</tbody>
                      </table>
                    </div>


                  </div>';



            $html.='<table style="width: 100%; table-layout: fixed;">
            <tr>
                <td style="width:58.33333333%; vertical-align: top; padding: 0 15px 0 0;">
                  <p class="lead" style="margin-bottom:10px; font-size: 21px; font-weight: 300; line-height: 1.4;">'.trans('labels.PaymentMethods').':</p>
                  <p class="text-muted well well-sm no-shadow" style="text-transform:capitalize; min-height: 20px; padding: 9px; border-radius: 3px; margin-bottom: 20px; background-color: #f5f5f5;
                        border: 1px solid #e3e3e3;">
                   	'.str_replace('_',' ',  $invoiceorderList[0]->payment_method).'
                  </p>

                </td>
                <td style="width:41.66666667%; vertical-align: top; padding: 0 0 0 15px;">

                  <div class="table-responsive ">
                    <table class="table order-table" align="left" style="text-align: left; width: 100%; max-width: 100%; margin-bottom: 20px; border-spacing: 0; border-collapse: collapse;">
                      <tr>
                        <th align="left" style="width:50%" style="text-align: left; padding: 8px; line-height: 1.42857143; vertical-align: top; border-top: 1px solid #f4f4f4;">'.trans('labels.Subtotal').':</th>
                        <td align="left" style="padding: 8px; line-height: 1.42857143; vertical-align: top; border-top: 1px solid #f4f4f4; text-align: center; text-transform: capitalize;">IQ '.$subtotals .'</td>
                      </tr>
                      <tr>
                        <th align="left" style="text-align: left;padding: 8px; line-height: 1.42857143; vertical-align: top; border-top: 1px solid #f4f4f4;">'.trans('labels.Tax').':</th>
                        <td align="left" style="padding: 8px; line-height: 1.42857143; vertical-align: top; border-top: 1px solid #f4f4f4; text-align: center; text-transform: capitalize;">IQ '.$invoiceorderList[0]['total_tax'].'</td>
                      </tr>
                      <tr>
                        <th align="left" style="text-align: left;padding: 8px; line-height: 1.42857143; vertical-align: top; border-top: 1px solid #f4f4f4;">'.trans('labels.ShippingCost').':</th>
                        <td align="left" style="padding: 8px; line-height: 1.42857143; vertical-align: top; border-top: 1px solid #f4f4f4; text-align: center; text-transform: capitalize;">IQ : 3000 </td>
                      </tr>';
                      if(!empty( '.$invoiceorderList[0]->coupon_code.')){
                      $html.='<tr>
                        <th align="left" style="text-align: left;padding: 8px; line-height: 1.42857143; vertical-align: top; border-top: 1px solid #f4f4f4;">'.trans('labels.DicountCoupon').':</th>
                        <td align="left" style="padding: 8px; line-height: 1.42857143; vertical-align: top; border-top: 1px solid #f4f4f4; text-align: center; text-transform: capitalize;">IQ '.$invoiceorderList[0]['coupon_amount'].'</td>
                      </tr>';
                      }


                      // echo "<pre>"; print_r($invoiceorderList[0]['pro_price']); die;

                      // $totalaaa = '3000' + $invoiceorderList[0]['total_tax'] + $invoiceorderList[0]['products_price'];
                      // echo $totalaaa; die;


                      //echo "<pre>"; print_r($data['orders_data'][0]->order_price); die;
		                // $prrr = $data['orders_data'][0]->data[0]->pro_price;
		                // echo $prrr + 3,000.000; die;


		                $ttt = 3000 + $invoiceorderList[0]['total_tax'] + $subtotals;


                      $html.='<tr>
                        <th align="left" style="text-align: left;padding: 8px; line-height: 1.42857143; vertical-align: top; border-top: 1px solid #f4f4f4;">'.trans('labels.Total').':</th>
                        <td align="left" style="padding: 8px; line-height: 1.42857143; vertical-align: top; border-top: 1px solid #f4f4f4; text-align: center; text-transform: capitalize;">IQ '.number_format($ttt).'.000</td>
                      </tr>
                    </table>
                  </div>

                </td>
          </tr>
      </table>';
        $html.='<table style="width: 100%; table-layout: fixed;">
                <tr>
                    <td style="width:100%;">
                    	<p class="lead" style="margin-bottom:10px font-weight: 300; line-height: 1.4; font-size: 21px;">'.trans('labels.Orderinformation').':</p>
                    	<p class="text-muted well well-sm no-shadow" style="text-transform:capitalize; word-break:break-all; padding: 9px; border-radius: 3px; min-height: 20px;
                	    margin-bottom: 20px; background-color: #f5f5f5; border: 1px solid #e3e3e3;">';
                        if(trim($invoiceorderList[0]['order_information']) != [] and !empty($invoiceorderList[0]['order_information'])){
                      		$invoiceorderList[0]['order_information'];
                        }
                        else
                        {
                            "---";
                        }

                        $html.='</p>

                    </td>
                </tr>
        </table>
                </section>
              <!-- /.content -->
            </div>
            <!-- ./wrapper -->
            </body></html>';   */

      //       $html .= '<strong> إيصال التبرع</strong>';
    		// $html .= '</body></html>';


            // echo $html; die;
		    /*$dompdf = new Dompdf();
		    $dompdf->load_html($html);
            $dompdf->render();
            $dompdf->stream('invoice.pdf');
            die;*/
             // echo "<pre>";print_r($dompdf); exit;


           /* $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

            // set header and footer fonts
            $pdf->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
            $pdf->setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));

            // set default monospaced font
            $pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);

            // set margins
            $pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
            $pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
            $pdf->SetFooterMargin(PDF_MARGIN_FOOTER);

            // set auto page breaks
            $pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);

            // set image scale factor
            $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);

            // set some language dependent data:
            $lg = Array();
            $lg['a_meta_charset'] = 'UTF-8';
            // $lg['a_meta_dir'] = 'rtl';
            $lg['a_meta_language'] = 'ar';
            $lg['w_page'] = 'page';

            // set some language-dependent strings (optional)
            $pdf->setLanguageArray($lg);

            // $pdf->setCellPaddings(2,2,2,2);
            // $pdf->setCellMargins(0, 0, 0, 0);
            // $pdf->SetFillColor(0,0,0);

            $pdf->SetFont('dejavusans', '', 8);
            // $pdf->SetFont('dejavusans', '', 8, '', true);

            // add a page
            $pdf->AddPage('P','A4');

            // Persian and English content
            $htmlpersian = $html;
            // $pdf->WriteHTML($htmlpersian, true, 0, true, 0);
            $pdf->WriteHTML($htmlpersian, true, false, true, false);
            // $pdf->writeHTMLCell(0, 0, '', '', $html, 0, 1, 0, true, '', true);

            // set LTR direction for english translation
            // $pdf->setRTL(false);

            $pdf->SetFontSize(2);

            // print newline
            $pdf->Ln();

            // echo "<pre>"; print_r($pdf);
            ob_end_clean();
            // $pdf->Output('Receipt.pdf', 'D');
            // $pdf->Output('Receipt.pdf', 'I');


            $fileatt = $pdf->Output('Receipt.pdf','S');*/
            // $data = chunk_split($fileatt);

            // echo $invoiceorderList; die;

            /*	\Mail::send('admin.invoicepdf', ['orderDetailData' => $invoiceorderList],
				function ($message) use ($invoiceorderList,$fileatt) {
					$message
						->from('harmistest@gmail.com')
						  ->to($invoiceorderList[0]['email'])->subject(Lang::get('labels.order invoice detail'))
						 ->to('online@bigmartauc.com')->subject(Lang::get('labels.order invoice detail'))
						// ->attachData($dompdf->output(), "order detail.pdf");;
						// ->attachData($pdf->Output('Receipt.pdf','E'), "order detail.pdf");;
						->attachData($fileatt, "order detail.pdf");;


				});*/

			$response = array('success' => 1, 'message' => Lang::get('labels.Order Placed Successfully'));
			echo json_encode($response, JSON_UNESCAPED_UNICODE);exit;

		} catch (Exception $e) {
			$response = array('success' => 0, 'message' => $e->getMessage());
			echo json_encode($response, JSON_UNESCAPED_UNICODE);exit;
		}
	}




    public function cancelOrder()
    {
        $input = file_get_contents('php://input');
		$post = json_decode($input, true);
		$urlnew = url('');
		$new = str_replace('index.php', '', $urlnew);

		$language="en";
	    if(!empty($post['language'])){ $language=$post['language']; }
	    App::setLocale($language);

	    try
		{
			if((empty($post['statusId'])) || (empty($post['orderId'])))
			{
			    $response = array('success' => 0, 'message' => Lang::get('labels.All Fields Are Required'));
				echo json_encode($response, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_UNESCAPED_UNICODE | JSON_HEX_AMP);exit;
			}
			        $getOrderStatusHistoryData=Ordersstatushistory::where('orders_id',$post['orderId'])->first();

		            $getOrderStatusHistoryData->orders_id           = $getOrderStatusHistoryData->orders_id;
		            $getOrderStatusHistoryData->orders_status_id    = $post['statusId'];
		            $getOrderStatusHistoryData->date_added          = $getOrderStatusHistoryData->date_added;
		            $getOrderStatusHistoryData->customer_notified   = $getOrderStatusHistoryData->customer_notified;
		            $getOrderStatusHistoryData->comments            = $getOrderStatusHistoryData->comments;
		    		$getOrderStatusHistoryData->save();

					$response = array('success' => 1, 'message' => Lang::get('labels.Order Cancel Successfully'));
					echo json_encode($response, JSON_UNESCAPED_UNICODE);exit;



		} catch (Exception $e) {
			$response = array('success' => 0, 'message' => $e->getMessage());
			echo json_encode($response, JSON_UNESCAPED_UNICODE);exit;
		}


    }
	public function brandList()
	{
		$input = file_get_contents('php://input');
		$post = json_decode($input, true);
		$urlnew = url('');
		$new = str_replace('index.php', '', $urlnew);

		$language="en";
	    if(!empty($post['language'])){ $language=$post['language']; }
	    App::setLocale($language);

		try
		{
			$getLangid = $this->get_language_code($post['language']);
		//	$BrandList = Brand::leftjoin('brand_description','brand_description.brand_id','brand.id')->where('language_id',$getLangid)->get();
			$BrandList =Manufacturers::all();

			$BrandListData = [];
			if (!empty($BrandList)) {
				foreach ($BrandList as $key => $value) {


					$BrandListData['id']            = $value->manufacturers_id;
					$BrandListData['name']    = $value->manufacturers_name;

					$brandsListData[] = $BrandListData;
				}
				$response = array('success' => 1, 'message' => Lang::get('labels.Manufacturers Loaded Successfully'), 'result' => $brandsListData);
				echo json_encode($response, JSON_UNESCAPED_UNICODE);exit;
			} else {
				$response = array('success' => 0, 'message' => Lang::get('labels.No Data found'));
				echo json_encode($response);exit;
			}
		} catch (Exception $e) {
			$response = array('success' => 0, 'message' => $e->getMessage());
			echo json_encode($response, JSON_UNESCAPED_UNICODE);exit;
		}
	}


	public function notificationList(Request $request) {

		$posts = $request->all();
		$input = file_get_contents('php://input');
		$post = json_decode($input, true);
		$urlnew = url('');
		$new = str_replace('index.php', '', $urlnew);

        $language="en";
        if(!empty($post['language'])){ $language=$post['language']; }
        App::setLocale($language);

		try
		{
			if ((!isset($post['customersId'])) || (empty($post['customersId']))) {
				$response = array('success' => 0, 'message' => Lang::get('All Fields Are Required'));
				echo json_encode($response, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_UNESCAPED_UNICODE | JSON_HEX_AMP);exit;
			}

			$posts = Notifications::where('customersId',$post['customersId'])->get();
			$data = [];
			if (count($posts)>0) {
				foreach ($posts as $key => $post) {
					$data1['id']            = $post->id;
		    		$data1['customersId']   = $post->customersId;
		    		$data1['subject']       = $post->subject;
		    		$data1['message']       = $post->message;
		    		$data1['isRead']        = $post->isRead;
		    		$data1['status']        = $post->status;
		    		$data1['date']          = date('d-m-Y',strtotime($post->date));
					$data[] = $data1;
				}
				$response = array('success' => 1, 'message' => Lang::get('labels.Notification List Successfully'), 'result' => $data);
				echo json_encode($response, JSON_UNESCAPED_UNICODE);exit;
			} else {
				$response = array('success' => 0, 'message' => Lang::get('labels.No Data found'));
				echo json_encode($response);exit;
			}
		} catch (Exception $e) {
			$response = array('success' => 0, 'message' => $e->getMessage());
			echo json_encode($response, JSON_UNESCAPED_UNICODE);exit;
		}
	}

	public function addReview(Request $request)
    {
        $input = file_get_contents('php://input');
        $post = json_decode($input, true);
        $urlnew = url('');
        $new = str_replace('index.php', '', $urlnew);

        $language="en";
        if(!empty($post['language'])){ $language=$post['language']; }
        App::setLocale($language);

        try
        {
            if ((!isset($post['productId'])) || (!isset($post['customerId'])) || (!isset($post['customerName'])) || (!isset($post['reviewsRating'])) || (!isset($post['reviewsText'])) || (empty($post['productId'])) || (empty($post['customerId'])) || (empty($post['customerName'])) || (empty($post['reviewsRating'])) || (empty($post['reviewsText']))) {

                $response = array('success' => 0, 'message' => Lang::get('labels.All Fields Are Required'));
                echo json_encode($response, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_UNESCAPED_UNICODE | JSON_HEX_AMP);exit;
            }

            $getLangid = $this->get_language_code($post['language']);

            $getdata['products_id']         = $post['productId'];
            $getdata['customers_id']        = $post['customerId'];
            $getdata['customers_name']      = $post['customerName'];
            $getdata['reviews_rating']      = $post['reviewsRating'];
            $getdata['reviews_status']      = "1";
            $getdata['date_added']          = date("Y-m-d H:i:s");
            $getdata['last_modified']       = date("Y-m-d H:i:s");

            $review = Review::insertGetId($getdata);

            $getdatas['reviews_id']         = $review;
            $getdatas['languages_id']       = $getLangid;
            $getdatas['reviews_text']       = $post['reviewsText'];

            $reviewdesc = Reviewdesc::insertGetId($getdatas);

            $data['reviewsId']              = $review;
            $data['productsId']             = $post['productId'];
            $data['customersId']            = $post['customerId'];
            $data['customersName']          = $post['customerName'];
            $data['reviewsRating']          = $post['reviewsRating'];
            $data['reviewsText']            = $post['reviewsText'];

            $response = array('success' => 1, 'message' => Lang::get('labels.Review Added Successfully'), 'result' => $data);
            echo json_encode($response, JSON_UNESCAPED_UNICODE);exit;

        } catch (Exception $e) {
            $response = array('success' => 0, 'message' => $e->getMessage());
            echo json_encode($response, JSON_UNESCAPED_UNICODE);exit;
        }
    }

     public function reviewList(Request $request)
    {
        $input = file_get_contents('php://input');
        $post = json_decode($input, true);
        $urlnew = url('');
        $new = str_replace('index.php', '', $urlnew);

        $language="en";
        if(!empty($post['language'])){ $language=$post['language']; }
        App::setLocale($language);

        try
        {
            if ((!isset($post['productId'])) || (empty($post['productId']))) {
                $response = array('success' => 0, 'message' => Lang::get('labels.All Fields Are Required'));
                echo json_encode($response, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_UNESCAPED_UNICODE | JSON_HEX_AMP);exit;
            }

            $getLangid = $this->get_language_code($language);
            $posts = Review::with('hasOneText')->where('products_id',$post['productsId'])->get();

            $data = array();
            if (count($posts) > 0) {
                foreach ($posts as $key => $post) {
                    $data1 = array();

                    $data1['reviewsId']         = $post->reviews_id;
                    $data1['productId']         = $post->products_id;
                    $data1['customerId']        = $post->customers_id;
                    $data1['customerName']      = $post->customers_name;
                    $data1['reviewsRating']     = $post->reviews_rating;
                    $data1['reviewsText']       = $post->hasOneText->reviews_text;

                    $data2[] = $data1;
                    $data['reviews'] = $data2;
                }
                $response = array('success' => 1, 'message' => trans('labels.Review List Successfully'), 'result' => $data);
                echo json_encode($response, JSON_UNESCAPED_UNICODE);exit;
            } else {
                $response = array('success' => 0, 'message' => Lang::get('labels.No Data found'));
                echo json_encode($response);exit;
            }

        } catch (Exception $e) {
            $response = array('success' => 0, 'message' => $e->getMessage());
            echo json_encode($response, JSON_UNESCAPED_UNICODE);exit;
        }
    }

    public function offerProductList() {


		$input = file_get_contents('php://input');
		$post = json_decode($input, true);
		$urlnew = url('');
		$new = str_replace('index.php', '', $urlnew);

		$language="en";
	    if(!empty($post['language'])){ $language=$post['language']; }
	    App::setLocale($language);

		try
		{
			$flashsaleproductId=[];
			$flashsalestatus=[];
			$flashsalelist=Flashsale::get()->all();

			if(empty($flashsalelist))
			{
				$response = array('success' => 0, 'message' => Lang::get('labels.No Data found'));
				echo json_encode($response);exit;
			}

			foreach ($flashsalelist as $key => $value) {

				$flashsaleproductId[]=$value['products_id'];
				$flashsaleexpdate[]=date('Y-m-d',$value['flash_expires_date']);
				# code...
			}

			$currentDate = date('Y-m-d');
            $getLangid = $this->get_language_code($post['language']);
			$getOfferProductlist = Products::leftjoin('products_description','products_description.products_id','products.products_id')
			->leftjoin('flash_sale','flash_sale.products_id','products.products_id')
			->where('products_description.language_id',$getLangid)
			->where('products.is_offer',1)
			->orWhere(function($query) use ($flashsaleproductId)
				    {

						$query->whereIn('flash_sale.products_id',$flashsaleproductId);
				    })
			->orWhere(function($query) use ($flashsaleexpdate,$currentDate)
				    {

						$query->WhereDate('flash_sale.flash_expires_date','>',$currentDate);
				    })
			->orWhere('flash_sale.flash_status',1)
			->groupBy('products.products_id')
			->get();

			$offerProductData = [];
			if (count($getOfferProductlist) > 0) {

				foreach ($getOfferProductlist as $key => $value) {

                    	$productsData=[];
                    	if(!empty($post['customerId']))
                    	{
                    	$cusmorelikedId=Likedproducts::where('liked_customers_id',$post['customerId'])->where('liked_products_id',$value->products_id)->get();
                    	}
                    	else
                    	{
                    	    $cusmorelikedId=array();
                    	}
                    	$productsData['prouductId']             = $value->products_id;
                    	$productsData['productName']            = $value->products_name;
                    	$productsData['productPrice']           = $value->discount_price== ! NULL ?$value->discount_price : $value->products_price;
                    	$productsData['productOriginalPrice']   = $value->products_price;
                    	$productsData['productsImage']          = $new.$value->products_image;
                    	$productsData['productOfferPercentage'] = $value->is_offer;

                    	if(count($cusmorelikedId) > 0)
                   		{
                   			$customersId=$cusmorelikedId[0]->liked_customers_id;
                   			$productsData['productLiked']=  true;
                   		}
                   		else
                   		{
                   			$productsData['productLiked']=false;
                   		}

	                	$productsDatas[] = $productsData;

				}
				$response = array('success' => 1, 'message' => Lang::get('labels.Productoffer list Loaded Successfully'), 'product_data' => $productsDatas);
				echo json_encode($response, JSON_UNESCAPED_UNICODE);exit;
			} else {
				$response = array('success' => 0, 'message' => Lang::get('labels.No Data found'));
				echo json_encode($response);exit;
			}
		} catch (Exception $e) {
			$response = array('success' => 0, 'message' => $e->getMessage());
			echo json_encode($response, JSON_UNESCAPED_UNICODE);exit;
		}
	}

	public function userDetail()
	{


		$input = file_get_contents('php://input');
		$post = json_decode($input, true);
		$urlnew = url('');
		$new = str_replace('index.php', '', $urlnew);

		$language="en";
	    if(!empty($post['language'])){ $language=$post['language']; }
	    App::setLocale($language);

		try
		{

			$user = Customers::where('customers_id',$post['customerId'])->first();

			if(!empty($user))
			{
			    $countrylist=Country::where('countries_id',$user->country_id)->first();
			    $userData = array();
				$userData['customerId']         = $user->customers_id;
				$userData['gender']             = $user->customers_gender;
				$userData['firstName']          = $user->customers_firstname;
				$userData['lastName']           = $user->customers_lastname;
				$userData['dob']                = $user->customers_dob;
				$userData['email']              = $user->email;
				$userData['userName']           = $user->user_name;
				$userData['mobile']             = $user->customers_telephone;
				$userData['countryCode']        = $countrylist->phone_code;
				$userData['countryId']          = $user->country_id;
				$userData['defaultAddressId']   = $user->customers_default_address_id !==NULL ? $user->customers_default_address_id : '0';
				$userData['isActive']           = $user->isActive;

				$response = array('success' => 1, 'message' => Lang::get('labels.Userdetail Loaded Successfully'), 'result' => $userData);
				echo json_encode($response, JSON_UNESCAPED_UNICODE);exit;
			} else {
				$response = array('success' => 0, 'message' => Lang::get('labels.No Data found'));
				echo json_encode($response);exit;
			}
		} catch (Exception $e) {
			$response = array('success' => 0, 'message' => $e->getMessage());
			echo json_encode($response, JSON_UNESCAPED_UNICODE);exit;
		}

	}

	public function productDetail()
	{

		$input = file_get_contents('php://input');
		$post = json_decode($input, true);
		$urlnew = url('');
		$new = str_replace('index.php', '', $urlnew.'/');

		$language="en";
	    if(!empty($post['language'])){ $language=$post['language']; }
	    App::setLocale($language);

	    try
	    {
	         $getLangid = $this->get_language_code($language);

	         $productId=$post['productId'];

	         $productDetailLists=Products::leftjoin('products_description','products_description.products_id','products.products_id')->where('products.products_id',$post['productId'])->where('language_id',$getLangid)->first();
	         $productDetail=Products::leftjoin('products_description','products_description.products_id','products.products_id')->where('products.barcode',$post['productId'])->where('language_id',$getLangid)->first();
	        // echo '<pre>';print_r($productDetailLists);exit;
	        if(!empty ($productDetailLists)){
    	         if($productDetailLists->products_id==$productId)
    	         {
    	             $productDetailList=Products::leftjoin('products_description','products_description.products_id','products.products_id')->where('products.products_id',$post['productId'])->where('language_id',$getLangid)->first();

    	         }
	         }
	          if(!empty ($productDetail)){
    	          if($productDetail->barcode==$productId) {
    	             $productDetailList=Products::leftjoin('products_description','products_description.products_id','products.products_id')->where('products.barcode',$post['productId'])->where('language_id',$getLangid)->first();

    	          }
	          }


	    	 if(!empty($productDetailList))
	    	 {
	    	 	$reviewDatas=[];
	    	 	if(!empty($post['customerId']))
	    	 	{
	    	 	$cusmorelikedId=Likedproducts::where('liked_customers_id',$post['customerId'])->where('liked_products_id',$productDetailList->products_id)->get();
	    	 	}
	    	    else
	    	    {

	    	        $post['customerId']="";
	    	        $cusmorelikedId=Likedproducts::where('liked_customers_id',$post['customerId'])->where('liked_products_id',$productDetailList->products_id)->get();
	    	    }

                $html='';
                $html=$productDetailList->products_id."<br>" .$productDetailList->products_name;
               // $barcodeImage = DNS1D::getBarcodeSVG(62325748489222, 'C39',2, 30, '#2A3239');
              //  $barcodeImageName = 'barcode'. time().'.svg';
              //  Storage::disk('public')->put($barcodeImageName, $barcodeImage);
                	if (str_contains($productDetailList->products_price, ',')) {
					    $priceproduct = substr($productDetailList->products_price, 0, -1);
					}
					else {

						$ppp1 = number_format($productDetailList->products_price, 2, '.', ',');
						$priceproduct = $ppp1;
					}


	    	 	$date = date('d-m-Y');
            	$productsData['prouductId']             = $productDetailList->products_id;
            	$productsData['productName']            = $productDetailList->products_name;
            	$productsData['productPrice']           = $productDetailList->discount_price== ! NULL ? $productDetailList->discount_price : $priceproduct;


            	$productsData['productOriginalPrice']   = $priceproduct ;
            	$productsData['productOfferPercentage'] = $productDetailList->is_offer;
            	$productsData['productDescription'] = $productDetailList->products_description;

            	//$productsData['barcodeImage'] =URL::asset('storage/app/public/'.$barcodeImageName);
            	$productsData['ScheduledDelivery']      = ($productDetailList->delivery_days==!NUll) ? date('d F, Y', strtotime('+'.$productDetailList->delivery_days.'days')) :date('d F, Y', strtotime('+3 days'));

            	if(count($cusmorelikedId) > 0)
                   		{
                   			$customersId=$cusmorelikedId[0]->liked_customers_id;
                   			$productsData['productLiked']= true;
                   		}
                   		else
                   		{
                   				$productsData['productLiked']= false;
                   		}
            	if ($productDetailList->products_image == '') {
					$productsData['productsImage'] = str_replace("/index.php/", "/", url('resources/assets/images/user_profile/default_user.png'));
				} else {

					$productsData['productImage'] = $new.$productDetailList->products_image;
				}
	    	    if(!empty($post['customerId']))
	    	    {
	    	        $reviews=Review::leftjoin('reviews_description','reviews_description.reviews_id','reviews.reviews_id')
        		                ->leftjoin('customers','customers.customers_id','reviews.customers_id')
        		                ->where('reviews.products_id',$productDetailList->products_id)
        		                ->where('reviews.customers_id',$post['customerId'])
        		                ->where('reviews_description.languages_id',$getLangid)
        		                ->get();
	    	    }
        		$reviews=Review::leftjoin('reviews_description','reviews_description.reviews_id','reviews.reviews_id')
        		                ->leftjoin('customers','customers.customers_id','reviews.customers_id')
        		                ->where('reviews.products_id',$productDetailList->products_id)->where('reviews_description.languages_id',$getLangid)
        		                ->get();

        		if(count($reviews) > 0){

        			$urlnew = url('');

        		    foreach($reviews as $review)
            		$reviewData['customerName']         = $review->customers_name;
            		//$customerspicture = str_replace('index.php', '', $review->customers_picture);
            		$reviewData['customerPicture']      =$new.$review->customers_picture;
            		//$reviewData['customerPicture']      = url($review->customers_picture);
            		$reviewData['reviewText']           = $review->reviews_text;
            		$reviewDatas[] = $reviewData;
        		}
                $bannerimg=array();
                if(!empty($productDetailList->products_left_banner))
                {
                	$left = $new.$productDetailList->products_left_banner;
                }
                else
                {
                	$left = "";
                }

                if(!empty($productDetailList->products_right_banner))
                {
                	$right= $new.$productDetailList->products_right_banner;
                }
                else
                {
                	$right= "";
                }

                $products_attribute = Productsattributes::where('products_id', '=', $productId)->groupBy('options_id')->get();

                $index = 0;
                        if (count($products_attribute)) {
                            $index2 = 0;
                            $index = 0;
                            foreach ($products_attribute as $attribute_data) {

                               $option_name = Productsoptions::leftJoin('products_options_descriptions', 'products_options_descriptions.products_options_id', '=', 'products_options.products_options_id')->select('products_options.products_options_id', 'products_options_descriptions.options_name as products_options_name', 'products_options_descriptions.language_id')->where('language_id', '=', $getLangid)->where('products_options.products_options_id', '=', $attribute_data->options_id)->get();

                               if (count($option_name) > 0) {

                                    $temp = array();
                                    $temp_option['id'] = $attribute_data->options_id;
                                    $temp_option['name'] = $option_name[0]->products_options_name;
                                    $temp_option['is_default'] = $attribute_data->is_default;
                                    $attr[$index2]['option'] = $temp_option;
                                    $attributes_value_query = Productsattributes::where('products_id', '=', $productId)->where('options_id', '=', $attribute_data->options_id)->get();

                                    $k = 0;
                                   foreach ($attributes_value_query as $products_option_value) {

                                        $option_value = Productsoptionsvalues::leftJoin('products_options_values_descriptions', 'products_options_values_descriptions.products_options_values_id', '=', 'products_options_values.products_options_values_id')->select('products_options_values.products_options_values_id', 'products_options_values_descriptions.options_values_name as products_options_values_name')->where('products_options_values_descriptions.language_id', '=', $getLangid)->where('products_options_values.products_options_values_id', '=', $products_option_value->options_values_id)->get();

                                        $attributes = Productsattributes::where([['products_id', '=', $productId], ['options_id', '=', $attribute_data->options_id], ['options_values_id', '=', $products_option_value->options_values_id]])->get();

                                        $temp_i['products_attributes_id'] = $attributes[0]->products_attributes_id;
                                        $temp_i['id'] = $products_option_value->options_values_id;
                                        $temp_i['value'] = $option_value[0]->products_options_values_name;
                                        $temp_i['price'] = $products_option_value->options_values_price;
                                        $temp_i['price_prefix'] = $products_option_value->price_prefix;
                                        array_push($temp, $temp_i);

                                    }

                                    $attr[$index2]['values'] = $temp;
                                    $result['attributes'] = $attr;
                                    $index2++;
                                }
                            }


                        } else {
                            $result = '';
                        }
                        $index++;

                $bannerimage[]  =$left;
                $bannerimage[]  =$right;
                // $bannerimg[]=$bannerimage;
               // echo "<pre>"; print_r($bannerimg); exit;
                $productsData['option']=$result;
        		$productsData['review']=$reviewDatas;
                $productsData['bannerimg']=$bannerimage;
	    	 	$response = array('success' => 1, 'message' => Lang::get('labels.Product Detail list Loaded Successfully'), 'result' => $productsData);
				echo json_encode($response, JSON_UNESCAPED_UNICODE);exit;

	    	}
	    	 else {
				$response = array('success' => 0, 'message' => Lang::get('labels.No Data found'));
				echo json_encode($response);exit;
			}

	    }
	    catch (Exception $e) {
			$response = array('success' => 0, 'message' => $e->getMessage());
			echo json_encode($response, JSON_UNESCAPED_UNICODE);exit;
		}

	}

		public function productdealsList ()
	{

    	$input = file_get_contents('php://input');
		$post = json_decode($input, true);
		$urlnew = url('');
		$new = str_replace('index.php', '', $urlnew);

    	$limit = 10;
        $min_price = 0;
        $max_price = 0;
        $language="en";
	    if(!empty($post['language'])){ $language=$post['language']; }
	    App::setLocale($language);

        try{
                $post['customerId']="0";
                $data = array('page_number' => '0', 'limit' => $limit, 'min_price' => $min_price, 'max_price' => $max_price);

               /* if (empty($post['pagination']) or $post['pagination'] == 0) {
                    $skip = $post['pagination'] . '0';
                } else {
                    $skip = $data['limit'] * $post['pagination'];
                   // echo "<pre>";  print_r($skip); exit;
                }*/
                $sortby='';
                $order='';


                $min_price = $data['min_price'];
                $max_price = $data['max_price'];
                $take = $data['limit'];
                $currentDate = time();
                $eliminateRecord = array();
                $getLangid = $this->get_language_code($language);
   //              $categories = Products::join('manufacturers', 'manufacturers.manufacturers_id', '=', 'products.manufacturers_id', 'left')
   //                  ->join('manufacturers_info', 'manufacturers.manufacturers_id', '=', 'manufacturers_info.manufacturers_id', 'left')
   //                  ->join('products_description', 'products_description.products_id', '=', 'products.products_id', 'left')->where('products_description.language_id',$getLangid);
   //              if(!empty($post['categoriesId'])){
			// 	$categories->LeftJoin('products_to_categories', 'products.products_id', '=', 'products_to_categories.products_id')
			// 			->leftJoin('categories','categories.categories_id','=','products_to_categories.categories_id')
			// 			->LeftJoin('categories_description','categories_description.categories_id','=','products_to_categories.categories_id');
			// }

   //              $categories->LeftJoin('specials', function ($join) use ($currentDate) {
   //                  $join->on('specials.products_id', '=', 'products.products_id')->where('status', '=', '1')->where('expires_date', '>', $currentDate);
   //              })->select('products.*', 'products_description.*', 'manufacturers.*', 'manufacturers_info.manufacturers_url', 'specials.specials_new_products_price as discount_price');

                   $categories = Products::join('products_description', 'products_description.products_id', '=', 'products.products_id', 'left')
              ->join('specials','specials.products_id', '=', 'products.products_id','left')->where('specials.status', '=', '1')->where('specials.expires_date', '>', $currentDate)
              ->where('products_description.language_id',$getLangid)->select('products.*', 'products_description.*','specials.specials_new_products_price as discount_price');


                $categories->whereNotIn('products.products_id', function ($query) use ($currentDate) {
                    $query->select('flash_sale.products_id')->from('flash_sale')->where('flash_sale.flash_status', '=', '1');
                });

                if (!empty($data['products_id']) && $data['products_id'] != "") {
                    $categories->where('products.products_id', '=', $data['products_id']);
                }

                //for min and maximum price
                if (!empty($max_price)) {
                    $categories->whereBetween('products.products_price', [$min_price, $max_price]);
                }


                if(!empty($post['categoriesId'])){
					$categories->where('products_to_categories.categories_id','=', $post['categoriesId'])->get();
				}

                if(!empty($post['brandId'])){

					$categories->where('brand_id','=', $post['brandId'])->get();
				}

                $categories->where('products_status', '=', 1)->get();

               // DB::enableQueryLog();
                if(!empty($sortbyname))
                {

	                $categories->orderBy('products_name',$sortbyname)->groupBy('products.products_id');

				}
				 if(!empty($sortbyprice))
                {

	                $categories->orderBy('products_name',$sortbyprice)->groupBy('products.products_id');

				}
                if(!empty($filter))
                {

                	$categories->where(function ($query) use ($filter) {
				    $query->where('products_description','LIKE', '%'.$filter.'%')
				         ->orWhere('products_name','LIKE', '%'.$filter.'%');
					})->groupBy('products.products_id');

                }


               	$categories->groupBy('products.products_id');

                //count
                $total_record = $categories->get();

                $products = $categories->get();
                $result = array();
                $result2 = array();

                //check if record exist
                if (count($products) > 0) {

                    $index = 0;
                    foreach ($products as $products_data) {
                        $products_id = $products_data->products_id;

                        $products_images = Productsimages::select('image')->where('products_id', '=', $products_id)->orderBy('sort_order', 'ASC')->get();

                        $img=[];

                        foreach ($products as $key => $value) {

                        	$products[$key]->products_image= url($value->products_image);

                        }

                        $categories = Productstocategories::leftjoin('categories', 'categories.categories_id', 'products_to_categories.categories_id')
                            ->leftjoin('categories_description', 'categories_description.categories_id', 'products_to_categories.categories_id')
                            ->select('categories.categories_id', 'categories_description.categories_name', 'categories.categories_image', 'categories.categories_icon', 'categories.parent_id')
                            ->where('products_id', '=', $products_id)
                            ->get();

                            foreach ($categories as $key => $value) {

                            	$categories[$key]->categories_image=url($value->categories_image);

                            }

                        $products_data->categories = $categories;
                        array_push($result, $products_data);

                        $options = array();
                        $attr = array();

                        $stocks = 0;
                        $stockOut = 0;
                        if ($products_data->products_type == '0') {
                            $stocks = Inventory::where('products_id', $products_data->products_id)->where('stock_type', 'in')->sum('stock');
                            $stockOut = Inventory::where('products_id', $products_data->products_id)->where('stock_type', 'out')->sum('stock');

                        }

                        $result[$index]->defaultStock = $stocks - $stockOut;

                        if (count($categories) > 0) {
                            $result[$index]->isLiked = '1';
                        } else {
                            $result[$index]->isLiked = '0';
                        }

                        $result[$index]->isLiked = '0';

                        $products_attribute = Productsattributes::where('products_id', '=', $products_id)->groupBy('options_id')->get();

                        if (count($products_attribute)) {
                            $index2 = 0;
                            foreach ($products_attribute as $attribute_data) {

                                $option_name = Productsoptions::leftJoin('products_options_descriptions', 'products_options_descriptions.products_options_id', '=', 'products_options.products_options_id')->select('products_options.products_options_id', 'products_options_descriptions.options_name as products_options_name', 'products_options_descriptions.language_id')->where('language_id', '=', Session::get('language_id'))->where('products_options.products_options_id', '=', $attribute_data->options_id)->get();

                                if (count($option_name) > 0) {

                                    $temp = array();
                                    $temp_option['id'] = $attribute_data->options_id;
                                    $temp_option['name'] = $option_name[0]->products_options_name;
                                    $temp_option['is_default'] = $attribute_data->is_default;
                                    $attr[$index2]['option'] = $temp_option;
                                    $attributes_value_query = Productsattributes::where('products_id', '=', $products_id)->where('options_id', '=', $attribute_data->options_id)->get();
                                    $k = 0;
                                    foreach ($attributes_value_query as $products_option_value) {

                                        $option_value = Productsoptionsvalues::leftJoin('products_options_values_descriptions', 'products_options_values_descriptions.products_options_values_id', '=', 'products_options_values.products_options_values_id')->select('products_options_values.products_options_values_id', 'products_options_values_descriptions.options_values_name as products_options_values_name')->where('products_options_values_descriptions.language_id', '=', Session::get('language_id'))->where('products_options_values.products_options_values_id', '=', $products_option_value->options_values_id)->get();

                                        $attributes = Productsattributes::where([['products_id', '=', $products_id], ['options_id', '=', $attribute_data->options_id], ['options_values_id', '=', $products_option_value->options_values_id]])->get();

                                        $temp_i['products_attributes_id'] = $attributes[0]->products_attributes_id;
                                        $temp_i['id'] = $products_option_value->options_values_id;
                                        $temp_i['value'] = $option_value[0]->products_options_values_name;
                                        $temp_i['price'] = $products_option_value->options_values_price;
                                        $temp_i['price_prefix'] = $products_option_value->price_prefix;
                                        array_push($temp, $temp_i);

                                    }
                                    $attr[$index2]['values'] = $temp;
                                    $result[$index]->attributes = $attr;
                                    $index2++;
                                }
                            }
                        } else {
                            $result[$index]->attributes = array();
                        }
                        $index++;
                    }


                    foreach ($result as $key => $products_data) {


                   		$cusmorelikedId=Likedproducts::where('liked_customers_id',$post['customerId'])->where('liked_products_id',$products_data['products_id'])->get();

                    	$productsData=[];

                    	if (str_contains($products_data['products_price'], ',')) {
					    $priceproduct = substr($products_data['products_price'], 0, -1);
					}
					else {

						$ppp1 = number_format($products_data['products_price'], 2, '.', ',');
						$priceproduct = $ppp1;
					}

                    	$productsData['prouductId']             = $products_data['products_id'];
                    	$productsData['productName']            = $products_data['products_name'];
                    	$productsData['productPrice']           =  $priceproduct;
                    	$productsData['productOriginalPrice']   = $priceproduct;
                    	$proimage = str_replace('index.php', '', $products_data['products_image']);
                    	$productsData['productsImage']          = $proimage;
                    	$productsData['productOfferPercentage'] = $products_data['is_offer'];
                    	if(count($cusmorelikedId) > 0)
                   		{
                   			$customersId=$cusmorelikedId[0]->liked_customers_id;
                   			$productsData['productLiked']= true;
                   		}
                   		else
                   		{
                   				$productsData['productLiked']= false;
                   		}

	                		 $productsDatas[] = $productsData;

                    }

                    $responseData = array('success' => 1, 'product_data' => $productsDatas, 'message' => Lang::get('labels.Product list'), 'total_record' => count($total_record));
                    	echo json_encode($responseData, JSON_UNESCAPED_UNICODE);exit;
                } else {
                    $responseData = array('success' => 0,'message' => Lang::get('labels.Empty record'), 'total_record' => count($total_record));
                    echo json_encode($responseData, JSON_UNESCAPED_UNICODE);exit;
                }

    	}catch (Exception $e) {
			$responseData = array('success' => 0, 'message' => $e->getMessage());
			echo json_encode($responseData, JSON_UNESCAPED_UNICODE);exit;
		}

	}


	public function editProfile()
	{

		$input = file_get_contents('php://input');
		$post = json_decode($input, true);
		$urlnew = url('');
		$new = str_replace('index.php', '', $urlnew);

		$language="en";
	    if(!empty($post['language'])){ $language=$post['language']; }
	    App::setLocale($language);

	    try
	    {

	    	  $userId=$post['customerId'];
	    	  $firstname=$post['firstName'];
	    	  $lastname=$post['lastName'];
	    	  $mobilephone=$post['mobilePhone'];
	    	  $dob=$post['dob'];
	    	  $gender=$post['gender'];
	    	  $countryid=$post['countryId'];

	    	if((empty($userId)) || (empty($firstname)) || (empty($lastname)) || (empty($mobilephone)) || (empty($gender)) || (empty($dob)) || (empty($countryid)))
	        {
	          $response = array('success' => 0, 'message' => Lang::get('labels.All Fields Are Required'));
				echo json_encode($response, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_UNESCAPED_UNICODE | JSON_HEX_AMP);exit;
	        }

		    	    $userupdates = Customers::where('customers_id',$userId)->update([
		            'customers_firstname'   =>$firstname,
		            'customers_lastname'    =>$lastname,
		            'customers_telephone'   =>$mobilephone,
		            'customers_gender'      =>$gender,
		            'customers_dob'         =>$dob,
		            'country_id'            =>$countryid
		          ]);
		    $userupdate=Customers::where('customers_id',$post['customerId'])->first();
		    if(!empty($userupdate))
		    {
		    $countrylist=Country::where('countries_id',$userupdate->country_id)->first();
		    $result=[];
			$result['customerId']       = $userupdate->customers_id;
			$result['gender']           = $userupdate->customers_gender;
			$result['firstName']        = $userupdate->customers_firstname;
			$result['lastName']         = $userupdate->customers_lastname;
			$result['dob']              = $userupdate->customers_dob;
			$result['email']            = $userupdate->email;
			$result['userName']         = $userupdate->user_name;
			$result['mobile']           = $userupdate->customers_telephone;
			$result['countryCode']      = $countrylist->phone_code;
			$result['countryId']        = $countrylist->country_id;
			$result['defaultAddressId'] = $userupdate->customers_default_address_id;
			$result['isActive']         = $userupdate->isActive;
			$result['image']            = url($userupdate->customers_picture);
			$result['countryId']        = $userupdate->country_id;
		    }
		    else {
				$response = array('success' => 0, 'message' => Lang::get('labels.No Data found'));
				echo json_encode($response);exit;
			}
		//	$results[]=$result;
			$response = array('success' => 1, 'message' => Lang::get('labels.User Profile Updated Succeessfully'), 'result' => $result);
			echo json_encode($response, JSON_UNESCAPED_UNICODE);exit;
		}
	    catch (Exception $e) {
			$responseData = array('success' => 0, 'message' => $e->getMessage());
			echo json_encode($responseData, JSON_UNESCAPED_UNICODE);exit;
		}

	}

	public function searchProduct()
        {


        	$input = file_get_contents('php://input');
    		$post = json_decode($input, true);
    		$urlnew = url('');
    		$new = str_replace('index.php', '', $urlnew);

    		$language="en";
    	    if(!empty($post['language'])){ $language=$post['language']; }
    	    App::setLocale($language);

        	$limit = 10;
            $min_price = 0;
            $max_price = 0;


            try{
                    $getLangid = $this->get_language_code($language);
                    $data = array('page_number' => '0', 'limit' => $limit, 'min_price' => $min_price, 'max_price' => $max_price);


                    if (empty($post['pagination']) or $post['pagination'] == 0) {
                        $skip = $post['pagination'] . '0';
                    } else {
                        $skip = $data['limit'] * $post['pagination'];

                    }
                    $post['sortbyname']=0;
                    $sortbyname=0;
                    $post['sortbyprice']=0;

                    $sortbyname=$post['sortbyname'];
                    $sortbyprice=$post['sortbyprice'];

                    $sortby='';
                    $order='';

                    $min_price = $data['min_price'];
                    $max_price = $data['max_price'];
                    $take = $data['limit'];
                    $currentDate = time();
                    $eliminateRecord = array();

                    $categories = Products::join('manufacturers', 'manufacturers.manufacturers_id', '=', 'products.manufacturers_id', 'left')
                        ->join('manufacturers_info', 'manufacturers.manufacturers_id', '=', 'manufacturers_info.manufacturers_id', 'left')
                        ->join('products_description', 'products_description.products_id', '=', 'products.products_id', 'left')->where('language_id',$getLangid);


                    if(!empty($post['categoriesId'])){
    				$categories->LeftJoin('products_to_categories', 'products.products_id', '=', 'products_to_categories.products_id')
    						->leftJoin('categories','categories.categories_id','=','products_to_categories.categories_id')
    						->LeftJoin('categories_description','categories_description.categories_id','=','products_to_categories.categories_id');
    			}


                    if(!empty($post['categoriesId'])){
    					$categories->where('products_to_categories.categories_id','=', $post['categoriesId'])->get();
    				}

                    if(!empty($post['brandId'])){

    					$categories->where('brand_id','=', $post['brandId'])->get();
    				}

                    $categories->where('products_status', '=', 1)->get();

                   // DB::enableQueryLog();
                    if(!empty($sortbyname))
                    {

    	                $categories->orderBy('products_name',$sortbyname)->groupBy('products.products_id');

    				}
    				 if(!empty($sortbyprice))
                    {

    	                $categories->orderBy('products_name',$sortbyprice)->groupBy('products.products_id');

    				}
    			     $filter=$post['filter'];
                    if(!empty($filter))
                    {

                    	$categories->where(function ($query) use ($filter) {
    				    //$query->where('products.products_description','LIKE', '%'.$filter.'%')
    				    $query->where('products_description.products_name','LIKE', '%'.$filter.'%')
    				       ->orWhere('products_description.products_description','LIKE', '%'.$filter.'%');
    					})->groupBy('products.products_id');

                    }

                    //count
                    $total_record = $categories->get();

                   // echo "<pre>"; print_r($total_record); exit;
                    $products = $categories->skip($skip)->take($take)->get();


                    $result = array();
                    $result2 = array();

                    //check if record exist
                    if (count($products) > 0) {

                        $index = 0;
                        foreach ($products as $products_data) {
                            $products_id = $products_data->products_id;

                            $products_images = Productsimages::select('image')->where('products_id', '=', $products_id)->orderBy('sort_order', 'ASC')->get();

                            $img=[];

                            foreach ($products as $key => $value) {

                            	$products[$key]->products_image= url($value->products_image);

                            }

                            $categories = Productstocategories::leftjoin('categories', 'categories.categories_id', 'products_to_categories.categories_id')
                                ->leftjoin('categories_description', 'categories_description.categories_id', 'products_to_categories.categories_id')
                                ->select('categories.categories_id', 'categories_description.categories_name', 'categories.categories_image', 'categories.categories_icon', 'categories.parent_id')
                                ->where('products_id', '=', $products_id)
                                ->get();

                                foreach ($categories as $key => $value) {

                                	$categories[$key]->categories_image=url($value->categories_image);

                                }

                            $products_data->categories = $categories;
                            array_push($result, $products_data);

                            $options = array();
                            $attr = array();

                            $stocks = 0;
                            $stockOut = 0;
                            if ($products_data->products_type == '0') {
                                $stocks = Inventory::where('products_id', $products_data->products_id)->where('stock_type', 'in')->sum('stock');
                                $stockOut = Inventory::where('products_id', $products_data->products_id)->where('stock_type', 'out')->sum('stock');

                            }

                            $result[$index]->defaultStock = $stocks - $stockOut;

                            if (count($categories) > 0) {
                                $result[$index]->isLiked = '1';
                            } else {
                                $result[$index]->isLiked = '0';
                            }

                            $result[$index]->isLiked = '0';

                            $products_attribute = Productsattributes::where('products_id', '=', $products_id)->groupBy('options_id')->get();

                            if (count($products_attribute)) {
                                $index2 = 0;
                                foreach ($products_attribute as $attribute_data) {

                                    $option_name = Productsoptions::leftJoin('products_options_descriptions', 'products_options_descriptions.products_options_id', '=', 'products_options.products_options_id')->select('products_options.products_options_id', 'products_options_descriptions.options_name as products_options_name', 'products_options_descriptions.language_id')->where('language_id', '=', Session::get('language_id'))->where('products_options.products_options_id', '=', $attribute_data->options_id)->get();

                                    if (count($option_name) > 0) {

                                        $temp = array();
                                        $temp_option['id'] = $attribute_data->options_id;
                                        $temp_option['name'] = $option_name[0]->products_options_name;
                                        $temp_option['is_default'] = $attribute_data->is_default;
                                        $attr[$index2]['option'] = $temp_option;
                                        $attributes_value_query = Productsattributes::where('products_id', '=', $products_id)->where('options_id', '=', $attribute_data->options_id)->get();
                                        $k = 0;
                                        foreach ($attributes_value_query as $products_option_value) {

                                            $option_value = Productsoptionsvalues::leftJoin('products_options_values_descriptions', 'products_options_values_descriptions.products_options_values_id', '=', 'products_options_values.products_options_values_id')->select('products_options_values.products_options_values_id', 'products_options_values_descriptions.options_values_name as products_options_values_name')->where('products_options_values_descriptions.language_id', '=', Session::get('language_id'))->where('products_options_values.products_options_values_id', '=', $products_option_value->options_values_id)->get();

                                            $attributes = Productsattributes::where([['products_id', '=', $products_id], ['options_id', '=', $attribute_data->options_id], ['options_values_id', '=', $products_option_value->options_values_id]])->get();

                                            $temp_i['products_attributes_id'] = $attributes[0]->products_attributes_id;
                                            $temp_i['id'] = $products_option_value->options_values_id;
                                            $temp_i['value'] = $option_value[0]->products_options_values_name;
                                            $temp_i['price'] = $products_option_value->options_values_price;
                                            $temp_i['price_prefix'] = $products_option_value->price_prefix;
                                            array_push($temp, $temp_i);

                                        }
                                        $attr[$index2]['values'] = $temp;
                                        $result[$index]->attributes = $attr;
                                        $index2++;
                                    }
                                }
                            } else {
                                $result[$index]->attributes = array();
                            }
                            $index++;
                        }

                        foreach ($result as $key => $products_data) {
                            if(!empty($post['customerId']))
                            {
                            $cusmorelikedId=Likedproducts::where('liked_customers_id',$post['customerId'])->where('liked_products_id',$products_data['products_id'])->get();
                            }
                            else
                            {
                                $post['customerId']=0;
                                $cusmorelikedId=Likedproducts::where('liked_customers_id',$post['customerId'])->where('liked_products_id',$products_data['products_id'])->get();
                            }
                        	$productsData=[];

                        	if (str_contains($products_data['products_price'], ',')) {
							    $priceproduct = substr($products_data['products_price'], 0, -1);
							}
							else {

								$ppp1 = number_format($products_data['products_price'], 2, '.', ',');
								$priceproduct = $ppp1;
							}

                        	$productsData['prouductId']             = $products_data['products_id'];
                        	$productsData['productName']            = $products_data['products_name'];
                        	$productsData['productPrice']           = $products_data['discount_price']== ! NULL ? $products_data['discount_price'] : $priceproduct;
                        	$productsData['productOriginalPrice']   = $priceproduct;
                        	$proimage = str_replace('index.php', '', $products_data['products_image']);
                        	$productsData['productsImage']          = $proimage;
                        	$productsData['productOfferPercentage'] = $products_data['is_offer'];

                        	if(count($cusmorelikedId) > 0)
                       		{
                       			$customersId=$cusmorelikedId[0]->liked_customers_id;
                       			$productsData['productLiked']=  true;
                       		}
                       		else
                       		{
                       				$productsData['productLiked']= false;
                       		}

    	                	$productsDatas[] = $productsData;

                        }

                        $responseData = array('success' => 1,'total_record' => count($total_record), 'product_data' => $productsDatas, 'message' => Lang::get('labels.Product list'));
                        	echo json_encode($responseData, JSON_UNESCAPED_UNICODE);exit;
                    } else {
                        $responseData = array('success' => 0,'message' => Lang::get('labels.Empty record'), 'total_record' => count($total_record));
                        echo json_encode($responseData, JSON_UNESCAPED_UNICODE);exit;
                    }

        	}catch (Exception $e) {
    			$responseData = array('success' => 0, 'message' => $e->getMessage());
    			echo json_encode($responseData, JSON_UNESCAPED_UNICODE);exit;
    		}

        }

    public function cartList()
    	{
    		$input = file_get_contents('php://input');
    		$post = json_decode($input, true);
    		$urlnew = url('');
    		$new = str_replace('index.php', '', $urlnew);


    	    if(empty($post['language']))
    	    {
    	    	$post['language']="en";
    		}
    	    App::setLocale($post['language']);

    		try
    		{
    			 $getLangid = $this->get_language_code($post['language']);
    			 if(empty($post['customerId']))
    			 {

    		       $basketlist= Customersbasket::leftjoin('products','products.products_id','customers_basket.products_id')
            			->leftjoin('products_description','products_description.products_id','customers_basket.products_id')
            		->where('customers_basket.device_id',$post['deviceId'])->where('language_id',$getLangid)
            		->get();
    			 }
    			 else
    			 {

    		        $basketlist= Customersbasket::leftjoin('products','products.products_id','customers_basket.products_id')
            			->leftjoin('products_description','products_description.products_id','customers_basket.products_id')
            		->where('customers_basket.customers_id',$post['customerId'])->where('language_id',$getLangid)
            		->get();
    			 }
            $totalprices =0;
            		if (count($basketlist) > 0) {

				foreach ($basketlist as $key => $value) {

					$data1 = array();
					$img = Products::where('products_id', $value->products_id)->first();


					  // $cusmorelikedId=Likedproducts::where('liked_customers_id',$post['customerId'])->where('liked_products_id',$value['products_id'])->get();
						$productsData=[];

						if (str_contains($value->products_price, ',')) {
						    $priceproduct = substr($value->products_price, 0, -1);
						}
						else {

							$ppp1 = number_format($value->products_price, 2, '.', ',');
							$priceproduct = $ppp1;
						}



                    	$productsData['prouductId']                 = $value->products_id;
                    	$productsData['customerBasketId']           = $value->customers_basket_id;
                    	$productsData['customerBasketQuantity']     = $value->customers_basket_quantity;
                    	$productsData['productName']                = $value->products_name;
                    	$productsData['productOriginalPrice']       = $priceproduct;
                    	$productsData['productPrice']               = $priceproduct;
                    	$productsData['productFinalPrice']          = $priceproduct;

                    	$implode = explode('.',$value->products_price);
                    	$totalval= str_replace(',','',$implode[0]);

                    	$totalprices += intval($totalval) * $value->customers_basket_quantity;

                    	$productsData['productOfferPercentage']=$value->is_offer;
                    	if(!empty($img)){
        					    $productsData['productsImage'] = str_replace("/index.php/", "/", url($img->products_image));
        				}else{
        						$productsData['productsImage'] = str_replace("/index.php/", "/", url('resources/assets/images/user_profile/default_user.png'));
        					}

					//$data2[] = $productsData;

					$data[] = $productsData;

				}

				$ppp = number_format($totalprices, 2, '.', ',');

				$response = array('success' => 1, 'message' => trans('labels.cart List Successfully'), 'totalprice'=>$ppp,'result' => $data );
				echo json_encode($response, JSON_UNESCAPED_UNICODE);exit;
			} else {
				$response = array('success' => 0, 'message' => Lang::get('labels.No Data found'));
				echo json_encode($response);exit;
			}


    		} catch (Exception $e) {
    			$response = array('success' => 0, 'message' => $e->getMessage());
    			echo json_encode($response, JSON_UNESCAPED_UNICODE);exit;
    		}
    	}
    	 public function addToCart()
    	{
    		$input = file_get_contents('php://input');
    		$post = json_decode($input, true);
    		$urlnew = url('');
    		$new = str_replace('index.php', '', $urlnew);




    		$language="en";
    	    if(!empty($post['language'])){ $language=$post['language']; }
    	    App::setLocale($language);

    		try
    		{
    		         $session_id = Session::getId();
    		         $customers_basket_date_added = date('Y-m-d H:i:s');
    		         $post['isOrder']=1;
    		         if(empty($post['customerBasketQuantity'])){
    		            $post['customerBasketQuantity']=1;
    		         }

                	if((empty($post['productId'])))
        	        {

        	          $response = array('success' => 0, 'message' => Lang::get('labels.All Fields Are Required'));
        				echo json_encode($response, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_UNESCAPED_UNICODE | JSON_HEX_AMP);exit;
        	        }
            		$customerBasketData= Customersbasket::leftjoin('products','products.products_id','customers_basket.products_id')
            		->where('customers_basket.customers_id',$post['customerId'])
            		->where('customers_basket.products_id',$post['productId'])->get();

                    $productdata= Products::where('products_id',$post['productId'])->get();

            		if(count($customerBasketData) > 0 )
            		{
            		    foreach($customerBasketData as $value)
            		    {
            		        // $data=$value->customers_basket_quantity;
            		        // $quantity=$data+1;
            		        $pprice=$value->products_price;
            		        $quantity=$post['customerBasketQuantity'];

            		        $price=(float)number_format(str_replace(",","",$pprice),3,".","") * $quantity;
            		        $finalprice=$value->final_price + $price;


                		    $customersbasket =  Customersbasket::where('customers_basket_id',$value->customers_basket_id)->update([

    		                	'customers_id'                  =>	$value->customers_id,
            					'products_id'                   =>	$value->products_id,
            					// 'session_id'                 => $session_id,
            					'is_order'                      => $post['isOrder'],
            					'customers_basket_quantity'     => $post['customerBasketQuantity'],
            					'final_price'                   => $finalprice,
            					'customers_basket_date_added'   => $customers_basket_date_added,
            					'device_id'						=> $post['deviceId'],
            					'device_type'					=> $post['deviceType'],
    		                ]);
            		    }
            		}

            	  else{

                    	   	$customersbasket = Customersbasket::insert(
                				[
                					'customers_id'                  =>$post['customerId'],
                					'products_id'                   =>$post['productId'],
                					//'session_id'                    =>$session_id,
                					'is_order'                      =>$post['isOrder'],
                					'customers_basket_quantity'     =>$post['customerBasketQuantity'],
                					'final_price'                   =>$productdata[0]->products_price,
                					'customers_basket_date_added'   => $customers_basket_date_added,
                					'device_id'						=> $post['deviceId'],
            						'device_type'					=> $post['deviceType'],
                				]);

            	    }

            			$response = array('success' => 1, 'message' => Lang::get('labels.Product added Succeessfully in Basket'), 'result' => $customersbasket);
			            echo json_encode($response, JSON_UNESCAPED_UNICODE);exit;

    		} catch (Exception $e) {
    			$response = array('success' => 0, 'message' => $e->getMessage());
    			echo json_encode($response, JSON_UNESCAPED_UNICODE);exit;
    		}
    	}

    	public function IncreamnetDecreamentQuantity()   // 1- add //-1 remove // 0- delete
    	{
    	    $input = file_get_contents('php://input');
    	    $post = json_decode($input, true);
    		$urlnew = url('');
    		$new = str_replace('index.php', '', $urlnew);

    		$language="en";
    	    if(!empty($post['language'])){ $language=$post['language']; }
    	    App::setLocale($language);

    	    try
    		{
    		        $quantity='';

                	if((empty($post['productId'])) || (empty($post['customerBasketId'])))
        	        {
        	          $response = array('success' => 0, 'message' => Lang::get('labels.All Fields Are Required'));
        				echo json_encode($response, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_UNESCAPED_UNICODE | JSON_HEX_AMP);exit;
        	        }

        	        if(empty($post['customerId']))
    			 	{
    			 		$decrementquentity= Customersbasket::leftjoin('products','products.products_id','customers_basket.products_id')
	            		->where('customers_basket.customers_basket_id',$post['customerBasketId'])
	            		->where('customers_basket.device_id',$post['deviceId'])
	            		->where('customers_basket.products_id',$post['productId'])->first();
    			 	}
    			 	else
    			 	{
    			 		$decrementquentity= Customersbasket::leftjoin('products','products.products_id','customers_basket.products_id')
            		->where('customers_basket.customers_basket_id',$post['customerBasketId'])
            		->where('customers_basket.customers_id',$post['customerId'])
            		->where('customers_basket.products_id',$post['productId'])->first();
    			 	}




            		if(!empty($decrementquentity))
            		{
            		    $customersbasket='';

            		    if($post['incrementDecremnet']==-1)
                        {
        		        	$quantity=$decrementquentity->customers_basket_quantity - 1;
        		         	$implode1 = explode('.',$decrementquentity->products_price);
	                    	$totalval1= str_replace(',','',$implode1[0]);
	                    	$totalprices12 = intval($totalval1);
	        		        $price=$totalprices12 * $quantity;
	        		        $finalprice=$price;

        		          	$customersbasket = Customersbasket::where('customers_basket_id',$post['customerBasketId'])->update(
            				[
            					'customers_id'                  => $post['customerId'],
            					'products_id'                   => $post['productId'],
            					'is_order'                      => $decrementquentity->is_order,
            					'customers_basket_quantity'     => $quantity,
            					'final_price'                   => $finalprice,
            					'customers_basket_date_added'   => date('Y-m-d H:i:s'),
            				]);

                        }
                        else if ($post['incrementDecremnet'] == 1)
                        {

	                        $quantity=$decrementquentity->customers_basket_quantity + 1;

	                        $implode1 = explode('.',$decrementquentity->products_price);
	                    	$totalval1 = str_replace(',','',$implode1[0]);
	                    	$totalprices12 = intval($totalval1);
	        		        $price =$totalprices12 * $quantity;

        		            $finalprice=$price;

        		          	$customersbasket = Customersbasket::where('customers_basket_id',$post['customerBasketId'])->update(
            				[
            					'customers_id' =>$post['customerId'],
            					'products_id' => $post['productId'],
            					'is_order' =>$decrementquentity->is_order,
            					'customers_basket_quantity' =>$quantity,
            					'final_price' =>$finalprice,
            					'customers_basket_date_added' =>date('Y-m-d H:i:s'),
            				]);

                        }

                        else if ($post['incrementDecremnet']==0)
                        {
                            $customersbasket1 = Customersbasket::where('customers_basket_id',$post['customerBasketId'])->delete();
                        }

    		      	    $customers_basket_date_added = date('Y-m-d H:i:s');
                	    $session_id = Session::getId();

                	    $getLangid = $this->get_language_code($language);
                	    if(empty($post['customerId']))
    			 		{
    			 			$basketlist= Customersbasket::leftjoin('products','products.products_id','customers_basket.products_id')
                			->leftjoin('products_description','products_description.products_id','customers_basket.products_id')
                			->where('customers_basket.device_id',$post['deviceId'])->where('language_id',$getLangid)
                			->get();
    			 		}
    			 		else
    			 		{
    			 			$basketlist= Customersbasket::leftjoin('products','products.products_id','customers_basket.products_id')
                			->leftjoin('products_description','products_description.products_id','customers_basket.products_id')
                			->where('customers_basket.customers_id',$post['customerId'])->where('language_id',$getLangid)
                			->get();
    			 		}


                		$totalprices =0;
                		if (count($basketlist) > 0)
                		{

            				foreach ($basketlist as $key => $value)
            				{
            					// echo "<pre>";
            					// print_r($value); exit;

        						$implode = explode('.',$value->products_price);
		                    	$totalval= str_replace(',','',$implode[0]);

		                    	$totalprices += intval($totalval) * $value->customers_basket_quantity;
                                	//$totalprices += $value->final_price ;
            				}
                		}
                		else
                		{
                		    $response = array('success' => 1, 'message' =>  Lang::get('labels.No Data found'));
    			            echo json_encode($response, JSON_UNESCAPED_UNICODE);exit;
                		}
                	    	$ppp = number_format($totalprices, 2, '.', ',');

                			$response = array('success' => 1, 'message' => Lang::get('labels.Product added Succeessfully in Basket'),'totalprice'=>$ppp, 'result' => $customersbasket);
    			            echo json_encode($response, JSON_UNESCAPED_UNICODE);exit;
                		}
            		else
            		{
            		    $response = array('success' => 0, 'message' =>  Lang::get('labels.No Data found'));
    			        echo json_encode($response, JSON_UNESCAPED_UNICODE);exit;
            		}


    		} catch (Exception $e) {
    			$response = array('success' => 0, 'message' => $e->getMessage());
    			echo json_encode($response, JSON_UNESCAPED_UNICODE);exit;
    		}
    	}

    	public function smsVerification()
    	{

    		$language="en";
    	    if(!empty($post['language'])){ $language=$post['language']; }
    	    App::setLocale($language);

    	    try
    	    {
    	        $sixotp = mt_rand(100000, 999999);
                $from_num       = '+15005550006';
                $to_num         = '+919537779595';
                $service_url    = "https://api.twilio.com/2010-04-01/Accounts/AC1b53f6fe913822a3b535568588d97bcf/Messages.json";
                $credentials    = base64_encode("AC1b53f6fe913822a3b535568588d97bcf:0bd4451b32ce7bdcb3feb963a2650819");

                $contentType = 'text/xml';
                $post = http_build_query(array('Body'=>'your verification code is '.$sixotp.'','From'=>$from_num,'To'=>$to_num));

                $headers        = [];
                $headers[]      = "Authorization: Basic :" .$credentials;
                $headers[]      = 'Content-Type: application/x-www-form-urlencoded';
                $headers[]      = 'Cache-Control: no-cache';

                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL, $service_url);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
                curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
                curl_setopt($ch, CURLOPT_POST, 1);
                curl_setopt($ch, CURLOPT_POSTFIELDS,$post);
                $data = curl_exec($ch);
                $posts = json_decode($data, true);
                $data=Smsverificationcode::insert(['code'=>$sixotp]);

                if($data)
                {
                    $response = array('success' => 1, 'message' => Lang::get('labels.Code send Successfully'));
    			    echo json_encode($response, JSON_UNESCAPED_UNICODE);exit;
                }

               /* echo "<pre>"; print_r($posts); exit;*/
                curl_close($ch);
    	    } catch (Exception $e) {
    			$response = array('success' => 0, 'message' => $e->getMessage());
    			echo json_encode($response, JSON_UNESCAPED_UNICODE);exit;
    		}
    	}


    	public function orderClose()
    	{
    	    $input = file_get_contents('php://input');
    	    $post = json_decode($input, true);
    		$urlnew = url('');
    		$new = str_replace('index.php', '', $urlnew);

    		try
    		{
    		    if((empty($post['status'])) || (empty($post['orderId'])))
        	        {
        	          $response = array('success' => 0, 'message' => Lang::get('labels.All Fields Are Required'));
        				echo json_encode($response, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_UNESCAPED_UNICODE | JSON_HEX_AMP);exit;
        	        }

    		  $customersbasket =  Ordersstatushistory::where('orders_id',$post['orderId'])->update([

    		                	'orders_id'                     => $post['orderId'],
            					'orders_status_id'              => $post['status'],
                				'date_added'                    =>date("Y-m-d H:i:s"),
            					'customer_notified'             =>"1",
            					'comments'                      =>""

    		                ]);

			$response = array('success' => 1, 'message' => Lang::get('labels.Order Closed Successfully'));
			echo json_encode($response, JSON_UNESCAPED_UNICODE);exit;

    		} catch (Exception $e) {
    			$response = array('success' => 0, 'message' => $e->getMessage());
    			echo json_encode($response, JSON_UNESCAPED_UNICODE);exit;
    		}


    	}

        public function buyAgain()
    	{
    	    $input = file_get_contents('php://input');
    	    $post = json_decode($input, true);
    		$urlnew = url('');
    		$new = str_replace('index.php', '', $urlnew);

    			$language="en";
        	    if(!empty($post['language'])){ $language=$post['language']; }
        	    App::setLocale($language);

    		try
    		{
    		    if((empty($post['orderId'])) || (!isset($post['orderId']))  || (!isset($post['paymentMethod'])) || (empty($post['paymentMethod'])) || (!isset($post['addressId'])) || (empty($post['addressId'])))
        	        {
        	          $response = array('success' => 0, 'message' => Lang::get('labels.All Fields Are Required'));
        				echo json_encode($response, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_UNESCAPED_UNICODE | JSON_HEX_AMP);exit;
        	        }

            $checkOrderData=Order::leftjoin('orders_products','orders_products.orders_id','orders.orders_id')->where('orders.orders_id',$post['orderId'])->get();

           foreach($checkOrderData as $value )
           {
                $customerData = Customers::where('customers_id',$value['customers_id'])->first();

           }

                $getLangid = $this->get_language_code($post['language']);
    			$customerBasketData=Order::leftjoin('orders_products','orders_products.orders_id','orders.orders_id')
    			->leftjoin('products','products.products_id','orders_products.products_id')
    			->leftjoin('products_description','products_description.products_id','products.products_id')
    			->where('orders.customers_id',$value['customers_id'])->groupBy('orders_products.products_id')->where('language_id',$getLangid)->get();
    			$firstname=$customerData->customers_firstname;
    			$lastname=$customerData->customers_lastname;
    			$name=$firstname.$lastname;
	//	echo "<pre>"; print_r($customerBasketData);exit;
    			if(empty($customerData)){
    				$response = array('success' => 0, 'message' => Lang::get('labels.No Data found'));
    				echo json_encode($response, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_UNESCAPED_UNICODE | JSON_HEX_AMP);exit;
    			}
    			$addressData = Addressbook::leftjoin('countries','countries.countries_id','=','address_book.entry_country_id')->where('address_book_id',$post['addressId'])->first();
    			if(empty($addressData)){
    				$response = array('success' => 0, 'message' => Lang::get('labels.No Data found'));
    				echo json_encode($response, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_UNESCAPED_UNICODE | JSON_HEX_AMP);exit;
    			}
		if(count($customerBasketData) > 0) {

		    foreach ($customerBasketData as $key => $product) {

		    }
		}
        			$getdata['customers_id']              = $product['customers_id'];
        			$getdata['total_tax']                 = "";
        			$getdata['customers_name']            = $name;
        			$getdata['customers_street_address']  = $addressData->entry_street_address;
        			$getdata['customers_city']            = $addressData->entry_city;
        			$getdata['customers_postcode']        = $addressData->entry_postcode;
        			$getdata['customers_country']         = $addressData->countries_name != NULL ? $addressData->countries_name :"0";
        			$getdata['customers_telephone']       = $addressData->entry_mobile != NULL ? $addressData->entry_mobile : "0";
        			$getdata['customers_suburb']          = "";
        			$getdata['customers_state']           = "";
        			$getdata['email']                     = $customerData->email;
        			$getdata['delivery_name']             = $firstname;
        			$getdata['delivery_street_address']   = $addressData->entry_street_address;
        			$getdata['delivery_city']             = $addressData->entry_city;
        			$getdata['delivery_postcode']         = $addressData->entry_postcode;
        			$getdata['delivery_country']          = $addressData->countries_name != NULL ? $addressData->countries_name :"0";
        			$getdata['delivery_phone']            = $addressData->entry_mobile != "" ? $addressData->entry_mobile : "0";
        			$getdata['delivery_suburb']           = "";
        			$getdata['delivery_state']            = "";
        			$getdata['billing_name']              = $name;
        			$getdata['billing_street_address']    = $addressData->entry_street_address;
        			$getdata['billing_city']              = $addressData->entry_city;
        			$getdata['billing_postcode']          = $addressData->entry_postcode;
        			$getdata['billing_country']           = $addressData->countries_name != NULL ? $addressData->countries_name :"0";
        			$getdata['billing_phone']             = $addressData->entry_mobile != "" ? $addressData->entry_mobile : "0";
        			$getdata['billing_suburb']            = "";
        			$getdata['billing_state']             = "";
        			$getdata['order_price']               = $product['final_price'];
        			$getdata['shipping_cost']             = "0.00";
        			$getdata['shipping_method']           = "Flat Rate";
        			$getdata['ordered_source']            = "2";
        			$getdata['order_information']         = "[]";
        			$getdata['currency']                  = "$";
        			$getdata['last_modified']             = date("Y-m-d H:i:s");
        			$getdata['date_purchased']            = date("Y-m-d H:i:s");
        			$getdata['payment_method']            = $post['paymentMethod'];
        			$getdata['cc_type']                   = "";
        			$getdata['cc_owner']                  = "";
        			$getdata['cc_number']                 = "";
        			$getdata['cc_expires']                = "";

        			$insertId = Order::insertGetId($getdata);

                	$newgetdata['orders_id'] = $insertId;
        			$newgetdata['orders_status_id'] = "1";
        			$newgetdata['date_added'] = date("Y-m-d H:i:s");
        			$newgetdata['customer_notified'] = "1";
        			$newgetdata['comments'] = "";
        			$statusId = Ordersstatushistory::insertGetId($newgetdata);

        		    if(count($customerBasketData) > 0) {

		    foreach ($customerBasketData as $key => $product) {
        					$data = array();
        					$data['orders_id'] = $insertId;
        					$data['products_id'] = $product['products_id'];
        					$data['products_model'] = NULL;
        					$data['products_name'] = $product['products_name'];
        					$data['products_price'] = $product['products_price'];
        					$data['final_price'] = $product['final_price'];
        					$data['products_tax'] = "1";
        					$data['products_quantity'] = $product['products_quantity'];
        					$Ordersproductsid = Ordersproducts::insertGetId($data);
		    }
		}


			$response = array('success' => 1, 'message' => Lang::get('labels.Order Placed Successfully'));
			echo json_encode($response, JSON_UNESCAPED_UNICODE);exit;

    		} catch (Exception $e) {
    			$response = array('success' => 0, 'message' => $e->getMessage());
    			echo json_encode($response, JSON_UNESCAPED_UNICODE);exit;
    		}


    	}


    public function getOrderProductList()
    	{
    		$input = file_get_contents('php://input');
    		$post = json_decode($input, true);
    		$urlnew = url('');
    		$new = str_replace('index.php', '', $urlnew);

    		$language="en";
    	    if(!empty($post['language'])){ $language=$post['language']; }
    	    App::setLocale($language);

    		try
    		{
	            $getLangid = $this->get_language_code($post['language']);
            	$orderproductlist=Order::select('orders.*','orders_products.*','products.*','products_description.*','orders_products.products_quantity as products_quantity')->leftjoin('orders_products','orders_products.orders_id','orders.orders_id')
    			->leftjoin('products','products.products_id','orders_products.products_id')
    			->leftjoin('products_description','products_description.products_id','products.products_id')
    			->where('orders.orders_id',$post['orderId'])->where('language_id',$getLangid)->get();

            		if (count($orderproductlist) > 0) {
            		    $totalprices =0;
				        foreach ($orderproductlist as $key => $value) {

        					$data1 = array();
        					$img = Productsimages::where('products_id', $value->products_id)->orderBy('sort_order', 'ASC')->first();

        						$productsData=[];
                            	$productsData['prouductId']                 = $value->products_id;
                            	$productsData['customerBasketQuantity']     = $value->products_quantity;
                            	$productsData['productName']                = $value->products_name;
                            	$productsData['productPrice']               = $value->products_price;
                            	$productsData['productOriginalPrice']       = $value->products_price;
                            	$productsData['productFinalPrice']          =strval($value->final_price*$value->products_quantity);
                            	$totalprices += $value->final_price ;
                            	$productsData['productOfferPercentage']     =$value->is_offer;
                            	if(!empty($img)){
                					    $productsData['productsImage'] = str_replace("/index.php/", "/", url($img->image));
                				}else{
                						$productsData['productsImage'] = str_replace("/index.php/", "/", url('resources/assets/images/user_profile/default_user.png'));
                					}

        					    $data[] = $productsData;

        				}

				$response = array('success' => 1, 'message' => trans('labels.Order Product List Successfully'), 'totalprice'=>$totalprices,'result' => $data );
				echo json_encode($response, JSON_UNESCAPED_UNICODE);exit;
			} else {
				$response = array('success' => 0, 'message' => Lang::get('labels.No Data found'));
				echo json_encode($response);exit;
			}


    		} catch (Exception $e) {
    			$response = array('success' => 0, 'message' => $e->getMessage());
    			echo json_encode($response, JSON_UNESCAPED_UNICODE);exit;
    		}
    	}

    	function orderStatusList()
    	{
    	    $input = file_get_contents('php://input');
    	    $post = json_decode($input, true);
    		$urlnew = url('');
    		$new = str_replace('index.php', '', $urlnew);

    		$language="en";
    	    if(!empty($post['language'])){ $language=$post['language']; }
    	    App::setLocale($language);
    		try
    		{
    		    if((empty($post['orderId'])))
        	        {
        	          $response = array('success' => 0, 'message' => Lang::get('labels.All Fields Are Required'));
        				echo json_encode($response, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_UNESCAPED_UNICODE | JSON_HEX_AMP);exit;
        	        }

    		$orderStatusData=Ordersstatushistory::where('orders_id',$post['orderId'])->get();
    		$Status=DB::table('orders_status')->get();
    	//	echo "<pre>";print_r($Status);exit;
    		$orderStatusDataList=[];

    		if(count($Status) > 0 )
    		{
    		    foreach($Status as $value)
    		    {
    		                    $status=DB::table('orders_status')->where('orders_status_id',$value->orders_status_id)->first();
    		                    $orderStatusData=Ordersstatushistory::where('orders_id',$post['orderId'])->orderBy('orders_status_id','ASC')->get();


    		        	        $orderStatusDataList=[];
    		        	         foreach($orderStatusData as $data)
    		    {
    		        	        $orderStatusDataList['orderStatusHistoryId']       = $data->orders_status_history_id;
    		        	        $orderStatusDataList['orderId']                    = $data->orders_id;
    		        	        $orderStatusDataList['orderStatusId']              = $data->orders_status_id;
                            	$orderStatusDataList['Date']                       = $data->date_added;
                            	$orderStatusDataList['status']                     = ($post['language']== 'en') ?$status->orders_status_name :$status->orders_ar_status_name ;
                            	$orderStatusDataList['comments']                   = $data->comments;

                            	$orderStatusDataList['isStatusComplete']           = false;
                                        if($value->orders_status_id <= $data->orders_status_id)
                                        {
                            	    	    $orderStatusDataList['isStatusComplete']           =true;
                                        }



    		    }
                                $orderStatusDataLists[]=$orderStatusDataList;
    		    }

    		}
    		else
    		{
    		    $response = array('success' => 0, 'message' => Lang::get('labels.No Data found'));
				echo json_encode($response);exit;
    		}

			$response = array('success' => 1, 'message' => Lang::get('labels.Order Status'),'result'=>$orderStatusDataLists);
			echo json_encode($response, JSON_UNESCAPED_UNICODE);exit;

    		} catch (Exception $e) {
    			$response = array('success' => 0, 'message' => $e->getMessage());
    			echo json_encode($response, JSON_UNESCAPED_UNICODE);exit;
    		}
    	}

    	function getSimilarProduct()
    	{
    	    $input = file_get_contents('php://input');
    	    $post = json_decode($input, true);
    		$urlnew = url('');
    		$new = str_replace('index.php', '', $urlnew);

    		$language="en";
    	    if(!empty($post['language'])){ $language=$post['language']; }
    	    App::setLocale($language);
    		try
    		{
    		    if((empty($post['productId'])))
            	        {
            	          $response = array('success' => 0, 'message' => Lang::get('labels.All Fields Are Required'));
            				echo json_encode($response, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_UNESCAPED_UNICODE | JSON_HEX_AMP);exit;
            	        }
        		  $checkCategoryId=DB::table('products_to_categories')->where('products_id',$post['productId'])->get();

        		  foreach($checkCategoryId as $value){
        		    $CategoryId=DB::table('categories')->where('categories_id',$value->categories_id)->first();
        		  }

        		$getLangid = $this->get_language_code($post['language']);
        		if($CategoryId->categories_id){

                  $CategoryWiseProductList=DB::table('products')
                   ->LeftJoin('products_description', 'products_description.products_id','products.products_id')
                  ->LeftJoin('products_to_categories', 'products.products_id','products_to_categories.products_id')
                  ->where('products_to_categories.categories_id',$CategoryId->categories_id)->where('language_id',$getLangid)->get();

                 foreach ($CategoryWiseProductList as $key => $products_data) {
                    	if(!empty($post['customerId']) && !empty($post['customerId']))
                    	{
                    		$cusmorelikedId=Likedproducts::where('liked_customers_id',$post['customerId'])->where('liked_products_id',$products_data->products_id)->get();
                    	}
                    	else
                    	{
                    		$cusmorelikedId= array();
                    	}

                    	$productsData=[];
                    	$productsData['prouductId']             = $products_data->products_id;
                    	$productsData['productName']            = $products_data->products_name;

                    	if (str_contains($products_data->products_price, ',')) {
						    $priceproduct = substr($products_data->products_price, 0, -1);
						}
						else {

							$ppp1 = number_format($products_data->products_price, 2, '.', ',');
							$priceproduct = $ppp1;
						}

                    	$productsData['productPrice']           = $priceproduct;
                        $productsData['productOriginalPrice']   = $priceproduct;
                    	$productsData['productsImage']          = $new.$products_data->products_image;
                    	$productsData['productOfferPercentage'] = $products_data->is_offer;


                    	if(count($cusmorelikedId) > 0)
                   		{
                   			$customersId=$cusmorelikedId[0]->liked_customers_id;
                   			$productsData['productLiked']=  true;
                   		}
                   		else
                   		{
                   			$productsData['productLiked']= false;
                   		}

	                	$productsDatas[] = $productsData;

                    }

    			    $response = array('success' => 1, 'message' => Lang::get('labels.Get Similar Proudct List'),'product_data'=>$productsDatas);
    			    echo json_encode($response, JSON_UNESCAPED_UNICODE);exit;
        		}
        		else {
    				$response = array('success' => 0, 'message' => Lang::get('labels.No Data found'));
    				echo json_encode($response);exit;
    			}
    		} catch (Exception $e) {
    			$response = array('success' => 0, 'message' => $e->getMessage());
    			echo json_encode($response, JSON_UNESCAPED_UNICODE);exit;
    		}
    	}

    	function get_language_code($objectName)
        {
            if (!empty($objectName))
            {
            	$languageId = "";
                $language = Language::where('code',$objectName)->first();
                $languageId = $language->languages_id;
                return $languageId;
            }
        }

    	function changeLanguage()
        {
            $input = file_get_contents('php://input');
            $post = json_decode($input, true);
            $urlnew = url('');
            $new = str_replace('index.php', '', $urlnew);

            $language="en";
            if(!empty($post['language'])){
                $language=$post['language'];

            }
            App::setLocale($language);
            if ((!isset($post['userId'])) || empty($post['userId']))
            {
                $response = array('success' => 0, 'message' => Lang::get('labels.All Fields Are Required'));
                echo json_encode($response, JSON_UNESCAPED_UNICODE);exit;
            }
            try
            {

                $userupdates = Customers::where('customers_id',$post['userId'])->update([
                    'language'   =>$language,
                ]);

                $response = array('success' => 1, 'message' => 'Language changed successfuly');
                echo json_encode($response, JSON_UNESCAPED_UNICODE);exit;

            }
            catch (Exception $e)
            {
                $response = array('success' => 0, 'message' => $e->getMessage());
                echo json_encode($response, JSON_UNESCAPED_UNICODE);exit;
            }
        }


        public function exchange_rate()
        {
            // echo 5454; die;

            $json = json_decode(file_get_contents('https://api.testwyre.com/v3/rates'), true);

            // echo "<pre>"; print_r($json); die;

            foreach($json as $key=>$val){

                // $values = array('pare_code' => $key,'exchange_rate' => $val);
                // DB::table('exchange_rate')->insert($values);
               $udpate_rate = DB::table('exchange_rate')->where('pare_code', $key)->update(array('exchange_rate' => $val));

               if($udpate_rate){
                    echo "update successfully";
                } else {
                    echo "Error";
                }

            }





        }



}
