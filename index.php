<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="./style.css">
    <title>login-form</title>
</head>

<body>
    <form action="login.php" method="POST">
        <h2>LOGIN</h2>
        <label for="">User Name</label>
        <input type="text" name="u-name" placeholder="User Name">

        <label for="">Password</label>
        <input type="password" name="pass" placeholder="Password">

        <div class="btn">
            <button type="submit">Login</button>
        </div>
    </form>
</body>

</html>