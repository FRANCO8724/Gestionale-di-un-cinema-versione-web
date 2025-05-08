<?php

session_start();

if (isset($_SESSION['email'])) {
    $email = $_SESSION['email'];
}

$action = $_POST['action'] ?? null;

$host = "127.0.0.1";
$user = "root";
$password = "";
$database = "cinema";

$connessione = new mysqli($host, $user, $password, $database);

if ($connessione === false) {
    die("Errore di connessione: " . $connessione->connect_error);
}

?>
<!DOCTYPE html>
<html>
<head>
    <title>Gestione Dati</title>
    <link rel="stylesheet" href="stylesute.css">
    <script>
        function mostraForm(tipo) {
            const forms = document.querySelectorAll('.form-container');
            forms.forEach(form => form.style.display = 'none');
            document.getElementById(tipo).style.display = 'block';
        }
    </script>
</head>
<body>
    <h2>Seleziona l'azione da eseguire</h2>
    <button onclick="mostraForm('Add_Biglietto')">Prenota Biglietto</button>
    <button onclick="mostraForm('Add_Film')">Valuta Film</button>

    <div id="Add_Biglietto" class="form-container" style="display: none;">
        <h2>Inserisci dati biglietto: </h2>
        <form method="POST">
            Posto: <input type="number" name="Posto" required min="1" max="100">
            <br><br>
            Sala: 
            <select name="sala" required>
                <?php
                $query = "SELECT Numero FROM sala";
                $result = $connessione->query($query);

                if ($result && $result->num_rows > 0) {
                    while ($row = $result->fetch_assoc()) {
                        echo "<option value=\"{$row['Numero']}\">{$row['Numero']}</option>";
                    }
                } else {
                    echo "<option value=\"\">Nessuna sala disponibile</option>";
                }
                ?>
            </select>
            <br><br>
            Orario: 
            <select name="orari" required>
                <?php
                $query = "SELECT Ora FROM orari";
                $result = $connessione->query($query);

                if ($result && $result->num_rows > 0) {
                    while ($row = $result->fetch_assoc()) {
                        echo "<option value=\"{$row['Ora']}\">{$row['Ora']}</option>";
                    }
                } else {
                    echo "<option value=\"\">Nessun orario disponibile</option>";
                }
                ?>
            </select>
            <br><br>
            Pagamento: 
            <select name="pagamento" required>
                <option value="carta">Carta</option>
                <option value="contanti">Contanti</option>
            </select>
            <br><br>
            <input type="hidden" name="email" value="<?php echo isset($_SESSION['email']) ? htmlspecialchars($_SESSION['email']) : ''; ?>">
            <input type="hidden" name="action" value="azione_biglietto">
            <input type="submit" value="Inserisci">
        </form>

        <?php
        if ($_SERVER['REQUEST_METHOD'] == 'POST' && $action === 'azione_biglietto') {
            $Posto = $_POST['Posto'];
            $Sala = $_POST['sala'];
            $Orario = $_POST['orari'];
            $Pagamento = $_POST['pagamento'];
            $Email = $_POST['email'] ?? '';

            // Esegui le query necessarie per ottenere ID
            $query = "SELECT USERNAME FROM utente WHERE Email = '$Email' LIMIT 1";
            $result = $connessione->query($query);
            $id_utente = ($result && $result->num_rows > 0) ? $result->fetch_assoc()['USERNAME'] : null;

            $query = "SELECT ID FROM sala WHERE Numero = '$Sala' LIMIT 1";
            $result = $connessione->query($query);
            $id_sala = ($result && $result->num_rows > 0) ? $result->fetch_assoc()['ID'] : null;

            $query = "SELECT CODICE FROM orari WHERE Ora = '$Orario' LIMIT 1";
            $result = $connessione->query($query);
            $id_orario = ($result && $result->num_rows > 0) ? $result->fetch_assoc()['CODICE'] : null;

            if ($id_utente && $id_sala && $id_orario) {
                $sql = "INSERT INTO biglietto (Posto, Id_utente, Id_orari, Id_sala, Pagamento, Prezzo) VALUES ('$Posto', '$id_utente', '$id_orario', '$id_sala', '$Pagamento', '6.90')";
                if ($connessione->query($sql)) {
                    echo "<p>Dati inseriti con successo!</p>";
                } else {
                    echo "<p>Errore: " . $connessione->error . "</p>";
                }
            } else {
                echo "<p>Errore nel recupero degli ID.</p>";
            }
        }
        ?>
    </div>     

    <!-- Valutazione -->

    <div id="Add_Film" class="form-container" style="display: none;">
    <h2>Inserisci voto film: </h2>

    <!-- Banner di errore, inizialmente nascosto -->
    <div id="error-banner" style="display: none; background-color: #f44336; color: white; padding: 15px; margin-bottom: 20px; border-radius: 5px;">
        <strong>Errore:</strong> <span id="error-message"></span>
    </div>

    <form method="POST">
        Film: 
        <select name="film" required>
            <?php
            // Query per recuperare tutti i film dal database
            $query = "SELECT Titolo FROM film";
            $result = $connessione->query($query);

            // Se ci sono film nel database, li mostriamo come opzioni
            if ($result && $result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    echo "<option value=\"{$row['Titolo']}\">{$row['Titolo']}</option>";
                }
            } else {
                echo "<option value=\"\">Nessun film disponibile</option>";
            }
            ?>
        </select>
        <br><br>

        <!-- Campo nascosto per l'email, precompilato dalla sessione -->
        <input type="hidden" name="email" value="<?php echo isset($_SESSION['email']) ? htmlspecialchars($_SESSION['email']) : ''; ?>">
        
        Voto: 
        <input type="number" name="voto" required min="1" max="10" placeholder="Inserisci un voto tra 1 e 10" class="input-lungo">
        <br><br>
        <input type="hidden" name="action" value="azione_valutazione">
        <input type="submit" value="Inserisci"> 
    </form>

    <?php
    if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] == 'azione_valutazione') {
        // Raccogli i dati inviati dal form
        $Film = $_POST['film'];
        $Email = $_POST['email'];  // L'email proviene dal campo nascosto
        $Voto = $_POST['voto'];

        // Controllo lato server per garantire che il voto sia numerico
        if (!is_numeric($Voto)) {
            echo "<script>document.getElementById('error-message').textContent = 'Il voto deve essere un numero.'; document.getElementById('error-banner').style.display = 'block';</script>";
            exit;  // Uscita dallo script se il voto non è numerico
        }

        // Controllo che il voto sia un valore valido (ad esempio tra 1 e 10)
        if ($Voto < 1 || $Voto > 10) {
            echo "<script>document.getElementById('error-message').textContent = 'Il voto deve essere compreso tra 1 e 10.'; document.getElementById('error-banner').style.display = 'block';</script>";
            exit;  // Uscita dallo script se il voto non è valido
        }

        // Esegui la query per trovare l'USERNAME della mail
        $query = "SELECT USERNAME FROM utente WHERE Email = '$Email' LIMIT 1";

        // Esegui la query
        $result = $connessione->query($query);

        if ($result && $result->num_rows > 0) {
            // Se la mail esiste, prendi l'ID
            $row = $result->fetch_assoc();
            $id_utente = $row['USERNAME'];
        } else {
            echo "<script>document.getElementById('error-message').textContent = 'Nome dell\'utente non trovato nel database.'; document.getElementById('error-banner').style.display = 'block';</script>";
            exit;  // Uscita dallo script in caso di errore
        }

        // Esegui la query per trovare il CODICE del titolo del film
        $query = "SELECT CODICE FROM film WHERE Titolo = '$Film' LIMIT 1";

        // Esegui la query
        $result = $connessione->query($query);

        if ($result && $result->num_rows > 0) {
            // Se il titolo esiste, prendi il CODICE
            $row = $result->fetch_assoc();
            $id_film = $row['CODICE'];
        } else {
            echo "<script>document.getElementById('error-message').textContent = 'Film non trovato nel database.'; document.getElementById('error-banner').style.display = 'block';</script>";
            exit;  // Uscita dallo script in caso di errore
        }

        // Crea una query per inserire i dati nella tabella "Classifica"
        $sql = "INSERT INTO classifica (Id_valutazione, Id_film, Voto) VALUES ('$id_utente', '$id_film', '$Voto')";

        // Esegui la query
        if ($connessione->query($sql)) {
            echo "<p>Dati inseriti con successo!</p>";
        } else {
            echo "<script>document.getElementById('error-message').textContent = 'Errore nell\'inserimento dei dati: " . $connessione->error . "'; document.getElementById('error-banner').style.display = 'block';</script>";
        }
    }
    ?>
</div>

</body>
</html>
