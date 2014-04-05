<?php
# ------------------------------------------------
# License and copyright:
# See license.txt for license information.
# ------------------------------------------------

include_once('config.php');

# ---------------------------------------------------------------------------------

function Run_UserQuery($query) {
  global $DB_ResponderID, $DB_ResponderName, $DB_OwnerEmail;
  global $DB_OwnerName, $DB_ReplyToEmail, $DB_MsgList, $DB_LastActivity;
  global $DB_result, $DB_LinkID, $DB_ResponderDesc, $DB_RespEnabled;
  global $Responder_ID, $action, $SearchCount;
  global $Search_EmailAddress, $Subscriber_ID, $SubsPerPage;
  global $DB_FirstName, $DB_LastName, $DB_IPaddy, $DB_Real_TimeJoined;
  global $siteURL, $ResponderDirectory;

  if ($SubsPerPage != 0) {
       $Limitedquery = $query." LIMIT $SearchCount, $SubsPerPage";
  } else { $Limitedquery = $query; }

  $DB_MaxList_result = mysql_query($query) or die("Invalid query: " . mysql_error());
  $DB_search_result = mysql_query($Limitedquery) or die("Invalid query: " . mysql_error());
  $Max_Results_Count = mysql_num_rows($DB_MaxList_result) - 1;

  if (mysql_num_rows($DB_search_result) > 0) {
    # User top template
    $alt = FALSE;
    include('templates/listuser_top.admin.php');

    # Display the rows
    while ($search_query_result = mysql_fetch_assoc($DB_search_result)) {
        $DB_SubscriberID    = $search_query_result['SubscriberID'];
        $DB_ResponderID     = $search_query_result['ResponderID'];
        $DB_SentMsgs        = $search_query_result['SentMsgs'];
        $DB_EmailAddress    = $search_query_result['EmailAddress'];
        $DB_TimeJoined      = $search_query_result['TimeJoined'];
        $DB_Real_TimeJoined = $search_query_result['Real_TimeJoined'];
        $CanReceiveHTML     = $search_query_result['CanReceiveHTML'];
        $DB_LastActivity    = $search_query_result['LastActivity'];
        $DB_FirstName       = $search_query_result['FirstName'];
        $DB_LastName        = $search_query_result['LastName'];
        $DB_IPaddy          = $search_query_result['IP_Addy'];
        $DB_ReferralSource  = $search_query_result['ReferralSource'];
        $DB_UniqueCode      = $search_query_result['UniqueCode'];
        $DB_Confirmed       = $search_query_result['Confirmed'];

        $Responder_ID = $DB_ResponderID;
        GetResponderInfo();

        # User row template
        $alt = (!($alt));
        include('templates/listuser_row.admin.php');
    }

    # List bottom template
    include('templates/listuser_bottom.admin.php');

    if ($SubsPerPage != 0) {
      $Search_Count_BackStr = $SearchCount - $SubsPerPage;
      $Search_Count_ForwardStr = $SearchCount + $SubsPerPage;
      if ($Search_Count_BackStr < 0) { $Search_Count_BackStr = 0; }
      if ($Search_Count_ForwardStr > $Max_Results_Count) { $Search_Count_ForwardStr = $Max_Results_Count; }

       # Back and forward buttons
       include('templates/back_forward.admin.php');
    }
 
    # Add new user button
    include('templates/addnew_button.admin.php');
  }
  else {
         print "<br><font size=\"4\" color=\"#330000\">User(s) NOT found.</font><br>\n";
         print "<br>\n";
  }

  # Back button
  print "<br> \n";
  print "<font size=\"4\" color=\"#000066\">Back to Main:</font><br>\n";
  $return_action = "list";
  include('templates/back_button.admin.php');
}

# ---------------------------------------------------------------------------------

# Redirect to config?
if (($config_row_inserted == TRUE) || ($config['admin_pass'] == "")) {
     include('edit_config.php');
     die();
}

# More config stuff
$Add_List_Size = $config['add_sub_size'];
$SubsPerPage   = $config['subs_per_page'];

