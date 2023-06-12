<?php
session_start();

if (!isset($_SESSION["username"])) {
    header("Location: login.php");
    exit;
}

$username = $_SESSION["username"];
$role = $_SESSION["role"];

// Funkcija za dodavanje novog zapisa o posudbi knjige
function addBorrowRecord($bookId, $borrower) {
    $records = simplexml_load_file("borrow_records.xml");

    $record = $records->addChild("record");
    $record->addChild("book_id", $bookId);
    $record->addChild("borrower", $borrower);
    $record->addChild("borrow_date", date("Y-m-d H:i:s"));

    $records->asXML("borrow_records.xml");
}

// Funkcija za provjeru valjanosti bookId-a
function checkBookIdValidity($bookId) {
    $books = simplexml_load_file("books.xml");

    foreach ($books->book as $book) {
        if ((string)$book->title === $bookId) {
            // Pronađen je odgovarajući naslov knjige
            return true;
        }
    }

    // Nije pronađen odgovarajući naslov knjige
    return false;
}

// Odjava korisnika
if (isset($_GET["logout"])) {
    session_destroy();
    header("Location: login.php");
    exit;
}

// Provjera posude knjige
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if ($role == "user") {
        $bookId = $_POST["book_id"];

        if (checkBookIdValidity($bookId)) {
            addBorrowRecord($bookId, $username);
            header("Location: books.php");
            exit;
        } else {
            $errorMessage = "Neuspješna posudba knjige.";
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Knjige</title>
    <style>
        body {
            margin: 0;
            padding: 0;
            display: flex;
            flex-direction: column;
            align-items: center;
            text-align: center;
            font-family: Arial, sans-serif;
        }

        header {
            background-color: #f1f1f1;
            padding: 10px;
            width: 100%;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        header a {
            margin: 0 10px;
            text-decoration: none;
        }

        main {
            margin-top: 50px;
        }

        h1 {
            margin-bottom: 20px;
        }

        table {
            border-collapse: collapse;
            width: 80%;
            margin: 0 auto;
        }

        th, td {
            border: 1px solid #ddd;
            padding: 8px;
        }

        th {
            background-color: #f2f2f2;
        }
    </style>
</head>
<body>
    <header>
        <nav>
            <a href="#" class="active">Knjige</a>
            <a href="my_borrowings.php">Moje posudbe</a>
        </nav>
        <a href="books.php?logout=1">Odjava</a>
    </header>

    <main>
        <h1>Popis knjiga</h1>
    
        <?php if ($role == "admin"): ?>
            <!-- Prikaz informacija za admina -->
            <?php
                // Ovdje možete prikazati informacije o posudbama knjiga za administratore
                $records = simplexml_load_file("borrow_records.xml");

                echo "<h2>Popis posudbi:</h2>";
                echo "<table>";
                echo "<tr><th>ID knjige</th><th>Posuđivač</th><th>Datum posudbe</th></tr>";
                foreach ($records->record as $record) {
                    echo "<tr>";
                    echo "<td>" . $record->book_id . "</td>";
                    echo "<td>" . $record->borrower . "</td>";
                    echo "<td>" . $record->borrow_date . "</td>";
                    echo "</tr>";
                }
                echo "</table>";
            ?>
        <?php elseif ($role == "user"): ?>
            <!-- Prikaz informacija za korisnika -->
            <h2>Popis knjiga:</h2>
            <?php
                // Ovdje prikazujemo popis knjiga dostupnih za posudbu za korisnike
                $books = simplexml_load_file("books.xml");

                echo "<table>";
                echo "<tr><th>ID knjige</th><th>Naziv knjige</th><th>Autor</th><th>Status</th><th></th></tr>";
                foreach ($books->book as $index => $book) {
                    echo "<tr>";
                    echo "<td>" . $index . "</td>";
                    echo "<td>" . $book->title . "</td>";
                    echo "<td>" . $book->author . "</td>";
                    echo "<td>" . $book->status . "</td>";
                    if ($book->status == "available") {
                        echo "<td><form method='POST'><input type='hidden' name='book_id' value='" . $book->title . "'><input type='submit' value='Posudi'></form></td>";
                    } else {
                        echo "<td></td>";
                    }
                    echo "</tr>";
                }
                echo "</table>";
            ?>
        <?php endif; ?>
    </main>
</body>
</html>
