<?php
header('Content-Type: application/json');
include_once("Controle.php");
$controle = new Controle();

// Contrôle de l'authentification
if (!isset($_SERVER['PHP_AUTH_USER']) || (isset($_SERVER['PHP_AUTH_USER']) && 
    !($_SERVER['PHP_AUTH_USER'] == 'admin' && ($_SERVER['PHP_AUTH_PW'] == 'adminpwd')))) {
    $controle->unauthorized();
} else {
    // Définition des filtres à utiliser
    $args = array(
        'table'  => FILTER_SANITIZE_FULL_SPECIAL_CHARS,
        'id'     => FILTER_SANITIZE_FULL_SPECIAL_CHARS,
        'champs' => array('filter' => FILTER_DEFAULT, 'flags' => FILTER_FLAG_NO_ENCODE_QUOTES)
    );
    
    // Récupération et filtration des données
    $input = filter_input_array(INPUT_GET, $args) ?? filter_input_array(INPUT_POST, $args);
    $table = $input['table'];
    $id = $input['id'];
    $champs = $input['champs'];
    
    if ($champs != "") {
        $champs = json_decode($champs, true);
    }

    // Traitement suivant le verbe HTTP utilisé
    switch ($_SERVER['REQUEST_METHOD']) {
        case 'GET':
            $controle->get($table, $champs);
            break;
        case 'POST':
            $controle->post($table, $champs);
            break;
        case 'PUT':
            $controle->put($table, $id, $champs);
            break;
        case 'DELETE':
            $controle->delete($table, $champs);
            break;
        default:
            // Gérer les méthodes HTTP non supportées ici
            break;
    }
}

