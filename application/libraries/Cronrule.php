<?php

class Cronrule
{
    static public $error;

    /**
     *  解析crontab的定时格式，linux只支持到分钟/，这个类支持到秒
     * @param string $crontab_string :
     *
     *      0     1    2    3    4    5
     *      *     *    *    *    *    *
     *      -     -    -    -    -    -
     *      |     |    |    |    |    |
     *      |     |    |    |    |    +----- day of week (0 - 6) (Sunday=0)
     *      |     |    |    |    +----- month (1 - 12)
     *      |     |    |    +------- day of month (1 - 31)
     *      |     |    +--------- hour (0 - 23)
     *      |     +----------- min (0 - 59)
     *      +------------- sec (0-59)
     * @param int $start_time timestamp [default=current timestamp]
     * @return int unix timestamp - 下一分钟内执行是否需要执行任务，如果需要，则把需要在那几秒执行返回
     * @throws InvalidArgumentException 错误信息
     */
    public function parse_crontab_rule($crontab_string = null, $start_time = null)
    {
        $crontab_string = '11-59 15 * * *';
        //$start_time = $this->_get_curr_timestamp_ms();
        if (!preg_match('/^((\*(\/[0-9]+)?)|[0-9\-\,\/]+)\s+((\*(\/[0-9]+)?)|[0-9\-\,\/]+)\s+((\*(\/[0-9]+)?)|[0-9\-\,\/]+)\s+((\*(\/[0-9]+)?)|[0-9\-\,\/]+)\s+((\*(\/[0-9]+)?)|[0-9\-\,\/]+)\s+((\*(\/[0-9]+)?)|[0-9\-\,\/]+)$/i',
            trim($crontab_string))
        ) {
            if (!preg_match('/^((\*(\/[0-9]+)?)|[0-9\-\,\/]+)\s+((\*(\/[0-9]+)?)|[0-9\-\,\/]+)\s+((\*(\/[0-9]+)?)|[0-9\-\,\/]+)\s+((\*(\/[0-9]+)?)|[0-9\-\,\/]+)\s+((\*(\/[0-9]+)?)|[0-9\-\,\/]+)$/i',
                trim($crontab_string))
            ) {
                self::$error = "Invalid cron string: " . $crontab_string;
                return false;
            }
        }
        if ($start_time && !is_numeric($start_time)) {
            self::$error = "\$start_time must be a valid unix timestamp ($start_time given)";
            return false;
        }
        $cron = preg_split("/[\s]+/i", trim($crontab_string));
        $start = empty($start_time) ? time() : $start_time;
        if (count($cron) == 6) {
            $date = array(
                'second' => (empty($cron[0])) ? array(1 => 1) : $this->_parse_cron_number($cron[0], 1, 59),
                'minutes' => $this->_parse_cron_number($cron[1], 0, 59),
                'hours' => $this->_parse_cron_number($cron[2], 0, 23),
                'day' => $this->_parse_cron_number($cron[3], 1, 31),
                'month' => $this->_parse_cron_number($cron[4], 1, 12),
                'week' => $this->_parse_cron_number($cron[5], 0, 6),
            );
        } elseif (count($cron) == 5) {
            $date = array(
                'second' => array(1 => 1),
                'minutes' => $this->_parse_cron_number($cron[0], 0, 59),
                'hours' => $this->_parse_cron_number($cron[1], 0, 23),
                'day' => $this->_parse_cron_number($cron[2], 1, 31),
                'month' => $this->_parse_cron_number($cron[3], 1, 12),
                'week' => $this->_parse_cron_number($cron[4], 0, 6),
            );
        }
        if (
            in_array(intval(date('i', $start)), $date['minutes']) &&
            in_array(intval(date('G', $start)), $date['hours']) &&
            in_array(intval(date('j', $start)), $date['day']) &&
            in_array(intval(date('w', $start)), $date['week']) &&
            in_array(intval(date('n', $start)), $date['month'])

        ) {
            return $date;
        }
        return null;
    }

    /**
     * 解析单个配置的含义
     * @param $s
     * @param $min
     * @param $max
     * @return array
     */
    private function _parse_cron_number($s, $min, $max)
    {
        $result = array();
        $v1 = explode(",", $s);
        foreach ($v1 as $v2) {
            $v3 = explode("/", $v2);
            $step = empty($v3[1]) ? 1 : $v3[1];
            $v4 = explode("-", $v3[0]);
            $_min = count($v4) == 2 ? $v4[0] : ($v3[0] == "*" ? $min : $v3[0]);
            $_max = count($v4) == 2 ? $v4[1] : ($v3[0] == "*" ? $max : $v3[0]);
            for ($i = $_min; $i <= $_max; $i += $step) {
                if (intval($i) < $min) {
                    $result[$min] = $min;
                } elseif (intval($i) > $max) {
                    $result[$max] = $max;
                } else {
                    $result[$i] = intval($i);
                }
            }
        }
        ksort($result);
        return $result;
    }

    /**
     * 获取当前毫秒
     * @return int
     */
    private function _get_curr_timestamp_ms()
    {
        return (int)(microtime(true) * 1000);
    }
}
