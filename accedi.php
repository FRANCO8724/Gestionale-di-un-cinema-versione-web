<?php

// Avvia la sessione
session_start();

$action = $_POST['action'] ?? null;

$host = "127.0.0.1";
$user = "root";
$password = "";
$database = "cinema";

// Connessione al database
$connessione = new mysqli($host, $user, $password, $database);

// Verifica connessione
if ($connessione === false) {
    die("Errore di connessione: " . $connessione->connect_error);
}

// Recupero dati dal form
$Password = $_POST['Password_utente2'];
$Email = $_POST['Email_utente2'];

// Prepara la query per ottenere la password criptata dal database
$stmt = $connessione->prepare("SELECT Password, USERNAME FROM utente WHERE Email = ?");

// Associa il parametro alla query
$stmt->bind_param("s", $Email);

// Esegui la query
$stmt->execute();

// Ottieni il risultato
$result = $stmt->get_result();

// Debug: verifica se l'email è stata trovata
if ($result->num_rows > 0) {
    // Recupera la password criptata dal database
    $row = $result->fetch_assoc();
    $hashedPasswordFromDb = $row['Password'];

// Crittografia della password
$hashedPassword = password_hash($Password, PASSWORD_BCRYPT);

    // Usa password_verify per confrontare la password in chiaro con quella criptata
    if (password_verify($Password, $hashedPasswordFromDb)) {

        // Se la password è corretta, memorizza l'email dell'utente nella sessione
        $_SESSION['email'] = $Email;

        // Reindirizza l'utente alla pagina utente.php
        header("Location: utente.php");
        exit;  // Assicurati che lo script venga interrotto dopo il reindirizzamento
    } else {
        // Se la password non è corretta
        echo "Password errata.<br>"; // Debug
    }
} else {
    // Se l'email non esiste
    echo "Email non trovata nel database.<br>"; // Debug
}

// Chiudi lo statement
$stmt->close();

// Chiudi la connessione
$connessione->close();

exit;  // Assicurati che lo script venga interrotto dopo il reindirizzamento

?>
