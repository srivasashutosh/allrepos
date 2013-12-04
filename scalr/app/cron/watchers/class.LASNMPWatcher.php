<?php

    class LASNMPWatcher
    {
        const COLOR_LA1 = "#FF0000";
        const COLOR_LA5 = "#0000FF";
        const COLOR_LA15 = "#00FF00";

        /**
         * Plot graphic
         *
         * @param integer $serverid
         */
        public static function PlotGraphic($rrddbpath, $image_path, $r)
        {
            $dt = date("M j, Y H:i:s");

            $rrdGraph = new RRDGraph($image_path);

            $options = array(
                    "--step" => $r["step"],
                    "--pango-markup",
                    "--vertical-label" => 'Load averages',
                    "--title" => "Load averages ({$dt})",
                    "--lower-limit" => '0',
                    "--alt-autoscale-max",
                    "--alt-autoscale-min",
                    "--rigid",
                    "--no-gridfit",
                    "--slope-mode",
                    "--alt-y-grid",
                    "--units-exponent" => '0',
                    "--x-grid" => $r["x_grid"],
                    "--end" => $r["end"],
                    "--start" => $r["start"],
                    "--width" => 440,
                    "--height" => 140,
                    "--font-render-mode" => "normal",
                    "DEF:la1={$rrddbpath}:la1:AVERAGE",
                    "DEF:la5={$rrddbpath}:la5:AVERAGE",
                    "DEF:la15={$rrddbpath}:la15:AVERAGE",
                    "VDEF:la1_min=la1,MINIMUM",
                    "VDEF:la1_last=la1,LAST",
                    "VDEF:la1_avg=la1,AVERAGE",
                    "VDEF:la1_max=la1,MAXIMUM",
                    "VDEF:la5_min=la5,MINIMUM",
                    "VDEF:la5_last=la5,LAST",
                    "VDEF:la5_avg=la5,AVERAGE",
                    "VDEF:la5_max=la5,MAXIMUM",
                    "VDEF:la15_min=la15,MINIMUM",
                    "VDEF:la15_last=la15,LAST",
                    "VDEF:la15_avg=la15,AVERAGE",
                    "VDEF:la15_max=la15,MAXIMUM",
                    'COMMENT:<b><tt>                                Minimum   Current     Average     Maximum</tt></b>\\j',
                    'AREA:la15'.self::COLOR_LA15.':<tt>15 Minutes system load </tt>',
                    'GPRINT:la15_min:<tt>%3.2lf</tt>',
                    'GPRINT:la15_last:<tt>%3.2lf</tt>',
                    'GPRINT:la15_avg:<tt>%3.2lf</tt>',
                    'GPRINT:la15_max:<tt>%3.2lf</tt>\\j',
                    'LINE1:la5'.self::COLOR_LA5.':<tt> 5 Minutes system load </tt>',
                    'GPRINT:la5_min:<tt>%3.2lf</tt>',
                    'GPRINT:la5_last:<tt>%3.2lf</tt>',
                    'GPRINT:la5_avg:<tt>%3.2lf</tt>',
                    'GPRINT:la5_max:<tt>%3.2lf</tt>\\j',
                    'LINE1:la1'.self::COLOR_LA1.':<tt> 1 Minute system load  </tt>',
                    'GPRINT:la1_min:<tt>%3.2lf</tt>',
                    'GPRINT:la1_last:<tt>%3.2lf</tt>',
                    'GPRINT:la1_avg:<tt>%3.2lf</tt>',
                    'GPRINT:la1_max:<tt>%3.2lf</tt>\\j',

            );

            $rrdGraph->setOptions($options);

            try {
                return $rrdGraph->save();
            } catch (Exception $e) {
                var_dump($e);
            }
        }
    }

