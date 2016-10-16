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
$password_segments_real_important = Array();
$shared_segments_arr = Array();
$shared_real_segments_arr = Array();
$shared_segments = 0;
$shared_real_segments = -1;
$total_passwords_arr = Array();
$unsuccessful_passwords_arr = Array();
$total_passwords_count_arr_all_users = Array();
$real_password_arr = Array();
$real_important_password_usage_non_important = -1;
$real_important_password_usage_non_important_arr = Array();
$sites_passwords_count_arr = Array();
$sites_unique_passwords = 0;
$sites_not_unique_passwords = 0;
$sites_unique_passwords_arr = Array();
$sites_not_unique_passwords_arr = Array();
$imp_sites_passwords_count_arr = Array();
$imp_sites_unique_passwords = 0;
$imp_sites_not_unique_passwords = 0;
$imp_sites_unique_passwords_arr = Array();
$imp_sites_not_unique_passwords_arr = Array();
$not_imp_sites_passwords_count_arr = Array();
$not_imp_sites_unique_passwords = 0;
$not_imp_sites_not_unique_passwords = 0;
$not_imp_sites_unique_passwords_arr = Array();
$not_imp_sites_not_unique_passwords_arr = Array();
$real_imp_sites_passwords_count_arr = Array();
$real_imp_sites_unique_passwords = 0;
$real_imp_sites_not_unique_passwords = 0;
$real_imp_sites_unique_passwords_arr = Array();
$real_imp_sites_not_unique_passwords_arr = Array();
$temp_password_website_arr = Array();
$pwset_id = 0;


$check_real_passwords_query = "SELECT DISTINCT pwset_id FROM user_websites WHERE real_important = 1 and user_website_id
                              in (select distinct user_website_id from transformed_credentials where auth_status = 1)
                              and pwset_id >= 213 order by pwset_id";
$result = mysqli_query($dbc, $check_real_passwords_query);
$real_passwords_participants = mysqli_fetch_all($result);
$real_passwords_participants_formatted = Array();
foreach ($real_passwords_participants as $participant) {
    array_push($real_passwords_participants_formatted, $participant[0]);
}

$check_real_password_sharing_users = "SELECT pwset_id FROM user_analysis WHERE shared_real_passwords = 0 AND pwset_id >= 213";
$result = mysqli_query($dbc, $check_real_password_sharing_users);
$real_password_not_sharing_users = mysqli_fetch_all($result);
$real_password_not_sharing_users_formatted = Array();
foreach($real_password_not_sharing_users as $participant) {
    array_push($real_password_not_sharing_users_formatted, $participant[0]);
}


