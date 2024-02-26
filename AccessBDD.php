<?php
include_once("ConnexionPDO.php");

/**
 * Classe de construction des requêtes SQL à envoyer à la BDD
 */
class AccessBDD {
	
    public $login="root";
    public $mdp="";
    public $bd="mediatek86";
    public $serveur="localhost";
    public $port="3306";	
    public $conn = null;

    /**
     * constructeur : demande de connexion à la BDD
     */
    public function __construct(){
        try{
            $this->conn = new ConnexionPDO($this->login, $this->mdp, $this->bd, $this->serveur, $this->port);
        }catch(Exception $e){
            throw $e;
        }
    }

    /**
     * récupération de toutes les lignes d'une table
     * @param string $table nom de la table
     *  
     * @return lignes de la requete
     */
    public function selectAll($table){
        if($this->conn != null){
            switch ($table) {
                case "livre" :
                    return $this->selectAllLivres();
                case "dvd" :
                    return $this->selectAllDvd();
                case "revue" :
                    return $this->selectAllRevues();
                case "exemplaire" :
                    return $this->selectExemplairesRevue();
                case "genre" :
                case "public" :
                case "rayon" :
                case "etat" :
                    // select portant sur une table contenant juste id et libelle
                    return $this->selectTableSimple($table);
                case "suivi": 
                    return $this->selectTable($table);
                case "commandedocument":
                    //print_r("selecttable utilisé pour chercher le document");
                    return $this->selectTable ($table);
                case "abonnement":
                    return $this->selectTable($table);
                default:
                    // select portant sur une table, sans condition
                    return $this->selectTable($table);
            }			
        }else{
            return null;
        }
    }

    /**
     * récupération des lignes concernées
     * @param string $table nom de la table
     * @param array $champs nom et valeur de chaque champs de recherche
     * @return lignes répondant aux critères de recherches
     */	
    public function select($table, $champs){
        if($this->conn != null && $champs != null){
            //print_r('select champs =');
            //print_r($champs);
            switch($table){
                case "exemplaire" :
                    return $this->selectExemplairesRevue($champs['id']);
                case "commandedocument":
                    return $this->selectCommandeDocumentByLivreId($champs['id']);
                case "abonnement":
                    
                    return $this->selectAbonnementById($champs['id']);
                default:                    
                    // cas d'un select sur une table avec recherche sur des champs
                    return $this->selectTableOnConditons($table, $champs);					
            }				
        }else{
                return null;
        }
    }

    /**
     * récupération de toutes les lignes d'une table simple (qui contient juste id et libelle)
     * @param string $table
     * @return lignes triées sur libelle
     */
    public function selectTableSimple($table){
        $req = "select * from $table order by libelle;";		
        return $this->conn->query($req);	    
    }
    
    /**
     * récupération de toutes les lignes d'une table
     * @param string $table
     * @return toutes les lignes de la table
     */
    public function selectTable($table){
        $req = "select * from $table;";		
        return $this->conn->query($req);        
    }
    
    /**
     * récupération des lignes d'une table dont les champs concernés correspondent aux valeurs
     * @param type $table
     * @param type $champs
     * @return type
     */
    public function selectTableOnConditons($table, $champs){
        // construction de la requête
        $requete = "select * from $table where ";
        foreach ($champs as $key => $value){
            $requete .= "$key=:$key and";
        }
        // (enlève le dernier and)
        $requete = substr($requete, 0, strlen($requete)-3);								
        return $this->conn->query($requete, $champs);		
    }

    /**
     * récupération de toutes les lignes de la table Livre et les tables associées
     * @return lignes de la requete
     */
    public function selectAllLivres(){
        $req = "Select l.id, l.ISBN, l.auteur, d.titre, d.image, l.collection, ";
        $req .= "d.idrayon, d.idpublic, d.idgenre, g.libelle as genre, p.libelle as lePublic, r.libelle as rayon ";
        $req .= "from livre l join document d on l.id=d.id ";
        $req .= "join genre g on g.id=d.idGenre ";
        $req .= "join public p on p.id=d.idPublic ";
        $req .= "join rayon r on r.id=d.idRayon ";
        $req .= "order by titre ";		
        return $this->conn->query($req);
    }	

    /**
     * récupération de toutes les lignes de la table DVD et les tables associées
     * @return lignes de la requete
     */
    public function selectAllDvd(){
        $req = "Select l.id, l.duree, l.realisateur, d.titre, d.image, l.synopsis, ";
        $req .= "d.idrayon, d.idpublic, d.idgenre, g.libelle as genre, p.libelle as lePublic, r.libelle as rayon ";
        $req .= "from dvd l join document d on l.id=d.id ";
        $req .= "join genre g on g.id=d.idGenre ";
        $req .= "join public p on p.id=d.idPublic ";
        $req .= "join rayon r on r.id=d.idRayon ";
        $req .= "order by titre ";	
        return $this->conn->query($req);
    }	

