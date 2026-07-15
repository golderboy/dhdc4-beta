/*
Navicat MySQL Data Transfer

Source Server         : DC_SOPMOEI_IN
Source Server Version : 50505
Source Host           : 192.168.1.225:3306
Source Database       : db_demo

Target Server Type    : MYSQL
Target Server Version : 50505
File Encoding         : 65001

Date: 2017-09-05 16:05:13
*/

SET FOREIGN_KEY_CHECKS=0;

DELETE FROM `sys_transform_plus` where t_name ='dhdc_module_hrp'  ;
INSERT INTO `sys_transform_plus` (`t_name`, `t_sql`, `bycase`,`active`, `version`) VALUES ('dhdc_module_hrp', 'CALL dhdc_module_hrp_cal;CALL dhdc_module_hrp_input;', 'pond', '1', '20170905');


-- ----------------------------
-- Procedure structure for dhdc_module_hrp
-- ----------------------------
DROP PROCEDURE IF EXISTS `dhdc_module_hrp`;
DELIMITER ;;
CREATE DEFINER=`root`@`localhost` PROCEDURE `dhdc_module_hrp`()
BEGIN 
CALL dhdc_module_hrp_cal;CALL dhdc_module_hrp_input;
 END
;;
DELIMITER ;

-- ----------------------------
-- Procedure structure for dhdc_module_hrp_cal
-- ----------------------------
DROP PROCEDURE IF EXISTS `dhdc_module_hrp_cal`;
DELIMITER ;;
CREATE DEFINER=`root`@`localhost` PROCEDURE `dhdc_module_hrp_cal`()
BEGIN 
SET	@b_year := (SELECT yearprocess FROM pk_byear LIMIT 1);
SET	@start_d := concat(@b_year-1,'1001');
SET @end_d := concat(@b_year,'0930');

DROP TABLE IF EXISTS dhdc_module_hrp;
CREATE TABLE dhdc_module_hrp (
  HOSPCODE varchar(5) NOT NULL,
	PID varchar(13) NOT NULL,
	PRENAME varchar(20) DEFAULT NULL,
  FNAME varchar(200) DEFAULT NULL,
  LNAME varchar(200) DEFAULT NULL,
	HID varchar(50) NOT NULL,
	HOUSE varchar(255) DEFAULT NULL,
	VILLAGE varchar(255) DEFAULT NULL,
	ADDR varchar(255) DEFAULT NULL,
	TYPEAREA char(1) NOT NULL,

	GRAVIDA varchar(2) NOT NULL,
	EDC date DEFAULT '0000-00-00',
	LMP date DEFAULT '0000-00-00',
	
	BDATE date DEFAULT '0000-00-00',
	BPLACE char(5) DEFAULT NULL,
	LABOR char(1) NOT NULL, 
	ANC12W char(1) NOT NULL, 
	ANC5 char(1) NOT NULL,
  PRIMARY KEY (HOSPCODE,PID,GRAVIDA),
	KEY (HOSPCODE,PID,GRAVIDA)

) ENGINE=MyISAM DEFAULT CHARSET=utf8;

INSERT IGNORE INTO dhdc_module_hrp (

SELECT 
p.check_hosp,a.pid,pn.prename,p.`NAME`,p.LNAME
,p.HID,p.addr,vl.villagename,concat('ต.' ,tb.tambonname,'  อ.',ap.ampurname,'  จ. ',cw.changwatname) as 'Home'
,p.check_typearea as 'TYPE'
,a.gravida,pt.EDC,pt.LMP,a.BDATE,a.bhosp
,if(a.BDATE is Not Null OR a.BDATE ="",'Y','N') as 'LABOR'
,if(a.g1_ga <=12,'Y','N') as 'ANC12W'
,if(a.g1_date is not null AND a.g2_date is not null AND a.g3_date is not null 
AND a.g4_date is not null AND a.g5_date is not null,'Y','N') as 'ANC5'

FROM t_person_anc a
#LEFT OUTER JOIN t_labor l ON l.HOSPCODE = a.hospcode AND l.PID = a.pid AND l.GRAVIDA = a.gravida
LEFT JOIN t_person_cid p ON p.CID= a.cid
LEFT OUTER JOIN cprename pn ON pn.id_prename = p.PRENAME
LEFT OUTER JOIN prenatal pt ON pt.HOSPCODE = a.hospcode AND pt.PID = a.pid AND pt.GRAVIDA = a.gravida
LEFT OUTER JOIN cchangwat cw ON cw.changwatcode = LEFT(p.check_vhid,2)
LEFT OUTER JOIN campur ap ON ap.ampurcodefull = LEFT(p.check_vhid,4)
LEFT OUTER JOIN ctambon tb ON tb.tamboncodefull = LEFT(p.check_vhid,6)
LEFT OUTER JOIN cvillage vl ON vl.villagecodefull = p.check_vhid

WHERE p.DISCHARGE = '9'
AND p.check_typearea in (1,3)
AND pt.EDC BETWEEN @start_d AND @end_d
GROUP BY p.check_hosp,a.pid,a.gravida
);
 END
;;
DELIMITER ;

-- ----------------------------
-- Procedure structure for dhdc_module_hrp_input
-- ----------------------------
DROP PROCEDURE IF EXISTS `dhdc_module_hrp_input`;
DELIMITER ;;
CREATE DEFINER=`root`@`localhost` PROCEDURE `dhdc_module_hrp_input`()
BEGIN

SET	@b_year := (SELECT yearprocess FROM pk_byear LIMIT 1);
SET	@start_d := concat(@b_year-1,'1001');
SET @end_d := concat(@b_year,'0930');


CREATE TABLE IF NOT EXISTS dhdc_module_hrp_input (
  HOSPCODE varchar(5) NOT NULL,
	PID varchar(13) NOT NULL,
	GRAVIDA varchar(2) NOT NULL,
	RISK1 varchar(200) DEFAULT NULL,
	RISK2 varchar(200) DEFAULT NULL,
	RISK3 varchar(200) DEFAULT NULL,
	RISK varchar(1) DEFAULT NULL,
	PLAN varchar(200) DEFAULT NULL,
  OSM varchar(200) DEFAULT NULL,
	INFO varchar(200) DEFAULT NULL,
	`STATUS` varchar(1) DEFAULT NULL,
  PRIMARY KEY (HOSPCODE,PID,GRAVIDA),
	KEY (HOSPCODE,PID,GRAVIDA)

) ENGINE=MyISAM DEFAULT CHARSET=utf8;

INSERT IGNORE INTO dhdc_module_hrp_input (
				SELECT HOSPCODE,PID,GRAVIDA,'','','','','','','','' FROM dhdc_module_hrp
);

UPDATE dhdc_module_hrp_input SET `STATUS` = "N" 
WHERE concat(HOSPCODE,PID,GRAVIDA) not in (SELECT concat(HOSPCODE,PID,GRAVIDA) FROM dhdc_module_hrp );

UPDATE dhdc_module_hrp_input SET `STATUS` = "Y" 
WHERE concat(HOSPCODE,PID,GRAVIDA) in (SELECT concat(HOSPCODE,PID,GRAVIDA) FROM dhdc_module_hrp );

END
;;
DELIMITER ;
