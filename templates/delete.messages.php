<?php
     print "<br>\n";
     print "<font size=5 color=\"#660000\"> \n";
     print "<center>-- Confirmation Required --</center> \n";
     print "</font> <br> \n";
     print "<center><table width=\"750\" bgcolor=\"#CCCCCC\" style=\"border: 1px solid #000000;\"> \n";
     print "<tr> \n";
     print "<td valign=\"top\" width=\"200\"><font size=4 color=\"#000066\"><br>Subject:</font></td> \n";
     print "<td valign=\"top\" width=\"740\"><br><font size=4 color=\"#003300\">$DB_MsgSub</font> \n";
     print "<br> \n";
     print "<hr style = \"border: 0; background-color: #000000; color: #000000; height: 1px; width: 100%;\"> \n";
     print "<br> </td>\n";
     print "</tr> \n";

     print "<tr> \n";
     print "<td valign=\"top\" width=\"200\"><font size=4 color=\"#000066\">Body text:</font></td> \n";
     print "<td valign=\"top\" width=\"740\"><font size=4 color=\"#330033\"> ".nl2br($DB_MsgBodyText)." </font> \n";
     print "<br> \n";
     print "<hr style = \"border: 0; background-color: #000000; color: #000000; height: 1px; width: 100%;\"> \n";
     print "<br> </td>\n";
     print "</tr> \n";

     print "<tr> \n";
     print "<td valign=\"top\" width=\"200\"><font size=4 color=\"#000066\">Body HTML:</font></td> \n";
     print "<td valign=\"top\" width=\"740\"><font size=4 color=\"#003300\"> ".nl2br(htmlentities($DB_MsgBodyHTML))." </font> \n";
     print "<br> \n";
     print "<hr style = \"border: 0; background-color: #000000; color: #000000; height: 1px; width: 100%;\"> \n";
     print "<br> </td>\n";
     print "</tr> \n";

     print "<tr> \n";
     print "<td valign=\"top\" width=\"200\"><font size=4 color=\"#000066\">Run after:</font></td> \n";
     print "<td valign=\"top\" width=\"740\"><font size=4 color=\"#330033\"> \n";
         print "$T_months months, ";
         print "$T_weeks weeks, ";
         print "$T_days days, ";
         print "$T_hours hours, ";
         print "$T_minutes minutes. (military time)\n";
     print "</font></td> \n";
     print "</tr> \n";
     print "<tr> \n";
     print "<td valign=\"top\" width=\"200\"><font size=4 color=\"#000066\">Run on: (Optional)</font></td> \n";
     print "<td valign=\"top\" width=\"740\"><font size=4 color=\"#330033\"> \n";
         print "$DB_absDay ";
         print "$DB_absHours hours : ";
         print "$DB_absMins mins.<br>\n";
     print "</font></td> \n";
     print "</tr> \n";
     print "</table> \n";
     print "<br> \n";
     print "<table cellspacing=\"10\" border=0 width=\"100%\"> \n";
     print "<tr><td colspan=\"2\">\n";
     print "<br> \n";
     print "<font size=4 color=\"#660000\"> \n";
     print "<center>Delete this message?</center>\n";
     print "</font> \n";
     print "</td></tr>\n";
     print "<tr> \n";
     print "<td> \n";
     print "<FORM action=\"messages.php\" method=POST> \n";
     print "<input type=\"hidden\" name=\"action\" value=\"do_delete\"> \n";
     print "<input type=\"hidden\" name=\"r_ID\"   value=\"$Responder_ID\"> \n";
     print "<input type=\"hidden\" name=\"MSG_ID\"  value=\"$M_ID\"> \n";
     print "<p align=\"right\"> \n";
     print "<input type=\"submit\" name=\"Yes\" value=\"Yes\"> \n";
     print "</p> \n";
     print "</FORM> \n";
     print "</td> \n";
     print "<td> \n";
     print "<FORM action=\"responders.php\" method=POST> \n";
     print "<input type=\"hidden\" name=\"action\" value=\"update\"> \n";
     print "<input type=\"hidden\" name=\"r_ID\"   value=\"$Responder_ID\"> \n";
     print "<p align=\"left\"> \n";
     print "<input type=\"submit\" name=\"No\" value=\"No\"> \n";
     print "</p> \n";
     print "</FORM> \n";
     print "</td> \n";
     print "</tr> \n";
     print "</table></center>\n";
?>