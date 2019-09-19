CREATE DATABASE IF NOT EXISTS db_hbg_php_dts DEFAULT CHARSET utf8 COLLATE utf8_general_ci;


CREATE TABLE `t_crontab` (
  `id` int(20) NOT NULL AUTO_INCREMENT COMMENT '主键id',
  `cron_name` varchar(512) NOT NULL DEFAULT '0' COMMENT '任务名称',
  `cron_rule` varchar(256) NOT NULL DEFAULT '* * * * *' COMMENT '任务规则',
  `cron_execution` TEXT COMMENT '任务执行语句',
  `cron_manager` varchar(128) NOT NULL DEFAULT 'kongming01' COMMENT '任务负责人',
  `cron_colony` varchar(128) NOT NULL DEFAULT 'hbg_php_dts' COMMENT '任务所在集群',
  `status` tinyint(2) NOT NULL DEFAULT '0' COMMENT '状态 1：无效，2：有效，3：删除',
  `createtime` int(11) NOT NULL DEFAULT '0' COMMENT '创建时间',
  `updatetime` int(11) NULL COMMENT '更新时间',
  `createuser` varchar(128) NOT NULL DEFAULT 'admin' COMMENT '创建人员',
  `updateuser` varchar(128) NULL COMMENT '更新人员',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='定时任务主表';

insert into t_crontab (cron_name, cron_rule, cron_execution, cron_manager, cron_colony, status, createtime, updatetime, createuser,updateuser) values ('php sleep', '* * * * *', 'ps -ef | grep php', 'liu', 'php_house', '2', '1560050050', '1560050050', 'liu', 'liu');
insert into t_crontab (cron_name, cron_rule, cron_execution, cron_manager, cron_colony, status, createtime, updatetime, createuser,updateuser) values ('php sleep', '* * * * *', 'ls -l | grep php', 'liu', 'php_house', '2', '1560050050', '1560050050', 'liu', 'liu');

DROP TABLE IF EXISTS `t_crontab_log`;
CREATE TABLE `t_crontab_log` (
    `id` bigint(20) NOT NULL AUTO_INCREMENT COMMENT '主键id',
    `cron_id` int NOT NULL DEFAULT 0 COMMENT '',
    `start_time` BIGINT NOT NULL DEFAULT 0 COMMENT '开始时间（毫秒）',
    `end_time` BIGINT NOT NULL DEFAULT 0 COMMENT '结束时间（毫秒）',
    `exec_ip` varchar(16) NOT NULL DEFAULT '127.0.0.1' COMMENT '',
    `exec_status` tinyint NOT NULL DEFAULT 0 COMMENT '运行状态；1-运行中；2-正常结束；3-失败；4-强行终止',
    `exec_result` text COMMENT '运行结果',
    PRIMARY KEY (`id`),
    INDEX `idx_cron_id` (`cron_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='定时任务执行记录表';