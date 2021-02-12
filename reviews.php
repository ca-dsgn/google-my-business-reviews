<?php

	$url = "../../";

	require_once $url.'php/Google/autoload.php';
	require_once $url.'php/Google/Client.php';
	require_once $url.'files/services/mybusiness.php';
	
	define('CLIENT_SECRET_PATH', 'private/client_secret.json');
	define('CREDENTIALS_PATH', 'private/credentials.json');
  
	$client = new Google_Client();
	
	//$client->setDeveloperKey($DEVELOPER_KEY);
	$client->setAuthConfigFile(CLIENT_SECRET_PATH);
	$client->setAccessType('offline');
	$client->setApprovalPrompt('force');
	$client->addScope("https://www.googleapis.com/auth/plus.business.manage");

	$client->setRedirectUri("http://localhost/you-dsgn/files/services/reviews.php");

	$accessToken = file_get_contents(CREDENTIALS_PATH);

	if (isset($_GET["code"])) {

		//Step 2: Authenticate and save to credentials

		$accessToken = $client->authenticate($_GET['code']);

		file_put_contents(CREDENTIALS_PATH, $accessToken);
	}

	if ($accessToken != "" and $accessToken != "\n") {

		$client->setAccessToken($accessToken);

		// Refresh the token if it's expired.

		if ($client->isAccessTokenExpired()) {

			$client->refreshToken($client->getRefreshToken());

			file_put_contents(CREDENTIALS_PATH, $client->getAccessToken());
		}

	} else {

		//Step 1: Get Auth URL

		$authUrl = $client->createAuthUrl();

		header("Location: ".$authUrl);
	}

	$service = new Google_Service_MyBusiness($client);

	$accounts = $service->accounts;

	$accountsList = $accounts->listAccounts()->getAccounts();

	$account = $accountsList[0];

	$locations = $service->accounts_locations;

	$locationsList = $locations->listAccountsLocations($account->name)->getLocations();

	if (empty($locationsList) === false) {

        foreach ($locationsList as $location) {

        	if ($location->getLocationName() == "CA Design") {

				$reviewsList = $service->accounts_locations_reviews;
			    $reviews = $reviewsList->listAccountsLocationsReviews($location->name)->getReviews();

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

			    	if (isset($review["comment"])) {

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