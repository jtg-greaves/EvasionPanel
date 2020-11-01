<?php
$message = "";

// Parse the URL 
$current_url = $_SERVER['REQUEST_URI'];
$parts = parse_url($current_url);
parse_str($parts['query'], $query);

// Attempt to pull current values 
try {
    $url = 'https://api.minetools.eu/uuid/' . $query["user"];
    $array =  json_decode(file_get_contents($url), true);

    if ($array["status"] != "OK") {
        $kill = true;
    } else {
        $nbanuuid = $array["id"];
        $nban = $array["name"];
    }

    if (!isset($kill)) {
        $dsn = 'mysql:host=localhost;dbname=ENTER_NAME';
        $dbusername = "ENTER_USERNAME";
        $dbpassword = "ENTER_PASSWORD";

        $dbh = new PDO($dsn, $dbusername, $dbpassword);

        $sql = "SELECT * FROM `evasion` WHERE nban ='" . $nbanuuid . "' LIMIT 1";
        $stmt = $dbh->query($sql);
        $row = $stmt->fetchObject();

        if ($row == "") {
            header("Location: new", true);
        } else {
            $nban_raw = $row->nban;
            $cban_raw = $row->cban;
            $staff_raw = $row->staff;
            $notes = $row->notes;
            $exempt = $row->exempt;

            // cbanuuid
            $url = 'https://api.minetools.eu/uuid/' . $cban_raw;
            $array =  json_decode(file_get_contents($url), true);

            if ($array["status"] != "OK") {
                $cban = $cban_raw;
            } else {
                $cban = $array["name"];
            }

            // staff
            $url = 'https://api.minetools.eu/uuid/' . $staff_raw;
            $array =  json_decode(file_get_contents($url), true);

            if ($array["status"] != "OK") {
                $staff = $staff_raw;
            } else {
                $staff = $array["name"];
            }

            $dbh = null;
        }
    } else {
        $message = "Error when processing - check the username is valid!";
    }
} catch (PDOException $e) {
    print "Error!: " . $e->getMessage() . "<br>";
    die();
}


// START OF VIEW SCRIPT 
if (isset($_POST["submit"])) {
    try {
        // cbanuuid
        $url = 'https://api.minetools.eu/uuid/' . $_POST["cban"];
        $array =  json_decode(file_get_contents($url), true);
        if ($array["status"] != "OK") {
            $kill = true;
        } else {
            $cbanuuid = $array["id"];
        }

        // staffuuid
        $url = 'https://api.minetools.eu/uuid/' . $_POST["staff"];
        $array =  json_decode(file_get_contents($url), true);
        if ($array["status"] != "OK") {
            $kill = true;
        } else {
            $staffuuid = $array["id"];
        }

        if (!isset($kill)) {
            $dsn = 'mysql:host=localhost;dbname=jtgreave_minesuperior';
            $dbusername = "jtgreave_minesuperior";
            $dbpassword = "minesuperior";

            $dbh = new PDO($dsn, $dbusername, $dbpassword);

            if ($_POST["exempt"] == "true") {
                $exemptdb = 1;
            } else {
                $exemptdb = 0;
            }

            $data = [
                'cban' => $cbanuuid,
                'staff' => $staffuuid,
                'notes' => $_POST["notes"],
                'exempt' => $exemptdb
            ];

            $sql = "UPDATE `evasion` SET cban=:cban, staff=:staff, notes=:notes, exempt=:exempt WHERE nban='$nbanuuid'";
            $stmt = $dbh->prepare($sql);
            $stmt->execute($data);

            $dbh = null;
            $_POST["submit"] = null;
            header("Location: view?user=" . $nban, true);
        } else {
            $message = "Error when processing - Check the usernames and try again!";
        }
    } catch (PDOException $e) {
        print "Error!: " . $e->getMessage() . "<br>";
        die();
    }
}


if ($message != "") {
    echo "<script> alert('$message') </script>";
}
?>

<link rel="icon" href="https://minotar.net/helm/<?php echo $nban; ?>/100.png" sizes="any" type="image/png">
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
        width: 60%;
    }
</style>

<body>
    <div class="form">
        <div style="padding: 1.25rem; padding-bottom: 0px;">

            <h1> Evasion Editor </h1>
            <hr>
            <form method="post" autocomplete="off">
                <label for="nban">Not ban: <span style="color: red;">*</span> </label>
                <input required type="text" name="nban" maxlength="16" placeholder="Who should we not ban?" disabled value="<?php echo $nban ?>"> <br> <br>
                <label for="cban">Currently banned: <span style="color: red;">*</span> </label>
                <input required type="text" name="cban" maxlength="16" placeholder="1 username; others in notes" value="<?php echo $cban; ?>"> <br> <br>
                <label for="staff">Staff member: <span style="color: red;">*</span> </label>
                <input required type="text" name="staff" maxlength="16" placeholder="Who are you?" value="<?php echo $staff; ?>"> <br> <br>
                <label for="notes">Additional note: </label>
                <textarea rows="2" type="text" name="notes" placeholder="Anything else?"> <?php echo $notes; ?> </textarea> <br> <br>
                <br> <label for="exempt">Exempt? </label>
                <input style="width: 15px; height: 15px; background: white; border-radius: 5px; border: 2px solid #555; float: left;" type="checkbox" value="true" name="exempt" <?php if ($exempt == 1) {
                                                                                                                                                                                        echo "checked";
                                                                                                                                                                                    } ?>> <br> <br>


                <br>
                <hr style="margin-bottom: 0px;">
                <input id="submit" style="margin-top: 17px; width: 40%;" class="button" type="submit" value="Submit" name="submit">
            </form>
            <button id="new" style="width: 40%" class="button" onclick="newRedirect()"> Checker </button>

        </div>
    </div>
</body>

<script>
    function newRedirect() {
        window.location.href = "index";
    }
</script>
