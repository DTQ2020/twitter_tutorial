<?php namespace App\Controllers;

class Tweet extends BaseController
{

	var $tweet_model;

	public function initController(\CodeIgniter\HTTP\RequestInterface $request, \CodeIgniter\HTTP\ResponseInterface $response, \Psr\Log\LoggerInterface $logger)
	{
		// Do Not Edit This Line
		parent::initController($request, $response, $logger);

		$this->tweet_model = new \App\Models\Tweet_model();
	}

	public function index() 
	{
		echo view("tweet/index.php", array());
	}

	public function auth_twitter() 
	{
		require APPPATH . "/ThirdParty/vendor/autoload.php";

		$twitter_api_key = "your_key";
		$twitter_api_key_secret = "your_key_secret";

		$verifier = $_GET['oauth_verifier'];
		$oauth_token = $_SESSION['oauth_token'];
		$oauth_token_secret = $_SESSION['oauth_token_secret'];


		// These values are not empty when the user has been sent to Twitter,
		// authroised their account and then returned back to us.
		if(!empty($verify) && !empty($oauth_token) && !empty($oauth_token_secret)) {
			// Connect to oauth
			$auth = new \Abraham\TwitterOAuth\TwitterOAuth(
		    	$twitter_api_key,
			    $twitter_api_key_secret,
				$oauth_token,
				$oauth_token_secret
			);

			// Get access token
			$access_token = $auth->oauth("oauth/access_token", 
		    	array(
		    		"oauth_verifier" => $verifier
		    	)
		    );

			// If bad HTTP code, we output errors
			if($auth->getLastHttpCode() != 200) {
		    	$errors = "";
		    	foreach($access_token->errors as $error) {
					$errors .= $error->message . "<br /><br />";
				}
				echo $errors;
				exit();
		    }

		    // Great, we can now get our user details and store them for future use
		    $ui = new \Abraham\TwitterOAuth\TwitterOAuth(
				$twitter_api_key,
			    $twitter_api_key_secret,
				$access_token['oauth_token'], 
				$access_token['oauth_token_secret']);

			$userData = $ui->get('account/verify_credentials');

			// Check for errors getting user info data
			$errors = "";
			if ($ui->getLastHttpCode() != 200) {
				foreach($userData->errors as $error) {
					$errors .= $error->message . "<br /><br />";
				}
				echo $errors;
				exit();
			}

			// Twitter username
			$name = $userData->screen_name;

			$this->tweet_model->add_auth(array(
				"type" => 1,
				"username" => $name,
				"oauth_token" => $access_token['oauth_token'],
				"oauth_token_secret" => $access_token['oauth_token_secret']
				)
			);

			return redirect()->to("/Tweet/index");
		} else {
			$auth = new \Abraham\TwitterOAuth\TwitterOAuth(
				$twitter_api_key,
				$twitter_api_key_secret
			);

			// OAUTH REquest
			// We redirect the user to this url once we have our tokens
			$request_token = $auth->oauth("oauth/request_token", 
				array(
					"oauth_callback" => site_url("Tweet/auth_twitter")
				)
			);

			// Set oauth tokens
			$_SESSION['oauth_token'] = $request_token['oauth_token'];
			$_SESSION['oauth_token_secret'] = $request_token['oauth_token_secret'];

			if($request_token['oauth_callback_confirmed']) {

		 		$url = $auth->url("oauth/authenticate", 
		 			array(
		 				"oauth_token" => $request_token['oauth_token']
		 			)
		 		);

		 		return redirect()->to($url);

		 	} else {
		 		echo "Invalid Redirect URL!";
		 		exit();
		 	}
		}

		 echo "done";
		 exit();
	}

	public function make_tweet() 
	{
		require APPPATH . "/ThirdParty/vendor/autoload.php";

		$twitter_api_key = "your_key";
		$twitter_api_key_secret = "your_key_secret";

		$id = 1;

		$twitter = $this->tweet_model->get_auth($id);
		if(!$twitter) {
			echo "Invalid Auth";
			exit();
		}

		
		$tweet = $this->request->getPost("tweet");
		if(empty($tweet)) {
			echo "Tweet cannot be empty!";
			exit();
		}


		$ui = new \Abraham\TwitterOAuth\TwitterOAuth(
				$twitter_api_key,
				$twitter_api_key_secret,
				$twitter->oauth_token, 
				$twitter->oauth_token_secret);

		$userData = $ui->get('account/verify_credentials');

		$errors = "";
		if ($ui->getLastHttpCode() != 200) {
			foreach($userData->errors as $error) {
				$errors .= $error->message . "<br /><br />";
			}
			echo $errors;
			exit();
		}
		
		// Twitter username
		$name = $userData->screen_name;
		
		// Make the tweet
		$ui->post("statuses/update", ["status" => $tweet]);

		return redirect()->to("Tweet/index");
	}

}

?>