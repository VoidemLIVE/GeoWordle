<?php
function getClientIP() {
    $ipaddress = '';
    if (isset($_SERVER['HTTP_CLIENT_IP']))
        $ipaddress = $_SERVER['HTTP_CLIENT_IP'];
    else if(isset($_SERVER['HTTP_X_FORWARDED_FOR']))
        $ipaddress = $_SERVER['HTTP_X_FORWARDED_FOR'];
    else if(isset($_SERVER['HTTP_X_FORWARDED']))
        $ipaddress = $_SERVER['HTTP_X_FORWARDED'];
    else if(isset($_SERVER['HTTP_FORWARDED_FOR']))
        $ipaddress = $_SERVER['HTTP_FORWARDED_FOR'];
    else if(isset($_SERVER['HTTP_FORWARDED']))
        $ipaddress = $_SERVER['HTTP_FORWARDED'];
    else if(isset($_SERVER['REMOTE_ADDR']))
        $ipaddress = $_SERVER['REMOTE_ADDR'];
    else
        $ipaddress = 'UNKNOWN';
    return $ipaddress;
}

function logToConsole($data) {
    echo "<script>console.log('{$data}')</script>";
}

// DEBUG MENU:
$debug = isset($_GET['d']) ? $_GET['d'] : '';
if ($debug){
    echo '<script>document.getElementById("debugMenu").style.display = "flex";</script>';
}

//error_reporting(E_ALL);
//ini_set('display_errors', 1);
ini_set('session.save_path', '/var/www/html/geole/sessions');
ini_set('session.name', 'GeoWordle');
session_start();
$_SESSION['timestamp'] = time();
$_SESSION['user_ip'] = getClientIP();
$currentCountry = fopen("currentCountry.json", "r") or die("Unable to open current country file!");
$currentCountryData = fread($currentCountry, filesize("currentCountry.json"));
fclose($currentCountry);
$currentCountryDataArray = json_decode($currentCountryData, true);
$countryList = fopen("sorted_countries.json", "r") or die("Unable to open countries file!");
$countryData = fread($countryList, filesize("sorted_countries.json"));
fclose($countryList);
$countryDataArray = json_decode($countryData, true);
$_SESSION['tries'] = [];

if (!isset($_SESSION['countryData'])) {
    $_SESSION['countryData'] = array();
}


if (!isset($_SESSION['turn'])) {
    $_SESSION['turn'] = 0;
}

if ($_SESSION['turn'] >= 1) {
    $_SESSION['hint1'] = true;
    echo '<script>document.getElementById("hint1Lock").style.display = "none";</script>';
}

if ($_SESSION['turn'] >= 3) {
    $_SESSION['hint2'] = true;
    echo '<script>document.getElementById("hint2Lock").style.display = "none";</script>';
}

if ($_SESSION["won"] || $_SESSION["lost"]) {
    echo '<script>document.getElementById("copyBtn").style.display = "flex";</script>';
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['guessBtn'])) {
        $turn = $_SESSION['turn'];


        if ($turn >= 8) {
            $_SESSION['lost'] = true;

            echo '<script>document.getElementById("messageDiv").style.display = "flex";</script>';
        } else {
            if ($turn >= 1) {
                $_SESSION['hint1'] = true;
                echo '<script>document.getElementById("hint1Lock").style.display = "none";</script>';
            }
            
            if ($turn >= 3) {
                $_SESSION['hint2'] = true;
                echo '<script>document.getElementById("hint2Lock").style.display = "none";</script>';
            }
            $index = $turn;
            $index1 = array_search($_POST['chosenCountry'], array_column($countryDataArray, 'Country'));
            $_SESSION['countryData'][$index] = $countryDataArray[$index1];

            $_SESSION['turn'] = $turn + 1;

            if ($countryDataArray[$index1]["Country"] == $currentCountryDataArray["Country"] ) {
                $_SESSION['won'] = true;
                echo '<script>document.getElementById("messageDivWin").style.display = "flex";</script>';
            }
        }
    } elseif (isset($_POST['restartBtn'])) {
        session_destroy();
        session_start();
        $_SESSION['lost'] = false;
        header("Location: index.php");
    }
}

?>



