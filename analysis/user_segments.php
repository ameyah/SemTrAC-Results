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
$passwords_important_capital_special = Array();
$passwords_real_important = Array();
$passwords_real_important_capital_special = Array();
$passwords_non_important = Array();
$passwords_non_important_capital_special = Array();
$shared_passwords = 0;
$shared_passwords_capital_special = 0;
$shared_real_passwords = 0;
$shared_real_passwords_capital_special = 0;
$shared_passwords_arr = Array();
$shared_passwords_arr_capital_special = Array();
$shared_real_passwords_arr = Array();
$shared_real_passwords_arr_capital_special = Array();
$password_segments_important = Array();
$password_segments_non_important = Array();
$pwset_id = 0;


$check_real_passwords_query = "SELECT DISTINCT pwset_id FROM user_websites WHERE real_important = 1 and user_website_id
                              in (select distinct user_website_id from transformed_credentials where auth_status = 1)
                              and pwset_id >= 213 order by pwset_id";
$result = mysqli_query($dbc, $check_real_passwords_query);
$real_passwords_participants = mysqli_fetch_all($result);
$real_passwords_participants_formatted = Array();
foreach($real_passwords_participants as $participant) {
    array_push($real_passwords_participants_formatted, $participant[0]);
}


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

        foreach($passwords_important_capital_special as $imp_password) {
            if(in_array($imp_password, $passwords_non_important_capital_special)) {
                // password has been shared
                $shared_passwords_capital_special += 1;
            }
        }

        // calculate password sharing between non important and real important websites
        /*if($pwset_id == 217) {
            print_r($passwords_real_important);
            echo "<br><Br>";
            print_r($passwords_non_important);
        }*/
        foreach($passwords_real_important as $imp_password) {
            if(in_array($imp_password, $passwords_non_important)) {
                // password has been shared
                $shared_real_passwords += 1;
            }
        }

        foreach($passwords_real_important_capital_special as $imp_password) {
            if(in_array($imp_password, $passwords_non_important_capital_special)) {
                // password has been shared
                $shared_real_passwords_capital_special += 1;
            }
        }

        $result_query = "INSERT INTO user_analysis SET pwset_id = " . (int)$pwset_id . ", total_segments = " .
            $total_successful_segments . ", unique_segments = " . count($segments) . ",
                        unique_segments_capital_special = " . count($segments_capital_special) . ", sharing_segments_important = " .
            $sharing_important . ", sharing_segments_important_capital_special = " . $sharing_important_capital_special . ",
            shared_passwords = " . $shared_passwords . ", shared_passwords_capital_special = " . $shared_passwords_capital_special .
            ", shared_real_passwords = " . $shared_real_passwords . ", shared_real_passwords_capital_special = " . $shared_real_passwords_capital_special;
        mysqli_query($dbc, $result_query);
        if((int)$pwset_id != 0) {
            array_push($shared_passwords_arr, $shared_passwords);
            array_push($shared_passwords_arr_capital_special, $shared_passwords_capital_special);
        }

        if((int)$pwset_id != 0 && in_array($pwset_id, $real_passwords_participants_formatted)) {
            array_push($shared_real_passwords_arr, $shared_real_passwords);
            array_push($shared_real_passwords_arr_capital_special, $shared_real_passwords_capital_special);
        }
        $segments = Array();
        $segments_capital_special = Array();
        $segments_important = Array();
        $segments_important_capital_special = Array();
        $passwords_important = Array();
        $passwords_important_capital_special = Array();
        $passwords_real_important = Array();
        $passwords_real_important_capital_special = Array();
        $passwords_non_important = Array();
        $passwords_non_important_capital_special = Array();
        $shared_passwords = 0;
        $shared_passwords_capital_special = 0;
        $shared_real_passwords = 0;
        $shared_real_passwords_capital_special = 0;
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
              $response_row['user_website_id'] . ") website_importance, (SELECT real_important FROM user_websites WHERE
              user_website_id = " . $response_row['user_website_id'] . ") real_importance, transformed_cred_id,
              password_grammar_id FROM transformed_credentials WHERE user_website_id = " . $response_row['user_website_id'] .
              " AND auth_status = 1";
    $password_result = mysqli_query($dbc, $query);
    while($password_row = mysqli_fetch_array($password_result)) {
        // now add passwords to different arrays considering capital and special character usage
        $grammar_query = "SELECT grammar_text FROM grammar WHERE grammar_id = " . (int)$password_row['password_grammar_id'];
        $grammar_result = mysqli_query($dbc, $grammar_query);
        $grammar_row = mysqli_fetch_array($grammar_result);
        $grammar_arr = generate_grammar_arr($grammar_row['grammar_text']);
        $query = "SELECT * FROM transformed_segments WHERE transformed_cred_id = " . (int)$password_row['transformed_cred_id'];
        $password_segment_result = mysqli_query($dbc, $query);
        $temp_password = "";
        $password_segments_arr = Array();
        $i = 0;
        while($password_segment_row = mysqli_fetch_array($password_segment_result)) {
            $temp_segment = $password_segment_row['segment'] . $password_segment_row['capital'] . $password_segment_row['special'];
            $temp_password .= $temp_segment;
            // for similar passwords
            $temp_segment_arr = Array(
                $temp_segment => $grammar_arr[$i++]
            );
            array_push($password_segments_arr, $temp_segment_arr);
        }
        if((int)$password_row['website_importance'] == 1) {
            if(!in_array($password_row['password_text'], $passwords_important)) {
                array_push($passwords_important, $password_row['password_text']);
            }
            if(!in_array($temp_password, $passwords_important_capital_special)) {
                array_push($passwords_important_capital_special, $temp_password);
            }

            // for similar passwords
            /*if(array_key_exists($password_row['password_text'], $password_segments_important)) {
                // check if segments are also same
                $new_flag = false;
                foreach($password_segments_arr as $segment) {
                    if(!in_array($segment, $password_segments_important[$password_row['password_text']])) {
                        $new_flag = true;
                    }
                }
                if($new_flag) {
                    // add password and segments to $password_segments_important

                }
            }*/
        } else {
            if(!in_array($password_row['password_text'], $passwords_non_important)) {
                array_push($passwords_non_important, $password_row['password_text']);
            }
            if(!in_array($temp_password, $passwords_non_important_capital_special)) {
                array_push($passwords_non_important_capital_special, $temp_password);
            }
        }
        if(in_array($response_row['pwset_id'], $real_passwords_participants_formatted)) {
            if ((int)$password_row['real_importance'] == 1) {
                if (!in_array($password_row['password_text'], $passwords_real_important)) {
                    array_push($passwords_real_important, $password_row['password_text']);
                }
                if (!in_array($temp_password, $passwords_real_important_capital_special)) {
                    array_push($passwords_real_important_capital_special, $temp_password);
                }
            }
        }
    }
}

