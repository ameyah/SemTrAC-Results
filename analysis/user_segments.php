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
$segments_capital_special = Array();
$pwset_id = 0;
$query = "SELECT pwset_id, user_website_id FROM user_websites WHERE pwset_id >= 213 ORDER BY pwset_id";
$result = mysqli_query($dbc, $query);
while ($response_row = mysqli_fetch_array($result)) {
    if($response_row['pwset_id'] != $pwset_id) {
        $total_successful_segments = 0;
        foreach($segments as $segment) {
            $total_successful_segments += (int) $segment;
        }
        $result_query = "INSERT INTO user_analysis SET pwset_id = " . (int)$pwset_id . ", total_segments_successful = " .
                        $total_successful_segments . ", unique_segments_successful = " . count($segments) . ",
                        unique_segments_successful_capital_special = " . count($segments_capital_special);
        mysqli_query($dbc, $result_query);
        $segments = Array();
        $segments_capital_special = Array();
        $pwset_id = $response_row['pwset_id'];
    }
    $query = "SELECT segment, capital, special FROM transformed_segments INNER JOIN transformed_credentials ON
              transformed_segments.transformed_cred_id = transformed_credentials.transformed_cred_id WHERE
              transformed_credentials.transformed_cred_id IN (SELECT transformed_cred_id FROM transformed_credentials
              WHERE user_website_id = " . $response_row['user_website_id'] . ") AND transformed_credentials.auth_status =
              1";
    $segment_result = mysqli_query($dbc, $query);
    // store segments in the form of:
    // ['segment': 5, 'xx': 2]
    // where 'segment' and 'xx' are password segments. The value is the total number of times the segment
    // appeared in the current user's passwords
    // the length of associative array represents the number of unique segments in the current user's passwords
    // segments_capital_special is in the form of ['segment001010', 'segment100000']. The first part is the segment name.
    // 2nd and 3rd parts are the capital and password mangling information for the respective segment.
    while($segment_row = mysqli_fetch_array($segment_result)) {
        /*print "<pre>";
        print_r($segment_row);
        print_r($segment_row['segment']);
        print "</pre>";*/
        if(array_key_exists($segment_row['segment'], $segments)) {
            $segments[$segment_row['segment']] += 1;
        } else {
            $segments[$segment_row['segment']] = 1;
        }
        $entire_segment = $segment_row['segment'] . $segment_row['capital'] . $segment_row['special'];
        if(!in_array($entire_segment, $segments_capital_special)) {
            array_push($segments_capital_special, $entire_segment);
        }
    }
}