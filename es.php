<?php

$cmd=sprintf("/opt/bitnami/php/bin/php /app/PO_Back/ElaticSearch.php --method=SyncData");

exec($cmd);