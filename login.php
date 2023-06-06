<?php 



if (isset($_POST['u_name']) && isset($_POST['pass'])) {
    function validation($data){
        $data = trim($data);
        $data = stripcslashes($data);
        $data = htmlspecialchars($data);
        return $data;
    }
    $u_name =validation($_POST['u_name']);
    $pass =validation($_POST['pass']);

    if (empty($u_name)) {
        header('Location: index.php?error=User Name is required');
        exit();
    }else if(empty($pass)){
        header('Location: index.php?error=Password is required');
        exit();

    }else{
        echo "valid input";
    }
    
}else{
    header('Location: index.php');
    exit();
}




?>