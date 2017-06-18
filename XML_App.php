<!DOCTYPE HTML>
<?php
ini_set("display_errors",0);error_reporting(0);


$hostname = 'localhost';
$user = 'root';
$mdp = '';
$dbXml = 'xml_sql';


$con = mysqli_connect($hostname, $user, $mdp, $dbXml);

// Check connection
if (mysqli_connect_errno()) {
    echo "Failed to connect to MySQL: " . mysqli_connect_error();
}
?>
<head>
    <title>XML app</title>
    <meta charset="utf-8">
    <!-- Latest compiled and minified CSS -->
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css">
    <style>
        .xmlApp:hover{
            transform: translate(-20px, -10px);
            box-shadow: 8px 8px 0px #eee; 
            z-index: 10000;
        }
        .xmlApp {
            position: static;
            z-index: 1;
        }

    </style>
</head>
<body>
    <div class="container">
        <div class="row">
            <form method="POST" action="" class="form-group" enctype="multipart/form-data">
                <h1>Manipulation d'un fichier XML :</h1>
                <hr>
                <div class="col-lg-8">
                    <select name="listFiles" class="form-control">
                        <option value="">Sélectionner un fichier</option>
                        <?php
                        // Affichage des noms
                        $AfficherList = "SELECT id, nom FROM fichiers";
                        $result = $con->query($AfficherList);

                        if ($result->num_rows > 0) {

                            while ($row = $result->fetch_assoc()) {
                                echo "<option value='" . $row['id'] . "'>" . $row['nom'] . "</option>";
                            }
                        } else {
                            echo "0 results";
                        }
                        ?>
                    </select>
                    <br>
                    <input type="submit" name="Envoyer" value="Afficher" class="form-control btn btn-info">
                    <br>
                    <br>
                </div>
                <div class="col-lg-4">
                    <input type="hidden" name="MAX_FILE_SIZE" value="100000" />
                    <input type="file" class="form-control" name="mon_fichier">
                    <?php
                    if (isset($_FILES['mon_fichier']['name']) !== '') {
                        $fileName = $_FILES['mon_fichier']['name'];
                        $tmpName = $_FILES['mon_fichier']['tmp_name'];
                        $infosfichier = pathinfo($_FILES['mon_fichier']['name']);
                        $extension_upload = $infosfichier['extension'];
                        $contenu = addslashes(file_get_contents($_FILES['mon_fichier']['tmp_name']));
                        $extensions_autorisees = array('xml', 'XML');
                        if (in_array($extension_upload, $extensions_autorisees)) {
                            if ($contenu !== '') {
                                $uploadReq = "INSERT INTO fichiers VALUES('DEFAULT', '$contenu', '$fileName')";
                            }
                        } else {
                            echo '';
                        }
                    }
                    ?>
                    <br>
                    <input type="text" name="tag_value" class="form-control" placeholder="Rechercher par tags...">
                </div>
                <div class="col-lg-6 xmlApp" style="border:1px solid #aaa">
                    <h3>Contenu du fichier XML</h3>
                    <hr>
                    <?php
                    if (isset($_POST['listFiles']) && isset($_POST['Envoyer'])) {
                        $id = $_POST['listFiles'];
                        $_envoie = $_POST['Envoyer'];
                        $AffichierFichier = "SELECT * FROM fichiers WHERE id='$id'";
                        $result = $con->query($AffichierFichier);
                        if ($result->num_rows > 0) {
                            while ($row = $result->fetch_assoc()) {
                                $xmlFile = utf8_encode($row['content_xml']);
                                echo nl2br($xmlFile);
                            }
                        } else {
                            echo 'Undefined';
                        }
                    } else {
                        echo 'Undefined';
                    }
//   echo exec('mysql --host=localhost --user=root xml_sql -e '."SELECT content_xml FROM fichiers LIMIT 1 INTO @xml; SELECT ExtractValue(@xml, '//FILMS[1]/FILM[1]/TITRE[1]';");
// mysql --host=localhost --user=root  xml_sql -e "SELECT content_xml FROM fichiers LIMIT 1 INTO @xml; SELECT ExtractValue(@xml, '//FILMS[1]/FILM[1]/TITRE[1]')"
                    ?>   
                </div>
                <div class="col-lg-6 xmlApp" style="border:1px solid #aaa">
                    <h3>Tests :</h3>
                    <hr>
                    <?php
                    if (isset($_FILES['mon_fichier']['error']) > 0) {

                        if ($_FILES['mon_fichier']['name'] !== '') {
                            echo 'Ajout du fichier ' . $_FILES['mon_fichier']['name'];
                        }
                    } else {
                        echo 'Pas de fichier à upload';
                    }
                    if (isset($_envoie) && isset($xmlFile)) {
                        echo '<br>Affichage du fichier XML OK !<br>Affichage des tags';

                        if (isset($_POST['tag_value']) && $_POST['tag_value'] !== '') {
                            $tag_value = $_POST['tag_value'];
                            echo "<br>Tag entré : $tag_value";
                        } else {
                            echo '<br>Tag pas inséré';
                        }
                    } else {
                        echo '<br>Fichier XML manquant !<br>Tags manquants';
                    }
                    if (isset($_envoie) && isset($_FILES['mon_fichier']['name'])) {

                        if ($con->query($uploadReq) === TRUE) {
                            echo "<br>OK upload!";
                        } else {
                            echo "<br>Pas d'upload";
                        }
                    }
                    ?>
                </div>
                <div class="col-lg-6 xmlApp" style="border:1px solid #aaa">
                    <h3>Affiche les valeurs suivant le tag </h3>
                    <hr>
                    <?php
                    // Affiche le titre des films (equivalent de ExtractValue() en PHP
                    if (isset($_POST['tag_value'])) {
                        $tag_value = $_POST['tag_value'];

                        if (isset($xmlFile)) {
                            $dom = new DOMDocument;
                            $dom->loadXML($xmlFile);
                            $films = $dom->getElementsByTagName($tag_value);

                            foreach ($films as $film) {
                                echo $film->nodeValue, PHP_EOL;
                            }
                        } else {
                            echo 'Undefined';
                        }
                    } else {
                        echo 'Undefined';
                    }
                    ?>
                </div>
                <div class="col-lg-6 xmlApp"  style="border:1px solid #aaa">
                    <h3>Affiche les tags</h3>
                    <hr>
                    <?php
                    // Affiche tous les tagsname du fichier XML
                    if (isset($xmlFile)) {
                        $doc = new DOMDocument();
                        $doc->loadXML($xmlFile);

                        $xpath = new DOMXpath($doc);
                        $nodes = $xpath->query('//*');

                        $nodeNames = array();
                        foreach ($nodes as $node) {
                            $nodeNames[$node->nodeName] = $node->nodeName;
                        }
                        var_dump($nodeNames);
                    } else {
                        echo 'Undefined';
                    }
                    ?>
                </div>
                <div class="col-lg-6 xmlApp" style="border:1px solid #aaa">
                    <h3>Requête via le shell :</h3>
                    <hr>
                    J'ai pas trouvé.
                    <?php
                    // echo exec('mysql --host=localhost --user=root  xml_sql -e "SELECT * FROM fichiers\G"'); 
                    //    echo exec('ipconfig');                 
                    $con->close();
                    ?>
                </div>
            </form>
        </div>
    </div>
</body>