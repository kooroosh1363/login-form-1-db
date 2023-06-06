<?php 



if (isset($_POST['u_name']) && isset($_POST['pass'])) {
    function validation($data){
        $data = trim($data);
        $data = stripcslashes($data);
        $data = htmlspecialchars($data);
        return $data;
    }
 
    
}else{
    header('Location: index.php');
    exit();
}




?>