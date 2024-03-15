<?php
header('Content-Type: application/json');
include_once("Controle.php");
$controle = new Controle();

// Contrôle de l'authentification
if (!isset($_SERVER['PHP_AUTH_USER']) || (isset($_SERVER['PHP_AUTH_USER']) && 
    !($_SERVER['PHP_AUTH_USER'] == 'admin' && $_SERVER['PHP_AUTH_PW'] == 'btsSIO7!'))) {
    $controle->unauthorized();
} else {
    // Traitement suivant le verbe HTTP utilisé
    switch ($_SERVER['REQUEST_METHOD']) {
        case 'GET':
             // récupération des données
            // Nom de la table au format string
            $table = filter_input(INPUT_GET, 'table', FILTER_SANITIZE_SPECIAL_CHARS);
             
            // id de l'enregistrement au format string
            $id = filter_input(INPUT_GET, 'id', FILTER_SANITIZE_SPECIAL_CHARS);
            if($id!=''){
                $id= array('id'=> $id);
            }
            
             // nom et valeur des champs au format json
            $contenu = filter_input(INPUT_GET, 'contenu', FILTER_SANITIZE_SPECIAL_CHARS,FILTER_FLAG_NO_ENCODE_QUOTES);
            if($contenu != ""){
            $contenu = json_decode($contenu, true);}
            //print_r("id");
            //print_r($id);
            //print_r("fin id");
            $controle->get($table, $id);
            break;
            
        case 'POST':
            
                //$input = filter_input_array(INPUT_GET, $args);
            $urlPath = $_SERVER['REQUEST_URI'];
            //print_r($urlPath);
            // Division l'URL en segments
                $urlSegments = explode('/', $urlPath);
                 $jsonBody = json_decode(file_get_contents('php://input'), true);
                 //print_r("jsonbody");
                 //print_r($jsonBody);
                 //print_r("jsonbodyfin");
            //debut new
                //$table = $input['table'] ?? ''; 
                // = $jsonBody['id'] ?? []; // Idem
                //print_r("id is ");
                //print_r($jsonBody['id']);
                //$champs = $jsonBody;
              //print_r($jsonBody);
                 $table = $urlSegments[2];
                 //print_r("table name is ");
                 //print_r($table);
               if (is_array($jsonBody)) {
                   //fin new
               
                $controle->post($table, $jsonBody);
            } else { 
                // cas où $jsonBody n'est pas un tableau
                
                echo "Le corps de la requête JSON n'est pas un tableau.";
                    }
                break;
            
        case 'PUT':
             $urlPath = $_SERVER['REQUEST_URI'];
             $urlSegments = explode('/', $urlPath);
             $table = $urlSegments[2];
             //print($table);
            
            $jsonBody = json_decode(file_get_contents('php://input'), true);
            //$table = $jsonBody['table'] ?? '';
            //$id = $jsonBody['id'] ?? '';
            
            $champs = $jsonBody ?? [];
            //$champs = $champs[0];
            //unset($champs['id']);
            //print_r($champs);
            //print_r("id");
            //print_r($id);
            //print_r("fin id");
            $controle->put($table, $champs);
            break;
            
        case 'DELETE':
            $jsonBody = json_decode(file_get_contents('php://input'), true);
            $urlPath = $_SERVER['REQUEST_URI'];
             $urlSegments = explode('/', $urlPath);
             $table = $urlSegments[2];
             $id = $urlSegments[3];
             $champs =  ["id" => (string)$id];
            
            
            //$table = $jsonBody['table'] ?? '';
           // print_r("champs debut med ");
            //print_r($champs);
            // print_r(" champs med fin");
           
            //print_r("champs debut med ");
            //print_r($champs);
             //print_r(" champ med fin");
            //print_r($champs);
            //print_r($table);
            $controle->delete($table, $champs);
            break;
            
        default:
            //  erreur si la méthode HTTP n'est pas supportée
            echo json_encode(['error' => 'HTTP method not supported']);
            break;
    }
}
