<?php
/**
 * PHP Simple Server Health - What's going on with server resources?
 *
 * It aint pretty but it works...
 *
 * NOTE: Don't be a moron, password protect access to this on any publicly accessible server
 *
 * @author: Deej
 * @website: deejdesigns.com
 * @year: 2019
 *
 **/
require_once "_functions.php";
require_once "_conf.php";


// Prevent browsers caching this page
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

/**
 * Do all the processing before outputting anything as if there's any errors we'll return different apache response codes
 */

// Start timing script load time
$startTime = startTimer();

// Has an error occured?
$errorStatus = false;


/**
 * Apache
 */
$apacheStatus = apacheStatus();


/**
 * Server Load
 **/
$cores = num_cpus();
$serverLoad = cpuLoad($cores);
$avgLoadPerCore = avgLoadPerCore($cores);
$cpuErrors = getCpuErrors();


/**
 * System Memory
 **/
getRam();
$totalRam = totalRam();
$freeRam = freeRam();
$availableRam = availableRam();
$totalSwap = totalSwap();
$freeSwap = freeSwap();
$ramErrors = getRamErrors();


/**
 * Check MySQL connection
 **/
$mysqlStatus = mysqlStatus();
$mysqlError = mysqlError();


/**
 * Page load time by server
 */
$endTime = startTimer();
$loadTime = getLoadTime($startTime, $endTime);
$speedStatus = getSpeedStatus();
$speedError = getSpeedError();


// Lets get this party started
htmlHeader();
?>


    <div class="container">
		<?php if ($errorStatus) { ?>
            <div class="alert alert-danger" role="alert">
                <h1>BORKED!</h1>
            </div>
		<?php } ?>
        <div class="card-columns">
			
            
            <div class="card <?= !empty($cpuErrors) ? "alert-danger" : ""; ?>">
                <div class="card-body">
                    <h5 class="card-title">CPU</h5>
                    <h6 class="card-subtitle mb-2 text-muted">CORES: <?= $cores; ?></h6>
                    <h6 class="card-subtitle mb-2 text-muted">LOAD AVG: <?= $serverLoad; ?></h6>
                    <h6 class="card-subtitle mb-2 text-muted">AVG PER CORE: <?= $avgLoadPerCore; ?></h6>
					<?php if (!empty($cpuErrors)) { ?>
                        <p class="card-text"><?= $cpuErrors; ?></p>
					<?php } ?>
                    <p class="card-text small">
                        If avg per core:<br/>
                        &bull; < 0.7 - GOOD<br/>
                        &bull; 0.7 to 1 - HIGH LOAD<br/>
                        &bull; > 1 - OVERLOADED!!!
                    </p>
                </div>
            </div>

            <div class="card <?= !empty($ramErrors) ? "alert-danger" : ""; ?>">
                <div class="card-body">
                    <h5 class="card-title">MEMORY</h5>
                    <h6 class="card-subtitle mb-2 text-muted">TOTAL: <?= $totalRam; ?></h6>
                    <h6 class="card-subtitle mb-2 text-muted">FREE: <?= $freeRam; ?></h6>
					<?php if ($availableRam !== false) { ?>
                        <h6 class="card-subtitle mb-2 text-muted">AVAILABLE: <?= $availableRam; ?></h6>
					<?php } ?>
					<?php if ($totalSwap !== false) { ?>
                        <h6 class="card-subtitle mb-2 text-muted">TOTAL SWAP: <?= $totalSwap; ?></h6>
					<?php } ?>
					<?php if ($freeSwap !== false) { ?>
                        <h6 class="card-subtitle mb-2 text-muted">FREE SWAP: <?= $freeSwap; ?></h6>
					<?php } ?>
					<?php if (!empty($ramErrors)) { ?>
                        <p class="card-text"><?= $ramErrors; ?></p>
					<?php } ?>
                </div>
            </div>

            <div class="card <?= !empty($mysqlError) ? "alert-danger" : ""; ?>">
                <div class="card-body">
                    <h5 class="card-title">MySQL</h5>
                    <h6 class="card-subtitle mb-2 text-muted">STATUS: <?= $mysqlStatus; ?></h6>
					<?php if (!empty($mysqlError)) { ?>
                        <p class="card-text"><?= $mysqlError; ?></p>
					<?php } ?>
                </div>
            </div>

            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">Apache</h5>
                    <h6 class="card-subtitle mb-2 text-muted">STATUS: <?= $apacheStatus; ?></h6>
                </div>
            </div>

            <div class="card <?= !empty($speedError) ? "alert-danger" : ""; ?>">
                <div class="card-body">
                    <h5 class="card-title">Performance</h5>
                    <h6 class="card-subtitle mb-2 text-muted">STATUS: <?= $speedStatus; ?></h6>
                    <h6 class="card-subtitle mb-2 text-muted">LOAD TIME: <?= $loadTime; ?></h6>
                    <p class="card-text small">This page should load in well under 1 second</p>
                </div>
            </div>


        </div>
    </div>


<?php
// Parties over, go home
htmlFooter();
