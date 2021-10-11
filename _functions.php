<?php
/**
 * PHP Simple Server Health Functions
 */


// Prevent direct load
if (!defined('PSSH')) {
    header("HTTP/1.1 404 Not Found");
    exit();
}

/**
 * Get mirotime for calculating page load speed
 *
 * @return array|mixed
 */
function microtimer()
{
    // Page timer
    $time = microtime();
    $time = explode(' ', $time);
    $time = $time[1] + $time[0];
    return $time;
}

/**
 * Calculate page load time
 *
 * @param $startTime
 * @param $endTime
 * @return string
 */
function getLoadTime($startTime, $endTime)
{

    global $errorStatus, $speedStatus, $speedError;

    $totalTime = round(($endTime - $startTime), 4);

    $loadTime = "";
    $speedStatus = "";
    $speedError = "";

    if ($totalTime >= 1) {
        $loadTime .= "<span style='color: #" . RED . ";'>";
        $speedStatus = "<span style='color: #" . RED . ";'>SLOW</span>";
    } else if ($totalTime >= 0.3) {
        $loadTime .= "<span style='color: #" . ORANGE . ";'>";
        $speedStatus = "<span style='color: #" . ORANGE . ";'>OK</span>";
    } else {
        $loadTime .= "<span style='color: #" . GREEN . ";'>";
        $speedStatus = "<span style='color: #" . GREEN . ";'>GREAT</span>";
    }
    $loadTime .= $totalTime . 's</span>';


    if ($totalTime >= 2) {
        $errorStatus = true;
        $speedStatus = "<span style='color: #" . RED . ";'>CATASTROPHICALLY SLOW!</span>";
        $speedError = "SLOW!";
    }

    return $loadTime;

}

function getSpeedStatus()
{
    global $speedStatus;
    return @$speedStatus;
}

function getSpeedError()
{
    global $speedError;
    return @$speedError;
}

/**
 * Get apache status
 *
 * If you can see the page then apache is A-OK
 */
function apacheStatus()
{
    return "<span style='color: #" . GREEN . ";'>OK</span>";
}

/**
 * Get mysql status
 *
 * @return string
 */
function mysqlStatus()
{

    global $errorStatus, $mysqlError, $mysqlInfo;

    $status = "";
    $mysqlError = "";

    $link = @mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

    if (!$link) {

        http_response_code(500);
        $errorStatus = true;

        $status = "<span style='color: #" . RED . ";'>BORKED</span>";
        $mysqlError .= "<span style='color: #" . RED . ";'>Error: Unable to connect to MySQL.</span><br />";
        if (!empty($_GET['debug'])) {
            $mysqlError .= "Debugging errno: " . mysqli_connect_errno() . "<br />";
            $mysqlError .= "Debugging error: " . mysqli_connect_error() . "<br />";
            $mysqlError .= "Info: " . mysqli_get_host_info($link);
        }
    } else {
        $status = "<span style='color: #" . GREEN . ";'>OK</span>";
    }

    @mysqli_close($link);

    return $status;
}

/**
 * Get any MySQL errors
 *
 * @return mixed
 */
function mysqlError()
{
    global $mysqlError;
    return $mysqlError;
}

/**
 * Get time it takes to make a simple MYSQL query
 */
function getMysqlQueryTime() {

    $errorText = "<span style='color: #" . RED . ";'>Failed to get query time</span>";

    global $mysqlError;
    if (!empty($mysqlError)) {
        return $errorText;
    }

    $time = microtime();
    $time = explode(' ', $time);
    $startTime = $time[1] + $time[0];


    try {
        $link = @mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
        $result = mysqli_query($link, "SELECT NOW()");
        @mysqli_close($link);
    }
    catch (Exception $e) {
        return $errorText;
    }

    $endTime = microtimer();
    $totalTime = round(($endTime - $startTime), 6);
    return $totalTime. ".s";
}


/**
 * Get CPU load
 *
 * @param int $cores
 * @return string
 */
function cpuLoad($cores = 1)
{

    global $errorStatus, $cpuErrors;

    $cpuLoad = "";

    $load = sys_getloadavg();

    $cpuLoad .= "1min(" . printLoad($load[0], $cores) . ") ";
    $cpuLoad .= "5min(" . printLoad($load[1], $cores) . ") ";
    $cpuLoad .= "15min(" . printLoad($load[2], $cores) . ")";

    if (($load[0] / $cores) >= 1) {
        http_response_code(500);
        $cpuErrors = "<span style='color: #" . RED . ";'>High CPU load</span>";
        $errorStatus = true;
    }

    return $cpuLoad;
}

