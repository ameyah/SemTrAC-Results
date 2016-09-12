<?php
/**
 * Created by PhpStorm.
 * User: ameya
 * Date: 9/10/16
 * Time: 11:53 PM
 */

include('../conf.php');
include('../utils.php');

$segments = Array();
$pwset_id = 0;
$query = "SELECT pwset_id, user_website_id FROM user_websites WHERE auth_status = 1 AND pwset_id >= 213 ORDER BY pwset_id";
$result = mysqli_query($dbc, $query);
while ($response_row = mysqli_fetch_array($result)) {
    if($response_row['pwset_id'] != $pwset_id) {
        $segments = Array();
        $pwset_id = $response_row['pwset_id'];
    }
    $query = "SELECT segment, capital, special FROM transformed_segments INNER JOIN transformed_credentials ON
              transformed_segments.transformed_cred_id = transformed_credentials.transformed_cred_id WHERE
              transformed_credentials.transformed_cred_id IN (SELECT transformed_cred_id FROM transformed_credentials
              WHERE user_website_id = " . $response_row['user_website_id'] . ")";
    $segment_result = mysqli_query($dbc, $query);
    while($segment_row = mysqli_fetch_array($segment_result)) {
        
    }
}