<?php
/**
 * Created by PhpStorm.
 * User: ameya
 * Date: 9/21/16
 * Time: 1:50 PM
 */

include('../conf.php');
include('../utils.php');

$query = "SELECT (SELECT website_text FROM websites WHERE website_id = user_websites.website_id) website_text FROM
          user_websites WHERE pwset_id = 215 AND website_probability = 1";
$result = mysqli_query($dbc, $query);
$imp_websites = mysqli_fetch_all($result);
$imp_websites_filtered = Array();
foreach($imp_websites as $website) {
    echo $website[0];
    $domain = parse_url($website[0], PHP_URL_HOST);
    print_r($domain);
    array_push($imp_websites_filtered, $domain);
}
print_r($imp_websites_filtered);

$query = "SELECT (SELECT website_text FROM websites WHERE website_id = user_websites.website_id) website_text FROM
          user_websites WHERE pwset_id = 215 AND user_website_id IN (SELECT DISTINCT user_website_id FROM transformed_credentials
          WHERE user_website_id IN (SELECT user_website_id FROM user_websites WHERE pwset_id = 215))";
$result = mysqli_query($dbc, $query);
$logged_in_websites = mysqli_fetch_all($result);
