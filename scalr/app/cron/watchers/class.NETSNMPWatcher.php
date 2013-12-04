<?php

    class NETSNMPWatcher
    {
        const COLOR_INBOUND = "#00cc00";
        const COLOR_OUBOUND = "#0000ff";

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
                    "--vertical-label" => 'Bits per second',
                    "--title" => "Network usage ({$dt})",
                    "--lower-limit" => '0',
                    "--alt-autoscale-max",
                    "--alt-autoscale-min",
                    "--rigid",
                    "--no-gridfit",
                    "--slope-mode",
                    "--x-grid" => $r["x_grid"],
                    "--end" => $r["end"],
                    "--start" => $r["start"],
                    "--width" => 440,
                    "--height" => 100,
                    "--font-render-mode" => "normal",
                    "DEF:in={$rrddbpath}:in:AVERAGE",
                    "DEF:out={$rrddbpath}:out:AVERAGE",
                    "CDEF:in_bits=in,8,*",
                    "CDEF:out_bits=out,8,*",
                    "VDEF:in_last=in_bits,LAST",
                    "VDEF:in_avg=in_bits,AVERAGE",
                    "VDEF:in_max=in_bits,MAXIMUM",
                    "VDEF:out_last=out_bits,LAST",
                    "VDEF:out_avg=out_bits,AVERAGE",
                    "VDEF:out_max=out_bits,MAXIMUM",
                    'COMMENT:<b><tt>           Current   Average   Maximum</tt></b>\\j',
                    'AREA:in_bits'.self::COLOR_INBOUND.':<tt>In    </tt>',
                    'GPRINT:in_last:<tt>  %4.1lf%s</tt>',
                    'GPRINT:in_avg:<tt>  %4.1lf%s</tt>',
                    'GPRINT:in_max:<tt>  %4.1lf%s</tt>\n',
                    'LINE1:out_bits'.self::COLOR_OUBOUND.':<tt>Out   </tt>',
                    'GPRINT:out_last:<tt>  %4.1lf%s</tt>',
                    'GPRINT:out_avg:<tt>  %4.1lf%s</tt>',
                    'GPRINT:out_max:<tt>  %4.1lf%s</tt>\n'
            );

            $rrdGraph->setOptions($options);

            try {
                return $rrdGraph->save();
            } catch (Exception $e) {
                var_dump($e);
            }

        }
    }
