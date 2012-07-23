<h1><?=stripslashes(BRANDED_TITLE)?></h1>

<?=stripslashes(BRANDED_TEXT)?>

<?php

if (BRANDED_STATS === true)
{
    //uitlezen van de stats voor de grafiek
    $year = date("Y");
    $month = date("m");
    for ($day=1; $day<=date("t"); $day++){
        $res_unique = $db->fetch_assoc($db->query("SELECT COUNT(date_time) AS aantal FROM log_site WHERE date_time = '".$year."-".$month."-".$day."';"));
        $stats[$day]['unique'] = (int)$res_unique['aantal'];
    }
    ?>
    <script type="text/javascript" src="/beheer/resources/scripts/jquery.flot.min.js"></script>
    <script type="text/javascript">
        // <![cdata[
	$(document).ready(function () {
			
            <?php
                print "var visitors = [";
                for($day=1; $day<date("t"); $day++){
                        print '[' . $day . ', ' . $stats[$day]['unique'].'],';
                }
                $day++;
                print '[' . $day . ', 0]';
                print "];";
            ?>
			
	
            var plot = $.plot($("#statistieken_div"), [
                { label: "Unieke bezoekers", data: visitors }
            ], {
                lines: {
                    show: true
                },
                points: {
                    show: true
                },
                grid: {
                    backgroundColor: '#fffaff',
                    hoverable: true,
                    clickable: true
                },
                legend: {
                    show: false
                },
                yaxis: {
                    minTickSize: 1
                }
            });
	
            function showTooltip(x, y, contents) {
                $("<div id=\"tooltip\">" + contents + "<\/div>").css({
                    position: 'absolute',
                    display: 'none',
                    top: y + -36,
                    left: x + -6,
                    border: '1px solid #fdd',
                    padding: '4px',
                    'background-color': '#fee',
                    opacity: 0.80
                }).appendTo("body").fadeIn(200);
            }
	
            var previousPoint = null;

            $("#statistieken_div").bind("plothover", function (event, pos, item) {
                $("#x").text(pos.x.toFixed(2));
                $("#y").text(pos.y.toFixed(2));

                if (item) {
                    if (previousPoint != item.datapoint) {
                        previousPoint = item.datapoint;

                        $("#tooltip").remove();
                        var x = item.datapoint[0].toFixed(2),
                            y = item.datapoint[1].toFixed(2);

                        showTooltip(item.pageX, item.pageY, Math.round(y) + " " + item.series.label);
                    }
                }
                else {
                    $("#tooltip").remove();
                    previousPoint = null;
                }
            });
	});
    // ]]>
    </script>

    <div style="margin-bottom: 20px;">
        <div class="legend">
            <h6>Website statistieken</h6>
            <ul>
                <li class="visitors">Unieke bezoekers</li>
            </ul>
        </div>
        <div id="statistieken_div" style="width: auto; height: 400px; "></div>
    </div>
    <?php
}
?>

<?=stripslashes(BRANDED_FOOTER)?>