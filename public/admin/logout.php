<?php
setcookie("AdminID","",time()-(24*60*60),"/");
header("Location: login.php");
exit();
?>