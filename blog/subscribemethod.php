<?
$firstname = $_REQUEST['firstname'];
$lastname = $_REQUEST['lastname'];
$email = $_REQUEST['email'];
$message="ADD A+OPRODUCTIONS A+O-ADMIN ".$email." ".$firstname." ".$lastname;
mail("listserv@listserv.it.northwestern.edu","",$message, "From: a-ertell@northwestern.edu");
echo "<span style='font-weight:800;'>Thanks for subscribing!</span>";
?>