/**
 * Average load per core
 *
 * @param int $cores
 * @return string
 */
function avgLoadPerCore($cores = 1)
{
    $load = sys_getloadavg();
    $lastResult = $load[0];

    $perCore = $lastResult / $cores;

    return printLoad($perCore, 1);
}

/**
 * Get CPU load errors
 *
 * @return mixed
 */
function getCpuErrors()
{
    global $cpuErrors;
    return @$cpuErrors;
}

function getRam()
{

    global $errorStatus, $ramTotal, $ramFree, $ramAvailable, $swapTotal, $swapFree;

    $fh = fopen(MEM_INFO, 'r');
    $ramTotal = false;
    $ramFree = false;
    $ramAvailable = false;
    $swapTotal = false;
    $swapFree = false;
    while ($line = fgets($fh)) {
        $pieces = array();
        if (preg_match('/^MemTotal:\s+(\d+)\skB$/', $line, $pieces)) {
            $ramTotal = $pieces[1];
        }
        if (preg_match('/^MemFree:\s+(\d+)\skB$/', $line, $pieces)) {
            $ramFree = $pieces[1];
        }
        if (preg_match('/^MemAvailable:\s+(\d+)\skB$/', $line, $pieces)) {
            $ramAvailable = $pieces[1];
        }
        if (preg_match('/^SwapTotal:\s+(\d+)\skB$/', $line, $pieces)) {
            $swapTotal = $pieces[1];
        }
        if (preg_match('/^SwapFree:\s+(\d+)\skB$/', $line, $pieces)) {
            $swapFree = $pieces[1];
        }
    }
    fclose($fh);
}

function totalRam()
{
    global $errorStatus, $ramTotal, $ramFree, $ramAvailable, $swapTotal, $swapFree;
    return convert(@$ramTotal * 1000);
}

function freeRam()
{
    global $errorStatus, $ramTotal, $ramFree, $ramAvailable, $swapTotal, $swapFree;
    return convert(@$ramFree * 1000);
}

function availableRam()
{
    global $errorStatus, $ramTotal, $ramFree, $ramAvailable, $swapTotal, $swapFree, $ramError;

    if ($ramAvailable === false) {
        return false;
    }

    $available = "";

    $ram10 = $ramTotal / 10;
    $ram25 = $ramTotal / 4;

    if ($ramAvailable < $ram10) {
        $available .= "<span style='color: #" . RED . ";'>";
        http_response_code(500);
        $errorStatus = true;
        $ramError = "Less then 10% RAM available";

    } else if ($ramAvailable < $ram25) {
        $available .= "<span style='color: #" . ORANGE . ";'>";
    } else {
        $available .= "<span style='color: #" . GREEN . ";'>";
    }
    $available .= convert($ramAvailable * 1000) . "</span>";

    return $available;
}

function totalSwap()
{
    global $errorStatus, $ramTotal, $ramFree, $ramAvailable, $swapTotal, $swapFree, $ramError;

    if ($swapTotal === false) {
        return false;
    }

    return convert($swapTotal * 1000);
}

function freeSwap()
{
    global $errorStatus, $ramTotal, $ramFree, $ramAvailable, $swapTotal, $swapFree, $ramError;

    if ($swapFree === false) {
        return false;
    }

    $swap10 = $swapTotal / 10; // 10%
    $swap25 = $swapTotal / 4; // 25%

    $available = "";

    if ($swapFree < $swap10) {
        $available .= "<span style='color: #" . RED . ";'>";
        http_response_code(500);
        $errorStatus = true;
        $ramError = "Less then 10% SWAP available";
    } else if ($swapFree < $swap25) {
        $available .= "<span style='color: #" . ORANGE . ";'>";
    } else {
        $available .= "<span style='color: #" . GREEN . ";'>";
    }
    $available .= convert($swapFree * 1000) . "</span>";

    return $available;

}

function getRamErrors()
{
    global $ramError;
    return @$ramError;
}