function median($arr){
    if($arr){
        $count = count($arr);
        sort($arr);
        $mid = floor(($count-1)/2);
        return ($arr[$mid]+$arr[$mid+1-$count%2])/2;
    }
    return 0;
}
function average($arr){
    return ($arr) ? array_sum($arr)/count($arr) : 0;
}

echo "total users: " . count($shared_passwords_arr) . "<br>";
echo "median" . median($shared_passwords_arr) . "<br>";
echo "mean " . average($shared_passwords_arr) . "<br>";
echo 100 - (count(array_keys($shared_passwords_arr, 0))/count($shared_passwords_arr)) * 100;
echo "<br>-----<br>";
/*foreach($shared_passwords_arr as $pass) {
    echo $pass . "<br>";
}*/

echo "<br>total users: " . count($shared_passwords_arr_capital_special) . "<br>";
echo "median" . median($shared_passwords_arr_capital_special) . "<br>";
echo "mean " . average($shared_passwords_arr_capital_special) . "<br>";
echo 100 - (count(array_keys($shared_passwords_arr_capital_special, 0))/count($shared_passwords_arr_capital_special)) * 100;
echo "<br>-----<br>";
/*foreach($shared_passwords_arr_capital_special as $pass) {
    echo $pass . "<br>";
}*/

echo "total users: " . count($shared_real_passwords_arr) . "<br>";
echo "median" . median($shared_real_passwords_arr) . "<br>";
echo "mean " . average($shared_real_passwords_arr) . "<br>";
echo 100 - (count(array_keys($shared_real_passwords_arr, 0))/count($shared_real_passwords_arr)) * 100;
echo "<br>-----<br>";
/*foreach($shared_real_passwords_arr as $pass) {
    echo $pass . "<br>";
}*/

echo "<br>total users: " . count($shared_real_passwords_arr_capital_special) . "<br>";
echo "median" . median($shared_real_passwords_arr_capital_special) . "<br>";
echo "mean " . average($shared_real_passwords_arr_capital_special) . "<br>";
echo 100 - (count(array_keys($shared_real_passwords_arr_capital_special, 0))/count($shared_real_passwords_arr_capital_special)) * 100;
echo "<br>-----<br>";
/*foreach($shared_real_passwords_arr_capital_special as $pass) {
    echo $pass . "<br>";
}*/

//print_r($passwords_important_capital_special);
//print_r($passwords_important_capital_special);