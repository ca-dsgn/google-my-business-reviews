<?php

	$url = "../../";

	//Dev
	$redirect_url = "http://localhost/you-dsgn/files/services/reviews.php";

	//Production
	//$redirect_url = "https://www.ca-dsgn.de/files/services/reviews.php";

	$company_name = "CA Design";

	require_once $url.'php/helper.php';
	require_once($url."files/services/mybusiness.php");
	
	define('CLIENT_SECRET_PATH', 'private/client_secret.json');
	define('CREDENTIALS_PATH', 'private/credentials.json');
  
	$client = new Google\Client();
	
	//$client->setDeveloperKey($DEVELOPER_KEY);
	$client->setAuthConfig(CLIENT_SECRET_PATH);
	$client->setAccessType('offline');
	$client->setApprovalPrompt('force');
	$client->addScope("https://www.googleapis.com/auth/plus.business.manage");

	$client->setRedirectUri($redirect_url);

	$accessToken = json_decode(file_get_contents(CREDENTIALS_PATH),true);

	if (isset($_GET["code"])) {

		//Step 2: Authenticate and save to credentials

		$accessToken = $client->fetchAccessTokenWithAuthCode($_GET['code']);

		file_put_contents(CREDENTIALS_PATH, json_encode($accessToken));
	}

	if ($accessToken != "" and $accessToken != "\n") {

		$client->setAccessToken($accessToken);

		// Refresh the token if it's expired.

		if ($client->isAccessTokenExpired()) {

			$client->refreshToken($client->getRefreshToken());

			file_put_contents(CREDENTIALS_PATH, json_encode($client->getAccessToken()));
		}

	} else {

		//Step 1: Get Auth URL

		$authUrl = $client->createAuthUrl();

		header("Location: ".$authUrl);
	}

	$my_business_account = new Google_Service_MyBusinessAccountManagement($client);

	$list_accounts = $my_business_account->accounts->listAccounts();
	
	$account = $list_accounts["accounts"][0];

	$my_business_information = new Google_Service_MyBusinessBusinessInformation($client);

	$locations = $my_business_information->accounts_locations;

	$optParams = [
		'pageSize' => 20,
		'readMask' => array(
	       'name',
	       'title',
	       'profile'
	   )
	];

	$locationsList = $locations->listAccountsLocations($account->name, $optParams)->getLocations();

	$service = new Google_Service_MyBusiness($client);

	if (empty($locationsList) === false) {

        foreach ($locationsList as $location) {

        	if ($location->title == $company_name) {

				$reviewsList = $service->accounts_locations_reviews;

			    $reviews = $reviewsList->listAccountsLocationsReviews($account->name."/".$location->name)->getReviews();

			    $response = array("reviews" => array());

			    $total_review_value = 0;
			    $total_reviews = 0;

			    foreach($reviews as $key => $review) {

			    	$reviewer = $review->getReviewer();

			    	switch ($review["starRating"]) {

			    		case "FIVE":

			    			$rating = 5;
			    			break;

			    		case "FOUR":

			    			$rating = 4;
			    			break;

			    		case "THREE":

			    			$rating = 3;
			    			break;

			    		case "TWO":

			    			$rating = 2;
			    			break;

			    		case "ONE":

			    			$rating = 1;
			    			break;
			    	}

			    	$total_review_value+= $rating;
			    	$total_reviews++;

			    	if (isset($review["comment"]) and $rating > 3) {

			    		$comment = $review["comment"];

			    		if (strpos($comment,"\n\n(Translated by Google)") !== false) {
			    		
			    			$comment = substr($comment,0,strpos($comment,"\n\n(Translated by Google)"));
			    		}
			    		$comment = preg_replace('/\s+(?=[\.,])/', '', $comment);

				    	$r = array(

				    		"reviewer" => array(

				    			"name" => $reviewer["displayName"],
				    			"img" => $reviewer["profilePhotoUrl"]
				    		),
				    		"comment" => $comment,
				    		"created" => $review["createTime"],
				    		"rating" => $rating,
				    		"stars" => $review["starRating"]
				    	);

			    		array_push($response["reviews"],$r);
			    	}
			    }

			    $response["statistics"] = array("total" => $total_reviews,
			    								"average" => round($total_review_value/$total_reviews,1));

			    print json_encode($response, JSON_PRETTY_PRINT);
			}
		}
	}

?>
