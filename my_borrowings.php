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

// Funkcija za vraćanje knjige
function returnBook($bookId, $borrower) {
    $records = simplexml_load_file("borrow_records.xml");

    foreach ($records->record as $record) {
        if ((string)$record->book_id === $bookId && (string)$record->borrower === $borrower) {
            unset($record[0]);
            $records->asXML("borrow_records.xml");
            return true;
        }
    }

    return false;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if ($role == "user") {
        $bookId = $_POST["book_id"];

        if (returnBook($bookId, $username)) {
            header("Location: my_borrowings.php");
            exit;
        } else {
            $errorMessage = "Neuspješno vraćanje knjige.";
        }
    }
}
// Odjava korisnika
if (isset($_GET["logout"])) {
    session_destroy();
    header("Location: login.php");
    exit;
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Moje posudbe</title>
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
            <a href="books.php">Knjige</a>
            <a href="#" class="active">Moje posudbe</a>
        </nav>
        <a href="my_borrowings.php?logout=1">Odjava</a>
    </header>

    <main>
        <h1>Moje posudbe</h1>
        <?php if ($role == "user"): ?>
            <?php
                $records = simplexml_load_file("borrow_records.xml");

                echo "<table>";
                echo "<tr><th>ID knjige</th><th>Posuđivač</th><th>Datum posudbe</th><th>Akcija</th></tr>";
                foreach ($records->record as $record) {
                    if ((string)$record->borrower === $username) {
                        echo "<tr>";
                        echo "<td>" . $record->book_id . "</td>";
                        echo "<td>" . $record->borrower . "</td>";
                        echo "<td>" . $record->borrow_date . "</td>";
                        echo "<td><form method='POST'><input type='hidden' name='book_id' value='" . $record->book_id . "'><input type='submit' value='Vrati'></form></td>";
                        echo "</tr>";
                    }
                }
                echo "</table>";
            ?>
        <?php endif; ?>
    </main>
</body>
</html>
