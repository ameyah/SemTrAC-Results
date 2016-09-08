<?php

   $HOST="localhost";
   $WWW='leps';
   $PASS='leps12341';
   $DB='passwords';

   $mysqli = new mysqli($HOST, $WWW, $PASS, $DB);

  if (mysqli_connect_errno()) {
         error("Cannot connect to database!");
  }
   
   $iq1="select * from transformed_credentials tc left join user_websites uw on tc.user_website_id=uw.user_website_id where pwset_id>=213";
   $iq2="select * from transformed_credentials tc left join user_websites uw on tc.user_website_id=uw.user_website_id where pwset_id>=213 and auth_status=1";
   $iq3="select * from transformed_credentials tc left join user_websites uw on tc.user_website_id=uw.user_website_id where pwset_id>=213 and uw.website_probability=1 and auth_status=1";
   $iq4="select * from transformed_credentials tc left join user_websites uw on tc.user_website_id=uw.user_website_id where pwset_id>=213 and uw.website_probability=0 and auth_status=1";

   foreach (array($iq1, $iq2, $iq3, $iq4) as $iq)
   {  
    if ($iq == $iq1)
      print "<H1>Attempted logins</H1>";
    elseif($iq == $iq2)
      print "<H1>Successful logins</H1>";
    elseif($iq == $iq3)
      print "<H1>Important logins</H1>";
    elseif($iq == $iq4)
      print "<H1>Non-important logins</H1>";
    $plen=0;
    $pst=0;
    $result = $mysqli->query($iq);

    $max = $result->num_rows;
    echo "<TABLE><TR><TH>Attempt</TH><TH>Password length</TH><TH>Password strength</TH></TR>";
    for ($row_no = $max - 1; $row_no >= 0; $row_no--) {  
	$result->data_seek($row_no);
	$row = $result->fetch_assoc();
	$i = $max - $row_no;
	#echo "<TR><TD>$i</TD><TD>" . $row['password_length'] . "</TD><TD>" . $row['password_strength']. "</TD></TR>";
	$plen += $row['password_length'];
	$pst += $row['password_strength'];
    }
    $avg = round($plen/$max,2);
    $st = round($pst/$max,2);

    print "<TR><TD bgcolor='0xffffff'>$max</TD><TD bgcolor='0xffffff'>$avg</TD><TD bgcolor='0xffffff'>$st</TD></TR></TABLE>";
  }
?>
