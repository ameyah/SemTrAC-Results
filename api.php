<?php

include('conf.php');
include('utils.php');
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');

if(isset($_GET['getstats'])) {
    $result_obj = Array();

    // Get participants count
    $query = "SELECT (SELECT COUNT(*) FROM password_set) as participant_count, (SELECT COUNT(*) FROM websites)
              as website_count, (SELECT COUNT(*) FROM transformed_credentials) as credential_count FROM dual";
    $result = mysqli_query($dbc, $query);
    $response = mysqli_fetch_row($result);
    $result_obj['participants'] = intval($response[0]);
    $result_obj['websites'] = intval($response[1]);
    $result_obj['credentials'] = intval($response[2]);

    echo json_encode(utf8ize($result_obj));
}

if(isset($_GET['getparticipants'])) {
    $result_obj = Array();

    $query = "SELECT password_set.pwset_id, coalesce(websites_cnt.cnt, 0) AS websites_cnt, (SELECT
              COUNT(DISTINCT transformed_credentials.user_website_id) FROM transformed_credentials WHERE
              transformed_credentials.user_website_id IN (SELECT user_websites.user_website_id FROM user_websites
              WHERE user_websites.pwset_id = password_set.pwset_id)) AS websites_logged_in, (SELECT COUNT(*) FROM
              transformed_credentials WHERE transformed_credentials.user_website_id IN (SELECT user_websites.user_website_id
              FROM user_websites WHERE user_websites.pwset_id = password_set.pwset_id)) AS total_tries, (SELECT
              COUNT(DISTINCT password_text) FROM transformed_credentials WHERE transformed_credentials.user_website_id IN
              (SELECT user_websites.user_website_id FROM user_websites WHERE user_websites.pwset_id = password_set.pwset_id))
              AS unique_credentials FROM password_set LEFT OUTER JOIN (SELECT user_websites.pwset_id, COUNT(*) cnt FROM
              user_websites GROUP BY user_websites.pwset_id) websites_cnt ON password_set.pwset_id = websites_cnt.pwset_id";
    $result = mysqli_query($dbc, $query);
    while($response_row = mysqli_fetch_array($result)) {
        $temp_result = Array(
            'participant_id' => intval($response_row['pwset_id']),
            'total_websites' => intval($response_row['websites_cnt']),
            'websites_logged_in' => intval($response_row['websites_logged_in']),
            'total_tries' => intval($response_row['total_tries']),
            'unique_credentials' => intval($response_row['unique_credentials'])
        );
        array_push($result_obj, $temp_result);
    }
    echo json_encode(utf8ize($result_obj));
}

if(isset($_GET['participant-info'])) {
    $result_obj = Array();
    $pwset_id = mysqli_real_escape_string($dbc, $_GET['participant-info']);
    preg_match_all('/\d+/', $pwset_id, $matches);
    $pwset_id = $matches[0][0];
    $query = "SELECT (SELECT website_text FROM websites WHERE website_id = user_websites.website_id) as website_text,
            user_websites.website_probability, user_websites.password_reset_count, transformed_credentials.transformed_cred_id,
            transformed_credentials.username_text, transformed_credentials.password_text, grammar.grammar_text,
            transformed_credentials.password_strength, transformed_credentials.auth_status FROM user_websites INNER JOIN
            transformed_credentials ON user_websites.user_website_id = transformed_credentials.user_website_id JOIN grammar
            ON transformed_credentials.password_grammar_id = grammar.grammar_id WHERE user_websites.pwset_id = ".
            trim($pwset_id) . " ORDER BY user_websites.website_id";
    $result = mysqli_query($dbc, $query);
    while($response_row = mysqli_fetch_array($result)) {
        if($response_row['username_text'] == null) {
            $response_row['username_text'] = "";
        }
        $temp_result = Array(
            'website_text' => $response_row['website_text'],
            'website_probability' => intval($response_row['website_probability']),
            'password_reset_count' => intval($response_row['password_reset_count']),
            'username_text' => $response_row['username_text'],
            'password_text' => $response_row['password_text'],
            'password_strength' => $response_row['password_strength'],
            'password_segments' => Array(),
            'auth_status' => intval($response_row['auth_status'])
        );
        // Extract grammar and store individual segment grammar in array
        preg_match_all('/\([a-z]+[0-9]*\)/i', $response_row['grammar_text'], $grammar_arr);
        for($i = 0; $i < count($grammar_arr); $i++) {
            $grammar_arr[$i] = preg_replace("/[^a-zA-Z0-9]+/", "", $grammar_arr[$i]);
        }
        // Get transformed segments from transformed_cred_id
        $get_segments_query = "SELECT segment, capital, special FROM transformed_segments WHERE transformed_cred_id = "
            .trim($response_row['transformed_cred_id']);
        $result_segments = mysqli_query($dbc, $get_segments_query);
        $segment_grammar_count = 0;
        while($segment_row = mysqli_fetch_array($result_segments)) {
            $segment_info = Array(
                'segment' => $segment_row['segment'],
                'grammar' => $grammar_arr[0][$segment_grammar_count],
                'capital' => $segment_row['capital'],
                'special' => $segment_row['special']
            );
            array_push($temp_result['password_segments'], $segment_info);
            $segment_grammar_count++;
        }
        array_push($result_obj, $temp_result);
    }

    echo json_encode(utf8ize($result_obj));
}