function htmlHeader()
{
    ?>
    <!doctype html>
    <html lang=en-au><!-- Straya -->
                     <!-- Header -->
    <head>
        <meta charset=utf-8/>
        <meta name="robots" content="noindex"/>
        <meta name="googlebot" content="noindex"/>
        <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

        <title>Simple Server Health - <?= $_SERVER['HTTP_HOST']; ?></title>

        <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css"
              integrity="sha384-Gn5384xqQ1aoWXA+058RXPxPg6fy4IWvTNh0E263XmFcJlSAwiGgFAW/dAiS6JXm" crossorigin="anonymous">

        <style>
            .card-columns {
                -webkit-column-count: 1;
                -moz-column-count: 1;
                column-count: 1;
            }

            @media (min-width: 576px) {
                .card-columns {
                    -webkit-column-count: 2;
                    -moz-column-count: 2;
                    column-count: 2;
                }
            }

            .weight-normal {
                font-weight: normal !important;
            }
        </style>

    </head>
    <!-- Header -->
    <body>
    <br/>
    <div class="container">
        <header>
            <h3>Simple Server Health</h3>
            <h6 class="text-muted">SITE: <span class="weight-normal"><?= $_SERVER['HTTP_HOST']; ?></span></h6>
            <h6 class="text-muted">IP: <span class="weight-normal"><?= $_SERVER['SERVER_ADDR']; ?></span></h6>
            <?php if (SHOW_VERSION_INFO) { ?>
                <h6 class="text-muted">OS: <span class="weight-normal"><?= getServerVersion(); ?></span></h6>
            <?php } ?>
        </header>
    </div>
    <br/>
    <?php
}

function htmlFooter()
{
    ?>

    <!-- Footer -->
    <footer class="page-footer font-small blue pt-4">

        <!-- Copyright -->
        <div class="footer-copyright text-center py-3">© <?= date("Y"); ?> Copyright:
            <a href="https://deejdesigns.com" target="_blank"> DeejDesigns.com</a>
        </div>
        <!-- Copyright -->

    </footer>
    <!-- Footer -->

    <!-- Scripts -->
    <script src="https://code.jquery.com/jquery-3.2.1.slim.min.js"
            integrity="sha384-KJ3o2DKtIkvYIK3UENzmM7KCkRr/rE9/Qpg6aAZGJwFDMVNA/GpGFF93hXpG5KkN" crossorigin="anonymous"></script>
    <!-- <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.12.9/umd/popper.min.js" integrity="sha384-ApNbgh9B+Y1QKtv3Rn7W3mgPxhU9K/ScQsAP7hUibX39j7fakFPskvXusvfa0b4Q" crossorigin="anonymous"></script> -->
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/js/bootstrap.min.js"
            integrity="sha384-JZR6Spejh4U02d8jOt6vLEHfe/JQGiRRSQQxSfFWpi1MquVdAyjUar5+76PVCmYl" crossorigin="anonymous"></script>

    </body>
    </html>
    <?php
}


/**
 * Display load
 *
 * @param $load
 * @param int $cores
 * @return string
 */
function printLoad($load, $cores = 1)
{

    if (($load / $cores) < 0.7) {
        return "<span style='color: #" . GREEN . ";'>$load</span>";
    } else if (($load / $cores) <= 1.0) {
        return "<span style='color: #" . ORANGE . ";'>$load</span>";
    } else {
        return "<span style='color: #" . RED . ";'>$load</span>";
    }

}

/**
 * Bits to something more human readable
 *
 * @param $size
 * @return string
 */
function convert($size)
{
    if (empty($size)) {
        return "0 b";
    }
    $unit = array('b', 'kb', 'mb', 'gb', 'tb', 'pb');
    return @round($size / pow(1024, ($i = floor(log($size, 1024)))), 2) . ' ' . $unit[$i];
}


/**
 * Returns the number of available CPU cores
 *
 *  Should work for Linux, Windows, Mac & BSD
 *
 * @return integer
 */
function num_cpus()
{
    $numCpus = 1;
    if (is_file(CPU_INFO)) {
        $cpuinfo = file_get_contents(CPU_INFO);
        preg_match_all('/^processor/m', $cpuinfo, $matches);
        $numCpus = count($matches[0]);
    } else if ('WIN' == strtoupper(substr(PHP_OS, 0, 3))) {
        $process = @popen('wmic cpu get NumberOfCores', 'rb');
        if (false !== $process) {
            fgets($process);
            $numCpus = intval(fgets($process));
            pclose($process);
        }
    } else {
        $process = @popen('sysctl -a', 'rb');
        if (false !== $process) {
            $output = stream_get_contents($process);
            preg_match('/hw.ncpu: (\d+)/', $output, $matches);
            if ($matches) {
                $numCpus = intval($matches[1][0]);
            }
            pclose($process);
        }
    }

    return $numCpus;
}

function getServerVersion()
{
    return @file_get_contents(VERSION_INFO);
}

/**
 * Deej debug / dump & die
 */
function dd()
{
    $args = func_get_args();
    foreach ($args as $arg) {
        echo "<pre>";
        var_dump($arg);
        echo "</pre>";
    }
    die();
}