# Init vars
$action              = MakeSafe($_REQUEST['action']);
$Responder_ID        = MakeSafe($_REQUEST['r_ID']);
$Search_EmailAddress = MakeSafe($_REQUEST['email_addy']);
$Subscriber_ID       = MakeSafe($_REQUEST['sub_ID']);
$HandleHTML          = MakeSafe($_REQUEST['h']);
$SearchCount         = MakeSafe($_REQUEST['Search_Count']);
$FirstName           = MakeSafe($_REQUEST['firstname']);
$LastName            = MakeSafe($_REQUEST['lastname']);

# Bounds check
if ($HandleHTML != 1) {
   $HandleHTML = 0; 
}
if (!(is_numeric($Responder_ID))) {
   # A small bit of magic to filter out any screwy crackerness of the RespID
   $Responder_ID = NULL;
}
if (!(is_numeric($SearchCount))) {
   # Ditto
   $SearchCount = 0;
}

# Are we authed?
if ($Is_Auth = User_Auth()) {
  # Template top
  include('templates/open.page.php');

  if ($action == "edit_users") {
    # Panel top
    $help_section = "editusers";
    include('templates/controlpanel.php');

    $DBquery = "SELECT * FROM InfResp_subscribers WHERE ResponderID = '$Responder_ID' ORDER BY EmailAddress";
    Run_UserQuery($DBquery);
  }
  elseif ($action == "Email_Search") {
    # Panel top
    $help_section = "editusers";
    include('templates/controlpanel.php');

    $SubsPerPage = 0;
    if (($Search_EmailAddress == NULL) OR ($Search_EmailAddress == "")) { $Search_EmailAddress = '*'; }
   
    $DBquery = "SELECT * FROM InfResp_subscribers WHERE EmailAddress LIKE '%$Search_EmailAddress%' ORDER BY EmailAddress";
    Run_UserQuery($DBquery);
  }
  elseif ($action == "Form_Gen") {
     # Template
     include('templates/form_gen.admin.php');

     # Back button
     print "<br> \n";
     $return_action = "list";
     include('templates/back_button.admin.php');
  }
  elseif ($action == "sub_addnew") {

      # ResponderPulldown('r_ID');
      # -> Add a new user(s).  - Email - HTML Y/N. Pull down menu for responders.
      #     - Bulk add. Comma spliced. Universal HTML Y/N.

      # Top Template
      include('templates/adduser_top.admin.php');

      for ($i=1; $i<=$Add_List_Size; $i++) {
           # Row Template
           include('templates/adduser_row.admin.php');
      }

      # Top Template
      include('templates/adduser_bottom.admin.php');

      # Back button
      print "<br> \n";
      $return_action = "list";
      include('templates/back_button.admin.php');
  }
  elseif ($action == "sub_edit") {

    GetResponderInfo();
    GetSubscriberInfo($Subscriber_ID);

    $DB_SentMsgs = trim(trim($DB_SentMsgs), ",");
    $SentList_Array=explode(',',$DB_SentMsgs);
    $Max_Index = sizeof($SentList_Array);

    # Explode likes to treat NULL as an element. :/
    if (trim($DB_SentMsgs) == NULL) { $Max_Index = 0; }
    if ($DB_SentMsgs == "") { $Max_Index = 0; }

    # Build option list
    $option_list = "";
    for ($i=0; $i<=$Max_Index-1; $i++) {
       GetMsgInfo(trim($SentList_Array[$i]));
       $option_list .= "     <option value=\"$DB_MsgID\">$DB_MsgSub</option>\n";
    }

    # Template
    include('templates/sub_edit.admin.php');

    # Back button
    print "<br> \n";
    $return_action = "edit_users";
    include('templates/back_button.admin.php');
  }
  elseif ($action == "sub_delete") {
    GetSubscriberInfo($Subscriber_ID);
    $Responder_ID = $DB_ResponderID;
    GetResponderInfo();
    $JoinedStr = date("F j, Y, g:i a", $DB_TimeJoined);
    $LastActStr = date("F j, Y, g:i a", $DB_LastActivity);
    if ($CanReceiveHTML == 1) { $HTMLstr = "Yes"; } else { $HTMLstr = "No"; }

    # Template
    include('templates/sub_delete.admin.php');
  }
  elseif ($action == "sub_addnew_do") {

    $Resp_Cached = $Responder_ID;

    for ($i=1; $i<=$Add_List_Size; $i++) {

        print "<br>\n";

        $Blank = "";
        $SH_VAR = "send_html".$i;
        $AR_VAR = "chosen_resp".$i;
        $EA_VAR = "add_email".$i;
        $FN_VAR = "firstname".$i;
        $LN_VAR = "lastname".$i;

        $SendHTML[$i]       = MakeSafe($_REQUEST["$SH_VAR"]);
        $AddToResp[$i]      = MakeSafe($_REQUEST["$AR_VAR"]);
        $EmailToAdd[$i]     = MakeSafe($_REQUEST["$EA_VAR"]);
        $FirstNameArray[$i] = MakeSafe($_REQUEST["$FN_VAR"]);
        $LastNameArray[$i]  = MakeSafe($_REQUEST["$LN_VAR"]);

        $Responder_ID = $AddToResp[$i];
        $Email_Address = $EmailToAdd[$i];

        if (UserIsSubscribed()) {
          print "<strong>Duplicate address!</strong> Not Added: $Email_Address <br>\n";
        }
        else {
          if (($EmailToAdd[$i] != "") AND ($EmailToAdd[$i] != NULL) AND (!(isInBlacklist($EmailToAdd[$i])))) {
             $uniq_code = generate_unique_code();
             $Timestamper = time();
             $query = "INSERT INTO InfResp_subscribers (ResponderID, SentMsgs, EmailAddress, TimeJoined, Real_TimeJoined, CanReceiveHTML, LastActivity, FirstName, LastName, IP_Addy, ReferralSource, UniqueCode, Confirmed)
                       VALUES('$AddToResp[$i]','$Blank', '$EmailToAdd[$i]', '$Timestamper', '$Timestamper', '$SendHTML[$i]', '$Timestamper', '$FirstNameArray[$i]', '$LastNameArray[$i]', 'Added Manually', 'Manual Add', '$uniq_code', '1')";
             $DB_result = mysql_query($query) or die("Invalid query: " . mysql_error());
             print "<strong>Added: $Email_Address </strong><br>\n";
          }
        }
    }
    $Responder_ID = $Resp_Cached;

    # Back button
    $return_action = "list";
    include('templates/back_button.admin.php');
  }
  elseif ($action == "sub_edit_do") {

    $Resend_Msg = MakeSafe($_REQUEST['msg_to_resend']);
    $Reset_Time = MakeSafe($_REQUEST['Reset_Time']);
    $Ref_Src    = MakeSafe($_REQUEST['ReferralSource']);
    $UniqueCode = MakeSafe($_REQUEST['UniqueCode']);
    $Confirmed  = MakeSafe($_REQUEST['Confirmed']);

    GetSubscriberInfo($Subscriber_ID);

    if (($Resend_Msg != "") AND ($Resend_Msg != NULL) AND ($Resend_Msg != "none") AND ($Resend_Msg != "all")) {
        $DB_SentMsgs = RemoveFromList($DB_SentMsgs, $Resend_Msg);
    }
    if ($Confirmed != "1") {
        $Confirmed = "0";
    }

    if ($Resend_Msg == "all") { $DB_SentMsgs = ""; }
    if ($Reset_Time == "yes") {
        $DB_TimeJoined = time();
        $DB_SentMsgs = "";
    }

     $Set_LastActivity = time();
     $query = "UPDATE InfResp_subscribers
               SET SentMsgs       = '$DB_SentMsgs',
                   EmailAddress   = '$Search_EmailAddress',
                   TimeJoined     = '$DB_TimeJoined',
                   CanReceiveHTML = '$HandleHTML',
                   LastActivity   = '$Set_LastActivity',
                   FirstName      = '$FirstName',
                   LastName       = '$LastName',
                   ReferralSource = '$Ref_Src',
                   UniqueCode     = '$UniqueCode',
                   Confirmed      = '$Confirmed'
               WHERE SubscriberID = '$Subscriber_ID'";
     $DB_result = mysql_query($query) or die("Invalid query: " . mysql_error());

     $FullName = "$FirstName $LastName";
     $query = "UPDATE InfResp_customfields 
                  SET email_attached = '$Search_EmailAddress',
                      full_name = '$FullName'
                WHERE user_attached = '$Subscriber_ID'";
     $result = mysql_query($query)
               or die("Invalid query: " . mysql_error());

    # Done!
    print "<H3 style=\"color : #003300\">Subscriber Saved!</H3> \n";

    # Back button
    print "<br> \n";
    $return_action = "edit_users";
    include('templates/back_button.admin.php');
  }
  elseif ($action == "sub_delete_do") {

      $query = "DELETE FROM InfResp_subscribers WHERE SubscriberID = '$Subscriber_ID'";
      $DB_result = mysql_query($query)
           or die("Invalid query: " . mysql_error());

      $query = "DELETE FROM InfResp_customfields WHERE user_attached = '$Subscriber_ID'";
      $result = mysql_query($query)
                or die("Invalid query: " . mysql_error());

    # Done!
    print "<br> \n";
    print "<font size=\"4\" color=\"#660000\">Subscriber Deleted!</font><br>\n";

    # Back button
    print "<br> \n";
    $return_action = "edit_users";
    include('templates/back_button.admin.php');
  }
  elseif ($action == "bulk_add") {
    # Template
    include('templates/bulk_add.admin.php');

    # Back button
    print "<br> \n";
    $return_action = "list";
    include('templates/back_button.admin.php');
  }
  elseif ($action == "bulk_add_do") {

    $file_name = $_FILES['load_file']['tmp_name'];
    $file_size = $_FILES['load_file']['size'];
    $file_text = "";
    $file_handle = fopen($file_name, "r");
    if (($file_handle != "") AND ($file_handle != NULL)) {
     while (!feof($file_handle)) {
       $file_buffer = fgets($file_handle, $file_size);
       $file_text = $file_text.$file_buffer;
     }
     fclose ($file_handle);
    }

    $file_text = str_replace(' ', '', $file_text);
    $file_text = stripnl($file_text);
    $file_text = trim(trim($file_text), ",");
    $file_text = MakeSafe($file_text);
    $Comma_List = $_REQUEST['comma_list'];
    $Comma_List = str_replace(' ', '', $Comma_List);
    $Comma_List = stripnl($Comma_List);
    $Comma_List = trim(trim($Comma_List), ",");
    $Comma_List = MakeSafe($Comma_List);
    $Complete_List = $file_text.",".$Comma_List;

    $AddList_Array=explode(',',$Complete_List);
    $List_Max = sizeof($AddList_Array);

    # Explode likes to treat NULL as an element. :/
    if (trim($AddList_Array) == NULL) { $List_Max = 0; }
    if ($AddList_Array == "") { $List_Max = 0; }

    $Blank = "";

    for ($i=0; $i<=$List_Max-1; $i++) {
      $Email_Address = $AddList_Array[$i];
      if (UserIsSubscribed()) {
         print "<strong>Duplicate address!</strong> Not Added: $Email_Address <br>\n";
      } 
      else {
        if (($Email_Address != "") AND ($Email_Address != NULL) AND (!(isInBlacklist($Email_Address)))) {
           $Timestamper = time();
           $uniq_code = generate_unique_code();
           $query = "INSERT INTO InfResp_subscribers (ResponderID, SentMsgs, EmailAddress, TimeJoined, Real_TimeJoined, CanReceiveHTML, LastActivity, FirstName, LastName, IP_Addy, ReferralSource, UniqueCode, Confirmed)
                     VALUES('$Responder_ID','$Blank', '$Email_Address', '$Timestamper', '$Timestamper', '$HandleHTML', '$Timestamper', '$Blank', '$Blank', '$Blank', 'Bulk Add', '$uniq_code', '1')";
           $DB_result = mysql_query($query) or die("Invalid query: " . mysql_error());
           print "Added: $Email_Address <br>\n";
        }
      }
    }

    # Back button
    print "<br> \n";
    $return_action = "list";
    include('templates/back_button.admin.php');
  }
  elseif  ($action == "list_export") {

  }
  elseif  ($action == "list_export_do") {

  }
  elseif  ($action == "configure") {


     # Back button
     print "<br> \n";
     $return_action = "list";
     include('templates/back_button.admin.php');
  }
  elseif ($action == "configure_do") {


     # Back button
     print "<br> \n";
     $return_action = "list";
     include('templates/back_button.admin.php');
  }
  elseif ($action == "custom_edit") {
      $query = "SELECT * FROM InfResp_customfields WHERE user_attached = '$Subscriber_ID' OR (resp_attached = '$Responder_ID' AND email_attached = '$Search_EmailAddress')";
      $result = mysql_query($query)
              or die("Invalid query: " . mysql_error());

      if (mysql_num_rows($result) < 1) {
              $query = "INSERT INTO InfResp_customfields (user_attached, resp_attached, email_attached) VALUES('$Subscriber_ID','$Responder_ID','$Search_EmailAddress')";
              $DB_result = mysql_query($query)
                  or die("Invalid query: " . mysql_error());
      }

      $CustomFieldsArray = GetFieldNames('InfResp_customfields');
      $query = "SELECT * FROM InfResp_customfields WHERE user_attached = '$Subscriber_ID' OR (resp_attached = '$Responder_ID' AND email_attached = '$Search_EmailAddress') LIMIT 1";
      $result = mysql_query($query)
                or die("Invalid query: " . mysql_error());
      
      $DBarray = mysql_fetch_assoc($result);

      foreach ($CustomFieldsArray as $key => $value) {
         if (empty($DBarray[$value])) { $DBarray[$value] = ""; }
      }

      $display_it = TRUE;
      include('templates/customedit.admin.php');

      # Back button
      print "<br> \n";
      $return_action = "sub_edit";
      include('templates/back_button.admin.php');
  }
  elseif ($action == "custom_edit_do") {
      # Get the fields
      $CustomFieldsArray = GetFieldNames('InfResp_customfields');
      foreach ($CustomFieldsArray as $key => $value) {
          $blah = "cf_".$value;
          $reqblah = trim($_REQUEST[$blah]);
          if (!(Empty($reqblah))) {
              $DBarray[$value] = MakeSafe($reqblah);
          }
      }

      # Set static info
      $DBarray['user_attached']  = $Subscriber_ID;
      $DBarray['resp_attached']  = $Responder_ID;
      $DBarray['email_attached'] = $Search_EmailAddress;

      # Update the data
      if (is_numeric($Subscriber_ID)) {
           $where = "user_attached = '$Subscriber_ID'";
      }
      else {
           $where = "resp_attached = '$Responder_ID' AND email_attached = '$Search_EmailAddress'";
      }
      DB_Update_Array('InfResp_customfields', $DBarray, $where);

      # Done!
      print "<br> \n";
      print "<font size=\"4\" color=\"#660000\">Custom fields changed!</font><br>\n";

      # Back button
      print "<br> \n";
      $return_action = "edit_users";
      include('templates/back_button.admin.php');
  }
  elseif ($action == "custom_codeit") {
    # Code-it template
    $display_it = TRUE;
    include ('templates/custom_codeit.admin.php');

    # Back button
    print "<br> \n";
    $return_action = "Form_Gen";
    include('templates/back_button.admin.php');
  }
  else {
    # Panel top
    $help_section = "mainscreen";
    include('templates/controlpanel.php');

    # Email search button template
    include('templates/email_search.admin.php');

    # Query it
    $query = "SELECT * FROM InfResp_responders ORDER BY ResponderID";
    $DB_result = mysql_query($query) or die("Invalid query: " . mysql_error());

    if (mysql_num_rows($DB_result) > 0) {
      # List top template
      $alt = FALSE;
      include('templates/resplist_top.admin.php');

      $i = 0;
      while ($query_result = mysql_fetch_assoc($DB_result)) {
         $DB_ResponderID   = $query_result['ResponderID'];
         $DB_RespEnabled   = $query_result['Enabled'];
         $DB_ResponderName = $query_result['Name'];
         $DB_ResponderDesc = $query_result['ResponderDesc'];
         $DB_OwnerEmail    = $query_result['OwnerEmail'];
         $DB_OwnerName     = $query_result['OwnerName'];
         $DB_ReplyToEmail  = $query_result['ReplyToEmail'];
         $DB_MsgList       = $query_result['MsgList'];
         $DB_OptMethod     = $query_result['OptMethod'];
         $DB_OptInRedir    = $query_result['OptInRedir'];
         $DB_OptOutRedir   = $query_result['OptOutRedir'];
         $DB_OptInDisplay  = $query_result['OptInDisplay'];
	 $DB_OptInDisplayRedir = $query_result['OptInDisplayRedir'];
         $DB_OptOutDisplay = $query_result['OptOutDisplay'];
         $DB_NotifyOnSub   = $query_result['NotifyOwnerOnSub'];

         $Count_query = "SELECT * FROM InfResp_subscribers WHERE ResponderID = '$DB_ResponderID'";
         $DB_Count_result = mysql_query($Count_query) or die("Invalid query: " . mysql_error());
         $User_Count = mysql_num_rows($DB_Count_result);

         # List row template
         $alt = (!($alt));
         include('templates/resplist_row.admin.php');
      }

      # List bottom template
      include('templates/resplist_bottom.admin.php');
    } 
    else {
         print "<H2>No responders were found!</H2>\n";
         print "Click \"Edit Resps\" then \"Add New\" to create one.<br>\n";
    }
  }
}
else {
  if ($action == "do_login") {
     # Reset the user session
     reset_user_session();

     # Was it good or no?
     $l = trim($_REQUEST['login']);
     $p = trim($_REQUEST['pword']);
     if (($l == $config['admin_user']) AND ($p == $config['admin_pass'])) {
          # Reset our randoms
          $now = time();
          $str1 = generate_random_block();
          $str2 = generate_random_block();
          $query = "UPDATE InfResp_config
                    SET random_timestamp = '$now',
                    random_str_1 = '$str1', 
                    random_str_2 = '$str2'";
          $DB_result = mysql_query($query) or die("Invalid query: " . mysql_error());
          $config['random_timestamp'] = $now;
          $config['random_str_1'] = $str1;
          $config['random_str_2'] = $str2;

          # Init our session data
          $_SESSION['initialized'] = TRUE;
          $_SESSION['timestamp'] = time();
          $_SESSION['last_IP'] = $_SERVER['REMOTE_ADDR'];
          $_SESSION['l'] = md5(WebEncrypt($l, $config['random_str_1']));
          $_SESSION['p'] = md5(WebEncrypt($p, $config['random_str_2']));

          # Redirect
          $redir_URL = $siteURL.$ResponderDirectory.'/admin.php?action=list';
          header("Location: $redir_URL");
          print "<br>\n";
          print "If your browser doesn't support redirects then you'll need to <A HREF=\"$redir_URL\">click here.</A><br>\n";
          print "<br>\n";
          die();
     }
     else {
          # Template top
          include('templates/open.page.php');

          print "<br />\n";
          if (($_REQUEST['login'] != "") && ($_REQUEST['pword'] != "")) {
               print "<p class=\"err_msg\">Error: Invalid Login/Password.</p><br />\n";
          }

          # ------ Admin login panel -------
          include('templates/login.admin.php');
     }
  }
  else {
     # Template top
     include('templates/open.page.php');

     print "<br />\n";
     if (($_REQUEST['login'] != "") && ($_REQUEST['pword'] != "")) {
         print "<p class=\"err_msg\">Error: Invalid Login/Password.</p><br />\n";
     }

     # ------ Admin login panel -------
     include('templates/login.admin.php');
  }
}

# Template bottom
copyright();
include('templates/close.page.php');

DB_disconnect();
?>
