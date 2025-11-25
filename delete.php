<?php
include "connection.php";

$id=$_GET["id"];
mysqli_query($link,"delete from table1 where id=$id");
header("location.index.php");
?>

<script type="text/javascript">
 window.location="homescreen.php";
    </script>



