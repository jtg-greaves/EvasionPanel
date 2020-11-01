<?php
$message = "";

// Parse the URL 
$current_url = $_SERVER['REQUEST_URI'];
$parts = parse_url($current_url);
parse_str($parts['query'], $query);

// If submitted or the query has been set
if (isset($_POST["submit"]) or isset($query["user"])) {
    try {
        // Set the variable so both submit and query will work 
        if (isset($query["user"])) {
            $nban_input = $query["user"];
        } else {
            $nban_input = $_POST["nban"];
        }

        // Person to check processing
        $url = 'https://api.minetools.eu/uuid/' . $nban_input;
        $array =  json_decode(file_get_contents($url), true);
        if ($array["status"] != "OK") {
            $kill = true;
        } else {
            $nbanuuid = $array["id"];
            $nban = $array["name"];
        }

        // If it did not need to be killed
        if (!isset($kill)) {
            // Connect to the database and find the user
            $dsn = 'mysql:host=localhost;dbname=ENTER_NAME';
            $dbusername = "ENTER_USER";
            $dbpassword = "ENTER_PASSWORD";
            $dbh = new PDO($dsn, $dbusername, $dbpassword);
            $sql = "SELECT * FROM `evasion` WHERE nban ='" . $nbanuuid . "' LIMIT 1";
            $stmt = $dbh->query($sql);
            $row = $stmt->fetchObject();

            // If the row is empty set everything to N/A and False
            if ($row == "") {
                $cban = "N/A";
                $staff = "N/A";
                $notes = "N/A";
                $exempt = "False";
                $conclusion_colour = "background: rgba(255, 138, 138, 0.3);";
                $conclusion = "User is <span style='color: red; font-weight: bold;'> not exempt</span>!";
            } else { //Otherwise process that
                if ($row->exempt == 0) {
                    $conclusion_colour = "background: rgba(255, 138, 138, 0.3);";
                    $conclusion = "User is <span style='color: red; font-weight: bold;'> not exempt</span>!";
                    $exempt = "False";
                } else {
                    $conclusion_colour = "background: rgba(119, 221, 119, 0.4);";
                    $conclusion = "User is <span style='color: green; font-weight: bold;'> exempt</span>!";
                    $exempt = "True";
                }

                $nban_raw = $row->nban;
                $cban_raw = $row->cban;
                $staff_raw = $row->staff;
                $notes = $row->notes;

                if ($notes == "") {
                    $notes = "N/A";
                }

                // Currently banned Processing
                $url = 'https://api.minetools.eu/uuid/' . $cban_raw;
                $array =  json_decode(file_get_contents($url), true);
                if ($array["status"] != "OK") {
                    $cban = $cban_raw;
                } else {
                    $cban = $array["name"];
                }

                // Staff Processing
                $url = 'https://api.minetools.eu/uuid/' . $staff_raw;
                $array =  json_decode(file_get_contents($url), true);
                if ($array["status"] != "OK") {
                    $staff = $staff_raw;
                } else {
                    $staff = $array["name"];
                }

                // Clear variables
                $dbh = null;
                $_POST["submit"] = null;
            }
        } else {
            $message = "Error when processing - check the username is valid!"; //Any issues when dealing with the API
        }
    } catch (PDOException $e) {
        print "Error!: " . $e->getMessage() . "<br>"; //Any issues with the database. 
        die();
    }
} else {

    header("Location: index", true);
}
?>

<link rel="icon" href="https://minotar.net/helm/<?php echo $nban; ?>/100.png" sizes="any" type="image/png">
<link rel="stylesheet" href="stylesheet.css">

<title> Evasion Checker - <?php echo $nban; ?> </title>
<style>
    #back {
        background: #387ccf;
        color: #ffffff;
        border-color: #3842cf;
        bottom: 1%;
        float: left;
    }

    #edit {
        background: #da4453;
        border-color: #ed2d2d;
        color: #ffffff;
        bottom: 1%;
        float: right;
    }


    .label {
        float: left;
        width: 40%;
        margin: 0;
        margin-bottom: 5px;
        font-weight: bold;
    }

    .value-WI {
        float: left;
        width: 53%;
        margin: 0;
        margin-bottom: 5px;
    }

    .value-NI {
        float: left;
        width: 55%;
        margin: 0;
        margin-bottom: 5px;
    }

    .icon {
        width: 5%;
        float: left;
        padding-right: 2%;
    }
</style>

<body>
    <div class="form">
        <div style="padding: 1.25rem; padding-bottom: 0px;">
            <h1> Evasion Checker </h1>
            <hr>
            <p style="text-align: center; padding: 5px;<?php echo $conclusion_colour ?>"> Conclusion: <?php echo $conclusion ?></p>
            <p class="label">Not ban: </p>
            <img class="icon" src="https://minotar.net/helm/<?php echo $nban; ?>/64.png">
            <p class="value-WI"> <?php echo $nban; ?> </p>

            <p class="label">Currently banned: </p>
            <?php if ($cban != "N/A") {
                echo '<img class="icon" src="https://minotar.net/helm/' . $cban . '/64.png">';
                echo  '<p class="value-WI">';
            } else {
                echo '<p class="VALUE-NI">';
            }
            echo $cban; ?> </p> <br>

            <p class="label">Staff member: </p>
            <?php if ($staff != "N/A") {
                echo '<img class="icon" src="https://minotar.net/helm/' . $staff . '/64.png">';
                echo  '<p class="value-WI">';
            } else {
                echo '<p class="VALUE-NI">';
            }
            echo $staff; ?> </p> <br>

            <p class="label">Additional notes: </p>
            <p class="value-NI"> <?php echo $notes; ?> </p> <br>

            <p class="label">Exempt? </p>
            <p class="value-NI"> <?php echo $exempt; ?> </p> <br>

            <br> <br>
            <br> <br>
            <hr style="margin-bottom: 0px;">
            <br>
            <button id="back" style="width: 40%; padding: auto;" class="button" onclick="backRedirect()"> Back </button>
            <button id="edit" style="width: 40%; padding: auto;" class="button" onclick="editRedirect()"> Edit </button>

        </div>
    </div>
</body>

<script>
    function backRedirect() {
        window.location.href = "index";
    }

    function editRedirect() {
        window.location.href = "edit?user=<?php echo $nban; ?>";
    }
</script>