    /**
     * récupération de toutes les lignes de la table Revue et les tables associées
     * @return lignes de la requete
     */
    public function selectAllRevues(){
        $req = "Select l.id, l.periodicite, d.titre, d.image, l.delaiMiseADispo, ";
        $req .= "d.idrayon, d.idpublic, d.idgenre, g.libelle as genre, p.libelle as lePublic, r.libelle as rayon ";
        $req .= "from revue l join document d on l.id=d.id ";
        $req .= "join genre g on g.id=d.idGenre ";
        $req .= "join public p on p.id=d.idPublic ";
        $req .= "join rayon r on r.id=d.idRayon ";
        $req .= "order by titre ";
        return $this->conn->query($req);
    }	

    /**
     * récupération de tous les exemplaires d'une revue
     * @param string $id id de la revue
     * @return lignes de la requete
     */
    public function selectExemplairesRevue($id){
        $param = array(
                "id" => $id
        );
        $req = "Select e.id, e.numero, e.dateAchat, e.photo, e.idEtat ";
        $req .= "from exemplaire e join document d on e.id=d.id ";
        $req .= "where e.id = :id ";
        $req .= "order by e.dateAchat DESC";		
        return $this->conn->query($req, $param);
    }		

    /**
     * suppresion d'une ou plusieurs lignes dans une table
     * @param string $table nom de la table
     * @param array $champs nom et valeur de chaque champs
     * @return true si la suppression a fonctionné
     */	
      public function delete($table, $champs){
        if($this->conn != null){
            // construction de la requête
            //print_r("champs  ");
            //print_r($champs);
            // print_r(" champs fin");
            $requete = "delete from $table where ";
            foreach ($champs as $key => $value){
               $requete .= "$key=:$key and ";
            }
            // (enlève le dernier and)
            $requete = substr($requete, 0, strlen($requete)-5);
            //print_r("apirequetedbut ");
            //print_r($requete);
            // print_r(" apirequetedbut fin");
            return $this->conn->execute($requete, $champs);		
        }else{
            return null;
        }
    }

    /**
     * ajout d'une ligne dans une table
     * @param string $table nom de la table
     * @param array $champs nom et valeur de chaque champs de la ligne
     * @return true si l'ajout a fonctionné
     */	
    public function insertOne($table, $champs){
        if($this->conn != null && $champs != null){
            //if($table == 'suivi'|| $table == 'commande'){
               $champs = array($champs);
           // }
            //print_r("insertone called ");
            $champs = $champs[0];
            //print_r($champs);
            
            // construction de la requête
            $requete = "insert into $table (";
            foreach ($champs as $key => $value){
               // print_r("key is ");
               // print_r($key);
              //  print_r("finkey");
                $requete .= "$key,";
            }
            // (enlève la dernière virgule)
            $requete = substr($requete, 0, strlen($requete)-1);
            $requete .= ") values (";
            foreach ($champs as $key => $value){
                $requete .= "'$value',";
            }
            // (enlève la dernière virgule)
            $requete = substr($requete, 0, strlen($requete)-1);
            $requete .= ");";
            
            return $this->conn->execute($requete);		
        }else{
            return null;
        }
    }
       

    /**
     * modification d'une ligne dans une table
     * @param string $table nom de la table
     * @param string $id id de la ligne à modifier
     * @param array $param nom et valeur de chaque champs de la ligne
     * @return true si la modification a fonctionné
     */	
    public function updateOne($table, $champs){
        if($this->conn != null && $champs != null){
            
            // construction de la requête
            $id =$champs["id"];
            $requete = "update $table set ";
            foreach ($champs as $key => $value){
                $requete .= "$key=:$key,";
            }
            // (enlève la dernière virgule)
            $requete = substr($requete, 0, strlen($requete)-1);				
            //$champs["id"] = $id;
            $requete .= " where id=:id;";
          //    echo "SQL Query: " . $requete . "\n";
            //print_r($champs);
            return $this->conn->execute($requete, $champs);
        }else{
            return null;
        }
    }
       public function selectCommandeDocumentByLivreId($idLivre) {
         // print_r(" selectCommandeDocumentByLivreId");
        $param = array(
        "idLivre" => $idLivre
        );
        $req = "SELECT cd.id, cd.nbExemplaire, cd.idLivreDvd, cd.idsuivi, cd.date, cd.montant, ";
        $req .= "s.libelle as libelle ";
        $req .= "FROM commandedocument cd ";
        $req .= "JOIN commande c ON cd.id = c.id ";
        $req .= "JOIN suivi s ON cd.idsuivi = s.id ";
        $req .= "JOIN livres_dvd ld ON cd.idLivreDvd = ld.id ";
        $req .= "WHERE cd.idLivreDvd = :idLivre ";
        $req .= "ORDER BY c.dateCommande DESC";
    
         return $this->conn->query($req, $param);
        }
    
        public function selectAbonnementById($idRevue){
         $param = array(
        "idRevue" => $idRevue
         );
    
        $req = "SELECT c.id, c.dateCommande, c.montant, ab.dateFinAbonnement, ab.idRevue ";
        $req .= "FROM abonnement ab ";
        $req .= "JOIN commande c ON ab.id = c.id ";
        $req .= "WHERE ab.idRevue = :idRevue ";
        $req .= "ORDER BY c.dateCommande DESC";

        return $this->conn->query($req, $param);
        }




    

}