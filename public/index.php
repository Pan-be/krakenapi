<?php

require_once __DIR__ . '/../autoload.php';


use Core\Config;

$pairs = Config::load('pairs');

if (!is_array($pairs)) {
    die('Invalid pairs.json format.');
}

$defaultSelected = [
    'Pf_ETHUSD',
    'Pf_XBTUSD',
    'PF_ATOMUSD',
    'Pf_LTCUSD'
];

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Imperium 1.1.0</title>
</head>

<style>
    /* vt323-regular - latin_latin-ext */
    @font-face {
        font-display: swap;
        /* Check https://developer.mozilla.org/en-US/docs/Web/CSS/@font-face/font-display for other options. */
        font-family: 'VT323';
        font-style: normal;
        font-weight: 400;
        src: url('./fonts/vt323-v18-latin_latin-ext-regular.woff2') format('woff2');
        /* Chrome 36+, Opera 23+, Firefox 39+, Safari 12+, iOS 10+ */
    }

    body {
        background-color: #18181a;
        font-family: 'VT323';
        color: whitesmoke;
        display: grid;
        justify-items: center;
        text-align: center;
        padding: 40px;
    }

    .section {
        margin-bottom: 25px;
    }

    .pairs-container {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 10px;
    }

    button {
        padding: 10px 20px;
        cursor: pointer;
    }
</style>

<body>

    <h1>Welcome to the path.<br>It won't be easy but in the end you'll find peace and wealth.</h1>
    <!-- <form method="POST" action="candles.php">

        <div class="section">
            <h3>Select pairs:</h3>
            <div class="pairs-container">
                <?php // foreach ($pairs as $pair): 
                ?>
                    <label>
                        <input type="checkbox" name="pairs[]" value="<?php //echo htmlspecialchars($pair) 
                                                                        ?>"
                            <?php //echo in_array($pair, $defaultSelected) ? 'checked' : '' 
                            ?>>
                        <?php //echo htmlspecialchars($pair) 
                        ?>
                    </label>
                <?php // endforeach; 
                ?>
            </div>
        </div>

        <div class="section">
            <h3>Select interval:</h3>
            <select name="interval" required>
                <option value="1h">1h</option>
                <option value="4h">4h</option>
            </select>
        </div>

        <div class="section">
            <h3>Select start timestamp:</h3>
            <input type="datetime-local" name="start_date" required>
        </div>

        <div class="section">
            <button type="submit">Fetch candles</button>
        </div>

    </form> -->
    <?php
    $now = (new DateTime())->format('Y-m-d\TH:i');
    ?>

    <form method="POST" action="candles.php">

        <div class="section">
            <h3>Select pairs:</h3>
            <div class="pairs-container">
                <?php foreach ($pairs as $pair): ?>
                    <label>
                        <input type="checkbox" name="pairs[]" value="<?= htmlspecialchars($pair) ?>"
                            <?= in_array($pair, $defaultSelected) ? 'checked' : '' ?>>
                        <?= htmlspecialchars($pair) ?>
                    </label>
                <?php endforeach; ?>
            </div>
        </div>

        <div class="section">
            <h3>Select interval:</h3>
            <select name="interval" required>
                <option value="1h">1h</option>
                <option value="4h">4h</option>
            </select>
        </div>

        <div class="section">
            <h3>Number of candles:</h3>
            <input type="number" name="count" value="300" min="1" max="1000" required>
        </div>

        <div class="section">
            <h3>End timestamp (optional):</h3>
            <input type="datetime-local" name="start_date" value="<?= $now ?>">
            <small>If empty, current time will be used.</small>
        </div>

        <div class="section">
            <button type="submit">Fetch candles</button>
        </div>

    </form>

</body>

</html>