<?php  
$f = 'app/modules/students/controllers/student_controller.php';  
$c = file_get_contents($f);  
$line = "require_once __DIR__ . '/../services/student_profile_view_service.php';";  
$count = substr_count($c, $line);  
if ($count  {  
$pos = strpos($c, $line);  
$pos2 = strpos($c, $line, $pos + 1);  
$c = substr($c, 0, $pos2) . substr($c, $pos2 + strlen($line) + 2);  
file_put_contents($f, $c);  
echo 'FIXED';  
} else { echo 'OK'; }  
