<?php
if (isset($_SESSION['profile']) && $_SESSION['profile']) {
    $level = MongoProfiler::getProfilingLevel();
    echo "MongoDB profiler level: $level\n";
    $profile = MongoProfiler::getProfilingData();
    ?>
    <div id="profiler">
    <table>
    <thead>
    <tr><th>time</th><th>info</th></h>ms</th></tr>
    </thead>
    <tbody>
    <?php foreach ($profile as $row) { ?>
        <tr>
        <td><?php echo date("Y-m-d H:i:s", $row['ts']->sec); ?></td>
        <td><?php echo $row['info']; ?></td>
        <td><?php echo $row['millis']; ?></td>
        </tr>
    <?php } ?>
    </tbody>
    </table>
    </div>
    <?php
}