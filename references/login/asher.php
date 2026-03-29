<?php
    $data=[];
    $dataUn=['oil','intersg','3015','wheremst','hotpotato64','mamase','mamasa','mamakusa'];
    for($i=0;$i<count($dataUn);$i++){
        $data[]=password_hash($dataUn[$i], PASSWORD_DEFAULT);
    }
    for($i=0;$i<count($data);$i++){
        echo $data[$i]."<br>";
    }
?>