<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Geo Wordle</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css"/>
    <style>

        body {
            margin: 0;
        }
        .grid-container {
            display: grid;
            grid-template-columns: repeat(6, 1fr);
            gap: 4px;
        }

        .grid-item {
            background-color: #d1d5db;
            height: 50px;
            display: flex;
            justify-content: center;
            align-items: center;
            font-size: 1.25rem;
        }

        #messageDiv {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            color: white;
            justify-content: center;
            align-items: center;
            z-index: 9999;
        }
        
        #messageContent {
            background-color: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.3);
        }

        #dismissButton {
            margin-top: 20px;
            padding: 10px 20px;
            background-color: #4CAF50;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color 0.3s;
        }

        #dismissButton:hover {
            background-color: #45a049;
        }



        #messageDivWin {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            color: white;
            justify-content: center;
            align-items: center;
            z-index: 9999;
        }
        
        #messageContentWin {
            background-color: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.3);
        }

        #dismissButtonWin {
            margin-top: 20px;
            padding: 10px 20px;
            background-color: #4CAF50;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color 0.3s;
        }

        #dismissButtonWin:hover {
            background-color: #45a049;
        }
        #shareButtonWin {
            margin-top: 20px;
            padding: 10px 20px;
            background-color: #4CAF50;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color 0.3s;
        }

        #shareButtonWin:hover {
            background-color: #45a049;
        }


        
        #messageDivHint1 {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            color: white;
            justify-content: center;
            align-items: center;
            z-index: 9999;
        }
        
        #messageContentHint1 {
            background-color: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.3);
        }

        #dismissButtonHint1 {
            margin-top: 20px;
            padding: 10px 20px;
            background-color: #4CAF50;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color 0.3s;
        }

        #dismissButtonHint1:hover {
            background-color: #45a049;
        }



        #messageDivHint2 {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            color: white;
            justify-content: center;
            align-items: center;
            z-index: 9999;
        }
        
        #messageContentHint2 {
            background-color: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.3);
        }

        #dismissButtonHint2 {
            margin-top: 20px;
            padding: 10px 20px;
            background-color: #4CAF50;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color 0.3s;
        }

        #dismissButtonHint2:hover {
            background-color: #45a049;
        }


        #debugMenu {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            color: white;
            justify-content: center;
            align-items: center;
            z-index: 9999;
        }
        
        #messageContentDebugMenu {
            background-color: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.3);
        }

        #dismissButtonDebugMenu {
            margin-top: 20px;
            padding: 10px 20px;
            background-color: #4CAF50;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color 0.3s;
        }

        #dismissButtonDebugMenu:hover {
            background-color: #45a049;
        }

        .tooltip {
            display: none;
            position: absolute;
            bottom: calc(100% + 5px);
            left: 50%;
            transform: translateX(-50%);
            background-color: rgba(0, 0, 0, 0.8);
            color: #fff;
            padding: 5px 10px;
            border-radius: 5px;
            font-size: 12px;
            white-space: nowrap;
            width: auto;
            min-width: 100px;
            height: auto;
            min-height: 30px; 
        }



    </style>
</head>

<body class="bg-gray-100 p-8">
<div class="max-w-lg mx-auto bg-white rounded shadow-md p-8">
    <h1 class="text-3xl font-bold mb-6">Geo Wordle</h1>
    <form id="guessForm" method="post">
        <label for="chosenCountry" class="block text-sm font-medium text-gray-700">Select a country:</label>
        <div class="relative">
            <select name="chosenCountry" id="chosenCountry"
                class="mt-1 block w-full md:w-96 py-2 pl-3 pr-10 border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md appearance-none">
                <?php
                foreach ($countryDataArray as $country) {
                    $selected = ($country == reset($countryDataArray)) ? 'selected' : '';
                    echo '<option value="' . $country["Country"] . '" ' . $selected . '>' . $country["Country"] . '</option>';
                }
                ?>
            </select>
            <div class="absolute inset-y-0 right-0 flex items-center pr-2 pointer-events-none">
                <svg class="h-5 w-5 text-gray-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"
                    fill="currentColor" aria-hidden="true">
                    <path fill-rule="evenodd"
                        d="M10 12a1 1 0 01-.7-.29l-4-4a1 1 0 111.4-1.42L10 9.58l3.3-3.3a1 1 0 111.4 1.42l-4 4a1 1 0 01-.7.3z"
                        clip-rule="evenodd" />
                </svg>
            </div>
        </div>
        <button type="submit" name="guessBtn" class="mt-4 inline-flex items-center px-4 py-2 border border-transparent text-base font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500" <?php if(isset($_SESSION['won']) && $_SESSION['won']) echo 'disabled'; ?>>
            Guess
            <span class="ml-2 bg-indigo-400 px-2 py-1 rounded text-sm font-bold"><?php echo $_SESSION['turn'] ?>/8 Guesses</span>
        </button>
    </form>
    <div class="flex space-x-4 mt-4">
    <button type="submit" name="hint1Btn" id="hint1Btn" class="flex items-center px-4 py-2 border border-transparent text-base font-medium rounded-md shadow-sm text-white bg-pink-600 hover:bg-pink-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-pink-500" <?php if(!isset($_SESSION['hint1']) || !$_SESSION['hint1']) echo 'disabled'; ?>>
        Hint 1
        <span class="tooltip" <?php if(isset($_SESSION['hint1']) && !$_SESSION['hint1']) echo 'style="display:inline;"'; ?>>Guess twice to unlock</span>
        <img id="hint1Lock" src="lock.png" alt="Hint 1" class="w-4 h-4 ml-2" />
    </button>
    <button type="submit" name="hint2Btn" id="hint2Btn" class="flex items-center px-4 py-2 border border-transparent text-base font-medium rounded-md shadow-sm text-white bg-pink-600 hover:bg-pink-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-pink-500" <?php if(!isset($_SESSION['hint2']) || !$_SESSION['hint2']) echo 'disabled'; ?>>
        Hint 2
        <span class="tooltip" <?php if(isset($_SESSION['hint2']) && !$_SESSION['hint2']) echo 'style="display:inline;"'; ?>>Guess four times to unlock</span>
        <img id="hint2Lock" src="lock.png" alt="Hint 2" class="w-4 h-4 ml-2" />
    </button>
    <button type="submit" name="copyBtn" id="copyBtn" class="flex items-center px-4 py-2 border border-transparent text-base font-medium rounded-md shadow-sm text-white bg-yellow-600 hover:bg-yellow-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-yellow-500" style="display:none;">
        Copy Results
    </button>
    </div>

