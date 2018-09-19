<?php

use hillpy\ElemeSDK\Eleme;

$eleme = new Eleme('app_id', 'secret_key');
echo $eleme->getAccessToken();