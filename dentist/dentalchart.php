<?php require_once '../db/config.php';
$query = "SELECT id, CONCAT(first_name, ' ', middle_name, ' ', last_name) AS full_name, emergencyname, emergencycontact,gender, mobile, email, username
          FROM users";
$result = mysqli_query($db, $query);
if (!$result) {
    die("Query Error: " . mysqli_error($db));
}

?>

<!doctype html>
<html>

<head>
    <title>Cavity Chart</title>
    <meta name=" author" content="Jade Allen Cook">
    <link href="style_dentalchart.css" rel="stylesheet" type="text/css" />
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://code.jquery.com/ui/1.13.2/jquery-ui.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/html2canvas@1.4.1/dist/html2canvas.min.js"></script>

</head>

<body style="display:flex; align-items: center; justify-content: center; flex-direction: column;">
    <h1>Dental Chart</h1>
    <div id="app">
        <!-- tops -->
        <div class="gum-num-container gum-l-container" id="top-gum-l-container">
            <div class="gum-label">L</div>
        </div>
        <div class="gum-num-container gum-f-container" id="top-gum-f-container">
            <div class="gum-label">F</div>
        </div>
        <div class="container" id="top-teeth"></div>
        <div class="container" id="bottom-teeth"></div>
        <div class="gum-num-container gum-f-container" id="bottom-gum-f-container">
            <div class="gum-label">F</div>
        </div>
        <div class="gum-num-container gum-l-container" id="bottom-gum-l-container">
            <div class="gum-label">L</div>
        </div>
        <!-- etc -->
        <div class="container" id="btn-container">
            <div class="btn" id="remove">Remove</div>
            <div class="btn" id="mand">MAND</div>
            <div class="btn" id="max">MAX</div>
            <div class="btn" id="reset">Tooth</div>
            <div class="btn" id="screw">Screw</div>
            <div class="btn" id="extraction">Extraction</div>
            <div class="btn" id="crown">Crown</div>
            <div class="btn" id="bridge">Bridge</div>
            <!-- cavity btns -->
            <div class="btn" id="clear">Clear</div>
            <div class="btn cav-btn" id="b">B/F</div>
            <div class="btn cav-btn" id="m">M</div>
            <div class="btn cav-btn" id="o">O/I</div>
            <div class="btn cav-btn" id="d">D</div>
            <div class="btn cav-btn" id="l">L</div>
            <div class="btn cav-btn" id="v">V</div>
            <!-- system btns -->
            <div class="btn" id="deselect">Deselect</div>
            <!-- beta 
                    <div class="btn" id="undo">Undo</div>
                -->
            <div class="btn" id="select-all">Select All</div>
            <div class="btn" id="red-blue"></div>
        </div>
    </div>

    <div id="#img-out"></div>

    <br />
    <form method="post">
        <input type="hidden" id="teeth" placeholder="Teeth Data" />

        <input type="hidden" id="cavity" placeholder="Cavity Data" />

        <input type="hidden" id="gumf" placeholder="Gum F Data" />

        <input type="hidden" id="guml" placeholder="Gum L Data" />
        <div style="display:flex; justify-content: center;">
            <a id="download" download="chart.png" href="#" class="outside-ctrls">Generate Image</a>
            <button type="submit">Save</button>
        </div>
    </form>
</body>

<footer>
    <script src="app.js"></script>
</footer>

</html>