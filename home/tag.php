<?php

$HOST = "localhost";
$WWW = 'leps';
$PASS = 'leps12341';
$DB = 'passwords';

$mysqli = new mysqli($HOST, $WWW, $PASS, $DB);

if (mysqli_connect_errno()) {
    error("Cannot connect to database!");
}


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $qid = $_POST['q'];
    if ($qid == "" || $qid < 31 || $qid > 45)
        return;
    foreach ($_POST as $key => $value) {
        if (substr($key, 0, 4) === 'code' && $value != "") {
            $id = substr($key, 4);
            // first check if row for pwset_id and question_id exists
            $query = "SELECT * FROM study_responses_code WHERE pwset_id=" . $id . " AND question_id=" . $qid;
            $result = $mysqli->query($query);
            if ($result->num_rows == 0) {
                $insert_query = "INSERT INTO study_responses_code SET code='" . $value . "', pwset_id=" . $id . ", question_id=" . $qid;
                $mysqli->query($insert_query);
            } else {
                $up = "update study_responses_code set code='" . $value . "' where pwset_id=" . $id . " and question_id=" . $qid;
                $result = $mysqli->query($up);
            }
        }
    }
    echo "<H3>Your updates have been processed. You can make more updates if you like.</H3>";
} else {

    $qid = $_GET['q'];
}

$iq = "select pwset_id, question, response_sub, (select code from study_responses_code where pwset_id = sr.pwset_id and
       question_id = sr.question_id) code from study_responses sr left join study_questions sq on sr.question_id = sq.question_id where pwset_id>=213 and sr.question_id=$qid order by pwset_id";

$result = $mysqli->query($iq);

$max = $result->num_rows;
echo "<FORM action=tag.php method=POST>";
echo "<TABLE BORDER=1><TR><TH>Id</TH><TH>Question</TH><TH>Answer</TH><TH>Code</TH></TR>";
for ($row_no = $max - 1; $row_no >= 0; $row_no--) {
    $result->data_seek($row_no);
    $row = $result->fetch_assoc();
    $id = $row['pwset_id'];
    echo "<TR><TD>" . $row['pwset_id'] . "</TD><TD>" . $row['question'] . "</TD><TD>" . $row['response_sub'] . "</TD><TD><INPUT type=text name=code" . $id . " value='" . $row['code'] . "'></TR>";
}
echo "<INPUT TYPE=hidden name=q value=" . $qid . ">";
echo "<INPUT TYPE=submit></FORM>";

?>
