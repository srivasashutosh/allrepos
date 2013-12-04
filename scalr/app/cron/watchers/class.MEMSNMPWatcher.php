<?php

    class MEMSNMPWatcher
    {

        const COLOR_MEM_SHRD = "#00FFFF";
        const COLOR_MEM_BUFF = "#3399FF";
        const COLOR_MEM_CACH = "#0000FF";
        const COLOR_MEM_FREE = "#99FF00";
        const COLOR_MEM_REAL = "#00CC00";
        const COLOR_MEM_SWAP = "#FF0000";

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
                    "--vertical-label" => 'Memory Usage',
                    "--title" => "Memory Usage ({$dt})",
                    "--lower-limit" => '0',
                    "--base" => 1024,
                    "--alt-autoscale-max",
                    "--alt-autoscale-min",
                    "--rigid",
                    "--no-gridfit",
                    "--slope-mode",
                    "--x-grid" => $r["x_grid"],
                    "--end" => $r["end"],
                    "--start" => $r["start"],
                    "--width" => 440,
                    "--height" => 180,
                    "--font-render-mode" => "normal",
                    "DEF:mem1={$rrddbpath}:swap:AVERAGE",
                    "DEF:mem2={$rrddbpath}:swapavail:AVERAGE",
                    "DEF:mem3={$rrddbpath}:total:AVERAGE",
                    "DEF:mem4={$rrddbpath}:avail:AVERAGE",
                    "DEF:mem5={$rrddbpath}:free:AVERAGE",
                    "DEF:mem6={$rrddbpath}:shared:AVERAGE",
                    "DEF:mem7={$rrddbpath}:buffer:AVERAGE",
                    "DEF:mem8={$rrddbpath}:cached:AVERAGE",

                    "CDEF:swap_total=mem1,1024,*",
                    "VDEF:swap_total_min=swap_total,MINIMUM",
                    "VDEF:swap_total_last=swap_total,LAST",
                    "VDEF:swap_total_avg=swap_total,AVERAGE",
                    "VDEF:swap_total_max=swap_total,MAXIMUM",

                    "CDEF:swap_avail=mem2,1024,*",
                    "VDEF:swap_avail_min=swap_avail,MINIMUM",
                    "VDEF:swap_avail_last=swap_avail,LAST",
                    "VDEF:swap_avail_avg=swap_avail,AVERAGE",
                    "VDEF:swap_avail_max=swap_avail,MAXIMUM",

                    "CDEF:swap_used=swap_total,swap_avail,-",
                    "VDEF:swap_used_min=swap_used,MINIMUM",
                    "VDEF:swap_used_last=swap_used,LAST",
                    "VDEF:swap_used_avg=swap_used,AVERAGE",
                    "VDEF:swap_used_max=swap_used,MAXIMUM",

                    "CDEF:mem_total=mem3,1024,*",
                    "VDEF:mem_total_min=mem_total,MINIMUM",
                    "VDEF:mem_total_last=mem_total,LAST",
                    "VDEF:mem_total_avg=mem_total,AVERAGE",
                    "VDEF:mem_total_max=mem_total,MAXIMUM",

                    "CDEF:mem_avail=mem4,1024,*",
                    "VDEF:mem_avail_min=mem_avail,MINIMUM",
                    "VDEF:mem_avail_last=mem_avail,LAST",
                    "VDEF:mem_avail_avg=mem_avail,AVERAGE",
                    "VDEF:mem_avail_max=mem_avail,MAXIMUM",

                    "CDEF:mem_free=mem5,1024,*",
                    "VDEF:mem_free_min=mem_free,MINIMUM",
                    "VDEF:mem_free_last=mem_free,LAST",
                    "VDEF:mem_free_avg=mem_free,AVERAGE",
                    "VDEF:mem_free_max=mem_free,MAXIMUM",

                    "CDEF:mem_shared=mem6,1024,*",
                    "VDEF:mem_shared_min=mem_shared,MINIMUM",
                    "VDEF:mem_shared_last=mem_shared,LAST",
                    "VDEF:mem_shared_avg=mem_shared,AVERAGE",
                    "VDEF:mem_shared_max=mem_shared,MAXIMUM",

                    "CDEF:mem_buffer=mem7,1024,*",
                    "VDEF:mem_buffer_min=mem_buffer,MINIMUM",
                    "VDEF:mem_buffer_last=mem_buffer,LAST",
                    "VDEF:mem_buffer_avg=mem_buffer,AVERAGE",
                    "VDEF:mem_buffer_max=mem_buffer,MAXIMUM",

                    "CDEF:mem_cached=mem8,1024,*",
                    "VDEF:mem_cached_min=mem_cached,MINIMUM",
                    "VDEF:mem_cached_last=mem_cached,LAST",
                    "VDEF:mem_cached_avg=mem_cached,AVERAGE",
                    "VDEF:mem_cached_max=mem_cached,MAXIMUM",

                    'COMMENT:<b><tt>                        Minimum       Current       Average      Maximum</tt></b>\\j',

                    'AREA:mem_shared'.self::COLOR_MEM_SHRD.':<tt>Shared        </tt>',
                    'GPRINT:swap_total_min:<tt>  %4.1lf%s</tt>',
                    'GPRINT:swap_total_last:<tt>  %4.1lf%s</tt>',
                    'GPRINT:swap_total_avg:<tt>  %4.1lf%s</tt>',
                    'GPRINT:swap_total_max:<tt>  %4.1lf%s</tt>\\j',

                    'AREA:mem_buffer'.self::COLOR_MEM_BUFF.':<tt>Buffer         </tt>',
                    'GPRINT:mem_buffer_min:<tt>  %4.1lf%s</tt>',
                    'GPRINT:mem_buffer_last:<tt>  %4.1lf%s</tt>',
                    'GPRINT:mem_buffer_avg:<tt>  %4.1lf%s</tt>',
                    'GPRINT:mem_buffer_max:<tt>  %4.1lf%s</tt>\\j',

                    'AREA:mem_cached'.self::COLOR_MEM_CACH.':<tt>Cached        </tt>:STACK',
                    'GPRINT:mem_cached_min:<tt>  %4.1lf%s</tt>',
                    'GPRINT:mem_cached_last:<tt>  %4.1lf%s</tt>',
                    'GPRINT:mem_cached_avg:<tt>  %4.1lf%s</tt>',
                    'GPRINT:mem_cached_max:<tt>  %4.1lf%s</tt>\\j',

                    'AREA:mem_free'.self::COLOR_MEM_FREE.':<tt>Free           </tt>:STACK',
                    'GPRINT:mem_free_min:<tt>  %4.1lf%s</tt>',
                    'GPRINT:mem_free_last:<tt>  %4.1lf%s</tt>',
                    'GPRINT:mem_free_avg:<tt>  %4.1lf%s</tt>',
                    'GPRINT:mem_free_max:<tt>  %4.1lf%s</tt>\\j',

                    'AREA:mem_avail'.self::COLOR_MEM_REAL.':<tt>Real           </tt>:STACK',
                    'GPRINT:mem_avail_min:<tt>  %4.1lf%s</tt>',
                    'GPRINT:mem_avail_last:<tt>  %4.1lf%s</tt>',
                    'GPRINT:mem_avail_avg:<tt>  %4.1lf%s</tt>',
                    'GPRINT:mem_avail_max:<tt>  %4.1lf%s</tt>\\j',

                    'LINE1:swap_used'.self::COLOR_MEM_SWAP.':<tt>Swap In Use  </tt>:STACK',
                    'GPRINT:swap_used_min:<tt>  %4.1lf%s</tt>',
                    'GPRINT:swap_used_last:<tt>  %4.1lf%s</tt>',
                    'GPRINT:swap_used_avg:<tt>  %4.1lf%s</tt>',
                    'GPRINT:swap_used_max:<tt>  %4.1lf%s</tt>\\j'
            );
            $rrdGraph->setOptions($options);

            try {
                $retval =  $rrdGraph->save();
            } catch (Exception $e) {
                var_dump($e);
            }

            return $retval;
        }
    }
