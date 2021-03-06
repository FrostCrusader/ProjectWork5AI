<!--Work in progress-->
<?php
    session_start();
    if(!isset($_SESSION["credenziali"]))
        header("Location:accesso.php");
    require_once("commonFunctions.php");

    /*Codice che permette di inviare l'email d'invito*/
    if(isset($_POST["inviaEmail"]))
        {
        $email=$_POST["email"];
        $characters= '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz';
        $codice=substr(str_shuffle($characters),0, 10);

        $utenti=mysqli_query($db, "SELECT *
                                   FROM utente
                                   WHERE email='$email'");
            
        $utentiR=mysqli_fetch_array($utenti);
            
        if($utentiR==NULL)
            {
            mysqli_query($db, "INSERT INTO utente (codice)
                               VALUES ('$codice')");

            $subject='Invito a ProjectWork5AI';
            $message="Sei stato invitato a partecipare alla piattaforma di votazioni ProjectWork5AI, inserisci il codice $codice nella pagina di registrazione per poter iniziare a votare!
                        Il link al sito � il seguente:  https://projectwork5ai.altervista.org/votazioni/home.php";
            $header='From: projectwork5ai@gmail.com';
            mail($email,$subject,$message,$header);
            }

        }


    /*codice che permette di fare il logout dalla pagina*/
    if(isset($_POST["logout"]))
        {
        session_destroy();
        header("Location: home.php");
        }



    /*codice che permette di inserire i dati relativi ad una votazione nel db*/        
    if(isset($_POST["votCreation"]) || isset($_POST["votProposal"]))
        {
        $titolo=$_POST["titolo"];
        $testo=$_POST["testo"];
        $percMin=$_POST["percenMin"];
        $nOp=$_POST["nOpzioni"];
        $expDate=$_POST["expireDate"];
        $opzioni=$_POST["opzioni"];
        $credenziali=$_SESSION["credenziali"];
        $CF=$credenziali["CF"];
        $state;
        if(isset($_POST["astensione"]))
            $ast=true;
           else
            $ast=false;

        if(isset($_POST["chiaro"]))
            $votVisible=true;
           else
            $votVisible=false;

        $codiceT=mysqli_query($db, "SELECT codice
                                    FROM utente 
                                    WHERE CF='$CF'");

                                    
        $codiceR=mysqli_fetch_array($codiceT);
        $codice=$codiceR[0];


        if(isset($_POST["votCreation"]))
            {
            /*codice che inserisce le informazioni relative alla votazione appena creata nel db*/
            $state="in_corso";

            mysqli_query($db, "INSERT INTO crea(codice, testoQ)
                               VALUES('$codice', '$testo')");

            votAvailable($db, $testo);
            }
           else
            {
            /*codice che inserisce le informazioni relative alla votazione appena proposta nel db*/
            $state="pendente";

            mysqli_query($db, "INSERT INTO propone(codice, testoQ)
                               VALUES('$codice', '$testo')");
            
            /*codice che invia un'email all'admin*/
            $emailT=mysqli_query($db, "SELECT email
                                       FROM utente JOIN amministratore
                                                   ON utente.codice=amministratore.codice");
            
            $emailR=mysqli_fetch_array($emailT);
            $email=$emailR[0];

            $utenteT=mysqli_query($db, "SELECT nome, cognome
                                        FROM utente 
                                        WHERE codice='$codice'");
           
            $utenteR=mysqli_fetch_assoc($utenteT);

            $nome=$utenteR["nome"];
            $cognome=$utenteR["cognome"];

            $subject='Nuova votazione proposta';
            $message="$nome $cognome ha appena proposto una votazione! Accedi alla piattaforma per approvarla o eliminarla! 
                        https://projectwork5ai.altervista.org/votazioni/paginaUtente.php";
            $header='From: projectwork5ai@gmail.com';
            mail($email,$subject,$message,$header);
                
            }
        
        /*codice che inserisce il quesito nel db*/
        mysqli_query($db, "INSERT INTO quesito(testoQ, titolo, scadenza, percMinima, stato, astensione, votoChiaro)
                           VALUES('$testo', '$titolo' ,'$expDate', '$percMin', '$state', '$ast', '$votVisible')");

        /*codice che inserisce le risposte del quesito nel db*/
        for($i=0, $j=0;$i<strlen($opzioni);$i++)
            {
            if($opzioni{$i}==";")
                {
                $opzione=trim(substr($opzioni,$j, $i-$j));

                mysqli_query($db, "INSERT INTO risposta(testoR, testoQ)
                                   VALUES('$opzione', '$testo')");                         
                $j=$i+1;
                }
            } 
               
        }


    /*codice che permette di approvare una votazione proposta*/
    if(isset($_POST["approvePendingVot"]))
        {
        $testoQ=$_POST['pendingVotText'];
        mysqli_query($db, "UPDATE quesito
                           SET stato='in_corso'
                           WHERE testoQ='$testoQ'");

        $utentiT=mysqli_query($db, "SELECT codice
                                    FROM utente");

        for($utentiR=mysqli_fetch_assoc($utentiT);$utentiR!=NULL;$utentiR=mysqli_fetch_assoc($utentiT))
            {
            $codice=$utentiR['codice'];
            mysqli_query($db, "INSERT INTO partecipa(codice, testoQ)
                               VALUES('$codice', '$testoQ')");
            } 

        /*codice che invia un'email all'utente che ha proposto la votazione*/
        $emailT=mysqli_query($db, "SELECT email
                                   FROM utente JOIN propone 
                                               ON utente.codice=propone.codice AND
                                               testoQ='$testoQ'");
            
        $emailR=mysqli_fetch_assoc($emailT);
        $email=$emailR["email"];

        $subject='Votazione approvata';
        $message="Una votazione che avevi proposto � stata approvata! Accedi alla piattaforma per poter partecipare
                  https://projectwork5ai.altervista.org/votazioni/paginaUtente.php";
        $header='From: projectwork5ai@gmail.com';
        mail($email,$subject,$message,$header);

        sendEmails($db);
        }

    /*codice che permette di eliminare una votazione proposta*/
    if(isset($_POST["erasePendingVot"]))
        {
        $testoQ=$_POST['pendingVotText'];
        

        /*codice che invia un'email all'utente che ha proposto la votazione*/
        $emailT=mysqli_query($db, "SELECT email
                                   FROM utente JOIN propone 
                                               ON utente.codice=propone.codice AND
                                                  testoQ='$testoQ' ");
            
        $emailR=mysqli_fetch_array($emailT);
        $email=$emailR[0];

        $subject='Votazione non approvata';
        $message="Una votazione che avevi proposto non � stata approvata, ci dispiace. Accedi alla piattaforma per poter partecipare ad altre votazioni!
                  https://projectwork5ai.altervista.org/votazioni/paginaUtente.php";
        $header='From: projectwork5ai@gmail.com';
        mail($email,$subject,$message,$header);

        /*codice che elimina la votazione pendente dal db*/
        mysqli_query($db, "DELETE FROM quesito
                           WHERE testoQ='$testoQ'"); 

        mysqli_query($db, "DELETE FROM risposta
                           WHERE testoQ='$testoQ'"); 
        }

    /*codice che permette di impostare come cancellati gli account*/    
    if(isset($_POST["eraseAccount"]))
        {
        
        $codice=$_POST["choosenUser"];

        mysqli_query($db, "UPDATE utente
                           SET cancellato=1
                           WHERE codice='$codice'");
   
        
        }



    /*codice che permette di mettere una votazione in ballottaggio*/   
    if(isset($_POST["ballottaggio"]))
        {
        $testoQ=$_POST['choosenQ'];
        $maxVot=$_POST['nVoti'];

        mysqli_query($db, "UPDATE quesito
                           SET stato='ballottaggio'
                           WHERE testoQ='$testoQ'");

        mysqli_query($db, "INSERT INTO quesito 
	                       SELECT CONCAT('ballottaggio: ', testoQ), CONCAT('ballottaggio: ', titolo), DATE_ADD(CURRENT_TIMESTAMP, INTERVAL 1 WEEK), percMinima, 'in_corso', astensione, votoChiaro 
                           FROM quesito 
                           WHERE testoQ='$testoQ'");

        mysqli_query($db, "INSERT INTO risposta
                           SELECT testoR, '0', CONCAT('ballottaggio: ', testoQ)
                           FROM risposta 
                           WHERE testoQ='$testoQ' AND votiFavorevoli=$maxVot");

        votAvailable($db, "ballottaggio: $testoQ");
        }


    

/**
     *Funziona che rende disponibile agli utenti una votazione.
     *@param db, il database di riferimento. 
     *@param testoQ, il testo del quesito in questione. 
     */
    function votAvailable($db, $testoQ)
        {
        $utentiT=mysqli_query($db, "SELECT codice
                                    FROM utente");

        for($utentiR=mysqli_fetch_assoc($utentiT);$utentiR!=NULL;$utentiR=mysqli_fetch_assoc($utentiT))
            {
            $codice=$utentiR['codice'];
            mysqli_query($db, "INSERT INTO partecipa(codice, testoQ)
                               VALUES('$codice', '$testoQ')");
            }
            
        sendEmails($db);
        }



    /**
     *Funziona che invia un'email a tutti gli utenti registrati
     *@param db, il database di riferimento. 
     */
    function sendEmails($db)
        {
        $emailT=mysqli_query($db, "SELECT email
                                   FROM utente");

            
            for($emailR=mysqli_fetch_assoc($emailT);$emailR!=NULL;$emailR=mysqli_fetch_assoc($emailT))
                {
                $email=$emailR["email"];
                $subject='Nuova votazione disponibile';
                $message="Una nuova votazione � ora disponibile! Accedi alla piattaforma per votare e molto altro! 
                          https://projectwork5ai.altervista.org/votazioni/paginaUtente.php";
                $header='From: projectwork5ai@gmail.com';
                mail($email,$subject,$message,$header);
                }
        }

    header("Location: paginaUtente.php");

    
?>