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
$segments_important = Array();
$segments_important_capital_special = Array();
$passwords_important = Array();
$passwords_non_important = Array();
$shared_passwords = 0;
$pwset_id = 0;
$query = "SELECT pwset_id, user_website_id FROM user_websites WHERE pwset_id >= 213 ORDER BY pwset_id";
$result = mysqli_query($dbc, $query);
while ($response_row = mysqli_fetch_array($result)) {
    if ($response_row['pwset_id'] != $pwset_id) {
        $total_successful_segments = 0;
        foreach ($segments as $segment) {
            $total_successful_segments += (int)$segment;
        }
        if (count($segments) != 0) {
            $sharing_important = count($segments_important) / count($segments);
            $sharing_important_capital_special = count($segments_important_capital_special) / count($segments_capital_special);
        } else {
            $sharing_important = 0;
            $sharing_important_capital_special = 0;
        }

        // calculate password sharing between important and non important websites
        foreach($passwords_important as $imp_password) {
            if(in_array($imp_password, $passwords_non_important)) {
                // password has been shared
                $shared_passwords += 1;
            }
        }

        $result_query = "INSERT INTO user_analysis SET pwset_id = " . (int)$pwset_id . ", total_segments = " .
            $total_successful_segments . ", unique_segments = " . count($segments) . ",
                        unique_segments_capital_special = " . count($segments_capital_special) . ", sharing_segments_important = " .
            $sharing_important . ", sharing_segments_important_capital_special = " . $sharing_important_capital_special . ",
            shared_passwords = " . $shared_passwords;
        mysqli_query($dbc, $result_query);
        $segments = Array();
        $segments_capital_special = Array();
        $segments_important = Array();
        $segments_important_capital_special = Array();
        $passwords_important = Array();
        $passwords_non_important = Array();
        $shared_passwords = 0;
        $pwset_id = $response_row['pwset_id'];
    }
    $query = "SELECT segment, capital, special, (SELECT website_probability from user_websites WHERE user_website_id = " .
              $response_row['user_website_id'] . ") website_importance FROM transformed_segments INNER JOIN
              transformed_credentials ON transformed_segments.transformed_cred_id = transformed_credentials.transformed_cred_id
              WHERE transformed_credentials.transformed_cred_id IN (SELECT transformed_cred_id FROM transformed_credentials
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
    while ($segment_row = mysqli_fetch_array($segment_result)) {
        /*print "<pre>";
        print_r($segment_row);
        print_r($segment_row['segment']);
        print "</pre>";*/
        if (array_key_exists($segment_row['segment'], $segments)) {
            $segments[$segment_row['segment']] += 1;
        } else {
            $segments[$segment_row['segment']] = 1;
        }
        $entire_segment = $segment_row['segment'] . $segment_row['capital'] . $segment_row['special'];
        if (!in_array($entire_segment, $segments_capital_special)) {
            array_push($segments_capital_special, $entire_segment);
        }

        // sharing between important and non important websites
        // sharing = (# of unique segments in passwords for imp. websites) / (total # of unique segments)
        if ((int)$segment_row['website_importance'] == 1) {
            // segment is for important website
            if (!in_array($segment_row['segment'], $segments_important)) {
                array_push($segments_important, $segment_row['segment']);
            }
            $entire_segment_important = $segment_row['segment'] . $segment_row['capital'] . $segment_row['special'];
            if (!in_array($entire_segment_important, $segments_important_capital_special)) {
                array_push($segments_important_capital_special, $entire_segment_important);
            }
        }
    }

    // password sharing analysis between important and non-important websites
    $query = "SELECT password_text, (SELECT website_probability from user_websites WHERE user_website_id = " .
              $response_row['user_website_id'] . ") website_importance FROM transformed_credentials WHERE user_website_id = " .
              $response_row['user_website_id'];
    $password_result = mysqli_query($dbc, $query);
    while($password_row = mysqli_fetch_array($password_result)) {
        if((int)$password_row['website_importance'] == 1) {
            if(!in_array($password_row['password_text'], $passwords_important)) {
                array_push($passwords_important, $password_row['password_text']);
            }
        } else {
            if(!in_array($password_row['password_text'], $passwords_non_important)) {
                array_push($passwords_non_important, $password_row['password_text']);
            }
        }
    }
}