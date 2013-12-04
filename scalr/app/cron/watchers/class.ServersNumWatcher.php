<?php

    class ServersNumWatcher
    {
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
                "--vertical-label" => 'Servers',
                   "--title" => "Servers count ({$dt})",
                   "--alt-autoscale-max",
                   "--alt-autoscale-min",
                   "--lower-limit" => 0,
                   "--y-grid" => "1:1",
                   "--units-exponent" => '0',
                   "--rigid",
                   "--no-gridfit",
                   "--slope-mode",
                "--x-grid" => $r["x_grid"],
                "--end" => $r["end"],
                "--start" => $r["start"],
                   "--width" => 440,
                   "--height" => 100,
                   "--font-render-mode" => "normal",
                   "DEF:s_running={$rrddbpath}:s_running:AVERAGE",
                "VDEF:s_running_last=s_running,LAST",
                "VDEF:s_running_avg=s_running,AVERAGE",
                   "VDEF:s_running_max=s_running,MAXIMUM",
                   "VDEF:s_running_min=s_running,MINIMUM",
                   'COMMENT:<b><tt>                     Current    Average    Maximum    Minimum</tt></b>\j',
                   'LINE1:s_running'.self::COLOR_RUNNING_SERVERS.':<tt>Running servers </tt>',
                   'GPRINT:s_running_last:<tt>    %3.0lf</tt>',
                   'GPRINT:s_running_avg:<tt>     %3.0lf</tt>',
                   'GPRINT:s_running_max:<tt>     %3.0lf</tt>',
                   'GPRINT:s_running_min:<tt>     %3.0lf</tt>\n'
            );

            try {
                $rrdGraph->setOptions($options);

                return $rrdGraph->save();
            } catch (Exception $e) {
                var_dump($e->getMessage());
            }
        }
    }
