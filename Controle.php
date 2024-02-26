<?php
include_once("AccessBDD.php");

/**
 * Contrôleur : reçoit et traite les demandes du point d'entrée
 */
class Controle{
	
    private $accessBDD;

    /**
     * Constructeur : récupération de l'instance d'accès à la BDD
     */
    public function __construct(){
        try{
            $this->accessBDD = new AccessBDD();
        }catch(Exception $e){
            $this->reponse(500, "erreur serveur");
            die();
        }
    }

    /**
     * réponse renvoyée (affichée) au client au format json
     * @param int $code code standard HTTP
     * @param string $message message correspondant au code
     * @param array $result résultat de la demande 
     */
    private function reponse($code, $message, $result=""){
        $retour = array(
            'code' => $code,
            'message' => $message,
            'result' => $result
        );
        
        echo json_encode($retour, JSON_UNESCAPED_UNICODE);
    }

    /**
     * requete arrivée en GET (select)
     * @param string $table nom de la table
     * @param type $champs nom et valeur des champs de recherche
     */
    public function get($table, $champs){
        //print_r("champs = ");
        //print_r($champs);
        $result = null;
        if (empty($champs)){
            
            $result = $this->accessBDD->selectAll($table);
        }else{
            //print_r("champs n'est pas vide");
            //print_r($champs);
            if (!is_array($champs)) {
            $champs = json_decode($champs, true);
            //print_r("champs converit en tableau");
            //print_r($champs);
            }
            $result = $this->accessBDD->select($table, $champs);
        }
        if (gettype($result) != "array" && ($result == false || $result == null)){
            $this->reponse(400, "requete invalide");
        }else{	
            $this->reponse(200, "OK", $result);
        }
    }

    /**
     * requete arrivée en DELETE
     * @param string $table nom de la table
     * @param array $champs nom et valeur des champs
     */
    public function delete($table, $champs){
        $result = $this->accessBDD->delete($table, $champs);	
        if ($result == null || $result == false){
            $this->reponse(400, "requete invalide");
        }else{	
            $this->reponse(200, "OK");
        }
    }

    /**
     * requete arrivée en POST (insert)
     * @param string $table nom de la table
     * @param array $champs nom et valeur des champs
     */
    public function post($table, $champs){
        $result = $this->accessBDD->insertOne($table, $champs);	
        if ($result == null || $result == false){
            $this->reponse(400, "requete invalide");
        }else{	
            $this->reponse(200, "OK");
        }
    }

    /**
     * requete arrivée en PUT (update)
     * @param string $table nom de la table
     * @param string $id valeur de l'id
     * @param array $champs nom et valeur des champs
     */
    public function put($table, $champs){
        $result = $this->accessBDD->updateOne($table, $champs);	
        if ($result == null || $result == false){
            $this->reponse(400, "requete invalide");
        }else{	
            $this->reponse(200, "OK");
        }
    }
	
    /**
     * login et/ou pwd incorrects
     */
    public function unauthorized(){
        $this->reponse(401, "authentification incorrecte");
    }
}