if(isset($_GET['get-pre-study'])) {
    $result_obj = Array();
    $pwset_id = mysqli_real_escape_string($dbc, $_GET['get-pre-study']);
    preg_match_all('/\d+/', $pwset_id, $matches);
    $pwset_id = $matches[0][0];
    $query = "SELECT study_questions.question as question, study_responses.response_sub as response FROM study_responses
              INNER JOIN study_questions ON study_responses.question_id = study_questions.question_id WHERE study_responses.pwset_id
              =". trim($pwset_id) . " AND study_questions.type='PRE' ORDER BY study_responses.question_id";
    $result = mysqli_query($dbc, $query);
    while($response_row = mysqli_fetch_array($result)) {
        $temp_result = Array(
            'question' => $response_row['question'],
            'response' => $response_row['response']
        );
        array_push($result_obj, $temp_result);
    }

    echo json_encode(utf8ize($result_obj));
}

if(isset($_GET['get-current-practice'])) {
    $result_obj = Array();
    $pwset_id = mysqli_real_escape_string($dbc, $_GET['get-current-practice']);
    preg_match_all('/\d+/', $pwset_id, $matches);
    $pwset_id = $matches[0][0];
    $query = "SELECT study_questions.question as question, study_responses.response_obj as response FROM study_responses
              INNER JOIN study_questions ON study_responses.question_id = study_questions.question_id WHERE study_responses.pwset_id
              =". trim($pwset_id) . " AND study_questions.type='CURRENT' ORDER BY study_responses.question_id";
    $result = mysqli_query($dbc, $query);
    while($response_row = mysqli_fetch_array($result)) {
        $temp_result = Array(
            'question' => $response_row['question'],
            'response' => intval($response_row['response'])
        );
        array_push($result_obj, $temp_result);
    }

    echo json_encode(utf8ize($result_obj));
}

if(isset($_GET['get-risk-perception'])) {
    $result_obj = Array();
    $pwset_id = mysqli_real_escape_string($dbc, $_GET['get-risk-perception']);
    preg_match_all('/\d+/', $pwset_id, $matches);
    $pwset_id = $matches[0][0];
    $query = "SELECT study_questions.question as question, study_responses.response_obj as response FROM study_responses
              INNER JOIN study_questions ON study_responses.question_id = study_questions.question_id WHERE study_responses.pwset_id
              =". trim($pwset_id) . " AND study_questions.type='RISK' ORDER BY study_responses.question_id";
    $result = mysqli_query($dbc, $query);
    while($response_row = mysqli_fetch_array($result)) {
        $temp_result = Array(
            'question' => $response_row['question'],
            'response' => intval($response_row['response'])
        );
        array_push($result_obj, $temp_result);
    }

    echo json_encode(utf8ize($result_obj));
}

