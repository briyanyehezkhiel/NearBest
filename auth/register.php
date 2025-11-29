<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Daftar Akun</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            text-align: center;
            background-color: #f6f6f6;
            margin: 0;
        }
        .container {
            width: 400px;
            margin: 100px auto;
            background: #fff;
            padding: 25px;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }
        input {
            width: 90%;
            padding: 10px;
            margin: 8px 0;
            border-radius: 8px;
            border: 1px solid #ccc;
        }
        button {
            width: 95%;
            padding: 10px;
            background: #3b2db2;
            color: #fff;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-size: 16px;
        }
        button:hover{
            opacity: 0.9;
        }
        a {
            text-decoration: none;
            color: #3b2db2;
        }
    </style>
</head>
<body>

<div class="container">
    <h2>Daftar Akun</h2>
    <form action="process_register.php" method="POST">
        <input type="text" name="username" placeholder="Username" required>
        <input type="email" name="email" placeholder="Email" required>
        <input type="password" name="password" placeholder="Password" required>
        <button type="submit" name="register">Daftar</button>
    </form>
    <p>Sudah punya akun? <a href="login.php">Masuk</a></p>
</div>

</body>
</html>
