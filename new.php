<?php
// Variables
$message = "";

// If form has been submitted
if (isset($_POST["submit"])) {
    try {
        // Connect to database
        $dsn = 'mysql:host=localhost;dbname=jtgreave_minesuperior';
        $dbusername = "jtgreave_minesuperior";
        $dbpassword = "minesuperior";
        $dbh = new PDO($dsn, $dbusername, $dbpassword);

        // Nban Processing
        $url = 'https://api.minetools.eu/uuid/' . $_POST["nban"];
        $array =  json_decode(file_get_contents($url), true);
        if ($array["status"] != "OK") {
            $kill = true;
        } else {
            $nbanuuid = $array["id"];
        }

        // Look for the evasion record
        $sql = "SELECT * FROM `evasion` WHERE nban ='" . $nbanuuid . "' LIMIT 1";
        $stmt = $dbh->query($sql);
        $row = $stmt->fetchObject();

        if ($row == "") { //If no record found continue
            // Current banned processing
            $url = 'https://api.minetools.eu/uuid/' . $_POST["cban"];
            $array =  json_decode(file_get_contents($url), true);

            if ($array["status"] != "OK") {
                $kill = true;
            } else {
                $cbanuuid = $array["id"];
            }

            // Staff Processing
            $url = 'https://api.minetools.eu/uuid/' . $_POST["staff"];
            $array =  json_decode(file_get_contents($url), true);
            if ($array["status"] != "OK") {
                $kill = true;
            } else {
                $staffuuid = $array["id"];
            }

            // Prepare the exempt option for the DB
            if ($_POST["exempt"] == "true") {
                $exempt = 1;
            } else {
                $exempt = 0;
            }

            if (!isset($kill)) {
                $data = [ // Data array for the DB. 
                    'nban' => $nbanuuid,
                    'cban' => $cbanuuid,
                    'staff' => $staffuuid,
                    'notes' => $_POST["notes"],
                    'exempt' => $exempt
                ];

                $sql = "INSERT INTO `evasion` (nban, cban, staff, notes, exempt) VALUES (:nban, :cban, :staff, :notes, :exempt)";
                $stmt = $dbh->prepare($sql);
                $stmt->execute($data);

                // It has been added - now clear the variables. 
                $dbh = null;
                $_POST["submit"] = null;
                $message =  "Successful - I have added " . $_POST["nban"] . "!";
            } else {
                $message = "Error when processing!"; //Something killed it - Most likely username errors
            }
        } else { //Record already exists, but gives a link to the record.
            echo "<script>
            var r = confirm('This record already exists would you like to view it?');
            if (r == true) {
                window.location.href = 'view?user=" . $_POST["nban"] . "';
            }
            </script>";
        }
    } catch (PDOException $e) { // Catches any errors with the database
        print "Error!: " . $e->getMessage() . "<br/>";
        die();
    }
}

if ($message != "") { //If the message variable was changed alert the user.
    echo "<script> alert('$message') </script>";
}
?>

<link rel="icon" href="https://minesuperior.jtgreaves.com/favicon.ico" sizes="any" type="image/ico">
<link rel="stylesheet" href="stylesheet.css">
<title> Evasion Checker </title>
<style>
    #new {
        background: #387ccf;
        color: #ffffff;
        border-color: #3842cf;
        bottom: 1%;
        float: left;
    }

    label {
        float: left;
        width: 40%;
    }

    input,
    textarea {
        float: right;
        margin: 0;
    }

    .input-WI {
        width: 53%;
    }

    .input-NI {
        width: 60%;
    }

    .icon {
        width: 5%;
        padding-right: 2%;
    }
</style>

<body>
    <div class="form">
        <div style="padding: 1.25rem; padding-bottom: 0px;">

            <h1> Evasion Input </h1>
            <hr>
            <form method="post" autocomplete="off">
                <label for="nban">Not ban: <span style="color: red;">*</span> </label>
                <img class="icon" id="nban-icon" src="https://minotar.net/helm/MHF_Steve/64.png">
                <input class="input-WI" required type="text" oninput="nbanUpdate(this.value)" name="nban" maxlength="16" placeholder="Who should we not ban?" value="<?php echo $_POST["nban"]; ?>"> <br> <br>

                <label for="cban">Currently banned: <span style="color: red;">*</span> </label>
                <img class="icon" id="cban-icon" src="https://minotar.net/helm/MHF_Steve/64.png">
                <input class="input-WI" required type="text" oninput="cbanUpdate(this.value)" name="cban" maxlength="16" placeholder="1 username; others in notes" value="<?php echo $_POST["cban"]; ?>"> <br> <br>

                <label for="staff">Staff member: <span style="color: red;">*</span> </label>
                <img class="icon" id="staff-icon" src="https://minotar.net/helm/MHF_Steve/64.png">
                <input class="input-WI" required type="text" oninput="staffUpdate(this.value)" name="staff" maxlength="16" placeholder="Who are you?" value="<?php echo $_POST["staff"]; ?>"> <br> <br>

                <label for="notes">Additional note: </label>
                <textarea class="input-NI" rows="2" type="text" name="notes" placeholder="Anything else?"><?php echo $_POST["notes"]; ?> </textarea> <br> <br>
                <br> <label for="exempt">Exempt? </label>
                <input class="input-NI" style="width: 15px; height: 15px; background: white; border-radius: 5px; border: 2px solid #555; float: left;" type="checkbox" value="true" name="exempt" <?php if ($_POST["exempt"] == "true") {
                                                                                                                                                                                                        echo "checked";
                                                                                                                                                                                                    } ?>> <br> <br>

                <hr style="margin-bottom: 0px;">
                <input id="submit" style="margin-top: 17px; width: 40%;" class="button" type="submit" value="Submit" name="submit">
            </form>
            <button id="new" style="width: 40%" class="button" onclick="newRedirect()"> Checker </button>

        </div>
    </div>
</body>

<script>
    // Javascript for buttons, and type to update icons
    function newRedirect() {
        window.location.href = "index";
    }

    function nbanUpdate(value) {
        var element = document.getElementById("nban-icon");
        element.onerror = function() {
            this.src = 'cdn/default_icon.png';
        };
        element.src = "https://minotar.net/helm/" + value + "/64.png/";
    }

    function cbanUpdate(value) {
        var element = document.getElementById("cban-icon");
        element.onerror = function() {
            this.src = 'cdn/default_icon.png';
        };
        element.src = "https://minotar.net/helm/" + value + "/64.png/";
    }

    function staffUpdate(value) {
        var element = document.getElementById("staff-icon");
        element.onerror = function() {
            this.src = 'cdn/default_icon.png';
        };
        element.src = "https://minotar.net/helm/" + value + "/64.png/";
    }
</script>