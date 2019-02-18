<?php
    $hDbcDB2 = db2_connect(
        "DATABASE=$_SESSION[dbERPName];HOSTNAME=$_SESSION[dbERPHost];PORT=50000;PROTOCOL=TCPIP;UID=$_SESSION[dbERPUser];PWD=$_SESSION[dbERPPswd];",
        null, null);
?>