if(isset($_GET['get-post-study'])) {
    $result_obj = Array();
    $pwset_id = mysqli_real_escape_string($dbc, $_GET['get-post-study']);
    preg_match_all('/\d+/', $pwset_id, $matches);
    $pwset_id = $matches[0][0];
    $query = "SELECT (SELECT website_text FROM websites WHERE website_id = study_responses.website_id) AS website,
              (SELECT website_probability FROM user_websites WHERE pwset_id = ". trim($pwset_id) . " AND website_id =
              study_responses.website_id) AS website_importance, (SELECT password_reset_count FROM user_websites WHERE
              pwset_id = ". trim($pwset_id) . " AND website_id = study_responses.website_id) AS reset_count,
              study_questions.question as question, study_responses.response_obj as response FROM study_responses
              INNER JOIN study_questions ON study_responses.question_id = study_questions.question_id WHERE study_responses.pwset_id
              =". trim($pwset_id) . " AND study_questions.type='POST' ORDER BY study_responses.website_id";
    $result = mysqli_query($dbc, $query);
    $previous_website = "";
    $temp_result = Array();
    while($response_row = mysqli_fetch_array($result)) {
        if($response_row['website'] != $previous_website) {
            if(!empty($temp_result)) {
                array_push($result_obj, $temp_result);
            }
            $temp_result = Array(
                $response_row['website'] => Array(
                    'website_importance' => intval($response_row['website_importance']),
                    'reset_count' => intval($response_row['reset_count']),
                    'questionnaire' => Array(
                        Array(
                            'question' => $response_row['question'],
                            'response' => intval($response_row['response'])
                        )
                    )
                )
            );
            $previous_website = $response_row['website'];
        } else {
            array_push($temp_result[$previous_website]['questionnaire'],
                Array('question' => $response_row['question'],
                    'response' => intval($response_row['response'])));
        }
    }
    // Push the final temp_result of the last iteration
    if(!empty($temp_result)) {
        array_push($result_obj, $temp_result);
    }

    echo json_encode(utf8ize($result_obj));
}

if(isset($_GET['get-websites'])) {
    $result_obj = Array();
    $pwset_id = mysqli_real_escape_string($dbc, $_GET['get-websites']);
    preg_match_all('/\d+/', $pwset_id, $matches);
    $pwset_id = $matches[0][0];
    $query = "SELECT websites.website_text as website_text, user_websites.website_probability as website_important,
              user_websites.website_frequency as website_frequency, websites.probability as avg_importance,
              user_websites.date as date, (SELECT COUNT(*) FROM transformed_credentials
              WHERE user_website_id = user_websites.user_website_id) as total_tries, (SELECT COUNT(*) FROM transformed_credentials
              WHERE user_website_id = user_websites.user_website_id AND auth_status = 1) as auth_status FROM user_websites INNER JOIN websites ON
              user_websites.website_id = websites.website_id WHERE user_websites.pwset_id = ". trim($pwset_id);
    $result = mysqli_query($dbc, $query);
    while($response_row = mysqli_fetch_array($result)) {
        $temp_result = Array(
            'website_text' => $response_row['website_text'],
            'website_important' => intval($response_row['website_important'])? true: false,
            'website_frequency' => intval($response_row['website_frequency'])?true: false,
            'avg_importance' => floatval($response_row['avg_importance']),
            'date' => $response_row['date'],
            'total_tries' => intval($response_row['total_tries']),
            'auth_status' => intval($response_row['auth_status'])? true: false
        );
        array_push($result_obj, $temp_result);
    }

    echo json_encode(utf8ize($result_obj));
}

if(isset($_GET['get-discussion'])) {
    $result_obj = Array();
    $pwset_id = mysqli_real_escape_string($dbc, $_GET['get-discussion']);
    preg_match_all('/\d+/', $pwset_id, $matches);
    $pwset_id = $matches[0][0];
    $query = "SELECT study_questions.question, study_responses.response_sub FROM study_responses INNER JOIN study_questions ON
              study_responses.question_id = study_questions.question_id WHERE study_responses.pwset_id = ".trim($pwset_id) .
              " AND study_questions.type = 'DISCUSS'";
    $result = mysqli_query($dbc, $query);
    while($response_row = mysqli_fetch_array($result)) {
        $temp_result = Array(
            'question' => $response_row['question'],
            'response' => $response_row['response_sub']
        );
        array_push($result_obj, $temp_result);
    }

    echo json_encode(utf8ize($result_obj));
}
