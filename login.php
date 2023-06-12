<?php
session_start();

// Funkcija za provjeru korisničkog imena i lozinke
function authenticateUser($username, $password) {
    $users = simplexml_load_file("users.xml");

    foreach ($users->user as $user) {
        if (strcasecmp((string)$user->username, $username) === 0 && (string)$user->password === $password) {
            return (string)$user->role;
        }
    }

    return false;
}


// Registracija korisnika
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["register"])) {
    $username = $_POST["username"];
    $password = $_POST["password"];

    // Učitavanje postojećih korisnika iz XML datoteke
    $users = simplexml_load_file("users.xml");

    // Provjera je li korisničko ime već zauzeto
    foreach ($users->user as $user) {
        if ($user->username == $username) {
            $errorMessage = "Korisničko ime već postoji.";
            break;
        }
    }

    // Ako korisničko ime nije zauzeto, dodaj korisnika
    if (!isset($errorMessage)) {
        $newUser = $users->addChild("user");
        $newUser->addChild("username", $username);
        $newUser->addChild("password", $password);
        $newUser->addChild("role", "user"); // Dodajte ulogu "user" novom korisniku
        $users->asXML("users.xml");

        // Prijavi korisnika nakon registracije
        $_SESSION["username"] = $username;
        $_SESSION["role"] = "user";
        header("Location: books.php");
        exit;
    }
}


// Prijava korisnika
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["login"])) {
    $username = $_POST["username"];
    $password = $_POST["password"];

    $role = authenticateUser($username, $password);

    if ($role) {
        $_SESSION["username"] = $username;
        $_SESSION["role"] = $role;
        header("Location: books.php");
        exit;
    } else {
        $errorMessage = "Neuspješna prijava. Molimo provjerite korisničko ime i lozinku.";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Login</title>
    <style>
        body {
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            font-family: Arial, sans-serif;
        }

        .container {
            text-align: center;
        }

        form {
            display: inline-block;
            width: 300px;
            padding: 33px;
            border: 1px solid #ccc;
            border-radius: 5px;
            background-color: #f1f1f1;
        }

        form label {
            display: block;
            margin-bottom: 10px;
        }

        form input {
            width: 100%;
            padding: 10px;
            margin-bottom: 20px;
            border: 1px solid #ccc;
            border-radius: 5px;
        }

        form input[type="submit"] {
            background-color: #4CAF50;
            color: white;
            cursor: pointer;
        }

        form input[type="submit"]:hover {
            background-color: #45a049;
        }

        .error-message {
            color: red;
            margin-top: 10px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Knjižnica Bobi</h1>

        <form method="POST">
            <label>Korisničko ime:</label>
            <input name="username" type="text" required>

            <label>Lozinka:</label>
            <input name="password" type="password" required>

            <input type="submit" name="login" value="Prijavi se">
            <input type="submit" name="register" value="Registriraj se">
        </form>

        <?php if (isset($errorMessage)): ?>
            <p class="error-message"><?php echo $errorMessage; ?></p>
        <?php endif; ?>
    </div>
</body>
</html>
