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
</style>

<body>
    <div class="form">
        <div style="padding: 1.25rem; padding-bottom: 0px;">

            <h1> Evasion Checker </h1>
            <hr>
            <br>
            <form action="view" method="post" autocomplete="off">
                <label for="nban">Username: </label>
                <input type="text" name="nban" maxlength="16"> <br> <br>
                <br>
                <hr style="margin-bottom: 0px;">
                <input id="submit" style="margin-top: 20px; width: 40%;" class="button" type="submit" value="Submit" name="submit">
            </form>
            <button id="new" style="width: 40%" class="button" onclick="newRedirect()"> New </button>

        </div>
    </div>
</body>

<script>
    function newRedirect() {
        window.location.href = "new";
    }
</script>