</div>


    <div class="grid-container mt-8">
        <div class="grid-item">Country</div>
        <div class="grid-item">Region</div>
        <div class="grid-item">Population</div>
        <div class="grid-item">Area</div>
        <div class="grid-item">Population Density</div>
        <div class="grid-item">GDP Per Capita</div>
        <?php
            for ($i = 0; $i < $_SESSION['turn']; $i++) {
                if (isset($_SESSION['countryData'][$i])) {
                    $nameCorrect = ($_SESSION['countryData'][$i]["Country"] == $currentCountryDataArray["Country"]) ? " ✅" : " ❌";
                    $regionCorrect = ($_SESSION['countryData'][$i]["Region"] == $currentCountryDataArray["Region"]) ? " ✅" : " ❌";
                    $populationCorrect = ($_SESSION['countryData'][$i]["Population"] == $currentCountryDataArray["Population"]) ? " ✅" : ($_SESSION['countryData'][$i]["Population"] > $currentCountryDataArray["Population"] ? " ⬇️" : " ⬆️");
                    $areaCorrect = ($_SESSION['countryData'][$i]["Area"] == $currentCountryDataArray["Area"]) ? " ✅" : ($_SESSION['countryData'][$i]["Area"] > $currentCountryDataArray["Area"] ? " ⬇️" : " ⬆️");
                    $popDensCorrect = ($_SESSION['countryData'][$i]["popDens"] == $currentCountryDataArray["popDens"]) ? " ✅" : ($_SESSION['countryData'][$i]["popDens"] > $currentCountryDataArray["popDens"] ? " ⬇️" : " ⬆️");
                    $gdpCorrect = ($_SESSION['countryData'][$i]["GDPPC"] == $currentCountryDataArray["GDPPC"]) ? " ✅" : ($_SESSION['countryData'][$i]["GDPPC"] > $currentCountryDataArray["GDPPC"] ? " ⬇️" : " ⬆️");
                    $_SESSION['tries'][$i] = [$nameCorrect, $regionCorrect, $populationCorrect, $areaCorrect, $popDensCorrect, $gdpCorrect];

                    echo '<div class="grid-item" id="CN' . ($i + 1) . '">' . $_SESSION['countryData'][$i]["Country"] . $nameCorrect . '</div>';
                    echo '<div class="grid-item" id="R' . ($i + 1) . '">' . $_SESSION['countryData'][$i]["Region"] . $regionCorrect . '</div>';
                    echo '<div class="grid-item" id="P' . ($i + 1) . '">' . number_format($_SESSION['countryData'][$i]["Population"]) . $populationCorrect . '</div>';
                    echo '<div class="grid-item" id="A' . ($i + 1) . '">' . number_format($_SESSION['countryData'][$i]["Area"]) . " mi<sup>2</sup>" . $areaCorrect . '</div>';
                    echo '<div class="grid-item" id="PD' . ($i + 1) . '">' . number_format($_SESSION['countryData'][$i]["popDens"]) . " per mi<sup>2</sup>" . $popDensCorrect .'</div>';
                    echo '<div class="grid-item" id="GPC' . ($i + 1) . '">' . "$" . number_format($_SESSION['countryData'][$i]["GDPPC"]) . $gdpCorrect . '</div>';
                }
            }

        ?>
    </div>
    <footer class="bg-gray-300 py-4 mt-8 fixed bottom-0 left-0 w-full">
        <div class="flex justify-center items-center text-gray-600 text-lg">
            <span>&copy; <?php echo date("Y"); ?> <a href="https://voidem.com" target="_blank" class="hover:text-gray-800">Voidem</a> / <a href="https://github.com/voidemlive/geowordle" target="_blank" class="hover:text-gray-800">Geo Wordle</a></span>
        </div>
    </footer>


    <?php
    /*


    END OF ACTUAL PAGE HTML!!!!


    */
    ?>



    <div id="messageDiv">
        <?php if (isset($_SESSION['lost']) && $_SESSION['lost']): ?>
        <div id="messageContent" class="text-center text-gray-800">
            <p class="text-2xl mb-4">💔 You lost 💔</p>
            <p class="text-xl">The country was:</p>
            <p class="text-lg font-bold"><?php echo $currentCountryDataArray["Country"]  ?>!</p>
            <div class="flex justify-center items-center">
                <button id="dismissButton" class="mr-4 px-6 py-2 bg-green-500 hover:bg-green-600 text-white font-bold rounded">Dismiss</button>
                <button id="shareButtonWin" class="mr-4 px-6 py-2 bg-green-500 hover:bg-green-600 text-white font-bold rounded">Share</button>
            </div>
        </div>
        <?php endif; ?>
    </div>

    <div id="messageDivWin">
        <?php if (isset($_SESSION['won']) && $_SESSION['won']): ?>
        <div id="messageContentWin" class="text-center text-gray-800">
            <p class="text-2xl mb-4">🎉 You won! 🎉</p>
            <p class="text-xl">The country was:</p>
            <p class="text-lg font-bold"><?php echo $currentCountryDataArray["Country"]  ?>!</p>
            <div class="flex justify-center items-center">
                <button id="dismissButtonWin" class="mr-4 px-6 py-2 bg-green-500 hover:bg-green-600 text-white font-bold rounded">Dismiss</button>
                <button id="shareButtonWin" class="mr-4 px-6 py-2 bg-green-500 hover:bg-green-600 text-white font-bold rounded">Share</button>
            </div>
        </div>
        <?php endif; ?>
    </div>

    <div id="messageDivHint1" style="display:none;">
        <div id="messageContentHint1" class="text-center text-gray-800">
            <p class="text-2xl mb-4">Hint 1</p>
            <p class="text-xl">The country's region:</p>
            <p class="text-lg font-bold"><?php echo $currentCountryDataArray["Region"]  ?></p>
            <div class="flex justify-center items-center">
                <button id="dismissButtonHint1" class="mr-4 px-6 py-2 bg-green-500 hover:bg-green-600 text-white font-bold rounded">Dismiss</button>
            </div>
        </div>
    </div>
    <div id="messageDivHint2" style="display:none;">
        <div id="messageContentHint2" class="text-center text-gray-800">
            <p class="text-2xl mb-4">Hint 2</p>
            <p class="text-xl">First letter of the country's name:</p>
            <p class="text-lg font-bold"><?php echo $currentCountryDataArray["Country"][0]  ?></p>
            <div class="flex justify-center items-center">
                <button id="dismissButtonHint2" class="mr-4 px-6 py-2 bg-green-500 hover:bg-green-600 text-white font-bold rounded">Dismiss</button>
            </div>
        </div>
    </div>


    <div id="debugMenu" style="display:none;">
        <div id="messageContentDebugMenu" class="text-center text-gray-800">
            <p class="text-2xl mb-4">Debug Menu</p>
            <p class="text-xl">Your IP: <?php echo getClientIP()?></p>
            <p class="text-xl">Session ID: <?php echo 'sess_' . session_id(); ?></p>
            <p class="text-xl">Turns: <?php echo $_SESSION['turn']?></p>
            <p class="text-xl">Won: <?php echo ($_SESSION['won'] == 1) ? 'TRUE' : 'FALSE'; ?></p>
            <p class="text-xl">Lost: <?php echo ($_SESSION['lost'] == 1) ? 'TRUE' : 'FALSE'; ?></p>
            <div class="flex justify-center items-center">
                <button id="dismissButtonDebugMenu" class="mr-4 px-6 py-2 bg-green-500 hover:bg-green-600 text-white font-bold rounded">Dismiss</button>
            </div>
        </div>
    </div>

    <?php
        $string2 = '';
        foreach ($_SESSION['tries'] as $try) {
            $string2 .= implode($try) . "\n";
        }
    ?>

    <?php 
    # BUTTONS SCRIPTS BELOW
    ?>

    <script>
        document.getElementById('hint1Btn').addEventListener('click', function() {
            var hint1Truth = '<?php echo $_SESSION['hint1']; ?>';
            if (hint1Truth) {
                document.getElementById('messageDivHint1').style.display = 'flex';
            }
        });
    </script> 
    <script>
        document.getElementById('hint2Btn').addEventListener('click', function() {
            document.getElementById('messageDivHint2').style.display = 'flex';
        });
    </script> 
    <script>
        document.getElementById('copyBtn').addEventListener('click', function() {
            var copy1 = `<?php echo str_replace(PHP_EOL, '\n', str_replace("'", "\\'", $string2)); ?>`;
            var copyHead = 'Voidem Geo Wordle \n\n';
            var copyFoot = '\nPlay now at https://geo.voidem.com';
            var copy = copyHead + copy1 + copyFoot;
            navigator.clipboard.writeText(copy);
            document.getElementById('copyBtn').innerHTML = 'Copied!';
        });
    </script> 




    <script>
        document.getElementById('dismissButton').addEventListener('click', function() {
            document.getElementById('messageDiv').style.display = 'none';
        });
    
    </script>

    <script>
        document.getElementById('dismissButton').addEventListener('click', function() {
            document.getElementById('messageDiv').style.display = 'none';
        });
    
    </script>



    <script>
        document.getElementById('dismissButtonHint1').addEventListener('click', function() {
            document.getElementById('messageDivHint1').style.display = 'none';
        });
    </script>
    <script>
        document.getElementById('dismissButtonHint2').addEventListener('click', function() {
            document.getElementById('messageDivHint2').style.display = 'none';
        });
    </script>

    <script>
        document.getElementById('dismissButtonWin').addEventListener('click', function() {
            document.getElementById('messageDivWin').style.display = 'none';
            document.getElementById('copyBtn').style.display = 'flex';
        });
    </script>
    <script>
        document.getElementById('dismissButtonDebugMenu').addEventListener('click', function() {
            document.getElementById('debugMenu').style.display = 'none';
        });
    </script>
    <script>
        document.getElementById('shareButtonWin').addEventListener('click', function() {
            var copy1 = `<?php echo str_replace(PHP_EOL, '\n', str_replace("'", "\\'", $string2)); ?>`;
            var copyHead = 'Voidem Geo Wordle \n\n';
            var copyFoot = '\nPlay now at https://geo.voidem.com';
            var copy = copyHead + copy1 + copyFoot;
            navigator.clipboard.writeText(copy);
            document.getElementById('shareButtonWin').innerHTML = 'Copied!';
        });
    </script>

    <script>
            document.getElementById('hint1Btn').addEventListener('mouseover', function() {
        var hint1Btn = document.getElementById('hint1Btn');
        var tooltip = document.querySelector('#hint1Btn .tooltip');
        var hint1Truth = '<?php echo $_SESSION['hint1']; ?>';
        
        if (!hint1Truth) {
            var rect = hint1Btn.getBoundingClientRect();
            var left = rect.left + (rect.width / 2);
            var top = rect.top + rect.height + 5; 
            tooltip.style.left = left + 'px';
            tooltip.style.top = top + 'px';
            tooltip.style.display = 'inline';
        }
    });

    document.getElementById('hint1Btn').addEventListener('mouseout', function() {
        document.querySelector('#hint1Btn .tooltip').style.display = 'none';
    });
    </script>

    <script>
            document.getElementById('hint2Btn').addEventListener('mouseover', function() {
            var hint1Btn = document.getElementById('hint2Btn');
            var tooltip = document.querySelector('#hint2Btn .tooltip');
            var hint1Truth = '<?php echo $_SESSION['hint2']; ?>';
            
            if (!hint1Truth) {
                var rect = hint1Btn.getBoundingClientRect();
                var left = rect.left + (rect.width / 2);
                var top = rect.top + rect.height + 5; 
                tooltip.style.left = left + 'px';
                tooltip.style.top = top + 'px';
                tooltip.style.display = 'inline';
            }
        });

        document.getElementById('hint2Btn').addEventListener('mouseout', function() {
            document.querySelector('#hint2Btn .tooltip').style.display = 'none';
        });
        </script>

</body>
</html>
