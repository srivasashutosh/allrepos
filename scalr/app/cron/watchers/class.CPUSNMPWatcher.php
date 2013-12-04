<?php
    class CPUSNMPWatcher
    {
        const COLOR_CPU_USER = "#eacc00";
        const COLOR_CPU_SYST = "#ea8f00";
        const COLOR_CPU_NICE = "#ff3932";
        const COLOR_CPU_IDLE = "#fafdce";


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
                "--vertical-label" => 'Percent CPU Utilization',
                   "--title" => "CPU Utilization ({$dt})",
                   "--upper-limit" => 100,
                   "--alt-autoscale-max",
                   "--alt-autoscale-min",
                   "--rigid",
                   "--no-gridfit",
                   "--slope-mode",
                "--x-grid" => $r["x_grid"],
                "--end" => $r["end"],
                "--start" => $r["start"],
                   "--width" => 440,
                   "--height" => 160,
                   "--font-render-mode" => "normal",
                   "DEF:a={$rrddbpath}:user:AVERAGE",
                   "DEF:b={$rrddbpath}:system:AVERAGE",
                   "DEF:c={$rrddbpath}:nice:AVERAGE",
                   "DEF:d={$rrddbpath}:idle:AVERAGE",
                   "CDEF:total=a,b,c,d,+,+,+",
                "CDEF:a_perc=a,total,/,100,*",
                "VDEF:a_perc_last=a_perc,LAST",
                "VDEF:a_perc_avg=a_perc,AVERAGE",
                   "VDEF:a_perc_max=a_perc,MAXIMUM",
                   "CDEF:b_perc=b,total,/,100,*",
                   "VDEF:b_perc_last=b_perc,LAST",
                   "VDEF:b_perc_avg=b_perc,AVERAGE",
                   "VDEF:b_perc_max=b_perc,MAXIMUM",
                   "CDEF:c_perc=c,total,/,100,*",
                   "VDEF:c_perc_last=c_perc,LAST",
                   "VDEF:c_perc_avg=c_perc,AVERAGE",
                "VDEF:c_perc_max=c_perc,MAXIMUM",
                "CDEF:d_perc=d,total,/,100,*",
                "VDEF:d_perc_last=d_perc,LAST",
                   "VDEF:d_perc_avg=d_perc,AVERAGE",
                   "VDEF:d_perc_max=d_perc,MAXIMUM",
                   'COMMENT:<b><tt>               Current    Average    Maximum</tt></b>\j',
                   'AREA:a_perc#eacc00:<tt>user    </tt>',
                   'GPRINT:a_perc_last:<tt>    %3.0lf%%</tt>',
                   'GPRINT:a_perc_avg:<tt>     %3.0lf%%</tt>',
                   'GPRINT:a_perc_max:<tt>     %3.0lf%%</tt>\n',
                   'AREA:b_perc#ea8f00:<tt>system  </tt>:STACK',
                'GPRINT:b_perc_last:<tt>    %3.0lf%%</tt>',
                'GPRINT:b_perc_avg:<tt>     %3.0lf%%</tt>',
                'GPRINT:b_perc_max:<tt>     %3.0lf%%</tt>\n',
                   'AREA:c_perc#ff3932:<tt>nice    </tt>:STACK',
                   'GPRINT:c_perc_last:<tt>    %3.0lf%%</tt>',
                   'GPRINT:c_perc_avg:<tt>     %3.0lf%%</tt>',
                   'GPRINT:c_perc_max:<tt>     %3.0lf%%</tt>\n',
                   'AREA:d_perc#fafdce:<tt>idle    </tt>:STACK',
                   'GPRINT:d_perc_last:<tt>    %3.0lf%%</tt>',
                   'GPRINT:d_perc_avg:<tt>     %3.0lf%%</tt>',
                   'GPRINT:d_perc_max:<tt>     %3.0lf%%</tt>\n'
            );

            $rrdGraph->setOptions($options);

            try {
                return $rrdGraph->save();
            } catch (Exception $e) {
                var_dump($e);
            }
        }
    }
