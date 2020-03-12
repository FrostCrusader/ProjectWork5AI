<?php
	session_start();
?>
<html>
  <head>
  	<meta charset="utf-8">
    <title>Sito votazioni</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.4.1/css/bootstrap.min.css" integrity="sha384-Vkoo8x4CGsO3+Hhxv8T/Q5PaXtkKtu6ug5TOeNV6gBiFeWPGFN9MuhOf23Q9Ifjh" crossorigin="anonymous"/>
    <script src="https://code.jquery.com/jquery-3.4.1.slim.min.js" integrity="sha384-J6qa4849blE2+poT4WnyKhv5vZF5SrPo0iEjwBvKU7imGFAV0wwj1yYfoRSJoZ+n" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.0/dist/umd/popper.min.js" integrity="sha384-Q6E9RHvbIyZFJoft+2mJbHaEWldlvI9IOYy5n3zV9zzTtmI3UksdQRVvoxMfooAo" crossorigin="anonymous"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.4.1/js/bootstrap.min.js" integrity="sha384-wfSDF2E50Y2D1uUdj0O3uMBJnjuUD4Ih7YwaYd1iqfktj0Uod8GCExl3Og8ifwB6" crossorigin="anonymous"></script>
    <script type="text/javascript" src="js/scripts.js"></script>
  	<link rel="stylesheet" type="text/css" href="css/paginaUtente.css"/>
    <link rel="stylesheet" type="text/css" href="css/accesso.css"/>
  </head>
  <body class="container-fluid back text-center">
  	 <nav class="navbar navbar-expand-lg navbar-light bg-light fixed-top">
      <a class="navbar-brand" href="#">Sito votazioni</a>
      <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
        <span class="navbar-toggler-icon"></span>
      </button>
      <div class="collapse navbar-collapse" id="navbarSupportedContent">
        <ul class="navbar-nav mr-auto">
          <li class="nav-item active">
            <a class="nav-link" href="home.php">Home <span class="sr-only">(current)</span></a>
          </li>
          <li class="nav-item dropdown">
            <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
              Votazioni
            </a>
            <div class="dropdown-menu" aria-labelledby="navbarDropdown">
              <a class="dropdown-item" href="#" onclick="showLink('links','availableVot')">Votazioni disponibili</a>
              <a class="dropdown-item" href="#" onclick="showLink('links','endedVot')">Votazioni concluse</a>
              <a class="dropdown-item" href="#" onclick="showLink('links','currentVot')">Votazioni in corso</a>
              <div class="utente">
                  <div class="dropdown-divider"></div>
                  <a class="dropdown-item" href="#" onclick="showLink('links','proposeVot')">Proponi votazione</a>
              </div>
              <div class="amministratore">
              	<div class="dropdown-divider"></div>
              	<a class="dropdown-item"  href="#" onclick="showLink('links','createVot')">Crea votazione</a>
                <a class="dropdown-item"  href="#" onclick="showLink('links','invUser')">Invita nuovo utente</a>
              </div>
            </div>
          </li>
          <li class="nav-item active amministratore">
            <a class="nav-link" href="#">Utenti</a>
          </li>
        </ul>
      </div>
    </nav>
    <br><br><br>
    <p class="links" id="proposeVot">Ora propongo una votazione eh!</p>
    <p class="links" id="createVot">Ora creo una votazione eh!</p>
    <p class="links" id="availableVot">Votazioni disponibili</p>
    <p class="links" id="endedVot">Votazioni concluse</p>
    <p class="links" id="currentVot">Votazioni in corso</p>
    <div class="text-center exactCenter links" id="invUser">
      <h1 class="col-sm">Invita un utente</h1>	
      <form method="POST" action="#">	  
          <span class="row"><label class="col-xl-4">Email:</label><br><input class="col-xl-8" type="text" name="email" required><br></span>
          <input class="btn btn-info" type="submit" name="invia" value="Invia"><br>
       </form>
    </div>
    <?php
        require_once("commonFunctions.php");
    	if(isset($_SESSION["credenziali"]))
        	{
            $credenziali=$_SESSION["credenziali"];
            $CF=$credenziali["CF"];
            $password=$credenziali["password"];
            $table=mysqli_query($db, "SELECT nome, cognome
                            		  FROM Utente
                           		      WHERE CF='$CF' AND password='$password'"); 
            $row = mysqli_fetch_array($table, MYSQLI_ASSOC);  
            $nome=$row['nome'];
            $cognome=$row['cognome'];
            echo "<h1 class='col-sm links' style='display:block;'>Benvenuto/a $nome $cognome!";
            $table2=mysqli_query($db, "SELECT *
                             		   FROM Utente JOIN Amministratore 
                                       			   ON Utente.codice=Amministratore.codice AND CF='$CF'"); 
            $row2=mysqli_fetch_array($table2, MYSQLI_ASSOC);  
            if($row2!==NULL)
              {
        	  ?><script>hidShow('block', 'none');</script><?php
              }
             else
              {
        	  ?><script>hidShow('none', 'block');</script><?php
              }			
            }
        if(isset($_POST["invia"]))
            {
            $email=$_POST["email"];
            $characters= '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz';
            $codice=substr(str_shuffle($characters),0, 10);
            $table=mysqli_query($db, "INSERT INTO Utente (codice)
                                      VALUES ('$codice')");
            $subject='Invito a ProjectWork5AI';
            $message="Sei stato invitato a partecipare alla piattaforma di votazioni ProjectWork5AI, inserisci il codice $codice nella pagina di registrazione per poter iniziare a votare!
                      Il link al sito é il seguente:  https://projectwork5ai.altervista.org/votazioni/home.php";
            $headers='From: ProjectWork5AInoreply @ company . com';
            mail($email,$subject,$message,$headers);
            }
    ?>                    
  </body>
</html>
