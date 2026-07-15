/*
Navicat MySQL Data Transfer

Source Server         : DC_SOPMOEI_IN
Source Server Version : 50505
Source Host           : 192.168.1.222:3306
Source Database       : db_demo

Target Server Type    : MYSQL
Target Server Version : 50505
File Encoding         : 65001

Date: 2017-08-09 12:55:05
*/

SET FOREIGN_KEY_CHECKS=0;

-- ----------------------------
-- Table structure for sys_person_type
-- ----------------------------
DROP TABLE IF EXISTS `sys_person_type`;
CREATE TABLE `sys_person_type` (
  `hospcode` char(5) NOT NULL DEFAULT '',
  `hospname` varchar(255) DEFAULT NULL,
  `type1` decimal(23,0) DEFAULT NULL,
  `type2` decimal(23,0) DEFAULT NULL,
  `type3` decimal(23,0) DEFAULT NULL,
  `type4` decimal(23,0) DEFAULT NULL,
  `type5` decimal(23,0) DEFAULT NULL,
  `nottype` decimal(23,0) DEFAULT NULL,
  `total` bigint(21) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of sys_person_type
-- ----------------------------

-- ----------------------------
-- Table structure for sys_pyramid_level_1
-- ----------------------------
DROP TABLE IF EXISTS `sys_pyramid_level_1`;
CREATE TABLE `sys_pyramid_level_1` (
  `hospcode` char(5) NOT NULL DEFAULT '',
  `hospname` varchar(255) DEFAULT NULL,
  `am00` decimal(23,0) DEFAULT NULL,
  `af00` decimal(23,0) DEFAULT NULL,
  `am01` decimal(23,0) DEFAULT NULL,
  `af01` decimal(23,0) DEFAULT NULL,
  `am02` decimal(23,0) DEFAULT NULL,
  `af02` decimal(23,0) DEFAULT NULL,
  `am03` decimal(23,0) DEFAULT NULL,
  `af03` decimal(23,0) DEFAULT NULL,
  `am04` decimal(23,0) DEFAULT NULL,
  `af04` decimal(23,0) DEFAULT NULL,
  `am05` decimal(23,0) DEFAULT NULL,
  `af05` decimal(23,0) DEFAULT NULL,
  `am06` decimal(23,0) DEFAULT NULL,
  `af06` decimal(23,0) DEFAULT NULL,
  `am07` decimal(23,0) DEFAULT NULL,
  `af07` decimal(23,0) DEFAULT NULL,
  `am08` decimal(23,0) DEFAULT NULL,
  `af08` decimal(23,0) DEFAULT NULL,
  `am09` decimal(23,0) DEFAULT NULL,
  `af09` decimal(23,0) DEFAULT NULL,
  `am10` decimal(23,0) DEFAULT NULL,
  `af10` decimal(23,0) DEFAULT NULL,
  `am11` decimal(23,0) DEFAULT NULL,
  `af11` decimal(23,0) DEFAULT NULL,
  `am12` decimal(23,0) DEFAULT NULL,
  `af12` decimal(23,0) DEFAULT NULL,
  `am13` decimal(23,0) DEFAULT NULL,
  `af13` decimal(23,0) DEFAULT NULL,
  `am14` decimal(23,0) DEFAULT NULL,
  `af14` decimal(23,0) DEFAULT NULL,
  `am15` decimal(23,0) DEFAULT NULL,
  `af15` decimal(23,0) DEFAULT NULL,
  `am16` decimal(23,0) DEFAULT NULL,
  `af16` decimal(23,0) DEFAULT NULL,
  `am17` decimal(23,0) DEFAULT NULL,
  `af17` decimal(23,0) DEFAULT NULL,
  `am18` decimal(23,0) DEFAULT NULL,
  `af18` decimal(23,0) DEFAULT NULL,
  `am19` decimal(23,0) DEFAULT NULL,
  `af19` decimal(23,0) DEFAULT NULL,
  `am20` decimal(23,0) DEFAULT NULL,
  `af20` decimal(23,0) DEFAULT NULL,
  `am21` decimal(23,0) DEFAULT NULL,
  `af21` decimal(23,0) DEFAULT NULL,
  `am22` decimal(23,0) DEFAULT NULL,
  `af22` decimal(23,0) DEFAULT NULL,
  `am23` decimal(23,0) DEFAULT NULL,
  `af23` decimal(23,0) DEFAULT NULL,
  `am24` decimal(23,0) DEFAULT NULL,
  `af24` decimal(23,0) DEFAULT NULL,
  `am25` decimal(23,0) DEFAULT NULL,
  `af25` decimal(23,0) DEFAULT NULL,
  `am26` decimal(23,0) DEFAULT NULL,
  `af26` decimal(23,0) DEFAULT NULL,
  `am27` decimal(23,0) DEFAULT NULL,
  `af27` decimal(23,0) DEFAULT NULL,
  `am28` decimal(23,0) DEFAULT NULL,
  `af28` decimal(23,0) DEFAULT NULL,
  `am29` decimal(23,0) DEFAULT NULL,
  `af29` decimal(23,0) DEFAULT NULL,
  `am30` decimal(23,0) DEFAULT NULL,
  `af30` decimal(23,0) DEFAULT NULL,
  `am31` decimal(23,0) DEFAULT NULL,
  `af31` decimal(23,0) DEFAULT NULL,
  `am32` decimal(23,0) DEFAULT NULL,
  `af32` decimal(23,0) DEFAULT NULL,
  `am33` decimal(23,0) DEFAULT NULL,
  `af33` decimal(23,0) DEFAULT NULL,
  `am34` decimal(23,0) DEFAULT NULL,
  `af34` decimal(23,0) DEFAULT NULL,
  `am35` decimal(23,0) DEFAULT NULL,
  `af35` decimal(23,0) DEFAULT NULL,
  `am36` decimal(23,0) DEFAULT NULL,
  `af36` decimal(23,0) DEFAULT NULL,
  `am37` decimal(23,0) DEFAULT NULL,
  `af37` decimal(23,0) DEFAULT NULL,
  `am38` decimal(23,0) DEFAULT NULL,
  `af38` decimal(23,0) DEFAULT NULL,
  `am39` decimal(23,0) DEFAULT NULL,
  `af39` decimal(23,0) DEFAULT NULL,
  `am40` decimal(23,0) DEFAULT NULL,
  `af40` decimal(23,0) DEFAULT NULL,
  `am41` decimal(23,0) DEFAULT NULL,
  `af41` decimal(23,0) DEFAULT NULL,
  `am42` decimal(23,0) DEFAULT NULL,
  `af42` decimal(23,0) DEFAULT NULL,
  `am43` decimal(23,0) DEFAULT NULL,
  `af43` decimal(23,0) DEFAULT NULL,
  `am44` decimal(23,0) DEFAULT NULL,
  `af44` decimal(23,0) DEFAULT NULL,
  `am45` decimal(23,0) DEFAULT NULL,
  `af45` decimal(23,0) DEFAULT NULL,
  `am46` decimal(23,0) DEFAULT NULL,
  `af46` decimal(23,0) DEFAULT NULL,
  `am47` decimal(23,0) DEFAULT NULL,
  `af47` decimal(23,0) DEFAULT NULL,
  `am48` decimal(23,0) DEFAULT NULL,
  `af48` decimal(23,0) DEFAULT NULL,
  `am49` decimal(23,0) DEFAULT NULL,
  `af49` decimal(23,0) DEFAULT NULL,
  `am50` decimal(23,0) DEFAULT NULL,
  `af50` decimal(23,0) DEFAULT NULL,
  `am51` decimal(23,0) DEFAULT NULL,
  `af51` decimal(23,0) DEFAULT NULL,
  `am52` decimal(23,0) DEFAULT NULL,
  `af52` decimal(23,0) DEFAULT NULL,
  `am53` decimal(23,0) DEFAULT NULL,
  `af53` decimal(23,0) DEFAULT NULL,
  `am54` decimal(23,0) DEFAULT NULL,
  `af54` decimal(23,0) DEFAULT NULL,
  `am55` decimal(23,0) DEFAULT NULL,
  `af55` decimal(23,0) DEFAULT NULL,
  `am56` decimal(23,0) DEFAULT NULL,
  `af56` decimal(23,0) DEFAULT NULL,
  `am57` decimal(23,0) DEFAULT NULL,
  `af57` decimal(23,0) DEFAULT NULL,
  `am58` decimal(23,0) DEFAULT NULL,
  `af58` decimal(23,0) DEFAULT NULL,
  `am59` decimal(23,0) DEFAULT NULL,
  `af59` decimal(23,0) DEFAULT NULL,
  `am60` decimal(23,0) DEFAULT NULL,
  `af60` decimal(23,0) DEFAULT NULL,
  `am61` decimal(23,0) DEFAULT NULL,
  `af61` decimal(23,0) DEFAULT NULL,
  `am62` decimal(23,0) DEFAULT NULL,
  `af62` decimal(23,0) DEFAULT NULL,
  `am63` decimal(23,0) DEFAULT NULL,
  `af63` decimal(23,0) DEFAULT NULL,
  `am64` decimal(23,0) DEFAULT NULL,
  `af64` decimal(23,0) DEFAULT NULL,
  `am65` decimal(23,0) DEFAULT NULL,
  `af65` decimal(23,0) DEFAULT NULL,
  `am66` decimal(23,0) DEFAULT NULL,
  `af66` decimal(23,0) DEFAULT NULL,
  `am67` decimal(23,0) DEFAULT NULL,
  `af67` decimal(23,0) DEFAULT NULL,
  `am68` decimal(23,0) DEFAULT NULL,
  `af68` decimal(23,0) DEFAULT NULL,
  `am69` decimal(23,0) DEFAULT NULL,
  `af69` decimal(23,0) DEFAULT NULL,
  `am70` decimal(23,0) DEFAULT NULL,
  `af70` decimal(23,0) DEFAULT NULL,
  `am71` decimal(23,0) DEFAULT NULL,
  `af71` decimal(23,0) DEFAULT NULL,
  `am72` decimal(23,0) DEFAULT NULL,
  `af72` decimal(23,0) DEFAULT NULL,
  `am73` decimal(23,0) DEFAULT NULL,
  `af73` decimal(23,0) DEFAULT NULL,
  `am74` decimal(23,0) DEFAULT NULL,
  `af74` decimal(23,0) DEFAULT NULL,
  `am75` decimal(23,0) DEFAULT NULL,
  `af75` decimal(23,0) DEFAULT NULL,
  `am76` decimal(23,0) DEFAULT NULL,
  `af76` decimal(23,0) DEFAULT NULL,
  `am77` decimal(23,0) DEFAULT NULL,
  `af77` decimal(23,0) DEFAULT NULL,
  `am78` decimal(23,0) DEFAULT NULL,
  `af78` decimal(23,0) DEFAULT NULL,
  `am79` decimal(23,0) DEFAULT NULL,
  `af79` decimal(23,0) DEFAULT NULL,
  `am80` decimal(23,0) DEFAULT NULL,
  `af80` decimal(23,0) DEFAULT NULL,
  `am81` decimal(23,0) DEFAULT NULL,
  `af81` decimal(23,0) DEFAULT NULL,
  `am82` decimal(23,0) DEFAULT NULL,
  `af82` decimal(23,0) DEFAULT NULL,
  `am83` decimal(23,0) DEFAULT NULL,
  `af83` decimal(23,0) DEFAULT NULL,
  `am84` decimal(23,0) DEFAULT NULL,
  `af84` decimal(23,0) DEFAULT NULL,
  `am85` decimal(23,0) DEFAULT NULL,
  `af85` decimal(23,0) DEFAULT NULL,
  `am86` decimal(23,0) DEFAULT NULL,
  `af86` decimal(23,0) DEFAULT NULL,
  `am87` decimal(23,0) DEFAULT NULL,
  `af87` decimal(23,0) DEFAULT NULL,
  `am88` decimal(23,0) DEFAULT NULL,
  `af88` decimal(23,0) DEFAULT NULL,
  `am89` decimal(23,0) DEFAULT NULL,
  `af89` decimal(23,0) DEFAULT NULL,
  `am90` decimal(23,0) DEFAULT NULL,
  `af90` decimal(23,0) DEFAULT NULL,
  `am91` decimal(23,0) DEFAULT NULL,
  `af91` decimal(23,0) DEFAULT NULL,
  `am92` decimal(23,0) DEFAULT NULL,
  `af92` decimal(23,0) DEFAULT NULL,
  `am93` decimal(23,0) DEFAULT NULL,
  `af93` decimal(23,0) DEFAULT NULL,
  `am94` decimal(23,0) DEFAULT NULL,
  `af94` decimal(23,0) DEFAULT NULL,
  `am95` decimal(23,0) DEFAULT NULL,
  `af95` decimal(23,0) DEFAULT NULL,
  `am96` decimal(23,0) DEFAULT NULL,
  `af96` decimal(23,0) DEFAULT NULL,
  `am97` decimal(23,0) DEFAULT NULL,
  `af97` decimal(23,0) DEFAULT NULL,
  `am98` decimal(23,0) DEFAULT NULL,
  `af98` decimal(23,0) DEFAULT NULL,
  `am99` decimal(23,0) DEFAULT NULL,
  `af99` decimal(23,0) DEFAULT NULL,
  `am100` decimal(23,0) DEFAULT NULL,
  `af100` decimal(23,0) DEFAULT NULL,
  `am100u` decimal(23,0) DEFAULT NULL,
  `af100u` decimal(23,0) DEFAULT NULL,
  `totalm` decimal(23,0) DEFAULT NULL,
  `totalf` decimal(23,0) DEFAULT NULL,
  `total` decimal(23,0) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of sys_pyramid_level_1
-- ----------------------------

-- ----------------------------
-- Table structure for sys_pyramid_level_2
-- ----------------------------
DROP TABLE IF EXISTS `sys_pyramid_level_2`;
CREATE TABLE `sys_pyramid_level_2` (
  `hospcode` char(5) NOT NULL DEFAULT '',
  `hospname` varchar(255) DEFAULT NULL,
  `m0_4` decimal(27,0) DEFAULT NULL,
  `m5_9` decimal(27,0) DEFAULT NULL,
  `m10_14` decimal(27,0) DEFAULT NULL,
  `m15_19` decimal(27,0) DEFAULT NULL,
  `m20_24` decimal(27,0) DEFAULT NULL,
  `m25_29` decimal(27,0) DEFAULT NULL,
  `m30_34` decimal(27,0) DEFAULT NULL,
  `m35_39` decimal(27,0) DEFAULT NULL,
  `m40_44` decimal(27,0) DEFAULT NULL,
  `m45_49` decimal(27,0) DEFAULT NULL,
  `m50_54` decimal(27,0) DEFAULT NULL,
  `m55_59` decimal(27,0) DEFAULT NULL,
  `m60_64` decimal(27,0) DEFAULT NULL,
  `m65_69` decimal(27,0) DEFAULT NULL,
  `m70_74` decimal(27,0) DEFAULT NULL,
  `m75_79` decimal(27,0) DEFAULT NULL,
  `m80_84` decimal(27,0) DEFAULT NULL,
  `m85_89` decimal(27,0) DEFAULT NULL,
  `m90_94` decimal(27,0) DEFAULT NULL,
  `m95_99` decimal(27,0) DEFAULT NULL,
  `m100` decimal(23,0) DEFAULT NULL,
  `f0_4` decimal(27,0) DEFAULT NULL,
  `f5_9` decimal(27,0) DEFAULT NULL,
  `f10_14` decimal(27,0) DEFAULT NULL,
  `f15_19` decimal(27,0) DEFAULT NULL,
  `f20_24` decimal(27,0) DEFAULT NULL,
  `f25_29` decimal(27,0) DEFAULT NULL,
  `f30_34` decimal(27,0) DEFAULT NULL,
  `f35_39` decimal(27,0) DEFAULT NULL,
  `f40_44` decimal(27,0) DEFAULT NULL,
  `f45_49` decimal(27,0) DEFAULT NULL,
  `f50_54` decimal(27,0) DEFAULT NULL,
  `f55_59` decimal(27,0) DEFAULT NULL,
  `f60_64` decimal(27,0) DEFAULT NULL,
  `f65_69` decimal(27,0) DEFAULT NULL,
  `f70_74` decimal(27,0) DEFAULT NULL,
  `f75_79` decimal(27,0) DEFAULT NULL,
  `f80_84` decimal(27,0) DEFAULT NULL,
  `f85_89` decimal(27,0) DEFAULT NULL,
  `f90_94` decimal(27,0) DEFAULT NULL,
  `f95_99` decimal(27,0) DEFAULT NULL,
  `f100` decimal(23,0) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of sys_pyramid_level_2
-- ----------------------------

-- ----------------------------
-- Table structure for sys_pyramid_level_3
-- ----------------------------
DROP TABLE IF EXISTS `sys_pyramid_level_3`;
CREATE TABLE `sys_pyramid_level_3` (
  `age_range` varchar(10) NOT NULL,
  `hospcode` varchar(5) NOT NULL,
  `male` decimal(10,0) DEFAULT NULL,
  `female` decimal(10,0) DEFAULT NULL,
  PRIMARY KEY (`age_range`,`hospcode`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of sys_pyramid_level_3
-- ----------------------------

-- ----------------------------
-- Table structure for sys_transform_plus
-- ----------------------------
INSERT INTO `sys_transform_plus` VALUES ('2', 'sys_dhdc_pond', 'INSERT INTO hdc_log(p_date,p_name)values(now(),\'cal_pyramid_level_1\');\r\nCALL cal_pyramid_level_1;\r\nINSERT INTO hdc_log(p_date,p_name)values(now(),\'cal_pyramid_level_2\');\r\nCALL cal_pyramid_level_2;\r\nINSERT INTO hdc_log(p_date,p_name)values(now(),\'cal_pyramid_level_3\');\r\nCALL cal_pyramid_level_3;\r\nINSERT INTO hdc_log(p_date,p_name)values(now(),\'cal_sys_person_type\');\r\nCALL cal_sys_person_type;\r\n\r\n\r\n\r\n##END\r\nCALL t_version_db_update;\r\nCALL last_transform;\r\nINSERT INTO hdc_log(p_date,p_name)values(now(),\'end\');\r\nCALL end_process; ', '1', 'pond', '20170808');

-- ----------------------------
-- Procedure structure for cal_pyramid_level_1
-- ----------------------------
DROP PROCEDURE IF EXISTS `cal_pyramid_level_1`;
DELIMITER ;;
CREATE DEFINER=`root`@`%` PROCEDURE `cal_pyramid_level_1`()
BEGIN
UPDATE sys_check_process t set t.fnc_name = 'cal_pyramid_level_1' , t.time = NOW();	
	
#set @bdg_date=bdg_date;
set @bdg_date = (SELECT note2 FROM sys_config_main LIMIT 1);

DROP TABLE IF EXISTS sys_pyramid_level_1 ;
CREATE TABLE sys_pyramid_level_1 select * from (

select SQL_BIG_RESULT  dh.hoscode as hospcode,dh.hosname as hospname 
,sum(if(p.sex=1  and p.birth  between date_add(date_sub(@bdg_date,interval '1' year),interval '1' day) and date_sub(@bdg_date,interval '0' year),1,0)) as am00
,sum(if(p.sex=2  and p.birth  between date_add(date_sub(@bdg_date,interval '1' year),interval '1' day) and date_sub(@bdg_date,interval '0' year),1,0)) as af00  
,sum(if(p.sex=1  and p.birth  between date_add(date_sub(@bdg_date,interval '2' year),interval '1' day) and date_sub(@bdg_date,interval '1' year),1,0)) as am01
,sum(if(p.sex=2  and p.birth  between date_add(date_sub(@bdg_date,interval '2' year),interval '1' day) and date_sub(@bdg_date,interval '1' year),1,0)) as af01      
,sum(if(p.sex=1  and p.birth  between date_add(date_sub(@bdg_date,interval '3' year),interval '1' day) and date_sub(@bdg_date,interval '2' year),1,0)) as am02
,sum(if(p.sex=2  and p.birth  between date_add(date_sub(@bdg_date,interval '3' year),interval '1' day) and date_sub(@bdg_date,interval '2' year),1,0)) as af02  
,sum(if(p.sex=1  and p.birth  between date_add(date_sub(@bdg_date,interval '4' year),interval '1' day) and date_sub(@bdg_date,interval '3' year),1,0)) as am03
,sum(if(p.sex=2  and p.birth  between date_add(date_sub(@bdg_date,interval '4' year),interval '1' day) and date_sub(@bdg_date,interval '3' year),1,0)) as af03 
,sum(if(p.sex=1  and p.birth  between date_add(date_sub(@bdg_date,interval '5' year),interval '1' day) and date_sub(@bdg_date,interval '4' year),1,0)) as am04
,sum(if(p.sex=2  and p.birth  between date_add(date_sub(@bdg_date,interval '5' year),interval '1' day) and date_sub(@bdg_date,interval '4' year),1,0)) as af04   ,sum(if(p.sex=1  and p.birth  between date_add(date_sub(@bdg_date,interval '6' year),interval '1' day) and date_sub(@bdg_date,interval '5' year),1,0)) as am05,  sum(if(p.sex=2  and p.birth  between date_add(date_sub(@bdg_date,interval '6' year),interval '1' day) and date_sub(@bdg_date,interval '5' year),1,0)) as af05  ,sum(if(p.sex=1  and p.birth  between date_add(date_sub(@bdg_date,interval '7' year),interval '1' day) and date_sub(@bdg_date,interval '6' year),1,0)) as am06,  sum(if(p.sex=2  and p.birth  between date_add(date_sub(@bdg_date,interval '7' year),interval '1' day) and date_sub(@bdg_date,interval '6' year),1,0)) as af06  ,sum(if(p.sex=1  and p.birth  between date_add(date_sub(@bdg_date,interval '8' year),interval '1' day) and date_sub(@bdg_date,interval '7' year),1,0)) as am07
,sum(if(p.sex=2  and p.birth  between date_add(date_sub(@bdg_date,interval '8' year),interval '1' day) and date_sub(@bdg_date,interval '7' year),1,0)) as af07  ,sum(if(p.sex=1  and p.birth  between date_add(date_sub(@bdg_date,interval '9' year),interval '1' day) and date_sub(@bdg_date,interval '8' year),1,0)) as am08, sum(if(p.sex=2  and p.birth   between date_add(date_sub(@bdg_date,interval '9' year),interval '1' day) and date_sub(@bdg_date,interval '8' year),1,0)) as af08  ,sum(if(p.sex=1  and p.birth  between date_add(date_sub(@bdg_date,interval '10' year),interval '1' day) and date_sub(@bdg_date,interval '9' year),1,0)) as am09,  sum(if(p.sex=2  and p.birth   between date_add(date_sub(@bdg_date,interval '10' year),interval '1' day) and date_sub(@bdg_date,interval '9' year),1,0)) as af09  ,sum(if(p.sex=1  and p.birth  between date_add(date_sub(@bdg_date,interval '11' year),interval '1' day) and date_sub(@bdg_date,interval '10' year),1,0)) as am10, sum(if(p.sex=2  and p.birth   between date_add(date_sub(@bdg_date,interval '11' year),interval '1' day) and date_sub(@bdg_date,interval '10' year),1,0)) as af10  ,sum(if(p.sex=1  and p.birth  between date_add(date_sub(@bdg_date,interval '12' year),interval '1' day) and date_sub(@bdg_date,interval '11' year),1,0)) as am11, sum(if(p.sex=2  and p.birth   between date_add(date_sub(@bdg_date,interval '12' year),interval '1' day) and date_sub(@bdg_date,interval '11' year),1,0)) as af11  
,sum(if(p.sex=1  and p.birth  between date_add(date_sub(@bdg_date,interval '13' year),interval '1' day) and date_sub(@bdg_date,interval '12' year),1,0)) as am12,  sum(if(p.sex=2  and p.birth  between date_add(date_sub(@bdg_date,interval '13' year),interval '1' day) and date_sub(@bdg_date,interval '12' year),1,0)) as af12  ,sum(if(p.sex=1  and p.birth  between date_add(date_sub(@bdg_date,interval '14' year),interval '1' day) and date_sub(@bdg_date,interval '13' year),1,0)) as am13,  sum(if(p.sex=2  and p.birth  between date_add(date_sub(@bdg_date,interval '14' year),interval '1' day) and date_sub(@bdg_date,interval '13' year),1,0)) as af13  ,sum(if(p.sex=1  and p.birth  between date_add(date_sub(@bdg_date,interval '15' year),interval '1' day) and date_sub(@bdg_date,interval '14' year),1,0)) as am14,  sum(if(p.sex=2  and p.birth  between date_add(date_sub(@bdg_date,interval '15' year),interval '1' day) and date_sub(@bdg_date,interval '14' year),1,0)) as af14  ,sum(if(p.sex=1  and p.birth  between date_add(date_sub(@bdg_date,interval '16' year),interval '1' day) and date_sub(@bdg_date,interval '15' year),1,0)) as am15, sum(if(p.sex=2  and p.birth   between date_add(date_sub(@bdg_date,interval '16' year),interval '1' day) and date_sub(@bdg_date,interval '15' year),1,0)) as af15  ,sum(if(p.sex=1  and p.birth  between date_add(date_sub(@bdg_date,interval '17' year),interval '1' day) and date_sub(@bdg_date,interval '16' year),1,0)) as am16, sum(if(p.sex=2  and p.birth   between date_add(date_sub(@bdg_date,interval '17' year),interval '1' day) and date_sub(@bdg_date,interval '16' year),1,0)) as af16  ,sum(if(p.sex=1  and p.birth  between date_add(date_sub(@bdg_date,interval '18' year),interval '1' day) and date_sub(@bdg_date,interval '17' year),1,0)) as am17, sum(if(p.sex=2  and p.birth   between date_add(date_sub(@bdg_date,interval '18' year),interval '1' day) and date_sub(@bdg_date,interval '17' year),1,0)) as af17 ,sum(if(p.sex=1  and p.birth  between date_add(date_sub(@bdg_date,interval '19' year),interval '1' day) and date_sub(@bdg_date,interval '18' year),1,0)) as am18, sum(if(p.sex=2  and p.birth   between date_add(date_sub(@bdg_date,interval '19' year),interval '1' day) and date_sub(@bdg_date,interval '18' year),1,0)) as af18  ,sum(if(p.sex=1  and p.birth  between date_add(date_sub(@bdg_date,interval '20' year),interval '1' day) and date_sub(@bdg_date,interval '19' year),1,0)) as am19, sum(if(p.sex=2  and p.birth   between date_add(date_sub(@bdg_date,interval '20' year),interval '1' day) and date_sub(@bdg_date,interval '19' year),1,0)) as af19 ,sum(if(p.sex=1  and p.birth  between date_add(date_sub(@bdg_date,interval '21' year),interval '1' day) and date_sub(@bdg_date,interval '20' year),1,0)) as am20,  sum(if(p.sex=2  and p.birth   between date_add(date_sub(@bdg_date,interval '21' year),interval '1' day) and date_sub(@bdg_date,interval '20' year),1,0)) as af20  ,sum(if(p.sex=1  and p.birth  between date_add(date_sub(@bdg_date,interval '22' year),interval '1' day) and date_sub(@bdg_date,interval '21' year),1,0)) as am21, sum(if(p.sex=2  and p.birth   between date_add(date_sub(@bdg_date,interval '22' year),interval '1' day) and date_sub(@bdg_date,interval '21' year),1,0)) as af21  ,sum(if(p.sex=1  and p.birth  between date_add(date_sub(@bdg_date,interval '23' year),interval '1' day) and date_sub(@bdg_date,interval '22' year),1,0)) as am22, sum(if(p.sex=2  and p.birth   between date_add(date_sub(@bdg_date,interval '23' year),interval '1' day) and date_sub(@bdg_date,interval '22' year),1,0)) as af22  ,sum(if(p.sex=1  and p.birth  between date_add(date_sub(@bdg_date,interval '24' year),interval '1' day) and date_sub(@bdg_date,interval '23' year),1,0)) as am23,   sum(if(p.sex=2  and p.birth  between date_add(date_sub(@bdg_date,interval '24' year),interval '1' day) and date_sub(@bdg_date,interval '23' year),1,0)) as af23  ,sum(if(p.sex=1  and p.birth  between date_add(date_sub(@bdg_date,interval '25' year),interval '1' day) and date_sub(@bdg_date,interval '24' year),1,0)) as am24, sum(if(p.sex=2  and p.birth   between date_add(date_sub(@bdg_date,interval '25' year),interval '1' day) and date_sub(@bdg_date,interval '24' year),1,0)) as af24  ,sum(if(p.sex=1  and p.birth  between date_add(date_sub(@bdg_date,interval '26' year),interval '1' day) and date_sub(@bdg_date,interval '25' year),1,0)) as am25,  sum(if(p.sex=2  and p.birth  between date_add(date_sub(@bdg_date,interval '26' year),interval '1' day) and date_sub(@bdg_date,interval '25' year),1,0)) as af25  ,sum(if(p.sex=1  and p.birth  between date_add(date_sub(@bdg_date,interval '27' year),interval '1' day) and date_sub(@bdg_date,interval '26' year),1,0)) as am26,  sum(if(p.sex=2  and p.birth  between date_add(date_sub(@bdg_date,interval '27' year),interval '1' day) and date_sub(@bdg_date,interval '26' year),1,0)) as af26  ,sum(if(p.sex=1  and p.birth  between date_add(date_sub(@bdg_date,interval '28' year),interval '1' day) and date_sub(@bdg_date,interval '27' year),1,0)) as am27,  sum(if(p.sex=2  and p.birth  between date_add(date_sub(@bdg_date,interval '28' year),interval '1' day) and date_sub(@bdg_date,interval '27' year),1,0)) as af27  ,sum(if(p.sex=1  and p.birth  between date_add(date_sub(@bdg_date,interval '29' year),interval '1' day) and date_sub(@bdg_date,interval '28' year),1,0)) as am28,  sum(if(p.sex=2  and p.birth  between date_add(date_sub(@bdg_date,interval '29' year),interval '1' day) and date_sub(@bdg_date,interval '28' year),1,0)) as af28  ,sum(if(p.sex=1  and p.birth  between date_add(date_sub(@bdg_date,interval '30' year),interval '1' day) and date_sub(@bdg_date,interval '29' year),1,0)) as am29,  sum(if(p.sex=2  and p.birth  between date_add(date_sub(@bdg_date,interval '30' year),interval '1' day) and date_sub(@bdg_date,interval '29' year),1,0)) as af29  ,sum(if(p.sex=1  and p.birth  between date_add(date_sub(@bdg_date,interval '31' year),interval '1' day) and date_sub(@bdg_date,interval '30' year),1,0)) as am30,   sum(if(p.sex=2  and p.birth  between date_add(date_sub(@bdg_date,interval '31' year),interval '1' day) and date_sub(@bdg_date,interval '30' year),1,0)) as af30  ,sum(if(p.sex=1  and p.birth  between date_add(date_sub(@bdg_date,interval '32' year),interval '1' day) and date_sub(@bdg_date,interval '31' year),1,0)) as am31,   sum(if(p.sex=2  and p.birth  between date_add(date_sub(@bdg_date,interval '32' year),interval '1' day) and date_sub(@bdg_date,interval '31' year),1,0)) as af31  ,sum(if(p.sex=1  and p.birth  between date_add(date_sub(@bdg_date,interval '33' year),interval '1' day) and date_sub(@bdg_date,interval '32' year),1,0)) as am32,  sum(if(p.sex=2  and p.birth  between date_add(date_sub(@bdg_date,interval '33' year),interval '1' day) and date_sub(@bdg_date,interval '32' year),1,0)) as af32  ,sum(if(p.sex=1  and p.birth  between date_add(date_sub(@bdg_date,interval '34' year),interval '1' day) and date_sub(@bdg_date,interval '33' year),1,0)) as am33,  sum(if(p.sex=2  and p.birth  between date_add(date_sub(@bdg_date,interval '34' year),interval '1' day) and date_sub(@bdg_date,interval '33' year),1,0)) as af33  ,sum(if(p.sex=1  and p.birth  between date_add(date_sub(@bdg_date,interval '35' year),interval '1' day) and date_sub(@bdg_date,interval '34' year),1,0)) as am34,   sum(if(p.sex=2  and p.birth  between date_add(date_sub(@bdg_date,interval '35' year),interval '1' day) and date_sub(@bdg_date,interval '34' year),1,0)) as af34  ,sum(if(p.sex=1  and p.birth  between date_add(date_sub(@bdg_date,interval '36' year),interval '1' day) and date_sub(@bdg_date,interval '35' year),1,0)) as am35,  sum(if(p.sex=2  and p.birth  between date_add(date_sub(@bdg_date,interval '36' year),interval '1' day) and date_sub(@bdg_date,interval '35' year),1,0)) as af35  ,sum(if(p.sex=1  and p.birth  between date_add(date_sub(@bdg_date,interval '37' year),interval '1' day) and date_sub(@bdg_date,interval '36' year),1,0)) as am36,  sum(if(p.sex=2  and p.birth  between date_add(date_sub(@bdg_date,interval '37' year),interval '1' day) and date_sub(@bdg_date,interval '36' year),1,0)) as af36  ,sum(if(p.sex=1  and p.birth  between date_add(date_sub(@bdg_date,interval '38' year),interval '1' day) and date_sub(@bdg_date,interval '37' year),1,0)) as am37,  sum(if(p.sex=2  and p.birth  between date_add(date_sub(@bdg_date,interval '38' year),interval '1' day) and date_sub(@bdg_date,interval '37' year),1,0)) as af37  ,sum(if(p.sex=1  and p.birth  between date_add(date_sub(@bdg_date,interval '39' year),interval '1' day) and date_sub(@bdg_date,interval '38' year),1,0)) as am38,  sum(if(p.sex=2  and p.birth  between date_add(date_sub(@bdg_date,interval '39' year),interval '1' day) and date_sub(@bdg_date,interval '38' year),1,0)) as af38  ,sum(if(p.sex=1  and p.birth  between date_add(date_sub(@bdg_date,interval '40' year),interval '1' day) and date_sub(@bdg_date,interval '39' year),1,0)) as am39,  sum(if(p.sex=2  and p.birth  between date_add(date_sub(@bdg_date,interval '40' year),interval '1' day) and date_sub(@bdg_date,interval '39' year),1,0)) as af39  ,sum(if(p.sex=1  and p.birth  between date_add(date_sub(@bdg_date,interval '41' year),interval '1' day) and date_sub(@bdg_date,interval '40' year),1,0)) as am40,   sum(if(p.sex=2  and p.birth  between date_add(date_sub(@bdg_date,interval '41' year),interval '1' day) and date_sub(@bdg_date,interval '40' year),1,0)) as af40  ,sum(if(p.sex=1  and p.birth  between date_add(date_sub(@bdg_date,interval '42' year),interval '1' day) and date_sub(@bdg_date,interval '41' year),1,0)) as am41,  sum(if(p.sex=2  and p.birth  between date_add(date_sub(@bdg_date,interval '42' year),interval '1' day) and date_sub(@bdg_date,interval '41' year),1,0)) as af41   ,sum(if(p.sex=1  and p.birth  between date_add(date_sub(@bdg_date,interval '43' year),interval '1' day) and date_sub(@bdg_date,interval '42' year),1,0)) as am42,  sum(if(p.sex=2  and p.birth  between date_add(date_sub(@bdg_date,interval '43' year),interval '1' day) and date_sub(@bdg_date,interval '42' year),1,0)) as af42  ,sum(if(p.sex=1  and p.birth  between date_add(date_sub(@bdg_date,interval '44' year),interval '1' day) and date_sub(@bdg_date,interval '43' year),1,0)) as am43,  sum(if(p.sex=2  and p.birth  between date_add(date_sub(@bdg_date,interval '44' year),interval '1' day) and date_sub(@bdg_date,interval '43' year),1,0)) as af43  ,sum(if(p.sex=1  and p.birth  between date_add(date_sub(@bdg_date,interval '45' year),interval '1' day) and date_sub(@bdg_date,interval '44' year),1,0)) as am44,   sum(if(p.sex=2  and p.birth  between date_add(date_sub(@bdg_date,interval '45' year),interval '1' day) and date_sub(@bdg_date,interval '44' year),1,0)) as af44   ,sum(if(p.sex=1  and p.birth  between date_add(date_sub(@bdg_date,interval '46' year),interval '1' day) and date_sub(@bdg_date,interval '45' year),1,0)) as am45,  sum(if(p.sex=2  and p.birth  between date_add(date_sub(@bdg_date,interval '46' year),interval '1' day) and date_sub(@bdg_date,interval '45' year),1,0)) as af45  ,sum(if(p.sex=1  and p.birth  between date_add(date_sub(@bdg_date,interval '47' year),interval '1' day) and date_sub(@bdg_date,interval '46' year),1,0)) as am46,   sum(if(p.sex=2  and p.birth  between date_add(date_sub(@bdg_date,interval '47' year),interval '1' day) and date_sub(@bdg_date,interval '46' year),1,0)) as af46  ,sum(if(p.sex=1  and p.birth  between date_add(date_sub(@bdg_date,interval '48' year),interval '1' day) and date_sub(@bdg_date,interval '47' year),1,0)) as am47,  sum(if(p.sex=2  and p.birth  between date_add(date_sub(@bdg_date,interval '48' year),interval '1' day) and date_sub(@bdg_date,interval '47' year),1,0)) as af47  ,sum(if(p.sex=1  and p.birth  between date_add(date_sub(@bdg_date,interval '49' year),interval '1' day) and date_sub(@bdg_date,interval '48' year),1,0)) as am48,  sum(if(p.sex=2  and p.birth  between date_add(date_sub(@bdg_date,interval '49' year),interval '1' day) and date_sub(@bdg_date,interval '48' year),1,0)) as af48 ,sum(if(p.sex=1  and p.birth  between date_add(date_sub(@bdg_date,interval '50' year),interval '1' day) and date_sub(@bdg_date,interval '49' year),1,0)) as am49,   sum(if(p.sex=2  and p.birth  between date_add(date_sub(@bdg_date,interval '50' year),interval '1' day) and date_sub(@bdg_date,interval '49' year),1,0)) as af49   ,sum(if(p.sex=1  and p.birth  between date_add(date_sub(@bdg_date,interval '51' year),interval '1' day) and date_sub(@bdg_date,interval '50' year),1,0)) as am50,  sum(if(p.sex=2  and p.birth  between date_add(date_sub(@bdg_date,interval '51' year),interval '1' day) and date_sub(@bdg_date,interval '50' year),1,0)) as af50  ,sum(if(p.sex=1  and p.birth  between date_add(date_sub(@bdg_date,interval '52' year),interval '1' day) and date_sub(@bdg_date,interval '51' year),1,0)) as am51,   sum(if(p.sex=2  and p.birth  between date_add(date_sub(@bdg_date,interval '52' year),interval '1' day) and date_sub(@bdg_date,interval '51' year),1,0)) as af51   ,sum(if(p.sex=1  and p.birth  between date_add(date_sub(@bdg_date,interval '53' year),interval '1' day) and date_sub(@bdg_date,interval '52' year),1,0)) as am52,  sum(if(p.sex=2  and p.birth  between date_add(date_sub(@bdg_date,interval '53' year),interval '1' day) and date_sub(@bdg_date,interval '52' year),1,0)) as af52  ,sum(if(p.sex=1  and p.birth  between date_add(date_sub(@bdg_date,interval '54' year),interval '1' day) and date_sub(@bdg_date,interval '53' year),1,0)) as am53,  sum(if(p.sex=2  and p.birth  between date_add(date_sub(@bdg_date,interval '54' year),interval '1' day) and date_sub(@bdg_date,interval '53' year),1,0)) as af53  ,sum(if(p.sex=1  and p.birth  between date_add(date_sub(@bdg_date,interval '55' year),interval '1' day) and date_sub(@bdg_date,interval '54' year),1,0)) as am54,   sum(if(p.sex=2  and p.birth  between date_add(date_sub(@bdg_date,interval '55' year),interval '1' day) and date_sub(@bdg_date,interval '54' year),1,0)) as af54  ,sum(if(p.sex=1  and p.birth  between date_add(date_sub(@bdg_date,interval '56' year),interval '1' day) and date_sub(@bdg_date,interval '55' year),1,0)) as am55,  sum(if(p.sex=2  and p.birth  between date_add(date_sub(@bdg_date,interval '56' year),interval '1' day) and date_sub(@bdg_date,interval '55' year),1,0)) as af55   ,sum(if(p.sex=1  and p.birth  between date_add(date_sub(@bdg_date,interval '57' year),interval '1' day) and date_sub(@bdg_date,interval '56' year),1,0)) as am56,  sum(if(p.sex=2  and p.birth  between date_add(date_sub(@bdg_date,interval '57' year),interval '1' day) and date_sub(@bdg_date,interval '56' year),1,0)) as af56  ,sum(if(p.sex=1  and p.birth  between date_add(date_sub(@bdg_date,interval '58' year),interval '1' day) and date_sub(@bdg_date,interval '57' year),1,0)) as am57,  sum(if(p.sex=2  and p.birth  between date_add(date_sub(@bdg_date,interval '58' year),interval '1' day) and date_sub(@bdg_date,interval '57' year),1,0)) as af57 ,sum(if(p.sex=1  and p.birth  between date_add(date_sub(@bdg_date,interval '59' year),interval '1' day) and date_sub(@bdg_date,interval '58' year),1,0)) as am58,  sum(if(p.sex=2  and p.birth  between date_add(date_sub(@bdg_date,interval '59' year),interval '1' day) and date_sub(@bdg_date,interval '58' year),1,0)) as af58  ,sum(if(p.sex=1  and p.birth  between date_add(date_sub(@bdg_date,interval '60' year),interval '1' day) and date_sub(@bdg_date,interval '59' year),1,0)) as am59,  sum(if(p.sex=2  and p.birth  between date_add(date_sub(@bdg_date,interval '60' year),interval '1' day) and date_sub(@bdg_date,interval '59' year),1,0)) as af59  ,sum(if(p.sex=1  and p.birth  between date_add(date_sub(@bdg_date,interval '61' year),interval '1' day) and date_sub(@bdg_date,interval '60' year),1,0)) as am60,   sum(if(p.sex=2  and p.birth  between date_add(date_sub(@bdg_date,interval '61' year),interval '1' day) and date_sub(@bdg_date,interval '60' year),1,0)) as af60  ,sum(if(p.sex=1  and p.birth  between date_add(date_sub(@bdg_date,interval '62' year),interval '1' day) and date_sub(@bdg_date,interval '61' year),1,0)) as am61,  sum(if(p.sex=2  and p.birth  between date_add(date_sub(@bdg_date,interval '62' year),interval '1' day) and date_sub(@bdg_date,interval '61' year),1,0)) as af61  ,sum(if(p.sex=1  and p.birth  between date_add(date_sub(@bdg_date,interval '63' year),interval '1' day) and date_sub(@bdg_date,interval '62' year),1,0)) as am62,  sum(if(p.sex=2  and p.birth  between date_add(date_sub(@bdg_date,interval '63' year),interval '1' day) and date_sub(@bdg_date,interval '62' year),1,0)) as af62  ,sum(if(p.sex=1  and p.birth  between date_add(date_sub(@bdg_date,interval '64' year),interval '1' day) and date_sub(@bdg_date,interval '63' year),1,0)) as am63,  sum(if(p.sex=2  and p.birth  between date_add(date_sub(@bdg_date,interval '64' year),interval '1' day) and date_sub(@bdg_date,interval '63' year),1,0)) as af63  ,sum(if(p.sex=1  and p.birth  between date_add(date_sub(@bdg_date,interval '65' year),interval '1' day) and date_sub(@bdg_date,interval '64' year),1,0)) as am64,   sum(if(p.sex=2  and p.birth  between date_add(date_sub(@bdg_date,interval '65' year),interval '1' day) and date_sub(@bdg_date,interval '64' year),1,0)) as af64  ,sum(if(p.sex=1  and p.birth  between date_add(date_sub(@bdg_date,interval '66' year),interval '1' day) and date_sub(@bdg_date,interval '65' year),1,0)) as am65,   sum(if(p.sex=2  and p.birth  between date_add(date_sub(@bdg_date,interval '66' year),interval '1' day) and date_sub(@bdg_date,interval '65' year),1,0)) as af65   ,sum(if(p.sex=1  and p.birth  between date_add(date_sub(@bdg_date,interval '67' year),interval '1' day) and date_sub(@bdg_date,interval '66' year),1,0)) as am66,  sum(if(p.sex=2  and p.birth  between date_add(date_sub(@bdg_date,interval '67' year),interval '1' day) and date_sub(@bdg_date,interval '66' year),1,0)) as af66  ,sum(if(p.sex=1  and p.birth  between date_add(date_sub(@bdg_date,interval '68' year),interval '1' day) and date_sub(@bdg_date,interval '67' year),1,0)) as am67,  sum(if(p.sex=2  and p.birth  between date_add(date_sub(@bdg_date,interval '68' year),interval '1' day) and date_sub(@bdg_date,interval '67' year),1,0)) as af67   ,sum(if(p.sex=1  and p.birth  between date_add(date_sub(@bdg_date,interval '69' year),interval '1' day) and date_sub(@bdg_date,interval '68' year),1,0)) as am68,  sum(if(p.sex=2  and p.birth  between date_add(date_sub(@bdg_date,interval '69' year),interval '1' day) and date_sub(@bdg_date,interval '68' year),1,0)) as af68 ,sum(if(p.sex=1  and p.birth  between date_add(date_sub(@bdg_date,interval '70' year),interval '1' day) and date_sub(@bdg_date,interval '69' year),1,0)) as am69,  sum(if(p.sex=2  and p.birth  between date_add(date_sub(@bdg_date,interval '70' year),interval '1' day) and date_sub(@bdg_date,interval '69' year),1,0)) as af69   ,sum(if(p.sex=1  and p.birth  between date_add(date_sub(@bdg_date,interval '71' year),interval '1' day) and date_sub(@bdg_date,interval '70' year),1,0)) as am70,   sum(if(p.sex=2  and p.birth  between date_add(date_sub(@bdg_date,interval '71' year),interval '1' day) and date_sub(@bdg_date,interval '70' year),1,0)) as af70  ,sum(if(p.sex=1  and p.birth  between date_add(date_sub(@bdg_date,interval '72' year),interval '1' day) and date_sub(@bdg_date,interval '71' year),1,0)) as am71,  sum(if(p.sex=2  and p.birth  between date_add(date_sub(@bdg_date,interval '72' year),interval '1' day) and date_sub(@bdg_date,interval '71' year),1,0)) as af71  ,sum(if(p.sex=1  and p.birth  between date_add(date_sub(@bdg_date,interval '73' year),interval '1' day) and date_sub(@bdg_date,interval '72' year),1,0)) as am72,  sum(if(p.sex=2  and p.birth  between date_add(date_sub(@bdg_date,interval '73' year),interval '1' day) and date_sub(@bdg_date,interval '72' year),1,0)) as af72  ,sum(if(p.sex=1  and p.birth  between date_add(date_sub(@bdg_date,interval '74' year),interval '1' day) and date_sub(@bdg_date,interval '73' year),1,0)) as am73,  sum(if(p.sex=2  and p.birth  between date_add(date_sub(@bdg_date,interval '74' year),interval '1' day) and date_sub(@bdg_date,interval '73' year),1,0)) as af73  ,sum(if(p.sex=1  and p.birth  between date_add(date_sub(@bdg_date,interval '75' year),interval '1' day) and date_sub(@bdg_date,interval '74' year),1,0)) as am74,  sum(if(p.sex=2  and p.birth  between date_add(date_sub(@bdg_date,interval '75' year),interval '1' day) and date_sub(@bdg_date,interval '74' year),1,0)) as af74   ,sum(if(p.sex=1  and p.birth  between date_add(date_sub(@bdg_date,interval '76' year),interval '1' day) and date_sub(@bdg_date,interval '75' year),1,0)) as am75,   sum(if(p.sex=2  and p.birth  between date_add(date_sub(@bdg_date,interval '76' year),interval '1' day) and date_sub(@bdg_date,interval '75' year),1,0)) as af75  ,sum(if(p.sex=1  and p.birth  between date_add(date_sub(@bdg_date,interval '77' year),interval '1' day) and date_sub(@bdg_date,interval '76' year),1,0)) as am76,   sum(if(p.sex=2  and p.birth  between date_add(date_sub(@bdg_date,interval '77' year),interval '1' day) and date_sub(@bdg_date,interval '76' year),1,0)) as af76  ,sum(if(p.sex=1  and p.birth  between date_add(date_sub(@bdg_date,interval '78' year),interval '1' day) and date_sub(@bdg_date,interval '77' year),1,0)) as am77,   sum(if(p.sex=2  and p.birth  between date_add(date_sub(@bdg_date,interval '78' year),interval '1' day) and date_sub(@bdg_date,interval '77' year),1,0)) as af77  ,sum(if(p.sex=1  and p.birth  between date_add(date_sub(@bdg_date,interval '79' year),interval '1' day) and date_sub(@bdg_date,interval '78' year),1,0)) as am78,   sum(if(p.sex=2  and p.birth  between date_add(date_sub(@bdg_date,interval '79' year),interval '1' day) and date_sub(@bdg_date,interval '78' year),1,0)) as af78  ,sum(if(p.sex=1  and p.birth  between date_add(date_sub(@bdg_date,interval '80' year),interval '1' day) and date_sub(@bdg_date,interval '79' year),1,0)) as am79,   sum(if(p.sex=2  and p.birth  between date_add(date_sub(@bdg_date,interval '80' year),interval '1' day) and date_sub(@bdg_date,interval '79' year),1,0)) as af79  ,sum(if(p.sex=1  and p.birth  between date_add(date_sub(@bdg_date,interval '81' year),interval '1' day) and date_sub(@bdg_date,interval '80' year),1,0)) as am80,  sum(if(p.sex=2  and p.birth  between date_add(date_sub(@bdg_date,interval '81' year),interval '1' day) and date_sub(@bdg_date,interval '80' year),1,0)) as af80  ,sum(if(p.sex=1  and p.birth  between date_add(date_sub(@bdg_date,interval '82' year),interval '1' day) and date_sub(@bdg_date,interval '81' year),1,0)) as am81,  sum(if(p.sex=2  and p.birth  between date_add(date_sub(@bdg_date,interval '82' year),interval '1' day) and date_sub(@bdg_date,interval '81' year),1,0)) as af81  ,sum(if(p.sex=1  and p.birth  between date_add(date_sub(@bdg_date,interval '83' year),interval '1' day) and date_sub(@bdg_date,interval '82' year),1,0)) as am82,  sum(if(p.sex=2  and p.birth  between date_add(date_sub(@bdg_date,interval '83' year),interval '1' day) and date_sub(@bdg_date,interval '82' year),1,0)) as af82  ,sum(if(p.sex=1  and p.birth  between date_add(date_sub(@bdg_date,interval '84' year),interval '1' day) and date_sub(@bdg_date,interval '83' year),1,0)) as am83,  sum(if(p.sex=2  and p.birth  between date_add(date_sub(@bdg_date,interval '84' year),interval '1' day) and date_sub(@bdg_date,interval '83' year),1,0)) as af83   ,sum(if(p.sex=1  and p.birth  between date_add(date_sub(@bdg_date,interval '85' year),interval '1' day) and date_sub(@bdg_date,interval '84' year),1,0)) as am84,   sum(if(p.sex=2  and p.birth  between date_add(date_sub(@bdg_date,interval '85' year),interval '1' day) and date_sub(@bdg_date,interval '84' year),1,0)) as af84  ,sum(if(p.sex=1  and p.birth  between date_add(date_sub(@bdg_date,interval '86' year),interval '1' day) and date_sub(@bdg_date,interval '85' year),1,0)) as am85,   sum(if(p.sex=2  and p.birth  between date_add(date_sub(@bdg_date,interval '86' year),interval '1' day) and date_sub(@bdg_date,interval '85' year),1,0)) as af85  ,sum(if(p.sex=1  and p.birth  between date_add(date_sub(@bdg_date,interval '87' year),interval '1' day) and date_sub(@bdg_date,interval '86' year),1,0)) as am86,   sum(if(p.sex=2  and p.birth  between date_add(date_sub(@bdg_date,interval '87' year),interval '1' day) and date_sub(@bdg_date,interval '86' year),1,0)) as af86   ,sum(if(p.sex=1  and p.birth  between date_add(date_sub(@bdg_date,interval '88' year),interval '1' day) and date_sub(@bdg_date,interval '87' year),1,0)) as am87,  sum(if(p.sex=2  and p.birth  between date_add(date_sub(@bdg_date,interval '88' year),interval '1' day) and date_sub(@bdg_date,interval '87' year),1,0)) as af87  ,sum(if(p.sex=1  and p.birth  between date_add(date_sub(@bdg_date,interval '89' year),interval '1' day) and date_sub(@bdg_date,interval '88' year),1,0)) as am88,  sum(if(p.sex=2  and p.birth  between date_add(date_sub(@bdg_date,interval '89' year),interval '1' day) and date_sub(@bdg_date,interval '88' year),1,0)) as af88  ,sum(if(p.sex=1  and p.birth  between date_add(date_sub(@bdg_date,interval '90' year),interval '1' day) and date_sub(@bdg_date,interval '89' year),1,0)) as am89,   sum(if(p.sex=2  and p.birth  between date_add(date_sub(@bdg_date,interval '90' year),interval '1' day) and date_sub(@bdg_date,interval '89' year),1,0)) as af89   ,sum(if(p.sex=1  and p.birth  between date_add(date_sub(@bdg_date,interval '91' year),interval '1' day) and date_sub(@bdg_date,interval '90' year),1,0)) as am90,   sum(if(p.sex=2  and p.birth  between date_add(date_sub(@bdg_date,interval '91' year),interval '1' day) and date_sub(@bdg_date,interval '90' year),1,0)) as af90  ,sum(if(p.sex=1  and p.birth  between date_add(date_sub(@bdg_date,interval '92' year),interval '1' day) and date_sub(@bdg_date,interval '91' year),1,0)) as am91,  sum(if(p.sex=2  and p.birth  between date_add(date_sub(@bdg_date,interval '92' year),interval '1' day) and date_sub(@bdg_date,interval '91' year),1,0)) as af91 ,sum(if(p.sex=1  and p.birth  between date_add(date_sub(@bdg_date,interval '93' year),interval '1' day) and date_sub(@bdg_date,interval '92' year),1,0)) as am92,  sum(if(p.sex=2  and p.birth  between date_add(date_sub(@bdg_date,interval '93' year),interval '1' day) and date_sub(@bdg_date,interval '92' year),1,0)) as af92  ,sum(if(p.sex=1  and p.birth  between date_add(date_sub(@bdg_date,interval '94' year),interval '1' day) and date_sub(@bdg_date,interval '93' year),1,0)) as am93,  sum(if(p.sex=2  and p.birth  between date_add(date_sub(@bdg_date,interval '94' year),interval '1' day) and date_sub(@bdg_date,interval '93' year),1,0)) as af93  ,sum(if(p.sex=1  and p.birth  between date_add(date_sub(@bdg_date,interval '95' year),interval '1' day) and date_sub(@bdg_date,interval '94' year),1,0)) as am94,  sum(if(p.sex=2  and p.birth  between date_add(date_sub(@bdg_date,interval '95' year),interval '1' day) and date_sub(@bdg_date,interval '94' year),1,0)) as af94  ,sum(if(p.sex=1  and p.birth  between date_add(date_sub(@bdg_date,interval '96' year),interval '1' day) and date_sub(@bdg_date,interval '95' year),1,0)) as am95, sum(if(p.sex=2  and p.birth  between date_add(date_sub(@bdg_date,interval '96' year),interval '1' day) and date_sub(@bdg_date,interval '95' year),1,0)) as af95   ,sum(if(p.sex=1  and p.birth  between date_add(date_sub(@bdg_date,interval '97' year),interval '1' day) and date_sub(@bdg_date,interval '96' year),1,0)) as am96,   sum(if(p.sex=2  and p.birth  between date_add(date_sub(@bdg_date,interval '97' year),interval '1' day) and date_sub(@bdg_date,interval '96' year),1,0)) as af96  ,sum(if(p.sex=1  and p.birth  between date_add(date_sub(@bdg_date,interval '98' year),interval '1' day) and date_sub(@bdg_date,interval '97' year),1,0)) as am97,   sum(if(p.sex=2  and p.birth  between date_add(date_sub(@bdg_date,interval '98' year),interval '1' day) and date_sub(@bdg_date,interval '97' year),1,0)) as af97  ,sum(if(p.sex=1  and p.birth  between date_add(date_sub(@bdg_date,interval '99' year),interval '1' day) and date_sub(@bdg_date,interval '98' year),1,0)) as am98,  sum(if(p.sex=2  and p.birth  between date_add(date_sub(@bdg_date,interval '99' year),interval '1' day) and date_sub(@bdg_date,interval '98' year),1,0)) as af98  ,sum(if(p.sex=1  and p.birth  between date_add(date_sub(@bdg_date,interval '100' year),interval '1' day) and date_sub(@bdg_date,interval '99' year),1,0)) as am99,  sum(if(p.sex=2  and p.birth  between date_add(date_sub(@bdg_date,interval '100' year),interval '1' day) and date_sub(@bdg_date,interval '99' year),1,0)) as af99  ,sum(if(p.sex=1  and p.birth  between date_add(date_sub(@bdg_date,interval '101' year),interval '1' day) and date_sub(@bdg_date,interval '100' year),1,0)) as am100,   sum(if(p.sex=2  and p.birth  between date_add(date_sub(@bdg_date,interval '101' year),interval '1' day) and date_sub(@bdg_date,interval '100' year),1,0)) as af100  ,sum(if(p.sex=1  and p.birth  between date_add(date_sub(@bdg_date,interval '102' year),interval '1' day) and date_sub(@bdg_date,interval '150' year),1,0)) as am100u,  sum(if(p.sex=2  and p.birth  between date_add(date_sub(@bdg_date,interval '102' year),interval '1' day) and date_sub(@bdg_date,interval '150' year),1,0)) as af100u,   sum(if(p.sex=1  and p.birth  between date_add(date_sub(@bdg_date,interval '150' year),interval '1' day) and date_sub(@bdg_date,interval '0' year),1,0)) as totalm,   sum(if(p.sex=2  and p.birth  between date_add(date_sub(@bdg_date,interval '150' year),interval '1' day) and date_sub(@bdg_date,interval '0' year),1,0)) as totalf,   sum(if(p.birth  between date_add(date_sub(@bdg_date,interval '150' year),interval '1' day) and date_sub(@bdg_date,interval '0' year),1,0)) as total   
from person p    
inner  join chospital_amp  dh on p.hospcode = dh.hoscode  
where  p.discharge='9'   and p.nation ='099' and p.typearea in('1','3','5')   
group by dh.hoscode  order by hoscode asc


) t;

UPDATE sys_check_process t set t.fnc_name = 'end_cal_pyramid_level_1' , t.time = NOW();	

END
;;
DELIMITER ;

-- ----------------------------
-- Procedure structure for cal_pyramid_level_2
-- ----------------------------
DROP PROCEDURE IF EXISTS `cal_pyramid_level_2`;
DELIMITER ;;
CREATE DEFINER=`root`@`%` PROCEDURE `cal_pyramid_level_2`()
BEGIN

UPDATE sys_check_process t set t.fnc_name = 'cal_pyramid_level_2' , t.time = NOW();	

DROP TABLE IF EXISTS sys_pyramid_level_2;
CREATE TABLE sys_pyramid_level_2 select * from (
SELECT 
t.hospcode,t.hospname
,t.am00+t.am01+t.am02+t.am03+t.am04 as m0_4
,t.am05+t.am06+t.am07+t.am08+t.am09 as m5_9
,t.am10+t.am11+t.am12+t.am14+t.am14 as m10_14
,t.am15+t.am16+t.am17+t.am18+t.am19 as m15_19
,t.am20+t.am21+t.am22+t.am23+t.am24 as m20_24
,t.am25+t.am26+t.am27+t.am28+t.am29 as m25_29
,t.am30+t.am31+t.am32+t.am33+t.am34 as m30_34
,t.am35+t.am36+t.am37+t.am38+t.am39 as m35_39
,t.am40+t.am41+t.am42+t.am43+t.am44 as m40_44
,t.am45+t.am46+t.am47+t.am48+t.am49 as m45_49
,t.am50+t.am51+t.am52+t.am53+t.am54 as m50_54
,t.am55+t.am56+t.am57+t.am58+t.am59 as m55_59
,t.am60+t.am61+t.am62+t.am63+t.am64 as m60_64
,t.am65+t.am66+t.am67+t.am68+t.am69 as m65_69
,t.am70+t.am71+t.am72+t.am73+t.am74 as m70_74
,t.am75+t.am76+t.am77+t.am78+t.am79 as m75_79
,t.am80+t.am81+t.am82+t.am83+t.am84 as m80_84
,t.am85+t.am86+t.am87+t.am88+t.am89 as m85_89
,t.am90+t.am91+t.am92+t.am93+t.am94 as m90_94
,t.am95+t.am96+t.am97+t.am98+t.am99 as m95_99
,t.am100 as m100
#####
,t.af00+t.af01+t.af02+t.af03+t.af04 as f0_4
,t.af05+t.af06+t.af07+t.af08+t.af09 as f5_9
,t.af10+t.af11+t.af12+t.af14+t.af14 as f10_14
,t.af15+t.af16+t.af17+t.af18+t.af19 as f15_19
,t.af20+t.af21+t.af22+t.af23+t.af24 as f20_24
,t.af25+t.af26+t.af27+t.af28+t.af29 as f25_29
,t.af30+t.af31+t.af32+t.af33+t.af34 as f30_34
,t.af35+t.af36+t.af37+t.af38+t.af39 as f35_39
,t.af40+t.af41+t.af42+t.af43+t.af44 as f40_44
,t.af45+t.af46+t.af47+t.af48+t.af49 as f45_49
,t.af50+t.af51+t.af52+t.af53+t.af54 as f50_54
,t.af55+t.af56+t.af57+t.af58+t.af59 as f55_59
,t.af60+t.af61+t.af62+t.af63+t.af64 as f60_64
,t.af65+t.af66+t.af67+t.af68+t.af69 as f65_69
,t.af70+t.af71+t.af72+t.af73+t.af74 as f70_74
,t.af75+t.af76+t.af77+t.af78+t.af79 as f75_79
,t.af80+t.af81+t.af82+t.af83+t.af84 as f80_84
,t.af85+t.af86+t.af87+t.af88+t.af89 as f85_89
,t.af90+t.af91+t.af92+t.af93+t.af94 as f90_94
,t.af95+t.af96+t.af97+t.af98+t.af99 as f95_99
,t.af100 as f100

from sys_pyramid_level_1 t

)	t;

UPDATE sys_check_process t set t.fnc_name = 'end_cal_pyramid_level_2' , t.time = NOW();	
END
;;
DELIMITER ;

-- ----------------------------
-- Procedure structure for cal_pyramid_level_3
-- ----------------------------
DROP PROCEDURE IF EXISTS `cal_pyramid_level_3`;
DELIMITER ;;
CREATE DEFINER=`root`@`%` PROCEDURE `cal_pyramid_level_3`()
BEGIN	

UPDATE sys_check_process t set t.fnc_name = 'cal_pyramid_level_3' , t.time = NOW();	

TRUNCATE sys_pyramid_level_3;
REPLACE into sys_pyramid_level_3 SELECT 'a,0-4',t.hospcode,t.m0_4,t.f0_4 			from sys_pyramid_level_2 t;
REPLACE into sys_pyramid_level_3 SELECT 'b,5-9',t.hospcode,t.m5_9,t.f5_9 			from sys_pyramid_level_2 t;

REPLACE into sys_pyramid_level_3 SELECT 'c,10-14',t.hospcode,t.m10_14,t.f10_14 from sys_pyramid_level_2 t;
REPLACE into sys_pyramid_level_3 SELECT 'd,15-19',t.hospcode,t.m15_19,t.f15_19 from sys_pyramid_level_2 t;

REPLACE into sys_pyramid_level_3 SELECT 'e,20-24',t.hospcode,t.m20_24,t.f20_24 from sys_pyramid_level_2 t;
REPLACE into sys_pyramid_level_3 SELECT 'f,25-29',t.hospcode,t.m25_29,t.f25_29 from sys_pyramid_level_2 t;

REPLACE into sys_pyramid_level_3 SELECT 'g,30-34',t.hospcode,t.m30_34,t.f30_34 from sys_pyramid_level_2 t;
REPLACE into sys_pyramid_level_3 SELECT 'h,35-39',t.hospcode,t.m35_39,t.f35_39 from sys_pyramid_level_2 t;

REPLACE into sys_pyramid_level_3 SELECT 'i,40-44',t.hospcode,t.m40_44,t.f40_44 from sys_pyramid_level_2 t;
REPLACE into sys_pyramid_level_3 SELECT 'j,45-49',t.hospcode,t.m45_49,t.f45_49 from sys_pyramid_level_2 t;

REPLACE into sys_pyramid_level_3 SELECT 'k,50-54',t.hospcode,t.m50_54,t.f50_54 from sys_pyramid_level_2 t;
REPLACE into sys_pyramid_level_3 SELECT 'l,55-59',t.hospcode,t.m55_59,t.f55_59 from sys_pyramid_level_2 t;

REPLACE into sys_pyramid_level_3 SELECT 'm,60-64',t.hospcode,t.m60_64,t.f60_64 from sys_pyramid_level_2 t;
REPLACE into sys_pyramid_level_3 SELECT 'n,65-69',t.hospcode,t.m65_69,t.f65_69 from sys_pyramid_level_2 t;

REPLACE into sys_pyramid_level_3 SELECT 'o,70-74',t.hospcode,t.m70_74,t.f70_74 from sys_pyramid_level_2 t;
REPLACE into sys_pyramid_level_3 SELECT 'p,75-79',t.hospcode,t.m75_79,t.f75_79 from sys_pyramid_level_2 t;

REPLACE into sys_pyramid_level_3 SELECT 'q,80-84',t.hospcode,t.m80_84,t.f80_84 from sys_pyramid_level_2 t;
REPLACE into sys_pyramid_level_3 SELECT 'r,85-89',t.hospcode,t.m85_89,t.f85_89 from sys_pyramid_level_2 t;

REPLACE into sys_pyramid_level_3 SELECT 's,90-94',t.hospcode,t.m90_94,t.f90_94 from sys_pyramid_level_2 t;
REPLACE into sys_pyramid_level_3 SELECT 't,95-99',t.hospcode,t.m95_99,t.f95_99 from sys_pyramid_level_2 t;

REPLACE into sys_pyramid_level_3 SELECT 'u,100+',t.hospcode,t.m100,t.f100 from sys_pyramid_level_2 t;


UPDATE sys_check_process t set t.fnc_name = 'end_cal_pyramid_level_3' , t.time = NOW();	
END
;;
DELIMITER ;

-- ----------------------------
-- Procedure structure for cal_sys_person_type
-- ----------------------------
DROP PROCEDURE IF EXISTS `cal_sys_person_type`;
DELIMITER ;;
CREATE DEFINER=`root`@`%` PROCEDURE `cal_sys_person_type`()
BEGIN
UPDATE sys_check_process t set t.fnc_name = 'cal_sys_person_type' , t.time = NOW();	

drop TABLE  IF EXISTS sys_person_type;
CREATE TABLE sys_person_type SELECT * from (
select SQL_BIG_RESULT  h.hoscode as hospcode ,h.hosname as hospname,type1,type2,type3,type4,type5,nottype,total
from chospital_amp h
left join 
   (select person.hospcode  ,count(*) as total
		,sum(if(person.typearea='1',1,0)) as type1
    ,sum(if(person.typearea='2',1,0)) as type2
		,sum(if(person.typearea='3',1,0)) as type3
		,sum(if(person.typearea='4',1,0)) as type4
		,sum(if(person.typearea='5',1,0)) as type5
    ,sum(if(person.typearea not in ('1','2','3','4','5'),1,0)) as nottype
    from person
    where person.discharge = '9'  
		#and person.nation ='099' 
    group by person.hospcode
    order by hospcode) as pa
on h.hoscode = pa.hospcode

order by hoscode asc ) t	;

UPDATE sys_check_process t set t.fnc_name = 'end_cal_sys_person_type' , t.time = NOW();
END
;;
DELIMITER ;

-- ----------------------------
-- Procedure structure for sys_dhdc_pond
-- ----------------------------
DROP PROCEDURE IF EXISTS `sys_dhdc_pond`;
DELIMITER ;;
CREATE DEFINER=`root`@`%` PROCEDURE `sys_dhdc_pond`()
BEGIN 
INSERT INTO hdc_log(p_date,p_name)values(now(),'cal_pyramid_level_1');
CALL cal_pyramid_level_1;
INSERT INTO hdc_log(p_date,p_name)values(now(),'cal_pyramid_level_2');
CALL cal_pyramid_level_2;
INSERT INTO hdc_log(p_date,p_name)values(now(),'cal_pyramid_level_3');
CALL cal_pyramid_level_3;
INSERT INTO hdc_log(p_date,p_name)values(now(),'cal_sys_person_type');
CALL cal_sys_person_type;



##END
CALL t_version_db_update;
CALL last_transform;
INSERT INTO hdc_log(p_date,p_name)values(now(),'end');
CALL end_process; 
 END
;;
DELIMITER ;