$query = "SELECT pwset_id, user_website_id, website_id FROM user_websites WHERE pwset_id >= 213 ORDER BY pwset_id";
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
        foreach ($passwords_important as $imp_password) {
            if (in_array($imp_password, $passwords_non_important)) {
                // password has been shared
                $shared_passwords += 1;
            }
        }

        foreach ($passwords_important_capital_special as $imp_password) {
            if (in_array($imp_password, $passwords_non_important_capital_special)) {
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
        foreach ($passwords_real_important as $imp_password) {
            if (in_array($imp_password, $passwords_non_important)) {
                // password has been shared
                $shared_real_passwords += 1;
            }
        }

        foreach ($passwords_real_important_capital_special as $imp_password) {
            if (in_array($imp_password, $passwords_non_important_capital_special)) {
                // password has been shared
                $shared_real_passwords_capital_special += 1;
            }
        }

        // segment sharing between important and non important websites
        foreach ($password_segments_important as $imp_password) {
            foreach ($password_segments_non_important as $non_imp_password) {
                if (substr_count(get_longest_common_subsequence($imp_password, $non_imp_password), "$|$") >= 2) {
                    $shared_segments += 1;
                    break;
                }
            }
        }

        foreach ($password_segments_real_important as $imp_password) {
            if($shared_real_segments == -1) {
                $shared_real_segments = 0;
            }
            foreach ($password_segments_non_important as $non_imp_password) {
                if (substr_count(get_longest_common_subsequence($imp_password, $non_imp_password), "$|$") >= 2) {
                    $shared_real_segments += 1;
                    break;
                }
            }
        }

        // # of sites for which the password used on it was unique VS # of sites for which the password was shared with
        // at least one more site
        foreach($sites_passwords_count_arr as $key=>$value) {
            if($value == 1) {
                $sites_unique_passwords += 1;
            } else {
                $sites_not_unique_passwords += $value;
            }
        }

        foreach($imp_sites_passwords_count_arr as $key=>$value) {
            if($value == 1) {
                $imp_sites_unique_passwords += 1;
            } else {
                $imp_sites_not_unique_passwords += $value;
            }
        }

        foreach($not_imp_sites_passwords_count_arr as $key=>$value) {
            if($value == 1) {
                $not_imp_sites_unique_passwords += 1;
            } else {
                $not_imp_sites_not_unique_passwords += $value;
            }
        }

        foreach($real_imp_sites_passwords_count_arr as $key=>$value) {
            if($value == 1) {
                $real_imp_sites_unique_passwords += 1;
            } else {
                $real_imp_sites_not_unique_passwords += $value;
            }
        }


        $result_query = "INSERT INTO user_analysis SET pwset_id = " . (int)$pwset_id . ", total_segments = " .
            $total_successful_segments . ", unique_segments = " . count($segments) . ",
                        unique_segments_capital_special = " . count($segments_capital_special) . ", sharing_segments_important = " .
            $sharing_important . ", sharing_segments_important_capital_special = " . $sharing_important_capital_special . ",
            shared_passwords = " . $shared_passwords . ", shared_passwords_capital_special = " . $shared_passwords_capital_special .
            ", shared_real_passwords = " . $shared_real_passwords . ", shared_real_passwords_capital_special = " . $shared_real_passwords_capital_special .
            ", sharing_similar_passwords = " . $shared_segments . ", sharing_similar_real_passwords = " . $shared_real_segments .
            ", total_passwords = " . (int)count($total_passwords_arr) . ", sites_unique_passwords = " . (int)$sites_unique_passwords .
            ", sites_not_unique_passwords = " . (int)$sites_not_unique_passwords . ", sites_unique_passwords_important = " .
            (int)$imp_sites_unique_passwords . ", sites_not_unique_passwords_important = " . (int)$imp_sites_not_unique_passwords .
            ", sites_unique_passwords_non_important = " . (int)$not_imp_sites_unique_passwords . ", sites_not_unique_passwords_non_important = " .
            (int)$not_imp_sites_not_unique_passwords . ", sites_unique_passwords_real_important = " . (int)$real_imp_sites_unique_passwords .
            ", sites_not_unique_passwords_real_important = " . (int)$real_imp_sites_not_unique_passwords;
        mysqli_query($dbc, $result_query);
        if($shared_segments == 0) {
            echo "<br>" . $pwset_id . "<br>";
        }
        if ((int)$pwset_id != 0) {
            array_push($shared_passwords_arr, $shared_passwords);
            array_push($shared_passwords_arr_capital_special, $shared_passwords_capital_special);
            array_push($shared_segments_arr, $shared_segments);

            array_push($sites_unique_passwords_arr, $sites_unique_passwords);
            array_push($sites_not_unique_passwords_arr, $sites_not_unique_passwords);
            array_push($imp_sites_unique_passwords_arr, $imp_sites_unique_passwords);
            array_push($imp_sites_not_unique_passwords_arr, $imp_sites_not_unique_passwords);
            array_push($not_imp_sites_unique_passwords_arr, $not_imp_sites_unique_passwords);
            array_push($not_imp_sites_not_unique_passwords_arr, $not_imp_sites_not_unique_passwords);

            if($shared_real_segments != -1) {
                array_push($shared_real_segments_arr, $shared_real_segments);
            }
            // store total passwords count in array
            array_push($total_passwords_count_arr_all_users, (int)count($total_passwords_arr));
            if($real_important_password_usage_non_important > -1) {
                array_push($real_important_password_usage_non_important_arr, $real_important_password_usage_non_important);
//                echo $pwset_id . " - " . $real_important_password_usage_non_important . "<br>";
            }
        }

        if ((int)$pwset_id != 0 && in_array($pwset_id, $real_passwords_participants_formatted)) {
            array_push($shared_real_passwords_arr, $shared_real_passwords);
            array_push($shared_real_passwords_arr_capital_special, $shared_real_passwords_capital_special);

            array_push($real_imp_sites_unique_passwords_arr, $real_imp_sites_unique_passwords);
            array_push($real_imp_sites_not_unique_passwords_arr, $real_imp_sites_not_unique_passwords);
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
        $password_segments_important = Array();
        $password_segments_non_important = Array();
        $password_segments_real_important = Array();
        $shared_passwords = 0;
        $shared_passwords_capital_special = 0;
        $shared_real_passwords = 0;
        $shared_real_passwords_capital_special = 0;
        $shared_segments = 0;
        $shared_real_segments = -1;
        $total_passwords_arr = Array();
        $unsuccessful_passwords_arr = Array();
        $real_password_arr = Array();
        $real_important_password_usage_non_important = -1;
        $sites_passwords_count_arr = Array();
        $sites_unique_passwords = 0;
        $sites_not_unique_passwords = 0;
        $imp_sites_passwords_count_arr = Array();
        $imp_sites_unique_passwords = 0;
        $imp_sites_not_unique_passwords = 0;
        $not_imp_sites_passwords_count_arr = Array();
        $not_imp_sites_unique_passwords = 0;
        $not_imp_sites_not_unique_passwords = 0;
        $real_imp_sites_passwords_count_arr = Array();
        $real_imp_sites_unique_passwords = 0;
        $real_imp_sites_not_unique_passwords = 0;
        $temp_password_website_arr = Array();
        $pwset_id = $response_row['pwset_id'];

        // store real important passwords considering capitalization and mangling information
        if(in_array($response_row['pwset_id'], $real_password_not_sharing_users_formatted)) {
            $real_important_websites_query = "SELECT transformed_cred_id FROM transformed_credentials WHERE user_website_id IN
            (SELECT user_website_id FROM user_websites WHERE pwset_id = " . $response_row['pwset_id'] . " AND
            real_important = 1) AND auth_status = 1";
            $real_important_password_result = mysqli_query($dbc, $real_important_websites_query);
            while ($real_important_id = mysqli_fetch_array($real_important_password_result)) {
                $real_important_segment_query = "SELECT segment, capital, special FROM transformed_segments WHERE
              transformed_cred_id = " . (int)$real_important_id['transformed_cred_id'];
                $real_important_segment_result = mysqli_query($dbc, $real_important_segment_query);
                $real_important_password = "";
                while ($real_important_segment_row = mysqli_fetch_array($real_important_segment_result)) {
                    $real_important_password .= $real_important_segment_row['segment'] . $real_important_segment_row['capital'] .
                        $real_important_segment_row['special'];
                }
                if (!in_array($real_important_password, $real_password_arr)) {
                    array_push($real_password_arr, $real_important_password);
                }
                $real_important_password_usage_non_important = 0;
            }
        }
    }
    $query = "SELECT segment, capital, special, (SELECT website_probability from user_websites WHERE user_website_id = " .
        $response_row['user_website_id'] . ") website_importance, (SELECT website_text FROM websites WHERE website_id =" .
              $response_row['website_id'] . ") website_text FROM transformed_segments INNER JOIN
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
              password_grammar_id, (SELECT website_text FROM websites WHERE website_id = ". $response_row['website_id'] .
            ") website_text FROM transformed_credentials WHERE user_website_id = " . $response_row['user_website_id'] .
        " AND auth_status = 1";
    $password_result = mysqli_query($dbc, $query);
    while ($password_row = mysqli_fetch_array($password_result)) {
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
        while ($password_segment_row = mysqli_fetch_array($password_segment_result)) {
            $temp_segment = $password_segment_row['segment'] . $password_segment_row['capital'] . $password_segment_row['special'];
            $temp_password .= $temp_segment;
            // for similar passwords
//            $password_segments_arr[$temp_segment] = $grammar_arr[$i++];
            array_push($password_segments_arr, $password_segment_row['segment']);
        }
        $password_segments_separated = join("$|$", $password_segments_arr);
        $password_segments_separated = "$|$" . $password_segments_separated . "$|$";
        if ((int)$password_row['website_importance'] == 1) {
            if (!in_array($password_row['password_text'], $passwords_important)) {
                array_push($passwords_important, $password_row['password_text']);
            }
            if (!in_array($temp_password, $passwords_important_capital_special)) {
                array_push($passwords_important_capital_special, $temp_password);
            }

            // for similar passwords
            if (!in_array($password_segments_separated, $password_segments_important)) {
                // check if segments are also same
                /*$new_flag = false;
                foreach($password_segments_arr as $segment=>$grammar) {
                    if(!in_array($segment, $password_segments_important[$password_row['password_text']])) {
                        $new_flag = true;
                    }
                }
                if($new_flag) {
                    // add password and segments to $password_segments_important

                }*/
                array_push($password_segments_important, $password_segments_separated);
            }
        } else {
            if (!in_array($password_row['password_text'], $passwords_non_important)) {
                array_push($passwords_non_important, $password_row['password_text']);
            }
            if (!in_array($temp_password, $passwords_non_important_capital_special)) {
                array_push($passwords_non_important_capital_special, $temp_password);
            }
            if (!in_array($password_segments_separated, $password_segments_non_important)) {
                array_push($password_segments_non_important, $password_segments_separated);
            }
        }
        if (in_array($response_row['pwset_id'], $real_passwords_participants_formatted)) {
            if ((int)$password_row['real_importance'] == 1) {
                if (!in_array($password_row['password_text'], $passwords_real_important)) {
                    array_push($passwords_real_important, $password_row['password_text']);
                }
                if (!in_array($temp_password, $passwords_real_important_capital_special)) {
                    array_push($passwords_real_important_capital_special, $temp_password);
                }
                if (!in_array($password_segments_separated, $password_segments_real_important)) {
                    array_push($password_segments_real_important, $password_segments_separated);
                }
            }
        }

        // store capital_special password in sites_passwords_count_arr consisting of password => count for calculating
        // # of sites for which the password used on it was unique VS # of sites for which the password was shared with
        // at least one more site
//        $domain = get_domain("http://" . $password_row['website_text']);
        $unique_password = $temp_password . $password_row['website_text'];
        if(!in_array($unique_password, $temp_password_website_arr)) {
            array_push($temp_password_website_arr, $unique_password);
            if (array_key_exists($temp_password, $sites_passwords_count_arr)) {
                $sites_passwords_count_arr[$temp_password] += 1;
            } else {
                $sites_passwords_count_arr[$temp_password] = 1;
            }

            if ((int)$password_row['website_importance'] == 1) {
                if (array_key_exists($temp_password, $imp_sites_passwords_count_arr)) {
                    $imp_sites_passwords_count_arr[$temp_password] += 1;
                } else {
                    $imp_sites_passwords_count_arr[$temp_password] = 1;
                }
            } else {
                if (array_key_exists($temp_password, $not_imp_sites_passwords_count_arr)) {
                    $not_imp_sites_passwords_count_arr[$temp_password] += 1;
                } else {
                    $not_imp_sites_passwords_count_arr[$temp_password] = 1;
                }
            }

            if (in_array($response_row['pwset_id'], $real_passwords_participants_formatted)) {
                if ((int)$password_row['real_importance'] == 1) {
                    if (array_key_exists($temp_password, $real_imp_sites_passwords_count_arr)) {
                        $real_imp_sites_passwords_count_arr[$temp_password] += 1;
                    } else {
                        $real_imp_sites_passwords_count_arr[$temp_password] = 1;
                    }
                }
            }
        }
    }

    // calculate total # of passwords defined by # of unique passwords for successful logins + # of passwords which is
    // used for multiple unsuccessful attempts. Capitalization and mangling information is not considered.
    $query = "SELECT password_text, auth_status, transformed_cred_id FROM transformed_credentials WHERE user_website_id = " .
        $response_row['user_website_id'];
    $password_result = mysqli_query($dbc, $query);
    while ($password_row = mysqli_fetch_array($password_result)) {
        if ($password_row['auth_status'] == 1) {
            if (!in_array($password_row['password_text'], $total_passwords_arr)) {
                array_push($total_passwords_arr, $password_row['password_text']);
            }
        } else {
            if (!in_array($password_row['password_text'], $total_passwords_arr)) {
                if (!in_array($password_row['password_text'], $unsuccessful_passwords_arr)) {
                    array_push($unsuccessful_passwords_arr, $password_row['password_text']);
                } else {
                    // unsuccessful password multiple times. Add to total_passwords_arr
                    array_push($total_passwords_arr, $password_row['password_text']);
                }
            }
        }

        // analysis for: do users attempt to use “high security account” passwords when logging in to “low importance”
        // websites?
        if(count($real_password_arr) > 0) {
            if($password_row['auth_status'] == 0) {
                $password_segment_query = "SELECT segment, capital, special FROM transformed_segments WHERE
                  transformed_cred_id = " . $password_row['transformed_cred_id'];
                $password_segment_result = mysqli_query($dbc, $password_segment_query);
                $unsuccessful_password = "";
                while($unsuccessful_segment = mysqli_fetch_array($password_segment_result)) {
                    $unsuccessful_password .= $unsuccessful_segment['segment'] . $unsuccessful_segment['capital'] .
                        $unsuccessful_segment['special'];
                }
                if(in_array($unsuccessful_password, $real_password_arr)) {
                    $real_important_password_usage_non_important += 1;
                }
            }
        }
    }

}

function median($arr)
{
    if ($arr) {
        $count = count($arr);
        sort($arr);
        $mid = floor(($count - 1) / 2);
        return ($arr[$mid] + $arr[$mid + 1 - $count % 2]) / 2;
    }
    return 0;
}

function average($arr)
{
    return ($arr) ? array_sum($arr) / count($arr) : 0;
}

echo "total users: " . count($shared_passwords_arr) . "<br>";
echo "median" . median($shared_passwords_arr) . "<br>";
echo "mean " . average($shared_passwords_arr) . "<br>";
echo 100 - (count(array_keys($shared_passwords_arr, 0)) / count($shared_passwords_arr)) * 100;
echo "<br>-----<br>";
/*foreach($shared_passwords_arr as $pass) {
    echo $pass . "<br>";
}*/

echo "<br>total users: " . count($shared_passwords_arr_capital_special) . "<br>";
echo "median" . median($shared_passwords_arr_capital_special) . "<br>";
echo "mean " . average($shared_passwords_arr_capital_special) . "<br>";
echo 100 - (count(array_keys($shared_passwords_arr_capital_special, 0)) / count($shared_passwords_arr_capital_special)) * 100;
echo "<br>-----<br>";
/*foreach($shared_passwords_arr_capital_special as $pass) {
    echo $pass . "<br>";
}*/

echo "total users: " . count($shared_real_passwords_arr) . "<br>";
echo "median" . median($shared_real_passwords_arr) . "<br>";
echo "mean " . average($shared_real_passwords_arr) . "<br>";
echo 100 - (count(array_keys($shared_real_passwords_arr, 0)) / count($shared_real_passwords_arr)) * 100;
echo "<br>-----<br>";
/*foreach($shared_real_passwords_arr as $pass) {
    echo $pass . "<br>";
}*/

echo "<br>total users: " . count($shared_real_passwords_arr_capital_special) . "<br>";
echo "median" . median($shared_real_passwords_arr_capital_special) . "<br>";
echo "mean " . average($shared_real_passwords_arr_capital_special) . "<br>";
echo 100 - (count(array_keys($shared_real_passwords_arr_capital_special, 0)) / count($shared_real_passwords_arr_capital_special)) * 100;
echo "<br>-----<br>";
/*foreach($shared_real_passwords_arr_capital_special as $pass) {
    echo $pass . "<br>";
}*/
/*asort($shared_real_segments_arr);
foreach ($shared_real_segments_arr as $pass) {
    echo $pass . "<br>";
}*/

/*asort($total_passwords_count_arr_all_users);
foreach($total_passwords_count_arr_all_users as $count) {
    echo $count . "<br>";
}*/
/*foreach($real_important_password_usage_non_important_arr as $count) {
    echo $count . "<br>";
}*/

//asort($sites_unique_passwords_arr);
foreach($real_imp_sites_unique_passwords_arr as $count) {
    echo $count . "<br>";
}
echo "-----<br>";
//asort($sites_not_unique_passwords_arr);
foreach($real_imp_sites_not_unique_passwords_arr as $count) {
    echo $count . "<br>";
}
//print_r($passwords_important_capital_special);
//print_r($passwords_important_capital_special);